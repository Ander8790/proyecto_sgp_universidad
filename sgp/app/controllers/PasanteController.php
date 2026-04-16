<?php
/**
 * PasanteController — Vistas del rol Pasante + Vistas Admin Legacy
 *
 * Arquitectura basada en 3NF (usuarios -> datos_personales -> datos_pasante)
 *
 * RUTAS:
 *   GET  /pasante/index       → index()      Lista admin (legacy)
 *   GET  /pasante/show/{id}   → show()       Kardex individual (admin)
 *   GET  /pasante/dashboard   → dashboard()  Panel personal del pasante
 *   GET  /pasante/asistencia  → asistencia() Historial personal
 *
 * NOTA: asignar() y finalizar_pasantia() eliminados (VULN-08).
 *       Esa lógica es responsabilidad exclusiva de AsignacionesController.
 *
 * @version 4.0 — Limpieza DRY
 */

declare(strict_types=1);

class PasanteController extends Controller
{
    private $db;
    private $pasanteModel;

    public function __construct()
    {
        Session::start();

        AuthMiddleware::require();
        AuthMiddleware::verificarEstado();

        $config = require '../app/config/config.php';
        $this->db = Database::getInstance(); // SGP-FIX-v2 [6/2.1] aplicado

        require_once '../app/models/PasanteModel.php';
        require_once '../app/models/AsistenciaModel.php';
        $this->pasanteModel = new PasanteModel();
    }

    // ────────────────────────────────────────────────────────────────
    // VISTAS ADMINISTRATIVAS
    // ────────────────────────────────────────────────────────────────

    /**
     * Lista de pasantes para el Administrador.
     * Solo rol_id = 1 puede acceder.
     */
    public function index(): void
    {
        if (Session::get('role_id') != 1) {
            Session::setFlash('error', 'No tienes permisos para esta sección.');
            $this->redirect('/dashboard');
            return;
        }

        // Pasantes: tabla única usuarios
        $pasantes = $this->pasanteModel->getAll();

        // Departamentos activos para el select del modal
        $this->db->query("SELECT id, nombre, descripcion FROM departamentos WHERE activo = 1 ORDER BY nombre ASC");
        $departamentos = $this->db->resultSet();

        $this->view('admin/pasantes/index', [
            'title'         => 'Gestión de Pasantes',
            'pasantes'      => $pasantes,
            'departamentos' => $departamentos,
        ]);
    }

    /**
     * Kardex individual del pasante.
     *
     * @param int $id ID del usuario pasante
     */
    public function show(int $id): void
    {
        if (Session::get('role_id') != 1) {
            $this->redirect('/dashboard');
            return;
        }

        $pasante = $this->pasanteModel->getByUsuarioId($id);

        if (!$pasante) {
            Session::setFlash('error', 'Pasante no encontrado.');
            $this->redirect('/pasantes');
            return;
        }

        $this->view('admin/pasantes/show', [
            'title'   => 'Reporte de Pasantía — ' . ($pasante->nombres ?? '') . ' ' . ($pasante->apellidos ?? ''),
            'pasante' => $pasante,
        ]);
    }

