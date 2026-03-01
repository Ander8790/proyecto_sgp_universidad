<?php
/**
 * AsistenciasController — Panel de Control de Asistencias (Admin)
 *
 * RUTAS:
 *   GET  /asistencias                → index()            Lista de hoy
 *   GET  /asistencias/historial      → historial()        Búsqueda por rango/pasante
 *   POST /asistencias/registro_manual → registro_manual() JSON — justificar/registrar manual
 *
 * @version 2.0 — Fase 6
 */
class AsistenciasController extends Controller
{
    private $db;
    private $asistenciaModel;

    public function __construct()
    {
        Session::start();
        AuthMiddleware::require();
        AuthMiddleware::verificarEstado();

        $config  = require '../app/config/config.php';
        $this->db = new Database($config['db']);
        $this->asistenciaModel = $this->model('Asistencia');
    }

    // ────────────────────────────────────────────────────────────────
    // VISTA PRINCIPAL — Asistencias de Hoy
    // ────────────────────────────────────────────────────────────────

    /**
     * index() — Lista registros de asistencia según rango de fecha.
     * Soporta vistas Diaria (hoy), Semanal (semana actual) y Mensual (mes actual).
     */
    public function index(): void
    {
        $rolId = (int)Session::get('role_id');
        $userId = (int)Session::get('user_id');

        if ($rolId !== 1 && $rolId !== 2 && $rolId !== 3) { // solo Admin, Tutor y Pasante
            Session::setFlash('error', 'Sin permisos para esta sección.');
            $this->redirect('/dashboard');
            return;
        }

        $vista = $_GET['vista'] ?? 'diaria'; // 'diaria', 'semanal', 'mensual', 'anual'
        $hoy = date('Y-m-d');
        
        // Determinar fechas de inicio y fin según el rango
        $fechaInicio = $hoy;
        $fechaFin    = $hoy;
        $tituloRango = 'del día';

        $paramsUrl = [
            'fecha'  => $_GET['fecha'] ?? $hoy,
            'semana' => $_GET['semana'] ?? date('W'),
            'mes'    => str_pad($_GET['mes'] ?? date('m'), 2, '0', STR_PAD_LEFT),
            'anio'   => $_GET['anio'] ?? date('Y')
        ];

        if ($vista === 'diaria') {
            $fechaInicio = $paramsUrl['fecha'];
            $fechaFin    = $paramsUrl['fecha'];
            $tituloRango = 'del ' . date('d/m/Y', strtotime($fechaInicio));
        } elseif ($vista === 'semanal') {
            $dto = new DateTime();
            $dto->setISODate((int)$paramsUrl['anio'], (int)$paramsUrl['semana']);
            $fechaInicio = $dto->format('Y-m-d');
            $dto->modify('+6 days');
            $fechaFin = $dto->format('Y-m-d');
            $tituloRango = "de la semana " . $paramsUrl['semana'] . " del " . $paramsUrl['anio'];
        } elseif ($vista === 'mensual') {
            $fechaInicio = $paramsUrl['anio'] . '-' . $paramsUrl['mes'] . '-01';
            $fechaFin    = date('Y-m-t', strtotime($fechaInicio));
            $meses = ['01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'];
            $tituloRango = "de " . ($meses[$paramsUrl['mes']] ?? '') . " " . $paramsUrl['anio'];
        } elseif ($vista === 'anual') {
            $fechaInicio = $paramsUrl['anio'] . '-01-01';
            $fechaFin    = $paramsUrl['anio'] . '-12-31';
            $tituloRango = "del año " . $paramsUrl['anio'];
        }

        // Si es pasante, solo ve sus propios registros
        $wherePasante = "";
        $filtroBusquedaPasante = (int)($_GET['pasante_id'] ?? 0);

        if ($rolId === 3) {
            $wherePasante = " AND a.pasante_id = :uid_pasante ";
        } elseif ($filtroBusquedaPasante > 0) {
            $wherePasante = " AND a.pasante_id = :uid_filtro ";
        }

        // Registros en el rango con datos del pasante (3NF: JOINs a tablas normalizadas)
        $this->db->query("
            SELECT
                a.id,
                a.fecha,
                a.hora_registro,
                a.estado,
                a.metodo,
                a.motivo_justificacion,
                a.ruta_evidencia,
                u.id           AS pasante_id,
                u.cedula,
                dp.nombres,
                dp.apellidos,
                d.nombre       AS departamento_nombre
            FROM asistencias a
            INNER JOIN usuarios          u   ON u.id          = a.pasante_id
            LEFT  JOIN datos_personales  dp  ON dp.usuario_id = u.id
            LEFT  JOIN datos_pasante     dpa ON dpa.usuario_id = u.id
            LEFT  JOIN departamentos     d   ON d.id = COALESCE(dpa.departamento_asignado_id, u.departamento_id)
            WHERE a.fecha >= :fecha_inicio AND a.fecha <= :fecha_fin $wherePasante
            ORDER BY a.fecha DESC, a.hora_registro DESC
        ");
        $this->db->bind(':fecha_inicio', $fechaInicio);
        $this->db->bind(':fecha_fin', $fechaFin);
        if ($rolId === 3) {
            $this->db->bind(':uid_pasante', $userId);
        } elseif ($filtroBusquedaPasante > 0) {
            $this->db->bind(':uid_filtro', $filtroBusquedaPasante);
        }
        $registrosLista = $this->db->resultSet();

        // Obtener la cantidad de pasantes activos en total (para los cálculos y el select de 'filtro')
        $this->db->query("
            SELECT u.id, u.cedula, dp.nombres, dp.apellidos,
                   d.nombre AS departamento_nombre
            FROM   usuarios u
            LEFT JOIN datos_personales  dp  ON dp.usuario_id  = u.id
            LEFT JOIN datos_pasante     dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos     d   ON d.id = COALESCE(dpa.departamento_asignado_id, u.departamento_id)
            WHERE  u.rol_id = 3
              AND  u.estado = 'activo'
              AND  COALESCE(dpa.estado_pasantia, 'Sin Asignar') = 'Activo'
            ORDER BY IFNULL(dp.apellidos, u.correo) ASC
        ");
        $todosActivos = $this->db->resultSet();
        $totalActivos = count($todosActivos);

        // Cálculos dinámicos
        $presentes    = count(array_filter($registrosLista, fn($r) => $r->estado === 'Presente'));
        $justificados = count(array_filter($registrosLista, fn($r) => $r->estado === 'Justificado'));
        $ausentes     = count(array_filter($registrosLista, fn($r) => $r->estado === 'Ausente'));
        $sinMarcar    = [];
        $porcentajeAsistencia = -1;

        if ($vista === 'diaria') {
            // Ausentes HOY / Diaria son TODOS los activos que NO tienen registro ese día (ni p, ni j, ni a)
            $marcadasIds = array_map(fn($r) => (int)$r->pasante_id, $registrosLista);
            $sinMarcar = array_filter($todosActivos, fn($p) => !in_array((int)$p->id, $marcadasIds));
            // Actualizamos la métrica visual sumando los que no marcaron a los que están "Ausente" formalmente.
            $ausentes += count($sinMarcar);

            if ($totalActivos > 0) {
                $porcentajeAsistencia = round(($presentes + $justificados) / $totalActivos * 100);
            }
        }

        // ==========================================
        // PROCESADOR DE DATOS: VISTA SEMANAL
        // ==========================================
        $datosSemanales = [];
        $navSemana = [];
        
        if ($vista === 'semanal') {
            // 1. Inicializar la estructura con TODOS los pasantes activos (Incluso si no han marcado)
            foreach ($todosActivos as $pasante) {
                // Adaptado para usar objetos (como es costumbre de PDO en este proyecto)
                $depto = $pasante->departamento_nombre ?? 'Sin Asignar';
                $pasanteId = $pasante->id;
                $nombreCompleto = trim(($pasante->apellidos ?? '') . ', ' . ($pasante->nombres ?? ''));
            
                if (!isset($datosSemanales[$depto])) {
                    $datosSemanales[$depto] = [];
                }
                $datosSemanales[$depto][$pasanteId] = [
                    'nombre'  => $nombreCompleto,
                    'dias'    => [1 => '-', 2 => '-', 3 => '-', 4 => '-', 5 => '-'], // 1=Lun, 5=Vie
                    'totales' => ['P' => 0, 'A' => 0, 'J' => 0]
                ];
            }
            
            // 2. Rellenar el "Mapa de Calor" con los registros reales de la semana
            foreach ($registrosLista as $reg) {
                $depto = $reg->departamento_nombre ?? 'Sin Asignar';
                $pasanteId = $reg->pasante_id ?? null; 
                if ($pasanteId && isset($datosSemanales[$depto][$pasanteId])) {
                    $diaSemana = date('N', strtotime($reg->fecha)); // 1 (Lun) a 7 (Dom)
                    if ($diaSemana <= 5) { // Solo procesamos de Lunes a Viernes
                        $letra = '-';
                        if (stripos($reg->estado, 'Presente') !== false) { 
                            $letra = 'P'; 
                            $datosSemanales[$depto][$pasanteId]['totales']['P']++; 
                        } elseif (stripos($reg->estado, 'Ausente') !== false) { 
                            $letra = 'A'; 
                            $datosSemanales[$depto][$pasanteId]['totales']['A']++; 
                        } elseif (stripos($reg->estado, 'Justificado') !== false) { 
                            $letra = 'J'; 
                            $datosSemanales[$depto][$pasanteId]['totales']['J']++; 
                        }
                        $datosSemanales[$depto][$pasanteId]['dias'][$diaSemana] = $letra;
                    }
                }
            }
            
            // 3. Lógica de Paginación Semanal (Botones Ant y Sig)
            $semanaActual = $paramsUrl['semana'] ?? date('W');
            $anioActual = $paramsUrl['anio'] ?? date('Y');
            
            $fechaBase = new DateTime();
            $fechaBase->setISODate((int)$anioActual, (int)$semanaActual);
            $fechaAnt = clone $fechaBase;
            $fechaAnt->modify('-1 week');
            $fechaSig = clone $fechaBase;
            $fechaSig->modify('+1 week');
            
            $navSemana = [
                'ant_url' => URLROOT . "/asistencias?vista=semanal&semana=" . $fechaAnt->format('W') . "&anio=" . $fechaAnt->format('Y'),
                'sig_url' => URLROOT . "/asistencias?vista=semanal&semana=" . $fechaSig->format('W') . "&anio=" . $fechaSig->format('Y'),
                'texto'   => "Semana " . $semanaActual . " - " . $anioActual
            ];
        }

        $this->view('asistencias/index', [
            'title'         => 'Asistencias — ' . ucfirst($vista),
            'vista'         => $vista,
            'paramsUrl'     => $paramsUrl, // Para repoblar los selects en la UI
            'filtroPasante' => $filtroBusquedaPasante,
            'fechaInicio'   => $fechaInicio,
            'fechaFin'      => $fechaFin,
            'tituloRango'   => $tituloRango,
            'hoy'           => $hoy,
            'registrosLista'=> $registrosLista,
            'sinMarcar'     => array_values($sinMarcar),
            'presentes'     => $presentes,
            'justificados'  => $justificados,
            'ausentes'      => $ausentes,
            'totalActivos'  => $totalActivos,
            'pasantesActivos' => $todosActivos, // para modals y listados
            'porcAsistencia'=> $porcentajeAsistencia,
            'datosSemanales'=> $datosSemanales,
            'navSemana'     => $navSemana
        ]);
    }

    // ────────────────────────────────────────────────────────────────
    // ENDPOINT JSON — Registro Manual / Justificación
    // ────────────────────────────────────────────────────────────────

    /**
     * registro_manual() — El Admin justifica una falta o registra manualmente.
     *
     * POST /asistencias/registro_manual
     * Body (form-data):
     *   pasante_id          int      requerido
     *   fecha               Y-m-d    requerido  (puede ser hoy o retroactivo)
     *   estado              'Presente'|'Justificado'
     *   motivo_justificacion text    requerido si estado = Justificado
     *   evidencia           file     opcional (imagen del récipe médico)
     */
    public function registro_manual(): void
    {
        header('Content-Type: application/json');

        if ((int)Session::get('role_id') !== 1) {
            echo json_encode(['success' => false, 'message' => 'Solo el Administrador puede hacer esto.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        // Captura de datos
        $pasanteId = (int)($_POST['pasante_id']           ?? 0);
        $fecha     = trim($_POST['fecha']                  ?? date('Y-m-d'));
        $estado    = trim($_POST['estado']                 ?? 'Justificado');
        $motivo    = trim($_POST['motivo_justificacion']   ?? '');

        // Validaciones
        if ($pasanteId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Selecciona un pasante válido.']);
            exit;
        }

        if (!in_array($estado, ['Presente', 'Justificado', 'Ausente'])) {
            echo json_encode(['success' => false, 'message' => 'Estado no válido.']);
            exit;
        }

        if ($estado === 'Justificado' && !$motivo) {
            echo json_encode(['success' => false, 'message' => 'El motivo de justificación es obligatorio.']);
            exit;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido.']);
            exit;
        }

        // ── Upload de evidencia (récipe médico) ────────────────────
        $rutaEvidencia = null;

        if (!empty($_FILES['evidencia']['tmp_name'])) {
            $file      = $_FILES['evidencia'];
            $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed   = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];

            if (!in_array($ext, $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Formato de archivo no permitido. Use JPG, PNG o PDF.']);
                exit;
            }

            if ($file['size'] > 5 * 1024 * 1024) { // 5 MB máx
                echo json_encode(['success' => false, 'message' => 'El archivo supera el límite de 5 MB.']);
                exit;
            }

            $uploadDir = '../public/uploads/evidencias/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $nombreArchivo   = 'evidencia_' . $pasanteId . '_' . str_replace('-', '', $fecha) . '_' . time() . '.' . $ext;
            $destinoCompleto = $uploadDir . $nombreArchivo;

            if (!move_uploaded_file($file['tmp_name'], $destinoCompleto)) {
                echo json_encode(['success' => false, 'message' => 'Error al subir el archivo.']);
                exit;
            }

            $rutaEvidencia = '/uploads/evidencias/' . $nombreArchivo;
        }

        $registroId = (int)($_POST['registro_id'] ?? 0);
        $existente = null;

        if ($registroId > 0) {
            // Edición de un registro existente por su ID explícito
            $this->db->query("SELECT id, estado FROM asistencias WHERE id = :id LIMIT 1");
            $this->db->bind(':id', $registroId);
            $existente = $this->db->single();
        } else {
            // ── Verificar si ya existe registro para esa fecha ─────────
            $this->db->query("
                SELECT id, estado FROM asistencias
                WHERE pasante_id = :pid AND fecha = :fecha
                LIMIT 1
            ");
            $this->db->bind(':pid',   $pasanteId);
            $this->db->bind(':fecha', $fecha);
            $existente = $this->db->single();
        }

        if ($existente) {
            // Actualizar el registro existente
            $this->db->query("
                UPDATE asistencias
                SET estado = :estado,
                    motivo_justificacion = :motivo,
                    ruta_evidencia       = COALESCE(:evidencia, ruta_evidencia),
                    metodo               = 'Manual'
                WHERE id = :id
            ");
            $this->db->bind(':estado',    $estado);
            $this->db->bind(':motivo',    $motivo ?: null);
            $this->db->bind(':evidencia', $rutaEvidencia);
            $this->db->bind(':id',        (int)$existente->id);
        } else {
            // Insertar nuevo registro manual
            $this->db->query("
                INSERT INTO asistencias
                    (pasante_id, fecha, hora_registro, estado, metodo, motivo_justificacion, ruta_evidencia)
                VALUES
                    (:pid, :fecha, :hora, :estado, 'Manual', :motivo, :evidencia)
            ");
            $this->db->bind(':pid',      $pasanteId);
            $this->db->bind(':fecha',    $fecha);
            $this->db->bind(':hora',     date('H:i:s'));
            $this->db->bind(':estado',   $estado);
            $this->db->bind(':motivo',   $motivo ?: null);
            $this->db->bind(':evidencia', $rutaEvidencia);
        }

        $ok = $this->db->execute();

        // 3NF: horas_acumuladas vive en datos_pasante, NO en usuarios
        // Restar horas si venía de un estado con horas, y agregar si va a un estado con horas
        if ($ok) {
            $diffHoras = 0;
            // Estado anterior si existe
            $estadoAnterior = $existente ? $existente->estado : null;
            
            // Si antes ganaba horas y ahora no (Ej. Presente -> Ausente)
            if (in_array($estadoAnterior, ['Presente', 'Justificado']) && !in_array($estado, ['Presente', 'Justificado'])) {
                $diffHoras = -8;
            }
            // Si antes no ganaba y ahora sí (Ej. Ausente -> Presente/Justificado)
            elseif (!in_array($estadoAnterior, ['Presente', 'Justificado']) && in_array($estado, ['Presente', 'Justificado'])) {
                $diffHoras = 8;
            }

            if ($diffHoras !== 0) {
                // GREATEST para evitar horas negativas por cualquier desajuste lógico previo
                $this->db->query("
                    UPDATE datos_pasante
                    SET horas_acumuladas = GREATEST(0, horas_acumuladas + (:diff))
                    WHERE usuario_id = :pid
                ");
                $this->db->bind(':diff', $diffHoras);
                $this->db->bind(':pid', $pasanteId);
                $this->db->execute();
            }
        }

        echo json_encode([
            'success' => $ok,
            'message' => $ok
                ? ($existente ? '✅ Registro actualizado correctamente.' : '✅ Asistencia manual registrada.')
                : 'Error al guardar el registro.',
        ]);

        exit;
    }

    // ────────────────────────────────────────────────────────────────
    // ENDPOINT JSON — Exportar Datos (CSV / PDF)
    // ────────────────────────────────────────────────────────────────

    /**
     * exportar_datos() — Devuelve los registros en formato JSON según el rango.
     * Usado por el frontend para generar CSV o PDF desde el cliente.
     */
    public function exportar_datos(): void
    {
        header('Content-Type: application/json');

        $rolId = (int)Session::get('role_id');
        $userId = (int)Session::get('user_id');

        if ($rolId !== 1 && $rolId !== 2 && $rolId !== 3) {
            echo json_encode(['success' => false, 'message' => 'Sin permisos de exportación.']);
            exit;
        }

        $vista = $_GET['vista'] ?? 'diaria'; // 'diaria', 'semanal', 'mensual', 'anual'
        $hoy = date('Y-m-d');
        
        $fechaInicio = $hoy;
        $fechaFin    = $hoy;

        $paramsUrl = [
            'fecha'  => $_GET['fecha'] ?? $hoy,
            'semana' => $_GET['semana'] ?? date('W'),
            'mes'    => str_pad($_GET['mes'] ?? date('m'), 2, '0', STR_PAD_LEFT),
            'anio'   => $_GET['anio'] ?? date('Y')
        ];

        if ($vista === 'diaria') {
            $fechaInicio = $paramsUrl['fecha'];
            $fechaFin    = $paramsUrl['fecha'];
        } elseif ($vista === 'semanal') {
            $dto = new DateTime();
            $dto->setISODate((int)$paramsUrl['anio'], (int)$paramsUrl['semana']);
            $fechaInicio = $dto->format('Y-m-d');
            $dto->modify('+6 days');
            $fechaFin = $dto->format('Y-m-d');
        } elseif ($vista === 'mensual') {
            $fechaInicio = $paramsUrl['anio'] . '-' . $paramsUrl['mes'] . '-01';
            $fechaFin    = date('Y-m-t', strtotime($fechaInicio));
        } elseif ($vista === 'anual') {
            $fechaInicio = $paramsUrl['anio'] . '-01-01';
            $fechaFin    = $paramsUrl['anio'] . '-12-31';
        }

        $wherePasante = "";
        $filtroBusquedaPasante = (int)($_GET['pasante_id'] ?? 0);

        if ($rolId === 3) {
            $wherePasante = " AND a.pasante_id = :uid_pasante ";
        } elseif ($filtroBusquedaPasante > 0) {
            $wherePasante = " AND a.pasante_id = :uid_filtro ";
        }

        $this->db->query("
            SELECT
                a.id,
                a.fecha,
                a.hora_registro,
                a.estado,
                a.metodo,
                a.motivo_justificacion,
                u.cedula,
                dp.nombres,
                dp.apellidos,
                d.nombre AS departamento_nombre
            FROM asistencias a
            INNER JOIN usuarios          u   ON u.id          = a.pasante_id
            LEFT  JOIN datos_personales  dp  ON dp.usuario_id = u.id
            LEFT  JOIN datos_pasante     dpa ON dpa.usuario_id = u.id
            LEFT  JOIN departamentos     d   ON d.id = COALESCE(dpa.departamento_asignado_id, u.departamento_id)
            WHERE a.fecha >= :fecha_inicio AND a.fecha <= :fecha_fin $wherePasante
            ORDER BY a.fecha DESC, a.hora_registro DESC
        ");
        
        $this->db->bind(':fecha_inicio', $fechaInicio);
        $this->db->bind(':fecha_fin', $fechaFin);
        if ($rolId === 3) {
            $this->db->bind(':uid_pasante', $userId);
        } elseif ($filtroBusquedaPasante > 0) {
            $this->db->bind(':uid_filtro', $filtroBusquedaPasante);
        }
        
        $registrosLista = $this->db->resultSet();

        echo json_encode([
            'success' => true,
            'vista'   => $vista,
            'data'    => $registrosLista
        ]);
        exit;
    }

    // ────────────────────────────────────────────────────────────────
    // ENDPOINT JSON — Buscador Autocomplete
    // ────────────────────────────────────────────────────────────────

    /**
     * buscar_pasantes() — Endpoint para el Autocomplete del Modal de Búsqueda
     */
    public function buscar_pasantes(): void
    {
        header('Content-Type: application/json');
        
        $term = $_GET['q'] ?? '';
        if (strlen(trim($term)) < 2) {
            echo json_encode(['success' => true, 'data' => []]);
            exit;
        }

        $termSql = "%" . trim($term) . "%";

        // Mismos filtros que pasantesActivos: Rol 3, Usuario activo, Pasantía activa
        $this->db->query("
            SELECT u.id, u.cedula, dp.nombres, dp.apellidos,
                   d.nombre AS departamento_nombre
            FROM   usuarios u
            LEFT JOIN datos_personales  dp  ON dp.usuario_id  = u.id
            LEFT JOIN datos_pasante     dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos     d   ON d.id = COALESCE(dpa.departamento_asignado_id, u.departamento_id)
            WHERE  u.rol_id = 3
              AND  u.estado = 'activo'
              AND  COALESCE(dpa.estado_pasantia, 'Sin Asignar') = 'Activo'
              AND  (u.cedula LIKE :q OR dp.nombres LIKE :q OR dp.apellidos LIKE :q)
            ORDER BY IFNULL(dp.apellidos, u.correo) ASC
            LIMIT 15
        ");
        $this->db->bind(':q', $termSql);
        $resultados = $this->db->resultSet();

        echo json_encode(['success' => true, 'data' => $resultados]);
        exit;
    }

    // ────────────────────────────────────────────────────────────────
    // ENDPOINT JSON — Anular Registro
    // ────────────────────────────────────────────────────────────────

    /**
     * anular_registro() — Borrado lógico o cambio a estado 'Anulado'
     */
    public function anular_registro(): void
    {
        header('Content-Type: application/json');
        if ((int)Session::get('role_id') !== 1) {
            echo json_encode(['success' => false, 'message' => 'Sin permisos.']);
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de registro inválido.']);
            exit;
        }

        // Restar las 8 horas acumuladas si el estado anterior las proveía
        $this->db->query("SELECT pasante_id, estado FROM asistencias WHERE id = :id");
        $this->db->bind(':id', $id);
        $registro = $this->db->single();

        if ($registro && in_array($registro->estado, ['Presente', 'Justificado'])) {
            $this->db->query("
                UPDATE datos_pasante
                SET horas_acumuladas = GREATEST(0, horas_acumuladas - 8)
                WHERE usuario_id = :pid
            ");
            $this->db->bind(':pid', $registro->pasante_id);
            $this->db->execute();
        }

        // Convertir a 'Anulado'
        $this->db->query("UPDATE asistencias SET estado = 'Anulado' WHERE id = :id");
        $this->db->bind(':id', $id);
        $ok = $this->db->execute();

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Asistencia anulada (Se restaron las horas acumuladas si correspondía).' : 'Error al anular.'
        ]);
        exit;
    }

    // ==========================================
    // API AJAX: MÓDULO DE CONSULTA RÁPIDA
    // ==========================================

    /**
     * Endpoint 1: Buscador en vivo para el autocompletado del Modal
     */
    public function buscarPasanteAjax() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $query = trim($_POST['query'] ?? '');
            
            // Llamamos al modelo para buscar coincidencias por cédula o nombre
            // IMPORTANTE: Asegúrate de crear este método en tu AsistenciaModel
            $resultados = $this->asistenciaModel->buscarPasanteLive($query);
            
            header('Content-Type: application/json');
            echo json_encode($resultados);
            exit; // Evitamos que se renderice cualquier vista HTML
        }
    }

    /**
     * Endpoint 2: Extrae todo el expediente y marcajes de un pasante
     */
    public function obtenerDatosAuditoriaAjax() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $idPasante = intval($_POST['id_pasante'] ?? 0);
            
            if ($idPasante > 0) {
                // A. Obtener el perfil (Nombres, Cédula, Depto, fecha_inicio, fecha_fin)
                $perfil = $this->asistenciaModel->obtenerPerfilParaAuditoria($idPasante);
                
                // B. Obtener el historial completo de marcajes
                $historial = $this->asistenciaModel->obtenerHistorialCompletoPasante($idPasante);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'perfil' => $perfil,
                    'historial' => $historial
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            }
            exit;
        }
    }
}
