<?php
/**
 * PeriodosController — Gestión de Períodos Académicos
 *
 * URL base: /periodos  — exclusiva para Administradores (rol_id = 1).
 * Permite crear, editar, cerrar períodos y asignar pasantes a cohortes.
 */

declare(strict_types=1);

class PeriodosController extends Controller
{
    private Database $db;

    public function __construct()
    {
        Session::start();
        AuthMiddleware::require();

        if (!RoleMiddleware::hasAnyRole([1])) {
            RoleMiddleware::redirectToRoleDashboard(Session::get('role_id'));
        }

        $this->db = Database::getInstance();
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /periodos — lista todos los períodos con conteo de pasantes
    // ─────────────────────────────────────────────────────────────────
    public function index(): void
    {
        $this->db->query("
            SELECT
                pa.*,
                COUNT(dpa.usuario_id) AS total_pasantes
            FROM periodos_academicos pa
            LEFT JOIN datos_pasante dpa ON dpa.periodo_id = pa.id
            GROUP BY pa.id
            ORDER BY pa.fecha_inicio DESC
        ");
        $periodos = $this->db->resultSet();

        // KPI aggregates
        $totalPeriodos    = count($periodos);
        $totalActivos     = 0;
        $totalCerrados    = 0;
        $totalPasantesSum = 0;

        foreach ($periodos as $p) {
            if ($p->estado === 'Activo')   $totalActivos++;
            if ($p->estado === 'Cerrado')  $totalCerrados++;
            $totalPasantesSum += (int)($p->total_pasantes ?? 0);
        }

        $csrf = Session::generateCsrfToken();

        $this->view('periodos/index', [
            'periodos'         => $periodos,
            'totalPeriodos'    => $totalPeriodos,
            'totalActivos'     => $totalActivos,
            'totalCerrados'    => $totalCerrados,
            'totalPasantesSum' => $totalPasantesSum,
            'csrf'             => $csrf,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /periodos/ver/ID — detalle con pasantes del período
    // ─────────────────────────────────────────────────────────────────
    public function ver(int $id = 0): void
    {
        if ($id <= 0) {
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        // Datos del período
        $this->db->query("SELECT * FROM periodos_academicos WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $periodo = $this->db->single();

        if (!$periodo) {
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        // Pasantes asignados al período con sus datos y conteo de asistencias
        $this->db->query("
            SELECT
                u.id,
                u.correo,
                u.estado                                        AS estado_usuario,
                dp.nombres,
                dp.apellidos,
                u.cedula,
                COALESCE(dpa.estado_pasantia, 'Sin Asignar')   AS estado_pasantia,
                COALESCE(dpa.horas_acumuladas, 0)              AS horas_acumuladas,
                COALESCE(dpa.horas_meta, 0)                    AS horas_meta,
                dpa.fecha_inicio_pasantia,
                dpa.fecha_fin_estimada,
                dept.nombre                                     AS departamento,
                inst.nombre                                     AS institucion,
                COUNT(CASE
                    WHEN a.estado IN ('Presente','Tardanza') THEN 1
                END)                                            AS dias_presentes,
                COUNT(a.id)                                     AS total_dias_registrados
            FROM usuarios u
            LEFT JOIN datos_personales dp   ON dp.usuario_id   = u.id
            LEFT JOIN datos_pasante    dpa  ON dpa.usuario_id  = u.id
            LEFT JOIN departamentos    dept ON dept.id = dpa.departamento_asignado_id
            LEFT JOIN instituciones    inst ON inst.id = dpa.institucion_procedencia
            LEFT JOIN asistencias      a    ON a.pasante_id    = u.id
            WHERE dpa.periodo_id = :pid AND u.rol_id = 3
            GROUP BY u.id
            ORDER BY dp.apellidos ASC, dp.nombres ASC
        ");
        $this->db->bind(':pid', $id);
        $pasantes = $this->db->resultSet();

        // KPIs del período
        $kpiPasantes   = count($pasantes);
        $kpiActivos    = 0;
        $kpiFinalizados = 0;
        $sumaAsistencia = 0;

        foreach ($pasantes as $p) {
            if ($p->estado_pasantia === 'Activo')     $kpiActivos++;
            if ($p->estado_pasantia === 'Finalizado') $kpiFinalizados++;
            if ($p->total_dias_registrados > 0) {
                $sumaAsistencia += round(($p->dias_presentes / $p->total_dias_registrados) * 100);
            }
        }
        $promAsistencia = $kpiPasantes > 0
            ? round($sumaAsistencia / $kpiPasantes)
            : 0;

        // Pasantes sin período asignado (para el modal de asignación)
        $this->db->query("
            SELECT u.id, dp.nombres, dp.apellidos, u.cedula
            FROM usuarios u
            LEFT JOIN datos_personales dp  ON dp.usuario_id = u.id
            LEFT JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
            WHERE u.rol_id = 3 AND u.estado = 'activo'
              AND (dpa.periodo_id IS NULL OR dpa.periodo_id = 0)
            ORDER BY dp.apellidos ASC
        ");
        $pasantesSinPeriodo = $this->db->resultSet();

        // Agrupar pasantes por departamento para la vista en acordeón
        $pasantesPorDepto = [];
        foreach ($pasantes as $p) {
            $depto = $p->departamento ?? 'Sin Departamento';
            $pasantesPorDepto[$depto][] = $p;
        }
        ksort($pasantesPorDepto);

        $csrf = Session::generateCsrfToken();

        $this->view('periodos/ver', [
            'periodo'            => $periodo,
            'pasantesPorDepto'   => $pasantesPorDepto,
            'kpiPasantes'        => $kpiPasantes,
            'kpiActivos'         => $kpiActivos,
            'kpiFinalizados'     => $kpiFinalizados,
            'promAsistencia'     => $promAsistencia,
            'pasantesSinPeriodo' => $pasantesSinPeriodo,
            'csrf'               => $csrf,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // POST /periodos/crear
    // ─────────────────────────────────────────────────────────────────
    public function crear(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de seguridad inválido. Recarga la página.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        $nombre      = trim($_POST['nombre']      ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $fechaInicio = trim($_POST['fecha_inicio'] ?? '');
        $fechaFin    = trim($_POST['fecha_fin']    ?? '');

        if (empty($nombre) || empty($fechaInicio) || empty($fechaFin)) {
            Session::setFlash('error', 'Nombre, fecha de inicio y fecha fin son obligatorios.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        if ($fechaFin <= $fechaInicio) {
            Session::setFlash('error', 'La fecha de fin debe ser posterior a la de inicio.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        $this->db->query("
            INSERT INTO periodos_academicos
                (nombre, descripcion, fecha_inicio, fecha_fin, estado)
            VALUES
                (:nombre, :descripcion, :fecha_inicio, :fecha_fin, 'Planificado')
        ");
        $this->db->bind(':nombre',       $nombre);
        $this->db->bind(':descripcion',  $descripcion ?: null);
        $this->db->bind(':fecha_inicio', $fechaInicio);
        $this->db->bind(':fecha_fin',    $fechaFin);

        if ($this->db->execute()) {
            Session::setFlash('success', 'Período académico creado exitosamente.');
        } else {
            Session::setFlash('error', 'Error al crear el período. Intenta de nuevo.');
        }

        header('Location: ' . URLROOT . '/periodos');
        exit;
    }

    // ─────────────────────────────────────────────────────────────────
    // POST /periodos/editar
    // ─────────────────────────────────────────────────────────────────
    public function editar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de seguridad inválido.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        $id          = (int)($_POST['periodo_id'] ?? 0);
        $nombre      = trim($_POST['nombre']       ?? '');
        $descripcion = trim($_POST['descripcion']  ?? '');
        $fechaInicio = trim($_POST['fecha_inicio']  ?? '');
        $fechaFin    = trim($_POST['fecha_fin']     ?? '');

        if ($id <= 0 || empty($nombre) || empty($fechaInicio) || empty($fechaFin)) {
            Session::setFlash('error', 'Datos incompletos para editar el período.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        // Solo editar si NO está Cerrado
        $this->db->query("SELECT estado FROM periodos_academicos WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $check = $this->db->single();

        if (!$check || $check->estado === 'Cerrado') {
            Session::setFlash('error', 'No se puede editar un período cerrado.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        if ($fechaFin <= $fechaInicio) {
            Session::setFlash('error', 'La fecha de fin debe ser posterior a la de inicio.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        $this->db->query("
            UPDATE periodos_academicos
            SET nombre       = :nombre,
                descripcion  = :descripcion,
                fecha_inicio = :fecha_inicio,
                fecha_fin    = :fecha_fin
            WHERE id = :id AND estado != 'Cerrado'
        ");
        $this->db->bind(':nombre',       $nombre);
        $this->db->bind(':descripcion',  $descripcion ?: null);
        $this->db->bind(':fecha_inicio', $fechaInicio);
        $this->db->bind(':fecha_fin',    $fechaFin);
        $this->db->bind(':id',           $id);

        if ($this->db->execute()) {
            Session::setFlash('success', 'Período actualizado correctamente.');
        } else {
            Session::setFlash('error', 'Error al actualizar el período.');
        }

        header('Location: ' . URLROOT . '/periodos');
        exit;
    }

    // ─────────────────────────────────────────────────────────────────
    // POST /periodos/cerrar — cierra el período y desactiva usuarios
    // ─────────────────────────────────────────────────────────────────
    public function cerrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de seguridad inválido.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        $id = (int)($_POST['periodo_id'] ?? 0);

        if ($id <= 0) {
            Session::setFlash('error', 'ID de período inválido.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        // Verificar que existe y no está ya cerrado
        $this->db->query("SELECT id, estado FROM periodos_academicos WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $periodo = $this->db->single();

        if (!$periodo || $periodo->estado === 'Cerrado') {
            Session::setFlash('error', 'El período ya está cerrado o no existe.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        // 1. Cambiar estado del período a 'Cerrado'
        $this->db->query("UPDATE periodos_academicos SET estado = 'Cerrado' WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();

        // 2. Marcar pasantes del período como 'Finalizado'
        $this->db->query("
            UPDATE datos_pasante SET estado_pasantia = 'Finalizado'
            WHERE periodo_id = :pid AND estado_pasantia = 'Activo'
        ");
        $this->db->bind(':pid', $id);
        $this->db->execute();

        // 3. Deshabilitar usuarios pasantes del período
        $this->db->query("
            UPDATE usuarios SET estado = 'inactivo'
            WHERE id IN (
                SELECT usuario_id FROM datos_pasante WHERE periodo_id = :pid
            )
            AND rol_id = 3
        ");
        $this->db->bind(':pid', $id);
        $this->db->execute();

        Session::setFlash('success', 'Período cerrado. Los pasantes asignados han sido deshabilitados.');
        header('Location: ' . URLROOT . '/periodos');
        exit;
    }

    // ─────────────────────────────────────────────────────────────────
    // POST /periodos/asignarPasante — asigna un pasante al período
    // ─────────────────────────────────────────────────────────────────
    public function asignarPasante(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de seguridad inválido.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        $periodoId = (int)($_POST['periodo_id'] ?? 0);
        $usuarioId = (int)($_POST['usuario_id'] ?? 0);

        if ($periodoId <= 0 || $usuarioId <= 0) {
            Session::setFlash('error', 'Datos inválidos para la asignación.');
            header('Location: ' . URLROOT . '/periodos/ver/' . $periodoId);
            exit;
        }

        // Verificar que el período existe y está Activo o Planificado
        $this->db->query("SELECT estado FROM periodos_academicos WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $periodoId);
        $periodo = $this->db->single();

        if (!$periodo || $periodo->estado === 'Cerrado') {
            Session::setFlash('error', 'No se puede asignar pasantes a un período cerrado.');
            header('Location: ' . URLROOT . '/periodos/ver/' . $periodoId);
            exit;
        }

        // UPSERT: insertar o actualizar periodo_id en datos_pasante
        $this->db->query("
            INSERT INTO datos_pasante (usuario_id, periodo_id)
            VALUES (:uid, :pid)
            ON DUPLICATE KEY UPDATE periodo_id = VALUES(periodo_id)
        ");
        $this->db->bind(':uid', $usuarioId);
        $this->db->bind(':pid', $periodoId);

        if ($this->db->execute()) {
            Session::setFlash('success', 'Pasante asignado al período correctamente.');
        } else {
            Session::setFlash('error', 'Error al asignar el pasante.');
        }

        header('Location: ' . URLROOT . '/periodos/ver/' . $periodoId);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /periodos/cartaCulminacion/PASANTE_ID — PDF carta formal
    // ─────────────────────────────────────────────────────────────────
    public function cartaCulminacion(int $pasanteId = 0): void
    {
        if ($pasanteId <= 0) {
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        // Datos del pasante
        $this->db->query("
            SELECT
                u.id,
                dp.nombres,
                dp.apellidos,
                u.cedula,
                dept.nombre                         AS departamento,
                inst.nombre                         AS institucion,
                dpa.fecha_inicio_pasantia           AS fecha_inicio,
                dpa.fecha_fin_estimada              AS fecha_fin,
                dpa.periodo_id,
                COALESCE(dpa.estado_pasantia, '')   AS estado_pasantia
            FROM usuarios u
            LEFT JOIN datos_personales dp  ON dp.usuario_id  = u.id
            LEFT JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos    dept ON dept.id = dpa.departamento_asignado_id
            LEFT JOIN instituciones    inst ON inst.id = dpa.institucion_procedencia
            WHERE u.id = :uid AND u.rol_id = 3
            LIMIT 1
        ");
        $this->db->bind(':uid', $pasanteId);
        $pasante = $this->db->single();

        if (!$pasante) {
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        // Datos del período asociado
        $periodo = null;
        if (!empty($pasante->periodo_id)) {
            $this->db->query("SELECT * FROM periodos_academicos WHERE id = :pid LIMIT 1");
            $this->db->bind(':pid', $pasante->periodo_id);
            $periodo = $this->db->single();
        }

        // Total de días presentes (estado Presente o Tardanza)
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM asistencias
            WHERE pasante_id = :uid AND estado IN ('Presente','Tardanza')
        ");
        $this->db->bind(':uid', $pasanteId);
        $row = $this->db->single();
        $totalPresentes = (int)($row->total ?? 0);

        require_once '../app/lib/PdfGenerator.php';
        $pdf = new PdfGenerator();

        ob_start();
        include APPROOT . '/views/periodos/pdf_carta_culminacion.php';
        $html = ob_get_clean();

        $pdf->renderDomPdf(
            $html,
            'Carta_Culminacion_' . ($pasante->cedula ?? $pasanteId),
            true
        );
    }

    // ─────────────────────────────────────────────────────────────────
    // POST /periodos/desactivarPasante/ID — Finaliza a un pasante
    // ─────────────────────────────────────────────────────────────────
    public function desactivarPasante(int $pasanteId = 0): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de seguridad inválido.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        $periodoId = (int)($_POST['periodo_id'] ?? 0);

        if ($pasanteId <= 0) {
            Session::setFlash('error', 'ID de pasante inválido.');
            header('Location: ' . URLROOT . '/periodos/ver/' . $periodoId);
            exit;
        }

        // Marcar finalizado y desactivar su usuario
        $this->db->query("UPDATE datos_pasante SET estado_pasantia = 'Finalizado' WHERE usuario_id = :uid");
        $this->db->bind(':uid', $pasanteId);
        $this->db->execute();

        $this->db->query("UPDATE usuarios SET estado = 'inactivo' WHERE id = :uid AND rol_id = 3");
        $this->db->bind(':uid', $pasanteId);
        $this->db->execute();

        Session::setFlash('success', 'El pasante ha sido desactivado y su pasantía marcada como finalizada.');
        header('Location: ' . URLROOT . '/periodos/ver/' . $periodoId);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /periodos/informeGeneral/ID — PDF reporte global del periodo
    // ─────────────────────────────────────────────────────────────────
    public function informeGeneral(int $id = 0): void
    {
        if ($id <= 0) {
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        // Obtener datos del período
        $this->db->query("SELECT * FROM periodos WHERE id = :id");
        $this->db->bind(':id', $id);
        $periodo = $this->db->single();

        if (!$periodo) {
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        // Obtener pasantes
        $this->db->query("
            SELECT
                dp.nombres,
                dp.apellidos,
                u.cedula,
                COALESCE(dpa.estado_pasantia, 'Sin Asignar')   AS estado_pasantia,
                COALESCE(dpa.horas_acumuladas, 0)              AS horas_acumuladas,
                COALESCE(dpa.horas_meta, 0)                    AS horas_meta,
                dept.nombre                                     AS departamento,
                COUNT(CASE WHEN a.estado IN ('Presente','Tardanza') THEN 1 END) AS dias_presentes
            FROM usuarios u
            LEFT JOIN datos_personales dp   ON dp.usuario_id   = u.id
            LEFT JOIN datos_pasante    dpa  ON dpa.usuario_id  = u.id
            LEFT JOIN departamentos    dept ON dept.id = dpa.departamento_asignado_id
            LEFT JOIN asistencias      a    ON a.pasante_id    = u.id
            WHERE dpa.periodo_id = :pid AND u.rol_id = 3
            GROUP BY u.id
            ORDER BY dept.nombre ASC, dp.apellidos ASC
        ");
        $this->db->bind(':pid', $id);
        $pasantes = $this->db->resultSet();

        require_once '../app/lib/PdfGenerator.php';
        $pdf = new PdfGenerator();

        $html = '<h1 style="text-align:center; color:#1e3a8a;">Informe de Cohorte: ' . htmlspecialchars($periodo->nombre) . '</h1>';
        $html .= '<p style="text-align:center;">Estado: ' . $periodo->estado . ' | Fechas: ' . date('d/m/Y', strtotime($periodo->fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($periodo->fecha_fin)) . '</p>';
        
        $html .= '<table border="1" cellpadding="8" cellspacing="0" width="100%" style="border-collapse: collapse; font-family: sans-serif; font-size: 12px;">';
        $html .= '<thead style="background-color: #f1f5f9;"><tr>';
        $html .= '<th>Cédula</th><th>Apellidos y Nombres</th><th>Departamento</th><th>Estado</th><th>Horas</th><th>Asistencias</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($pasantes as $p) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($p->cedula ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars(($p->apellidos ?? '') . ', ' . ($p->nombres ?? '')) . '</td>';
            $html .= '<td>' . htmlspecialchars($p->departamento ?? 'Sin Asignar') . '</td>';
            $html .= '<td>' . htmlspecialchars($p->estado_pasantia) . '</td>';
            $html .= '<td>' . (int)$p->horas_acumuladas . ' / ' . (int)$p->horas_meta . '</td>';
            $html .= '<td>' . (int)$p->dias_presentes . ' días</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        $pdf->renderDomPdf($html, 'Informe_Cohorte_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $periodo->nombre), true);
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /periodos/reporteAsistencia/PASANTE_ID — PDF asistencias
    // ─────────────────────────────────────────────────────────────────
    public function reporteAsistencia(int $pasanteId = 0): void
    {
        if ($pasanteId <= 0) {
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        $this->db->query("
            SELECT dp.nombres, dp.apellidos, u.cedula, d.nombre AS departamento
            FROM usuarios u
            LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
            LEFT JOIN datos_pasante dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos d ON d.id = dpa.departamento_asignado_id
            WHERE u.id = :uid AND u.rol_id = 3
        ");
        $this->db->bind(':uid', $pasanteId);
        $pasante = $this->db->single();

        if (!$pasante) {
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        $this->db->query("
            SELECT fecha, hora_registro, estado, metodo, motivo_justificacion
            FROM asistencias
            WHERE pasante_id = :uid
            ORDER BY fecha ASC, hora_registro ASC
        ");
        $this->db->bind(':uid', $pasanteId);
        $asistencias = $this->db->resultSet();

        require_once '../app/lib/PdfGenerator.php';
        $pdf = new PdfGenerator();

        $nombre = htmlspecialchars(($pasante->nombres ?? '') . ' ' . ($pasante->apellidos ?? ''));
        $html = '<h2 style="text-align:center; color:#1e3a8a;">Reporte de Asistencias</h2>';
        $html .= '<p><strong>Pasante:</strong> ' . $nombre . '<br>';
        $html .= '<strong>Cédula:</strong> ' . htmlspecialchars($pasante->cedula ?? '') . '<br>';
        $html .= '<strong>Departamento:</strong> ' . htmlspecialchars($pasante->departamento ?? 'Sin Asignar') . '</p>';

        $html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%" style="border-collapse: collapse; font-family: sans-serif; font-size: 11px;">';
        $html .= '<thead style="background-color: #f1f5f9;"><tr>';
        $html .= '<th>Fecha</th><th>Hora</th><th>Estado</th><th>Método</th><th>Observación</th>';
        $html .= '</tr></thead><tbody>';

        $totalPresentes = 0;
        foreach ($asistencias as $a) {
            if ($a->estado === 'Presente' || $a->estado === 'Tardanza') $totalPresentes++;
            $html .= '<tr>';
            $html .= '<td>' . date('d/m/Y', strtotime($a->fecha)) . '</td>';
            $html .= '<td>' . ($a->hora_registro ? date('h:i A', strtotime($a->hora_registro)) : '—') . '</td>';
            $html .= '<td>' . htmlspecialchars($a->estado) . '</td>';
            $html .= '<td>' . htmlspecialchars($a->metodo ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($a->motivo_justificacion ?? '') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        $html .= '<p style="text-align:right; margin-top:20px;"><strong>Total días laborados:</strong> ' . $totalPresentes . '</p>';

        $pdf->renderDomPdf($html, 'Asistencias_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombre), true);
    }

    // ─────────────────────────────────────────────────────────────────
    // POST /periodos/activar
    // ─────────────────────────────────────────────────────────────────
    public function activar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de seguridad inválido.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        $id = (int)($_POST['periodo_id'] ?? 0);
        if ($id <= 0) {
            Session::setFlash('error', 'ID de período inválido.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        try {
            $this->db->query("SELECT id FROM periodos_academicos WHERE id = :id AND LOWER(estado) = 'planificado'");
            $this->db->bind(':id', $id);
            if (!$this->db->single()) {
                Session::setFlash('error', 'El período no existe o no está en estado Planificado.');
                header('Location: ' . URLROOT . '/periodos');
                exit;
            }

            // Move current Activo to Cerrado
            $this->db->query("UPDATE periodos_academicos SET estado = 'Cerrado' WHERE LOWER(estado) = 'activo'");
            $this->db->execute();

            // Set the new selected period to Activo
            $this->db->query("UPDATE periodos_academicos SET estado = 'Activo' WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            Session::setFlash('success', '¡Cohorte iniciada! El nuevo período ahora está Activo.');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Ocurrió un error en la base de datos al activar el período.');
        }

        header('Location: ' . URLROOT . '/periodos');
        exit;
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /periodos/buscarGlobal (AJAX)
    // ─────────────────────────────────────────────────────────────────
    public function buscarGlobal(): void
    {
        header('Content-Type: application/json');
        
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            echo json_encode([]);
            exit;
        }

        $this->db->query("
            SELECT 
                u.id, u.cedula, dp.nombres, dp.apellidos,
                pa.nombre AS periodo_nombre, pa.estado AS periodo_estado,
                COALESCE(dpa.estado_pasantia, 'Sin Asignar') AS estado_pasantia,
                COALESCE(dpa.horas_acumuladas, 0) AS horas_acumuladas,
                COALESCE(dpa.horas_meta, 1440) AS horas_meta,
                dept.nombre AS departamento
            FROM usuarios u
            LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
            LEFT JOIN datos_pasante dpa ON dpa.usuario_id = u.id
            LEFT JOIN periodos_academicos pa ON pa.id = dpa.periodo_id
            LEFT JOIN departamentos dept ON dept.id = dpa.departamento_asignado_id
            WHERE u.rol_id = 3 
            AND (u.cedula LIKE :q OR CONCAT(dp.nombres, ' ', dp.apellidos) LIKE :q)
            LIMIT 15
        ");
        $this->db->bind(':q', "%$q%");
        $resultados = $this->db->resultSet();

        echo json_encode($resultados);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /periodos/reporteHistoricoPasante/ID
    // ─────────────────────────────────────────────────────────────────
    public function reporteHistoricoPasante(int $id = 0): void
    {
        if ($id <= 0) {
            die("ID inválido");
        }

        // Obtener pasante
        $this->db->query("
            SELECT u.cedula, dp.nombres, dp.apellidos,
                   pa.nombre AS periodo_nombre, pa.estado AS periodo_estado,
                   COALESCE(dpa.estado_pasantia, 'Sin Asignar') AS estado_pasantia,
                   COALESCE(dpa.horas_acumuladas, 0) AS horas_acumuladas,
                   COALESCE(dpa.horas_meta, 1440) AS horas_meta,
                   dept.nombre AS departamento
            FROM usuarios u
            LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
            LEFT JOIN datos_pasante dpa ON dpa.usuario_id = u.id
            LEFT JOIN periodos_academicos pa ON pa.id = dpa.periodo_id
            LEFT JOIN departamentos dept ON dept.id = dpa.departamento_asignado_id
            WHERE u.id = :id AND u.rol_id = 3
        ");
        $this->db->bind(':id', $id);
        $pasante = $this->db->single();

        if (!$pasante) die("Pasante no encontrado.");

        require_once '../app/lib/PdfGenerator.php';
        $pdf = new PdfGenerator();

        $nombreCompleto = htmlspecialchars(($pasante->nombres ?? '') . ' ' . ($pasante->apellidos ?? ''));
        
        $html = '<h1 style="text-align:center; color:#1e3a8a;">Expediente Histórico del Pasante</h1>';
        $html .= '<hr>';
        $html .= '<h3><strong>Datos Personales</strong></h3>';
        $html .= '<p><strong>Pasante:</strong> ' . $nombreCompleto . '<br>';
        $html .= '<strong>Cédula:</strong> V-' . htmlspecialchars($pasante->cedula ?? 'N/A') . '</p>';
        
        $html .= '<h3><strong>Datos Académicos / Vinculación</strong></h3>';
        $html .= '<p><strong>Cohorte / Período:</strong> ' . htmlspecialchars($pasante->periodo_nombre ?? 'No Asignado') . ' (Estado Período: ' . htmlspecialchars($pasante->periodo_estado ?? 'N/A') . ')<br>';
        $html .= '<strong>Departamento Asignado:</strong> ' . htmlspecialchars($pasante->departamento ?? 'N/A') . '<br>';
        $html .= '<strong>Estado Actual Pasantía:</strong> ' . htmlspecialchars($pasante->estado_pasantia) . '<br>';
        $html .= '<strong>Horas Acumuladas:</strong> ' . (int)$pasante->horas_acumuladas . ' de ' . (int)$pasante->horas_meta . '</p>';

        // Consultar asistencias para sumarizar
        $this->db->query("SELECT COUNT(id) as total_registros, 
                                 SUM(CASE WHEN estado IN ('Presente', 'Tardanza') THEN 1 ELSE 0 END) as dias_asistidos
                          FROM asistencias WHERE pasante_id = :id");
        $this->db->bind(':id', $id);
        $asist = $this->db->single();

        $html .= '<h3><strong>Métricas Generales</strong></h3>';
        $html .= '<p><strong>Días Laborados Detectados:</strong> ' . (int)($asist->dias_asistidos ?? 0) . ' de ' . (int)($asist->total_registros ?? 0) . ' registros procesados.</p>';
        
        $html .= '<br><br><br><p style="text-align:center; font-size:11px; color:#64748b; border-top:1px solid #ccc; padding-top:10px;">Reporte generado automáticamente por el Sistema de Gestión de Pasantías (SGP) - ' . date('d/m/Y H:i:s') . '</p>';

        $pdf->renderDomPdf($html, 'Expediente_SGP_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombreCompleto), true);
    }
}