    /**
     * Dashboard del pasante (su propia vista — rol_id = 3).
     */
    public function dashboard(): void
    {
        if (Session::get('role_id') != 3) {
            Session::setFlash('error', 'No tienes permisos para esta sección.');
            $this->redirect('/dashboard');
            return;
        }

        $userId  = (int)Session::get('user_id');
        $pasante = $this->pasanteModel->getByUsuarioId($userId);

        // ✅ PRO-RATA: Calcular horas dinámicamente desde asistencias
        $asistenciaModel = new AsistenciaModel($this->db);
        $horasMeta = (int)(($pasante->horas_meta ?? 0) > 0 ? $pasante->horas_meta : 1440);
        $proRata   = $asistenciaModel->calcularProgresoProRata($userId, $horasMeta);

        // Calendario (tiempo transcurrido)
        if (!empty($pasante->fecha_inicio_pasantia)) {
            $calendario = $asistenciaModel->calcularProgresoPorCalendario($pasante->fecha_inicio_pasantia, $horasMeta);
            if ($calendario) {
                $proRata->porcentaje_calendario = $calendario->porcentaje_calendario ?? 0;
            }
        }

        // Sobreescribir horas_acumuladas con el valor Pro-Rata real
        if ($pasante) {
            $pasante->horas_acumuladas = $proRata->horas_mostradas;
            $pasante->horas_meta       = $proRata->horas_meta;
        }

        // Feed de actividades recientes (últimas 10)
        $this->db->query("
            SELECT a.*, d.nombre as departamento_nombre
            FROM asistencias a
            JOIN datos_pasante dp ON a.pasante_id = dp.usuario_id
            LEFT JOIN departamentos d ON dp.departamento_asignado_id = d.id
            WHERE a.pasante_id = :uid
            ORDER BY a.fecha DESC, a.hora_registro DESC
            LIMIT 10
        ");
        $this->db->bind(':uid', $userId);
        $actividades = $this->db->resultSet();

        $this->view('pasante/dashboard', [
            'title'      => 'Mi Panel',
            'pasante'    => $pasante,
            'actividades'=> $actividades,
            'user_name'  => Session::get('user_name') ?? 'Pasante',
            'proRata'    => $proRata,
        ]);

    }

    /**
     * Mi Asistencia — Historial personal del pasante.
     */
    public function asistencia(): void
    {
        if (Session::get('role_id') != 3) {
            Session::setFlash('error', 'No tienes permisos para esta sección.');
            $this->redirect('/dashboard');
            return;
        }

        $userId = (int)Session::get('user_id');
        $pasante = $this->pasanteModel->getByUsuarioId($userId);

        // Pro-rata progress (needed for progress bars in view)
        $asistenciaModel = new AsistenciaModel($this->db);
        $horasMeta = (int)(($pasante->horas_meta ?? 0) > 0 ? $pasante->horas_meta : 1440);
        $proRata   = $asistenciaModel->calcularProgresoProRata($userId, $horasMeta);
        $calendario = null;
        if (!empty($pasante->fecha_inicio_pasantia)) {
            $calendario = $asistenciaModel->calcularProgresoPorCalendario($pasante->fecha_inicio_pasantia, $horasMeta);
            if ($calendario) {
                $proRata->porcentaje_calendario = $calendario->porcentaje_calendario ?? 0;
            }
        }

        $this->db->query("
            SELECT * FROM asistencias
            WHERE pasante_id = :uid
            ORDER BY fecha DESC, hora_registro DESC
        ");
        $this->db->bind(':uid', $userId);
        $asistencias = $this->db->resultSet();

        $this->view('pasante/asistencia', [
            'title'      => 'Mi Asistencia',
            'pasante'    => $pasante,
            'asistencias'=> $asistencias,
            'proRata'    => $proRata,
        ]);

    }

    /**
     * Endpoint AJAX para verificar el estado actual de la pasantía.
     * Utilizado para el polling de actualización automática.
     */
    public function getStatusAjax(): void
    {
        if (Session::get('role_id') != 3) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        $userId = (int)Session::get('user_id');
        $pasante = $this->pasanteModel->getByUsuarioId($userId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'estado'  => $pasante->estado_pasantia ?? 'Sin Asignar'
        ]);
    }

    // ────────────────────────────────────────────────────────────────
    // MÓDULOS PERSONALES DEL PASANTE
    // ────────────────────────────────────────────────────────────────

    /**
     * GET /pasante/analiticas
     * Gráfica personal de asistencia del pasante autenticado.
     */
    public function analiticas(): void
    {
        if (Session::get('role_id') != 3) {
            $this->redirect('/dashboard');
            return;
        }

        $userId  = (int)Session::get('user_id');
        $pasante = $this->pasanteModel->getByUsuarioId($userId);

        // Asistencias agrupadas por mes (últimos 6 meses)
        $this->db->query("
            SELECT
                DATE_FORMAT(fecha, '%Y-%m')                                    AS mes,
                DATE_FORMAT(fecha, '%b %Y')                                    AS mes_label,
                COUNT(CASE WHEN estado = 'Presente'    THEN 1 END)             AS presentes,
                COUNT(CASE WHEN estado = 'Ausente'     THEN 1 END)             AS ausentes,
                COUNT(CASE WHEN estado = 'Justificado' THEN 1 END)             AS justificados,
                COUNT(*)                                                        AS total
            FROM asistencias
            WHERE pasante_id = :uid
              AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(fecha, '%Y-%m'), DATE_FORMAT(fecha, '%b %Y')
            ORDER BY mes ASC
        ");
        $this->db->bind(':uid', $userId);
        $porMes = $this->db->resultSet();

        // Totales globales
        $this->db->query("
            SELECT
                COUNT(CASE WHEN estado = 'Presente'    THEN 1 END) AS presentes,
                COUNT(CASE WHEN estado = 'Ausente'     THEN 1 END) AS ausentes,
                COUNT(CASE WHEN estado = 'Justificado' THEN 1 END) AS justificados,
                COUNT(*)                                            AS total
            FROM asistencias
            WHERE pasante_id = :uid
        ");
        $this->db->bind(':uid', $userId);
        $totales = $this->db->single();

        // Progreso pro-rata
        $asistenciaModel = new AsistenciaModel($this->db);
        $horasMeta = (int)(($pasante->horas_meta ?? 0) > 0 ? $pasante->horas_meta : 1440);
        $proRata   = $asistenciaModel->calcularProgresoProRata($userId, $horasMeta);

        $this->view('pasante/analiticas', [
            'title'   => 'Mis Analíticas',
            'pasante' => $pasante,
            'porMes'  => $porMes,
            'totales' => $totales,
            'proRata' => $proRata,
        ]);
    }

    /**
     * GET /pasante/misEvaluaciones
     * Evaluaciones recibidas por el pasante (solo lectura).
     */
    public function misEvaluaciones(): void
    {
        if (Session::get('role_id') != 3) {
            $this->redirect('/dashboard');
            return;
        }

        $userId  = (int)Session::get('user_id');
        $pasante = $this->pasanteModel->getByUsuarioId($userId);

        $this->db->query("
            SELECT
                e.id,
                e.lapso_academico,
                e.promedio_final,
                e.observaciones,
                DATE_FORMAT(e.fecha_evaluacion, '%d/%m/%Y') AS fecha_formateada,
                CONCAT(tp.nombres, ' ', tp.apellidos)       AS tutor_nombre
            FROM evaluaciones e
            LEFT JOIN datos_personales tp ON tp.usuario_id = e.tutor_id
            WHERE e.pasante_id = :uid
            ORDER BY e.fecha_evaluacion DESC
        ");
        $this->db->bind(':uid', $userId);
        $evaluaciones = $this->db->resultSet();

        $this->view('pasante/mis_evaluaciones', [
            'title'       => 'Mis Evaluaciones',
            'pasante'     => $pasante,
            'evaluaciones'=> $evaluaciones,
        ]);
    }

    /**
     * GET /pasante/constancia
     * Vista de constancia — muestra info y botón de descarga.
     * Si no está asignado, la vista muestra SweetAlert de aviso.
     */
    public function constancia(): void
    {
        if (Session::get('role_id') != 3) {
            $this->redirect('/dashboard');
            return;
        }

        $userId  = (int)Session::get('user_id');
        $pasante = $this->_getConstanciaData($userId);

        $this->view('pasante/constancia', [
            'title'   => 'Mi Constancia',
            'pasante' => $pasante,
        ]);
    }

    /**
     * GET /pasante/descargarConstancia
     * Genera y descarga el PDF de constancia.
     */
    public function descargarConstancia(): void
    {
        if (Session::get('role_id') != 3) {
            $this->redirect('/dashboard');
            return;
        }

        $userId  = (int)Session::get('user_id');
        $pasante = $this->_getConstanciaData($userId);

        if (!$pasante || in_array($pasante->estado_pasantia ?? '', ['Sin Asignar', 'Pendiente', ''])) {
            http_response_code(403);
            exit('No autorizado');
        }

        require_once '../app/lib/PdfGenerator.php';
        $pdf = new PdfGenerator();

        ob_start();
        include '../app/views/pasante/pdf_constancia.php';
        $html = ob_get_clean();

        $cedula = $pasante->cedula ?? $userId;
        $pdf->renderDomPdf($html, "Constancia_Pasantia_{$cedula}", false);
    }

    /**
     * Query compartida entre constancia() y descargarConstancia().
     */
    private function _getConstanciaData(int $userId): ?object
    {
        $this->db->query("
            SELECT
                dp.nombres, dp.apellidos, dp.telefono,
                u.cedula, u.correo AS email,
                dpa.fecha_inicio_pasantia,
                dpa.fecha_fin_estimada,
                dpa.horas_meta,
                dpa.estado_pasantia,
                d.nombre  AS departamento,
                CONCAT(tp.nombres, ' ', tp.apellidos) AS tutor_nombre,
                tu.cedula AS tutor_cedula
            FROM usuarios u
            JOIN datos_personales  dp  ON dp.usuario_id  = u.id
            LEFT JOIN datos_pasante     dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos d  ON d.id = dpa.departamento_asignado_id
            LEFT JOIN datos_personales tp ON tp.usuario_id = dpa.tutor_id
            LEFT JOIN usuarios         tu ON tu.id = dpa.tutor_id
            WHERE u.id = :uid AND u.rol_id = 3
            LIMIT 1
        ");
        $this->db->bind(':uid', $userId);
        return $this->db->single() ?: null;
    }
}
