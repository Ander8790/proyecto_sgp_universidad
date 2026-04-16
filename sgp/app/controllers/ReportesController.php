<?php
/**
 * ReportesController - Gestión de Reportes y Exportaciones
 * 
 * Este controlador centraliza la generación de informes técnicos y administrativos,
 * manejando tanto formatos genéricos como plantillas institucionales del ISP.
 */

class ReportesController extends Controller
{
    private $db;
    private $pdf;

    public function __construct()
    {
        Session::start();
        if (!Session::get('user_id') || Session::get('role_id') > 2) {
            header('Location: ' . URLROOT . '/dashboard');
            exit;
        }

        $this->db = Database::getInstance();
        require_once '../app/lib/PdfGenerator.php';
        $this->pdf = new PdfGenerator();
    }

    /**
     * Vista Principal de Informes
     */
    public function index()
    {
        // Cargar datos para filtros dinámicos
        $this->db->query("SELECT id, nombre FROM departamentos WHERE activo = 1 ORDER BY nombre ASC");
        $departamentos = $this->db->resultSet();

        $this->db->query("SELECT id, nombre FROM instituciones ORDER BY nombre ASC");
        $instituciones = $this->db->resultSet();

        // MULTI-TENANT: Si el usuario es Tutor (role=2), solo ve sus propios pasantes asignados.
        $rolId  = (int)Session::get('role_id');
        $userId = (int)Session::get('user_id');

        if ($rolId === 2) {
            // Solo pasantes cuyo tutor_id coincide con el tutor autenticado
            $this->db->query("
                SELECT u.id, CONCAT(dp.nombres, ' ', dp.apellidos) as nombre 
                FROM usuarios u 
                JOIN datos_personales dp ON u.id = dp.usuario_id 
                JOIN datos_pasante dpa   ON dpa.usuario_id = u.id
                WHERE u.rol_id = 3 
                  AND u.estado = 'activo'
                  AND dpa.tutor_id = :tutor_id
                ORDER BY dp.nombres ASC
            ");
            $this->db->bind(':tutor_id', $userId);
        } else {
            // Admin — todos los pasantes activos
            $this->db->query("
                SELECT u.id, CONCAT(dp.nombres, ' ', dp.apellidos) as nombre 
                FROM usuarios u 
                JOIN datos_personales dp ON u.id = dp.usuario_id 
                WHERE u.rol_id = 3 AND u.estado = 'activo'
                ORDER BY dp.nombres ASC
            ");
        }
        $pasantes = $this->db->resultSet();

        $this->view('reportes/index', [
            'title' => 'Centro de Reportes',
            'departamentos' => $departamentos,
            'instituciones' => $instituciones,
            'pasantes' => $pasantes
        ]);
    }

    /**
     * GET /reportes/ejecutivo — Resumen Ejecutivo directo (histórico, sin filtros)
     */
    public function ejecutivo(): void
    {
        $this->generarResumenEjecutivo(['tipo_periodo' => 'historico']);
    }

    /**
     * GET /reportes/pdfKardex/{id} — Kardex individual de asistencias de un pasante
     */
    public function pdfKardex($pasanteId = null): void
    {
        $pasanteId = (int)($pasanteId ?? $_GET['id'] ?? 0);

        if (!$pasanteId) {
            $this->redirect('/reportes');
            return;
        }

        // RBAC: tutor solo puede exportar sus propios pasantes
        $rolId    = (int)Session::get('role_id');
        $myUserId = (int)Session::get('user_id');
        if ($rolId === 2) {
            $this->db->query("SELECT tutor_id FROM datos_pasante WHERE usuario_id = :pid LIMIT 1");
            $this->db->bind(':pid', $pasanteId);
            $chk = $this->db->single();
            if (!$chk || (int)($chk->tutor_id ?? 0) !== $myUserId) {
                // [FIX-M4] Registrar intento de acceso no autorizado en bitácora
                AuditModel::log(
                    'ACCESO_DENEGADO_REPORTE',
                    'datos_pasante',
                    $pasanteId,
                    [
                        'tutor_solicitante' => $myUserId,
                        'tutor_propietario' => $chk->tutor_id ?? null,
                        'ip'               => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
                    ]
                );
                http_response_code(403);
                echo 'Acceso denegado.';
                exit;
            }
        }

        // Datos del pasante
        $this->db->query("
            SELECT u.id, u.cedula, dp.nombres, dp.apellidos,
                   d.nombre AS departamento_nombre,
                   COALESCE(inst.nombre, '') AS institucion_nombre
            FROM   usuarios u
            LEFT JOIN datos_personales    dp   ON dp.usuario_id   = u.id
            LEFT JOIN datos_pasante       dpa  ON dpa.usuario_id  = u.id
            LEFT JOIN departamentos       d    ON d.id = COALESCE(dpa.departamento_asignado_id, u.departamento_id)
            LEFT JOIN instituciones       inst ON inst.id = COALESCE(dpa.institucion_id, CAST(dpa.institucion_procedencia AS UNSIGNED))
            WHERE u.id = :pid AND u.rol_id = 3
            GROUP BY u.id, dp.nombres, dp.apellidos, u.cedula, d.nombre
            LIMIT 1
        ");
        $this->db->bind(':pid', $pasanteId);
        $pasante = $this->db->single();

        if (!$pasante) {
            $this->redirect('/reportes');
            return;
        }

        // Historial completo de asistencias
        $this->db->query("
            SELECT fecha, hora_entrada, hora_salida, estado,
                   estado AS observacion
            FROM asistencias
            WHERE pasante_id = :pid
            ORDER BY fecha DESC
        ");
        $this->db->bind(':pid', $pasanteId);
        $asistencias = $this->db->resultSet();

        $subtitulo_pdf = htmlspecialchars(
            ($pasante->apellidos ?? '') . ', ' . ($pasante->nombres ?? '')
            . ' — C.I. ' . ($pasante->cedula ?? '—')
        );

        ob_start();
        include '../app/views/reportes/pdf_asistencia_individual.php';
        $html = ob_get_clean();

        $this->pdf->renderDomPdf($html, 'Kardex_' . ($pasante->cedula ?? $pasanteId), false);
    }

    /**
     * Procesar Solicitud de Exportación
     */
    public function exportar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/reportes');
            return;
        }

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Token inválido. Recarga la página e intenta de nuevo.']);
            exit;
        }

        $modulo  = $_POST['modulo'] ?? '';
        $tipo    = $_POST['tipo'] ?? 'pdf'; // pdf o excel
        $filtros = $_POST;

        // ── RBAC: AISLAMIENTO POR TUTOR ────────────────────────────────────────
        // Si es Tutor (role=2) y el módulo exporta datos de un pasante específico,
        // se verifica que ese pasante pertenezca al tutor autenticado.
        // Esto previene que un Tutor altere el DOM o la URL para exportar datos ajenos.
        $rolId    = (int)Session::get('role_id');
        $myUserId = (int)Session::get('user_id');

        if ($rolId === 2) {
            $pasanteIdPost = isset($_POST['pasante_id']) ? (int)$_POST['pasante_id'] : null;

            // Si el módulo no es de pasantes individuales, bloquear módulos sensibles
            $modulosAdminOnly = ['ejecutivo', 'usuarios'];
            if (in_array($modulo, $modulosAdminOnly)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Módulo restringido para su rol.']);
                exit;
            }

            // Si viene un pasante_id explícito, verificar propiedad
            if ($pasanteIdPost > 0) {
                $this->db->query("
                    SELECT tutor_id FROM datos_pasante WHERE usuario_id = :pid LIMIT 1
                ");
                $this->db->bind(':pid', $pasanteIdPost);
                $chk = $this->db->single();
                if (!$chk || (int)($chk->tutor_id ?? 0) !== $myUserId) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Acceso denegado: este pasante no está asignado a su tutela.',
                    ]);
                    exit;
                }
            }
        }
        // ── Fin RBAC ────────────────────────────────────────────────────────────

        // Lógica de Enrutamiento
        switch ($modulo) {
            case 'ejecutivo':
                $this->generarResumenEjecutivo($filtros);
                break;

            case 'usuarios':
                $this->generarDirectorioUsuarios($filtros);
                break;

            case 'pasantes':
                $this->generarRegistroPasantes($filtros);
                break;

            case 'evaluaciones':
                if (isset($filtros['formato_isp'])) {
                    $this->generarEvaluacionISP($filtros);
                } else {
                    $this->generarEvaluacionISP($filtros); // siempre usa planilla ISP
                }
                break;

            case 'asistencias':
                if (isset($filtros['formato_trimestral'])) {
                    $this->generarAsistenciaTrimestralISP($filtros);
                } else {
                    $this->generarReporteAsistencias($filtros);
                }
                break;

            case 'bitacora':
                $this->generarBitacora($filtros);
                break;

            case 'asignaciones':
                $this->generarAsignaciones($filtros);
                break;

            case 'diaria':
                $this->generarFichaDiaria($filtros);
                break;

            case 'constancias':
                $this->generarConstancias($filtros);
                break;

            default:
                $this->generarReporteGenerico(ucfirst($modulo), $filtros);
                break;
        }
    }

    /**
     * Reporte Generico usando wrapHtml
     */
    private function generarReporteGenerico($titulo, $filtros)
    {
        // Esta es una implementación simplificada para el centro de informes
        // En un caso real, cada módulo tendría su propia lógica de consulta
        $html = "<h1>Reporte de $titulo</h1>";
        $html .= "<p>Generado el: " . date('d/m/Y H:i') . "</p>";
        $html .= "<table border='1' width='100%' style='border-collapse: collapse;'>";
        $html .= "<thead><tr><th>Filtros Aplicados</th></tr></thead>";
        $html .= "<tbody>";
        foreach ($filtros as $key => $val) {
            if ($key != 'modulo' && $key != 'tipo') {
                $html .= "<tr><td><strong>$key:</strong> $val</td></tr>";
            }
        }
        $html .= "</tbody></table>";

        $wrappedHtml = $this->pdf->wrapHtml($html, "Reporte_SGP_$titulo");
        $this->pdf->renderDomPdf($wrappedHtml, "Reporte_$titulo" . "_" . date('Ymd'), false);
    }

    /**
     * Hito 3: Plantilla Institucional de Evaluación ISP
     */
    private function generarEvaluacionISP($filtros)
    {
        $pasante_id = $filtros['pasante_id'] ?? null;
        if (!$pasante_id) {
            $this->renderErrorPage('Datos incompletos', 'Debe seleccionar un pasante para generar la planilla.');
        }

        // Obtener datos de la evaluación — LEFT JOINs para no fallar si el pasante
        // no tiene departamento_id en usuarios o tutor sin datos_personales.
        $this->db->query("
            SELECT e.*,
                   dp.nombres, dp.apellidos, u.cedula,
                   u.correo,
                   COALESCE(d.nombre, 'N/D') AS departamento,
                   t.nombres AS tutor_nombres, t.apellidos AS tutor_apellidos
            FROM evaluaciones e
            JOIN usuarios          u   ON u.id = e.pasante_id
            JOIN datos_personales  dp  ON dp.usuario_id = u.id
            LEFT JOIN datos_pasante dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos d   ON d.id = COALESCE(dpa.departamento_asignado_id, u.departamento_id)
            LEFT JOIN datos_personales t ON t.usuario_id = e.tutor_id
            WHERE e.pasante_id = :pid
            ORDER BY e.created_at DESC LIMIT 1
        ");
        $this->db->bind(':pid', $pasante_id);
        $evaluacion = $this->db->single();

        if (!$evaluacion) {
            $this->renderErrorPage(
                'Sin evaluación registrada',
                'El pasante seleccionado aún no tiene una evaluación registrada en el sistema. Debe completar la evaluación antes de imprimir la planilla.'
            );
        }

        // Cargar vista como variable (buffer)
        ob_start();
        $eval = $evaluacion;
        include '../app/views/reportes/pdf_evaluacion_isp.php';
        $html = ob_get_clean();

        // Renderizar SIN wrapHtml y en modo INLINE (false)
        $this->pdf->renderDomPdf($html, "Evaluacion_ISP_" . $evaluacion->cedula, false);
    }

    private function renderErrorPage(string $titulo, string $mensaje): never
    {
        http_response_code(400); // Changed from 500 to prevent global server error handler takeover
        echo '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
body { font-family: Helvetica, Arial, sans-serif; font-size: 13px;
       color: #333; display: flex; align-items: center;
       justify-content: center; min-height: 100vh; margin: 0; }
.box { text-align: center; max-width: 420px; padding: 40px; }
.box h2 { color: #162660; font-size: 16px; margin-bottom: 12px; }
.box p  { color: #555; line-height: 1.6; margin-bottom: 24px; }
.btn { display: inline-block; padding: 8px 20px; background: #162660;
       color: #fff; border-radius: 6px; text-decoration: none;
       font-size: 12px; cursor: pointer; border: none; }
</style>
</head>
<body>
<div class="box">
  <h2>' . htmlspecialchars($titulo) . '</h2>
  <p>'  . htmlspecialchars($mensaje) . '</p>
  <button class="btn" onclick="window.close()">Cerrar</button>
</div>
</body>
</html>';
        exit;
    }

    /**
     * Hito 4: Planilla Trimestral de Asistencias ISP
     */
    private function generarAsistenciaTrimestralISP(array $filtros): void
    {
        $pasante_id = $filtros['pasante_id'] ?? null;
        $trimestre  = max(1, min(3, (int)($filtros['trimestre'] ?? 1)));

        if (!$pasante_id) {
            $this->renderErrorPage(
                'Datos incompletos',
                'Debe seleccionar un pasante para generar la planilla.'
            );
        }

        try {

            // ── 1. Obtener fecha_inicio_pasantia para calcular rango real ──
            $this->db->query("
                SELECT dpa.fecha_inicio_pasantia,
                       dpa.fecha_fin_estimada
                FROM datos_pasante dpa
                WHERE dpa.usuario_id = :pid
            ");
            $this->db->bind(':pid', $pasante_id);
            $lapso = $this->db->single();

            if (!$lapso) {
                $this->renderErrorPage(
                    'Pasante no encontrado',
                    'No se encontraron datos de pasantía para este usuario.'
                );
            }

            // ── 2. Calcular rango del trimestre relativo al lapso del pasante ──
            // Trimestre 1 = semanas  1-14 desde fecha_inicio_pasantia
            // Trimestre 2 = semanas 15-28 (inicio + 98 días)
            // Trimestre 3 = semanas 29-42 (inicio + 196 días)
            $inicio_lapso = new DateTime($lapso->fecha_inicio_pasantia);
            $dias_offset  = ($trimestre - 1) * 98; // 14 semanas × 7 días
            $inicio_trim  = (clone $inicio_lapso)->modify("+{$dias_offset} days");
            $fin_trim     = (clone $inicio_trim)->modify('+97 days');

            $fecha_inicio = $inicio_trim->format('Y-m-d');
            $fecha_fin    = $fin_trim->format('Y-m-d');

            // ── 3. Obtener datos completos del pasante con tutor ──
            $this->db->query("
                SELECT
                    dp.nombres,
                    dp.apellidos,
                    u.cedula,
                    d.nombre                                AS departamento,
                    dpa.fecha_inicio_pasantia,
                    dpa.fecha_fin_estimada,
                    CONCAT(dpt.nombres, ' ', dpt.apellidos) AS tutor_nombre,
                    dpt.cargo                               AS tutor_cargo
                FROM usuarios u
                JOIN datos_personales dp       ON dp.usuario_id        = u.id
                JOIN datos_pasante dpa         ON dpa.usuario_id       = u.id
                LEFT JOIN departamentos d      ON d.id = dpa.departamento_asignado_id
                LEFT JOIN usuarios ut          ON ut.id  = dpa.tutor_id
                LEFT JOIN datos_personales dpt ON dpt.usuario_id = dpa.tutor_id
                WHERE u.id = :pid AND u.rol_id = 3
            ");
            $this->db->bind(':pid', $pasante_id);
            $pasante = $this->db->single();

            if (!$pasante) {
                $this->renderErrorPage(
                    'Pasante no encontrado',
                    'No se encontraron datos para el pasante seleccionado.'
                );
            }

            // ── 4. Obtener asistencias del trimestre (query no cambia) ──
            $this->db->query("
                SELECT fecha, hora_entrada, hora_salida, estado
                FROM asistencias
                WHERE pasante_id = :pid
                  AND fecha BETWEEN :inicio AND :fin
                ORDER BY fecha ASC
            ");
            $this->db->bind(':pid',    $pasante_id);
            $this->db->bind(':inicio', $fecha_inicio);
            $this->db->bind(':fin',    $fecha_fin);
            $asistencias_raw = $this->db->resultSet();

            // ── 5. Indexar asistencias por [fecha Y-m-d][día semana 0-6] ──
            // 0=Dom, 1=Lun, 2=Mar, 3=Mié, 4=Jue, 5=Vie, 6=Sáb
            $asistencias_procesadas = [];
            foreach ($asistencias_raw as $as) {
                $fkey = $as->fecha;
                $dow  = (int) date('w', strtotime($as->fecha));
                $asistencias_procesadas[$fkey][$dow] = $as;
            }

            // ── 6. Construir array de 14 semanas ──
            // Cada entrada = fecha Y-m-d del primer día de esa semana
            $semanas = [];
            for ($s = 0; $s < 14; $s++) {
                $primer_dia = (clone $inicio_trim)->modify("+{$s} weeks");
                $semanas[$s + 1] = $primer_dia->format('Y-m-d');
            }

            // ── 7. Renderizar vista ──
            ob_start();
            include '../app/views/reportes/pdf_asistencia_trimestral.php';
            $html = ob_get_clean();

            $this->pdf->renderDomPdf(
                $html,
                'Asistencia_Trimestral_' . $pasante->cedula,
                false
            );

        } catch (\RuntimeException $e) {
            error_log('[SGP Planilla Trimestral] ' . $e->getMessage());
            $this->renderErrorPage(
                'Error al generar la planilla',
                'No se pudo acceder a los datos de asistencia. Intente nuevamente.'
            );
        } catch (\Exception $e) {
            error_log('[SGP Planilla Trimestral] Excepción: ' . $e->getMessage());
            $this->renderErrorPage(
                'Error inesperado',
                'Ocurrió un error al procesar la planilla trimestral.'
            );
        }
    }

    /**
     * Pre-flight AJAX — Verifica si hay datos antes de generar el PDF.
     * Recibe: modulo, fecha_inicio (opcional), fecha_fin (opcional)
     * Devuelve: JSON { success: bool, count: int, message: string }
     */
    public function validarDatos()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'count' => 0, 'message' => 'Método no permitido.']);
            exit;
        }

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'count' => 0, 'message' => 'Token inválido. Recarga la página e intenta de nuevo.']);
            exit;
        }

        $modulo       = $_POST['modulo']       ?? '';
        $fecha_inicio = !empty($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : null;
        $fecha_fin    = !empty($_POST['fecha_fin'])    ? trim($_POST['fecha_fin'])    : $fecha_inicio;

        $sqlFiltroFecha = $fecha_inicio
            ? 'AND dpa.fecha_inicio_pasantia BETWEEN :fi AND :ff'
            : '';

        $count = 0;

        switch ($modulo) {
            case 'ejecutivo':
                $this->db->query("
                    SELECT COUNT(*) AS total
                    FROM usuarios u
                    JOIN datos_pasante dpa ON dpa.usuario_id = u.id
                    WHERE u.rol_id = 3
                    {$sqlFiltroFecha}
                ");
                if ($fecha_inicio) {
                    $this->db->bind(':fi', $fecha_inicio);
                    $this->db->bind(':ff', $fecha_fin);
                }
                $count = (int)($this->db->single()->total ?? 0);
                break;

            case 'pasantes':
                $pInstitucion = $_POST['institucion_id'] ?? 'todas';
                $pEstado      = $_POST['estado']         ?? 'todos';

                $pWhere = ['u.rol_id = 3'];
                $pBinds = [];

                if ($fecha_inicio) {
                    $pWhere[] = 'dpa.fecha_inicio_pasantia BETWEEN :fi AND :ff';
                    $pBinds[':fi'] = $fecha_inicio;
                    $pBinds[':ff'] = $fecha_fin;
                }
                if ($pInstitucion !== 'todas') {
                    $pWhere[] = 'dpa.institucion_procedencia = :inst';
                    $pBinds[':inst'] = $pInstitucion;
                }
                if ($pEstado !== 'todos') {
                    $pWhere[] = 'dpa.estado_pasantia = :estado';
                    $pBinds[':estado'] = ucfirst($pEstado);
                }

                $this->db->query("
                    SELECT COUNT(*) AS total
                    FROM usuarios u
                    JOIN datos_pasante dpa ON dpa.usuario_id = u.id
                    WHERE " . implode(' AND ', $pWhere)
                );
                foreach ($pBinds as $p => $v) {
                    $this->db->bind($p, $v);
                }
                $count = (int)($this->db->single()->total ?? 0);
                break;

            case 'asistencias':
                $tipo_reporte = $_POST['tipo_reporte'] ?? 'diario';

                if ($tipo_reporte === 'planilla_isp') {
                    $pasante_id = (int)($_POST['pasante_id'] ?? 0);
                    if (!$pasante_id) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Seleccione un pasante'
                        ], JSON_HEX_TAG | JSON_HEX_QUOT);
                        exit;
                    }

                    $this->db->query("
                        SELECT fecha_inicio_pasantia
                        FROM datos_pasante
                        WHERE usuario_id = :pid
                    ");
                    $this->db->bind(':pid', $pasante_id);
                    $lapso = $this->db->single();

                    if (!$lapso || !$lapso->fecha_inicio_pasantia) {
                        echo json_encode([
                            'success'                => false,
                            'message'                => 'Pasante sin fecha de inicio registrada',
                            'trimestres_disponibles' => []
                        ], JSON_HEX_TAG | JSON_HEX_QUOT);
                        exit;
                    }

                    $inicio      = new DateTime($lapso->fecha_inicio_pasantia);
                    $hoy         = new DateTime();
                    $disponibles = [];

                    for ($t = 1; $t <= 3; $t++) {
                        $offset   = ($t - 1) * 98;
                        $ini_trim = (clone $inicio)->modify("+{$offset} days");
                        $fin_trim = (clone $ini_trim)->modify('+97 days');

                        if ($ini_trim <= $hoy) {
                            $this->db->query("
                                SELECT COUNT(*) AS total
                                FROM asistencias
                                WHERE pasante_id = :pid
                                  AND fecha BETWEEN :ini AND :fin
                            ");
                            $this->db->bind(':pid', $pasante_id);
                            $this->db->bind(':ini', $ini_trim->format('Y-m-d'));
                            $this->db->bind(':fin', $fin_trim->format('Y-m-d'));
                            $cnt = $this->db->single();

                            if ($cnt && $cnt->total > 0) {
                                $disponibles[] = $t;
                            }
                        }
                    }

                    echo json_encode([
                        'success'                => true,
                        'trimestres_disponibles' => $disponibles,
                        'message'                => count($disponibles) > 0
                            ? count($disponibles) . ' trimestre(s) disponibles'
                            : 'Sin asistencias registradas'
                    ], JSON_HEX_TAG | JSON_HEX_QUOT);
                    exit;
                }

                // Flujo grupal — contar registros con filtros
                $aDepto = $_POST['departamento'] ?? 'todos';
                $aWhere = ['1=1'];
                $aBinds = [];

                if ($fecha_inicio) {
                    $aWhere[] = 'a.fecha BETWEEN :fi AND :ff';
                    $aBinds[':fi'] = $fecha_inicio;
                    $aBinds[':ff'] = $fecha_fin;
                }
                if ($aDepto !== 'todos') {
                    $aWhere[] = 'u.departamento_id = :depto';
                    $aBinds[':depto'] = (int)$aDepto;
                }

                $this->db->query("
                    SELECT COUNT(*) AS total
                    FROM asistencias a
                    JOIN usuarios u ON u.id = a.pasante_id
                    WHERE " . implode(' AND ', $aWhere)
                );
                foreach ($aBinds as $p => $v) {
                    $this->db->bind($p, $v);
                }
                $count = (int)($this->db->single()->total ?? 0);
                break;

            case 'usuarios':
                $uRol    = $_POST['rol']            ?? 'todos';
                $uDepto  = $_POST['departamento']   ?? 'todos';
                $uEstado = $_POST['estado_usuario'] ?? '';

                $uWhere = ['u.rol_id <= 2'];
                $uBinds = [];

                if ($uRol !== 'todos') {
                    $uWhere[] = 'r.nombre = :rol';
                    $uBinds[':rol'] = $uRol;
                }
                if ($uDepto !== 'todos') {
                    $uWhere[] = 'u.departamento_id = :depto';
                    $uBinds[':depto'] = (int)$uDepto;
                }
                if ($uEstado !== '') {
                    $uWhere[] = 'u.estado = :estado';
                    $uBinds[':estado'] = $uEstado;
                }

                $this->db->query("
                    SELECT COUNT(*) AS total
                    FROM usuarios u
                    JOIN roles r ON r.id = u.rol_id
                    WHERE " . implode(' AND ', $uWhere)
                );
                foreach ($uBinds as $p => $v) {
                    $this->db->bind($p, $v);
                }
                $count = (int)($this->db->single()->total ?? 0);
                break;

            case 'asignaciones':
                $this->db->query("SELECT COUNT(*) AS total FROM asignaciones");
                $count = (int)($this->db->single()->total ?? 0);
                break;

            case 'evaluaciones':
                // Verificar que el pasante tenga al menos una evaluación registrada
                $ePid = (int)($_POST['pasante_id'] ?? 0);
                if (!$ePid) {
                    echo json_encode(['success' => false, 'message' => 'Seleccione un pasante.']);
                    exit;
                }
                $this->db->query("SELECT COUNT(*) AS total FROM evaluaciones WHERE pasante_id = :pid");
                $this->db->bind(':pid', $ePid);
                $eCount = (int)($this->db->single()->total ?? 0);
                if ($eCount === 0) {
                    echo json_encode(['success' => false, 'message' => 'Este pasante aún no tiene evaluaciones registradas.']);
                    exit;
                }
                echo json_encode(['success' => true, 'count' => $eCount, 'message' => 'Evaluación disponible.']);
                exit;

            case 'constancias':
                // Verificar que el pasante exista y tenga datos completos
                $cPid  = (int)($_POST['pasante_id'] ?? 0);
                $cTipo = $_POST['tipo_constancia'] ?? 'servicio';
                if (!$cPid) {
                    echo json_encode(['success' => false, 'message' => 'Seleccione un pasante.']);
                    exit;
                }
                $this->db->query("
                    SELECT dpa.estado_pasantia, dpa.horas_acumuladas, dpa.horas_meta
                    FROM datos_pasante dpa WHERE dpa.usuario_id = :pid LIMIT 1
                ");
                $this->db->bind(':pid', $cPid);
                $cRow = $this->db->single();
                if (!$cRow) {
                    echo json_encode(['success' => false, 'message' => 'Pasante no encontrado.']);
                    exit;
                }
                if ($cTipo === 'culminacion' && $cRow->estado_pasantia !== 'Finalizado') {
                    echo json_encode(['success' => false, 'message' => 'La carta de culminación solo se puede emitir para pasantes con estado “Finalizado”.']);
                    exit;
                }
                echo json_encode(['success' => true, 'count' => 1, 'message' => 'OK']);
                exit;

            case 'bitacora':
                $bWhere = ['1=1'];
                $bBinds = [];
                if ($fecha_inicio) {
                    $bWhere[] = 'b.fecha BETWEEN :fi AND :ff';
                    $bBinds[':fi'] = $fecha_inicio;
                    $bBinds[':ff'] = $fecha_fin;
                }
                $this->db->query("SELECT COUNT(*) AS total FROM bitacora b WHERE " . implode(' AND ', $bWhere));
                foreach ($bBinds as $p => $v) { $this->db->bind($p, $v); }
                $count = (int)($this->db->single()->total ?? 0);
                break;

            case 'diaria':
                // Siempre hay datos (reporte del día actual)
                echo json_encode(['success' => true, 'count' => 1, 'message' => 'OK']);
                exit;

            default:
                // Siempre pasa
                echo json_encode(['success' => true, 'count' => 1, 'message' => 'OK']);
                exit;
        }

        if ($count > 0) {
            echo json_encode(['success' => true,  'count' => $count, 'message' => "Se encontraron {$count} registros."]);
        } else {
            echo json_encode(['success' => false, 'count' => 0,      'message' => 'No hay registros para el período seleccionado.']);
        }
        exit;
    }

    /**
     * Hito 6: Resumen Ejecutivo Administrativo
     *
     * Variables que pasa a la vista:
     *   $stats        — Array: total, activo, finalizado, retirado, pendiente
     *   $departamentos — Array de objetos: top 3 depto con más pasantes
     *   $instituciones — Array de objetos: pasantes agrupados por institución de procedencia
     *   $fecha_inicio / $fecha_fin — Strings para mostrar el período en el PDF
     */
    private function generarResumenEjecutivo($filtros)
    {
        $fecha_inicio = !empty($filtros['fecha_inicio']) ? trim($filtros['fecha_inicio']) : null;
        $fecha_fin    = !empty($filtros['fecha_fin'])    ? trim($filtros['fecha_fin'])    : $fecha_inicio;
        $tipo_periodo = $filtros['tipo_periodo'] ?? 'historico';

        // ── Subtítulo semántico para el PDF ──────────────────────────────────
        $meses_es = ['', 'Enero','Febrero','Marzo','Abril','Mayo','Junio',
                     'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

        $subtitulo_pdf = match ($tipo_periodo) {
            'hoy'   => 'Reporte del día: ' . date('d/m/Y'),
            'mes'   => 'Mes Actual: ' . $meses_es[(int)date('n')] . ' ' . date('Y'),
            'rango' => 'Rango Personalizado: '
                       . ($fecha_inicio ? date('d/m/Y', strtotime($fecha_inicio)) : '—')
                       . ' al '
                       . ($fecha_fin    ? date('d/m/Y', strtotime($fecha_fin))    : '—'),
            default => 'Histórico Total — Todos los períodos',
        };

        // Cláusula de filtro por fecha reutilizable (se inyecta en el SQL como literal
        // porque es una cadena fija — los valores reales van por :fi y :ff con bind)
        $sqlFiltroFecha = $fecha_inicio
            ? 'AND dpa.fecha_inicio_pasantia BETWEEN :fi AND :ff'
            : '';

        // ── 1. Conteo de pasantes por estado ─────────────────────────────────
        $this->db->query("
            SELECT
                COUNT(*)                                                        AS total,
                SUM(CASE WHEN dpa.estado_pasantia = 'Activo'     THEN 1 ELSE 0 END) AS activo,
                SUM(CASE WHEN dpa.estado_pasantia = 'Finalizado' THEN 1 ELSE 0 END) AS finalizado,
                SUM(CASE WHEN dpa.estado_pasantia = 'Retirado'   THEN 1 ELSE 0 END) AS retirado,
                SUM(CASE WHEN dpa.estado_pasantia = 'Pendiente'  THEN 1 ELSE 0 END) AS pendiente
            FROM usuarios u
            JOIN datos_pasante dpa ON dpa.usuario_id = u.id
            WHERE u.rol_id = 3
            {$sqlFiltroFecha}
        ");
        if ($fecha_inicio) {
            $this->db->bind(':fi', $fecha_inicio);
            $this->db->bind(':ff', $fecha_fin);
        }
        $row   = $this->db->single();
        $stats = [
            'total'      => (int)($row->total      ?? 0),
            'activo'     => (int)($row->activo      ?? 0),
            'finalizado' => (int)($row->finalizado  ?? 0),
            'retirado'   => (int)($row->retirado    ?? 0),
            'pendiente'  => (int)($row->pendiente   ?? 0),
        ];

        // ── 2. Top 3 departamentos con más pasantes ───────────────────────────
        $this->db->query("
            SELECT d.nombre, COUNT(dpa.usuario_id) AS total
            FROM departamentos d
            JOIN datos_pasante dpa ON dpa.departamento_asignado_id = d.id
            JOIN usuarios      u   ON u.id = dpa.usuario_id
            WHERE u.rol_id = 3
              AND d.activo = 1
            {$sqlFiltroFecha}
            GROUP BY d.id, d.nombre
            ORDER BY total DESC
            LIMIT 3
        ");
        if ($fecha_inicio) {
            $this->db->bind(':fi', $fecha_inicio);
            $this->db->bind(':ff', $fecha_fin);
        }
        $departamentos = $this->db->resultSet();

        // ── 3. Pasantes por institución de procedencia ────────────────────────
        // El campo VARCHAR(150) puede contener texto libre o un ID numérico heredado.
        // CASE: si el valor es numérico y existe en la tabla maestra → usa el nombre oficial;
        //       si es texto libre → úsalo tal cual; vacío/nulo → 'Sin especificar'.
        // GROUP BY sobre el nombre resuelto para evitar filas duplicadas.
        $this->db->query("
            SELECT
                COALESCE(
                    NULLIF(TRIM(
                        CASE
                            WHEN inst.id IS NOT NULL THEN inst.nombre
                            ELSE dpa.institucion_procedencia
                        END
                    ), ''),
                    'Sin especificar'
                ) AS nombre,
                COUNT(*) AS total
            FROM datos_pasante dpa
            JOIN  usuarios     u    ON u.id   = dpa.usuario_id
            LEFT JOIN instituciones inst
                   ON inst.id = COALESCE(dpa.institucion_id, CAST(dpa.institucion_procedencia AS UNSIGNED))
            WHERE u.rol_id = 3
            {$sqlFiltroFecha}
            GROUP BY nombre
            ORDER BY total DESC
            LIMIT 10
        ");
        if ($fecha_inicio) {
            $this->db->bind(':fi', $fecha_inicio);
            $this->db->bind(':ff', $fecha_fin);
        }
        $instituciones = $this->db->resultSet();

        // ── Renderizar vista como buffer y pasar a DomPDF ─────────────────────
        ob_start();
        include '../app/views/reportes/pdf_ejecutivo.php';
        $html = ob_get_clean();

        $this->pdf->renderDomPdf($html, 'Resumen_Ejecutivo_' . date('Ymd'), false);
    }

    /**
     * Directorio de Usuarios del Sistema (PDF institucional ultra-ligero)
     *
     * Variables que pasa a la vista:
     *   $usuarios      — Array de objetos con datos del personal
     *   $subtitulo_pdf — String descriptivo del filtro aplicado
     */
    private function generarDirectorioUsuarios($filtros)
    {
        $rol           = $filtros['rol']            ?? 'todos';
        $departamento  = $filtros['departamento']   ?? 'todos';
        $estado        = $filtros['estado_usuario'] ?? '';

        // ── Subtítulo semántico ───────────────────────────────────────────────
        $partes = [];
        if ($rol          !== 'todos') $partes[] = 'Rol: ' . htmlspecialchars($rol);
        if ($departamento !== 'todos') $partes[] = 'Depto. ID: ' . (int)$departamento;
        if ($estado       !== '')      $partes[] = 'Estado: ' . htmlspecialchars(ucfirst($estado));
        $subtitulo_pdf = $partes ? implode(' | ', $partes) : 'Directorio Completo — Todo el Personal';

        // ── Cláusulas WHERE dinámicas (sin interpolación de valores) ─────────
        $where  = ['u.rol_id <= 2'];   // solo admin y tutores
        $binds  = [];

        if ($rol !== 'todos') {
            $where[] = 'r.nombre = :rol';
            $binds[':rol'] = $rol;
        }
        if ($departamento !== 'todos') {
            $where[] = 'u.departamento_id = :depto';
            $binds[':depto'] = (int)$departamento;
        }
        if ($estado !== '') {
            $where[] = 'u.estado = :estado';
            $binds[':estado'] = $estado;
        }

        $sqlWhere = 'WHERE ' . implode(' AND ', $where);

        $this->db->query("
            SELECT
                u.id,
                u.cedula,
                dp.nombres,
                dp.apellidos,
                u.correo,
                r.nombre          AS rol,
                d.nombre          AS departamento_nombre,
                u.estado
            FROM usuarios u
            JOIN datos_personales dp ON dp.usuario_id = u.id
            JOIN roles            r  ON r.id          = u.rol_id
            LEFT JOIN departamentos d ON d.id          = u.departamento_id
            {$sqlWhere}
            ORDER BY dp.apellidos ASC, dp.nombres ASC
        ");
        foreach ($binds as $param => $value) {
            $this->db->bind($param, $value);
        }
        $usuarios = $this->db->resultSet();

        // ── Render ────────────────────────────────────────────────────────────
        $tipo = $filtros['tipo'] ?? 'pdf';

        if ($tipo === 'excel') {
            $filename = 'Directorio_Usuarios_' . date('Ymd') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-store, no-cache');

            $out = fopen('php://output', 'w');
            // BOM para compatibilidad UTF-8 en Excel
            fputs($out, "\xEF\xBB\xBF");

            fputcsv($out, ['N°', 'Cédula', 'Nombre y Apellido', 'Correo / Usuario', 'Rol', 'Departamento', 'Estado'], ';');

            foreach ($usuarios as $i => $u) {
                $nombre    = trim(($u->apellidos ?? '') . ', ' . ($u->nombres ?? ''));
                $estadoTxt = ($u->estado === 'activo' || $u->estado == 1) ? 'Activo' : 'Inactivo';
                fputcsv($out, [
                    $i + 1,
                    $u->cedula            ?? '—',
                    $nombre,
                    $u->correo            ?? '—',
                    $u->rol               ?? '—',
                    $u->departamento_nombre ?? 'Sin asignar',
                    $estadoTxt,
                ], ';');
            }
            fclose($out);
            exit;
        }

        ob_start();
        include '../app/views/reportes/pdf_directorio_usuarios.php';
        $html = ob_get_clean();

        $this->pdf->renderDomPdf($html, 'Directorio_Usuarios_' . date('Ymd'), false);
    }

    /**
     * Registro General de Pasantes (PDF/CSV institucional)
     *
     * Variables que pasa a la vista:
     *   $pasantes      — Array de objetos con datos del pasante
     *   $subtitulo_pdf — String descriptivo del filtro aplicado
     */
    private function generarRegistroPasantes($filtros)
    {
        $institucion_id = $filtros['institucion_id'] ?? 'todas';
        $estado         = $filtros['estado']         ?? 'todos';
        $fecha_inicio   = !empty($filtros['fecha_inicio']) ? trim($filtros['fecha_inicio']) : null;
        $fecha_fin      = !empty($filtros['fecha_fin'])    ? trim($filtros['fecha_fin'])    : $fecha_inicio;

        // ── Subtítulo semántico ───────────────────────────────────────────────
        $partes = [];
        if ($institucion_id !== 'todas') $partes[] = 'Institución ID: ' . (int)$institucion_id;
        if ($estado         !== 'todos') $partes[] = 'Estado: ' . ucfirst($estado);
        if ($fecha_inicio)               $partes[] = date('d/m/Y', strtotime($fecha_inicio))
                                                    . ' al ' . date('d/m/Y', strtotime($fecha_fin));
        $subtitulo_pdf = $partes ? implode(' | ', $partes) : 'Registro Completo — Todos los Períodos';

        // ── WHERE dinámico ────────────────────────────────────────────────────
        $where = ['u.rol_id = 3'];
        $binds = [];

        if ($fecha_inicio) {
            $where[] = 'dpa.fecha_inicio_pasantia BETWEEN :fi AND :ff';
            $binds[':fi'] = $fecha_inicio;
            $binds[':ff'] = $fecha_fin;
        }
        if ($institucion_id !== 'todas') {
            $where[] = 'dpa.institucion_procedencia = :inst';
            $binds[':inst'] = (string)$institucion_id;
        }
        if ($estado !== 'todos') {
            $where[] = 'dpa.estado_pasantia = :estado';
            $binds[':estado'] = ucfirst($estado);
        }

        $sqlWhere = 'WHERE ' . implode(' AND ', $where);

        $this->db->query("
            SELECT
                u.cedula,
                dp.nombres,
                dp.apellidos,
                COALESCE(
                    NULLIF(TRIM(
                        CASE
                            WHEN inst.id IS NOT NULL THEN inst.nombre
                            ELSE dpa.institucion_procedencia
                        END
                    ), ''),
                    'Sin especificar'
                )                          AS institucion,
                d.nombre                   AS departamento,
                dpa.fecha_inicio_pasantia  AS fecha_inicio,
                dpa.estado_pasantia        AS estado
            FROM usuarios u
            JOIN datos_personales dp  ON dp.usuario_id  = u.id
            JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
            LEFT JOIN instituciones inst
                   ON inst.id = COALESCE(dpa.institucion_id, CAST(dpa.institucion_procedencia AS UNSIGNED))
            LEFT JOIN departamentos d ON d.id = dpa.departamento_asignado_id
            {$sqlWhere}
            ORDER BY dp.apellidos ASC, dp.nombres ASC
        ");
        foreach ($binds as $param => $value) {
            $this->db->bind($param, $value);
        }
        $pasantes = $this->db->resultSet();

        // ── Render ────────────────────────────────────────────────────────────
        $tipo = $filtros['tipo'] ?? 'pdf';

        if ($tipo === 'excel') {
            $filename = 'Registro_Pasantes_' . date('Ymd') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-store, no-cache');

            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['N°', 'Cédula', 'Nombres y Apellidos', 'Institución', 'Departamento', 'F. Inicio', 'Estado'], ';');

            foreach ($pasantes as $i => $p) {
                $nombre = trim(($p->apellidos ?? '') . ', ' . ($p->nombres ?? ''));
                fputcsv($out, [
                    $i + 1,
                    $p->cedula       ?? '—',
                    $nombre,
                    $p->institucion  ?? 'Sin especificar',
                    $p->departamento ?? 'Sin asignar',
                    $p->fecha_inicio ? date('d/m/Y', strtotime($p->fecha_inicio)) : '—',
                    $p->estado       ?? '—',
                ], ';');
            }
            fclose($out);
            exit;
        }

        ob_start();
        include '../app/views/reportes/pdf_registro_pasantes.php';
        $html = ob_get_clean();

        $this->pdf->renderDomPdf($html, 'Registro_Pasantes_' . date('Ymd'), false);
    }

    /**
     * Control General de Asistencias (PDF/CSV institucional)
     *
     * Variables que pasa a la vista:
     *   $asistencias   — Array de objetos con registros de asistencia
     *   $subtitulo_pdf — String descriptivo del filtro aplicado
     */
    private function generarReporteAsistencias($filtros)
    {
        $departamento = $filtros['departamento']  ?? 'todos';
        $tipo_reporte = $filtros['tipo_reporte']  ?? 'diario';
        $fecha_inicio = !empty($filtros['fecha_inicio']) ? trim($filtros['fecha_inicio']) : null;
        $fecha_fin    = !empty($filtros['fecha_fin'])    ? trim($filtros['fecha_fin'])    : $fecha_inicio;

        // ── Subtítulo semántico ───────────────────────────────────────────────
        $tipoLabel = [
            'diario'       => 'Diario',
            'semanal'      => 'Semanal',
            'mensual'      => 'Mensual',
            'total'        => 'Total Consolidado',
            'planilla_isp' => 'Planilla ISP Trimestral',
        ][$tipo_reporte] ?? ucfirst($tipo_reporte);

        $partes = ['Tipo: ' . $tipoLabel];
        if ($departamento !== 'todos') $partes[] = 'Depto. ID: ' . (int)$departamento;
        if ($fecha_inicio)             $partes[] = date('d/m/Y', strtotime($fecha_inicio))
                                                  . ' al ' . date('d/m/Y', strtotime($fecha_fin));
        $subtitulo_pdf = implode(' | ', $partes);

        // ── WHERE dinámico ────────────────────────────────────────────────────
        $where = ['1=1'];
        $binds = [];

        if ($fecha_inicio) {
            $where[] = 'a.fecha BETWEEN :fi AND :ff';
            $binds[':fi'] = $fecha_inicio;
            $binds[':ff'] = $fecha_fin;
        }
        if ($departamento !== 'todos') {
            $where[] = 'u.departamento_id = :depto';
            $binds[':depto'] = (int)$departamento;
        }

        $sqlWhere = 'WHERE ' . implode(' AND ', $where);

        $this->db->query("
            SELECT
                u.cedula,
                dp.nombres,
                dp.apellidos,
                a.fecha,
                a.hora_entrada,
                a.hora_salida,
                a.estado        AS observacion
            FROM asistencias a
            JOIN usuarios         u  ON u.id  = a.pasante_id
            JOIN datos_personales dp ON dp.usuario_id = u.id
            {$sqlWhere}
            ORDER BY a.fecha DESC, dp.apellidos ASC
        ");
        foreach ($binds as $param => $value) {
            $this->db->bind($param, $value);
        }
        $asistencias = $this->db->resultSet();

        // ── Render ────────────────────────────────────────────────────────────
        $tipo = $filtros['tipo'] ?? 'pdf';

        if ($tipo === 'excel') {
            $filename = 'Control_Asistencias_' . date('Ymd') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-store, no-cache');

            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['N°', 'Cédula', 'Pasante', 'Fecha', 'Hora Entrada', 'Hora Salida', 'Observación'], ';');

            foreach ($asistencias as $i => $a) {
                $nombre = ucwords(strtolower(trim(($a->apellidos ?? '') . ', ' . ($a->nombres ?? ''))));
                fputcsv($out, [
                    $i + 1,
                    $a->cedula      ?? '—',
                    $nombre,
                    $a->fecha       ? date('d/m/Y', strtotime($a->fecha)) : '—',
                    $a->hora_entrada ?? '—',
                    $a->hora_salida  ?? '—',
                    $a->observacion  ?? '—',
                ], ';');
            }
            fclose($out);
            exit;
        }

        ob_start();
        include '../app/views/reportes/pdf_control_asistencias.php';
        $html = ob_get_clean();

        $this->pdf->renderDomPdf($html, 'Control_Asistencias_' . date('Ymd'), false);
    }

    /**
     * Nómina anual de pasantes con resumen de asistencias.
     * GET /reportes/pdfNomina?download=0|1&anio=YYYY
     */
    public function pdfNomina(): void
    {
        $download = (int)($_GET['download'] ?? 0) === 1;
        $anio     = (int)($_GET['anio'] ?? date('Y'));

        // Nómina: todos los pasantes activos con totales del año indicado
        $this->db->query("
            SELECT
                u.id,
                u.cedula,
                dp.nombres,
                dp.apellidos,
                dep.nombre                                              AS departamento,
                inst.nombre                                             AS institucion,
                dpa.estado_pasantia,
                dpa.fecha_inicio,
                dpa.fecha_fin,
                COUNT(a.id)                                             AS total_dias,
                SUM(CASE WHEN a.estado = 'Presente'    THEN 1 ELSE 0 END) AS presentes,
                SUM(CASE WHEN a.estado = 'Ausente'     THEN 1 ELSE 0 END) AS ausentes,
                SUM(CASE WHEN a.estado = 'Justificado' THEN 1 ELSE 0 END) AS justificados
            FROM usuarios u
            JOIN datos_personales dp   ON dp.usuario_id   = u.id
            JOIN datos_pasante    dpa  ON dpa.usuario_id  = u.id
            LEFT JOIN departamentos dep  ON dep.id  = dpa.departamento_asignado_id
            LEFT JOIN instituciones inst ON inst.id = COALESCE(dpa.institucion_id, CAST(dpa.institucion_procedencia AS UNSIGNED))
            LEFT JOIN asistencias   a    ON a.pasante_id  = u.id
                                        AND YEAR(a.fecha) = :anio
            WHERE u.rol_id = 3
            GROUP BY u.id, u.cedula, dp.nombres, dp.apellidos,
                     dep.nombre, inst.nombre, dpa.estado_pasantia,
                     dpa.fecha_inicio, dpa.fecha_fin
            ORDER BY dep.nombre ASC, dp.apellidos ASC, dp.nombres ASC
        ");
        $this->db->bind(':anio', $anio);
        $pasantes = $this->db->resultSet();

        $subtitulo_pdf = 'Nómina General de Pasantes — Año ' . $anio;

        ob_start();
        include '../app/views/reportes/pdf_nomina_pasantes.php';
        $html = ob_get_clean();

        $this->pdf->renderDomPdf($html, 'Nomina_Pasantes_' . $anio, $download);
    }
    // ══════════════════════════════════════════════════════════════
    // NUEVOS MÓDULOS: Auditóría, Asignaciones, Ficha Diaria, Constancias
    // ══════════════════════════════════════════════════════════════

    /**
     * Auditóría — Reporte de la bitácora del sistema con filtros de fecha y módulo
     */
    private function generarBitacora(array $filtros): void
    {
        $moduloLog  = $filtros['modulo_log']   ?? 'todos';
        $fecha_ini  = !empty($filtros['fecha_inicio']) ? trim($filtros['fecha_inicio']) : date('Y-m-01');
        $fecha_fin  = !empty($filtros['fecha_fin'])    ? trim($filtros['fecha_fin'])    : date('Y-m-d');
        $tipo       = $filtros['tipo'] ?? 'pdf';

        $where = ['b.fecha BETWEEN :fi AND :ff'];
        $binds = [':fi' => $fecha_ini, ':ff' => $fecha_fin];

        if ($moduloLog !== 'todos') {
            $where[] = 'b.accion LIKE :mod';
            $binds[':mod'] = '%' . $moduloLog . '%';
        }

        $this->db->query("
            SELECT
                b.fecha,
                b.accion,
                b.tabla_afectada   AS modulo,
                b.descripcion,
                COALESCE(CONCAT(dp.nombres,' ',dp.apellidos), u.correo) AS usuario,
                u.cedula
            FROM bitacora b
            LEFT JOIN usuarios         u  ON u.id = b.usuario_id
            LEFT JOIN datos_personales dp ON dp.usuario_id = b.usuario_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY b.fecha DESC
            LIMIT 500
        ");
        foreach ($binds as $p => $v) { $this->db->bind($p, $v); }
        $registros = $this->db->resultSet();

        $subtitulo_pdf = 'Audit\u00f3r\u00eda del sistema | '
            . date('d/m/Y', strtotime($fecha_ini))
            . ' al ' . date('d/m/Y', strtotime($fecha_fin));

        if ($tipo === 'excel') {
            $filename = 'Auditoria_' . date('Ymd') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-store, no-cache');
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['N\u00b0', 'Fecha/Hora', 'Usuario', 'C\u00e9dula', 'M\u00f3dulo', 'Acci\u00f3n', 'Descripci\u00f3n'], ';');
            foreach ($registros as $i => $r) {
                fputcsv($out, [
                    $i + 1,
                    $r->fecha        ?? '\u2014',
                    $r->usuario      ?? '\u2014',
                    $r->cedula       ?? '\u2014',
                    $r->modulo       ?? '\u2014',
                    $r->accion       ?? '\u2014',
                    $r->descripcion  ?? '\u2014',
                ], ';');
            }
            fclose($out);
            exit;
        }

        ob_start();
        include '../app/views/reportes/pdf_auditoria.php';
        $html = ob_get_clean();
        $this->pdf->renderDomPdf($html, 'Auditoria_' . date('Ymd'), false);
    }

    /**
     * Asignaciones — Lista de pasantes por departamento
     */
    private function generarAsignaciones(array $filtros): void
    {
        $deptoId   = $filtros['departamento_id'] ?? 'todos';
        $tipo      = $filtros['tipo'] ?? 'pdf';

        $where = ['a.estado = \'activo\''];
        $binds = [];

        if ($deptoId !== 'todos') {
            $where[] = 'dpa.departamento_asignado_id = :depto';
            $binds[':depto'] = (int)$deptoId;
        }

        $this->db->query("
            SELECT
                dp.apellidos, dp.nombres, u.cedula,
                d.nombre                                      AS departamento,
                CONCAT(tp.nombres, ' ', tp.apellidos)         AS tutor,
                COALESCE(inst.nombre, dpa.institucion_procedencia, 'N/D') AS institucion,
                a.fecha_inicio, a.estado
            FROM asignaciones a
            JOIN usuarios          u    ON u.id   = a.pasante_id
            JOIN datos_personales  dp   ON dp.usuario_id = u.id
            JOIN datos_pasante     dpa  ON dpa.usuario_id = u.id
            LEFT JOIN departamentos d   ON d.id   = dpa.departamento_asignado_id
            LEFT JOIN usuarios      tu  ON tu.id  = dpa.tutor_id
            LEFT JOIN datos_personales tp ON tp.usuario_id = dpa.tutor_id
            LEFT JOIN instituciones    inst ON inst.id = COALESCE(dpa.institucion_id, CAST(dpa.institucion_procedencia AS UNSIGNED))
            WHERE " . implode(' AND ', $where) . "
            ORDER BY d.nombre ASC, dp.apellidos ASC
        ");
        foreach ($binds as $p => $v) { $this->db->bind($p, $v); }
        $asignaciones = $this->db->resultSet();

        $subtitulo_pdf = $deptoId !== 'todos'
            ? 'Departamento ID: ' . (int)$deptoId
            : 'Todos los Departamentos';

        if ($tipo === 'excel') {
            $filename = 'Asignaciones_' . date('Ymd') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-store, no-cache');
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['N\u00b0', 'C\u00e9dula', 'Pasante', 'Departamento', 'Tutor', 'Instituci\u00f3n', 'F. Inicio', 'Estado'], ';');
            foreach ($asignaciones as $i => $a) {
                $nombre = trim(($a->apellidos ?? '') . ', ' . ($a->nombres ?? ''));
                fputcsv($out, [
                    $i + 1,
                    $a->cedula       ?? '\u2014',
                    $nombre,
                    $a->departamento ?? 'Sin asignar',
                    $a->tutor        ?? '\u2014',
                    $a->institucion  ?? 'Sin especificar',
                    $a->fecha_inicio ? date('d/m/Y', strtotime($a->fecha_inicio)) : '\u2014',
                    $a->estado       ?? '\u2014',
                ], ';');
            }
            fclose($out);
            exit;
        }

        ob_start();
        include '../app/views/reportes/pdf_asignaciones.php';
        $html = ob_get_clean();
        $this->pdf->renderDomPdf($html, 'Asignaciones_' . date('Ymd'), false);
    }

    /**
     * Ficha Diaria — Asistencias del día por departamento
     */
    private function generarFichaDiaria(array $filtros): void
    {
        $deptoId = $filtros['departamento'] ?? 'todos';
        $fecha   = date('Y-m-d'); // siempre hoy

        $where = ['a.fecha = :fecha', 'a.estado != \'Anulado\''];
        $binds = [':fecha' => $fecha];

        if ($deptoId !== 'todos') {
            $where[] = 'dpa.departamento_asignado_id = :depto';
            $binds[':depto'] = (int)$deptoId;
        }

        $this->db->query("
            SELECT
                dp.apellidos, dp.nombres, u.cedula,
                d.nombre   AS departamento,
                a.estado, a.hora_registro,
                a.metodo
            FROM asistencias a
            JOIN usuarios          u    ON u.id   = a.pasante_id
            JOIN datos_personales  dp   ON dp.usuario_id = u.id
            LEFT JOIN datos_pasante dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos d   ON d.id   = dpa.departamento_asignado_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY d.nombre ASC, dp.apellidos ASC
        ");
        foreach ($binds as $p => $v) { $this->db->bind($p, $v); }
        $registros = $this->db->resultSet();

        // Agrupar por departamento para el PDF
        $porDepto = [];
        foreach ($registros as $r) {
            $dep = $r->departamento ?? 'Sin Departamento';
            $porDepto[$dep][] = $r;
        }

        $subtitulo_pdf = 'Ficha Diaria | ' . date('d/m/Y');

        ob_start();
        include '../app/views/reportes/pdf_ficha_diaria.php';
        $html = ob_get_clean();
        $this->pdf->renderDomPdf($html, 'Ficha_Diaria_' . date('Ymd'), false);
    }

    /**
     * Constancias Administrativas — Carta de Culminación / Constancia de Servicio
     */
    private function generarConstancias(array $filtros): void
    {
        $pasanteId = (int)($filtros['pasante_id'] ?? 0);
        $tipo      = $filtros['tipo_constancia'] ?? 'servicio';

        if (!$pasanteId) {
            $this->renderErrorPage('Datos incompletos', 'Seleccione un pasante.');
        }

        // Obtener datos completos del pasante
        $this->db->query("
            SELECT
                dp.nombres, dp.apellidos, u.cedula,
                d.nombre                                      AS departamento,
                COALESCE(inst.nombre, dpa.institucion_procedencia, 'N/D') AS institucion,
                dpa.estado_pasantia,
                dpa.horas_acumuladas,
                dpa.horas_meta,
                dpa.fecha_inicio_pasantia  AS fecha_inicio,
                dpa.fecha_fin_estimada     AS fecha_fin,
                CONCAT(tp.nombres,' ',tp.apellidos)           AS tutor_nombre,
                tp.cargo                                      AS tutor_cargo
            FROM usuarios u
            JOIN datos_personales  dp   ON dp.usuario_id  = u.id
            JOIN datos_pasante     dpa  ON dpa.usuario_id = u.id
            LEFT JOIN departamentos d   ON d.id   = dpa.departamento_asignado_id
            LEFT JOIN instituciones inst ON inst.id = COALESCE(dpa.institucion_id, CAST(dpa.institucion_procedencia AS UNSIGNED))
            LEFT JOIN usuarios      tu  ON tu.id   = dpa.tutor_id
            LEFT JOIN datos_personales tp ON tp.usuario_id = dpa.tutor_id
            WHERE u.id = :pid AND u.rol_id = 3
            LIMIT 1
        ");
        $this->db->bind(':pid', $pasanteId);
        $pasante = $this->db->single();

        if (!$pasante) {
            $this->renderErrorPage('Pasante no encontrado', 'No se encontraron datos para este pasante.');
        }

        // Validación de culminación
        if ($tipo === 'culminacion' && ($pasante->estado_pasantia ?? '') !== 'Finalizado') {
            $this->renderErrorPage(
                'Carta no disponible',
                'La carta de culminación solo se genera cuando el pasante ha finalizado su pasant\u00eda.'
            );
        }

        $fechaEmision = date('d')  . ' de '
            . ['', 'enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'][(int)date('n')]
            . ' de ' . date('Y');

        ob_start();
        include '../app/views/reportes/pdf_constancia.php';
        $html = ob_get_clean();

        $nombreArchivo = ($tipo === 'culminacion' ? 'Carta_Culminacion' : 'Constancia_Servicio')
            . '_' . ($pasante->cedula ?? $pasanteId);
        $this->pdf->renderDomPdf($html, $nombreArchivo, false);
    }
}
