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

        if (!RoleMiddleware::hasAnyRole([0, 1])) {
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
            LEFT JOIN instituciones    inst ON inst.id = dpa.institucion_id
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
        $tipo        = in_array($_POST['tipo'] ?? '', ['Regular', 'Corto']) ? $_POST['tipo'] : 'Regular';

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

        // Estado inicial: Planificado o Activo (si el admin quiere activarlo de inmediato)
        $estadoInicial = ($_POST['estado_inicial'] ?? '') === 'Activo' ? 'Activo' : 'Planificado';

        // Validar unicidad: no puede haber otro período del mismo tipo en estado Planificado o Activo
        $this->db->query("
            SELECT COUNT(*) AS total FROM periodos_academicos
            WHERE tipo = :tipo AND estado IN ('Planificado','Activo')
        ");
        $this->db->bind(':tipo', $tipo);
        $existe = $this->db->single();
        if ((int)($existe->total ?? 0) > 0) {
            Session::setFlash('error', "Ya existe un período de tipo «{$tipo}» activo o planificado. Ciérralo antes de crear uno nuevo.");
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        // Si se activa de inmediato, cerrar cualquier activo del mismo tipo primero
        if ($estadoInicial === 'Activo') {
            $this->db->query("UPDATE periodos_academicos SET estado = 'Cerrado' WHERE tipo = :tipo AND estado = 'Activo'");
            $this->db->bind(':tipo', $tipo);
            $this->db->execute();
        }

        $this->db->query("
            INSERT INTO periodos_academicos
                (nombre, tipo, descripcion, fecha_inicio, fecha_fin, estado)
            VALUES
                (:nombre, :tipo, :descripcion, :fecha_inicio, :fecha_fin, :estado)
        ");
        $this->db->bind(':nombre',       $nombre);
        $this->db->bind(':tipo',         $tipo);
        $this->db->bind(':descripcion',  $descripcion ?: null);
        $this->db->bind(':fecha_inicio', $fechaInicio);
        $this->db->bind(':fecha_fin',    $fechaFin);
        $this->db->bind(':estado',       $estadoInicial);

        if ($this->db->execute()) {
            $msg = $estadoInicial === 'Activo'
                ? 'Período académico creado y activado correctamente.'
                : 'Período académico creado en estado Planificado.';
            Session::setFlash('success', $msg);
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

        if (!$check || strtolower($check->estado) === 'cerrado') {
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
        $this->db->query("SELECT id, nombre, estado FROM periodos_academicos WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $periodo = $this->db->single();

        if (!$periodo || $periodo->estado === 'Cerrado') {
            Session::setFlash('error', 'El período ya está cerrado o no existe.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        // Validar: no cerrar si hay pasantes con pasantía activa
        $this->db->query("
            SELECT COUNT(*) AS total FROM datos_pasante
            WHERE periodo_id = :pid AND estado_pasantia = 'Activo'
        ");
        $this->db->bind(':pid', $id);
        $activos = (int)($this->db->single()->total ?? 0);

        $forzar = ($_POST['forzar'] ?? '') === '1';

        if ($activos > 0 && !$forzar) {
            Session::setFlash('error', "El período «{$periodo->nombre}» tiene {$activos} pasante(s) con pasantía activa. Usa el botón «Forzar cierre» si deseas cerrarlo de todas formas.");
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

        if (!$periodo || strtolower($periodo->estado) === 'cerrado') {
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
            LEFT JOIN instituciones    inst ON inst.id = dpa.institucion_id
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
    // GET /periodos/reporteAsistencia/PASANTE_ID?desde=Y-m-d&hasta=Y-m-d
    // ─────────────────────────────────────────────────────────────────
    public function reporteAsistencia(int $pasanteId = 0): void
    {
        if ($pasanteId <= 0) {
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        $this->db->query("
            SELECT
                dp.nombres, dp.apellidos, u.cedula,
                d.nombre  AS departamento,
                inst.nombre AS institucion,
                dpa.horas_meta, dpa.horas_acumuladas,
                dpa.estado_pasantia,
                pa.nombre AS periodo_nombre,
                pa.fecha_inicio AS periodo_inicio,
                pa.fecha_fin    AS periodo_fin
            FROM usuarios u
            LEFT JOIN datos_personales  dp  ON dp.usuario_id  = u.id
            LEFT JOIN datos_pasante     dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos     d   ON d.id  = dpa.departamento_asignado_id
            LEFT JOIN instituciones     inst ON inst.id = dpa.institucion_id
            LEFT JOIN periodos_academicos pa ON pa.id  = dpa.periodo_id
            WHERE u.id = :uid AND u.rol_id = 3
            LIMIT 1
        ");
        $this->db->bind(':uid', $pasanteId);
        $pasante = $this->db->single();

        if (!$pasante) {
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        // Rango de fechas (GET params, con fallback al período completo)
        $desde = trim($_GET['desde'] ?? '');
        $hasta = trim($_GET['hasta'] ?? '');

        if (empty($desde) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) {
            $desde = $pasante->periodo_inicio ?? date('Y-01-01');
        }
        if (empty($hasta) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            $hasta = $pasante->periodo_fin ?? date('Y-12-31');
        }

        $this->db->query("
            SELECT fecha, hora_registro, estado, metodo, motivo_justificacion
            FROM asistencias
            WHERE pasante_id = :uid
              AND fecha BETWEEN :desde AND :hasta
            ORDER BY fecha ASC, hora_registro ASC
        ");
        $this->db->bind(':uid',   $pasanteId);
        $this->db->bind(':desde', $desde);
        $this->db->bind(':hasta', $hasta);
        $asistencias = $this->db->resultSet();

        // Estadísticas del rango
        $totalRegistros = count($asistencias);
        $totalPresentes = 0;
        $totalAusentes  = 0;
        $totalJustif    = 0;
        foreach ($asistencias as $a) {
            if ($a->estado === 'Presente' || $a->estado === 'Tardanza') $totalPresentes++;
            elseif ($a->estado === 'Justificado') $totalJustif++;
            else $totalAusentes++;
        }
        $pctAsist = $totalRegistros > 0 ? round(($totalPresentes / $totalRegistros) * 100) : 0;

        require_once '../app/lib/PdfGenerator.php';
        $pdf = new PdfGenerator();

        $cintillo_path = $_SERVER['DOCUMENT_ROOT'] . '/proyecto_sgp/sgp/public/img/cintillo_isp_bolivar.jpg';
        if (!function_exists('imgToBase64')) {
            function imgToBase64(string $path): string {
                static $cache = [];
                if (isset($cache[$path])) return $cache[$path];
                if (file_exists($path)) {
                    $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    $mime = ($ext === 'jpg') ? 'jpeg' : $ext;
                    $cache[$path] = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                    return $cache[$path];
                }
                return $cache[$path] = '';
            }
        }
        $b64 = imgToBase64($cintillo_path);

        $nombreCompleto = htmlspecialchars(trim(($pasante->nombres ?? '') . ' ' . ($pasante->apellidos ?? '')));
        $desdeF = date('d/m/Y', strtotime($desde));
        $hastaF = date('d/m/Y', strtotime($hasta));

        $meses = ['','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

        $estadoColors = [
            'Presente'    => ['bg' => '#dcfce7', 'color' => '#166534'],
            'Tardanza'    => ['bg' => '#fef3c7', 'color' => '#92400e'],
            'Ausente'     => ['bg' => '#fee2e2', 'color' => '#991b1b'],
            'Justificado' => ['bg' => '#e0e7ff', 'color' => '#3730a3'],
        ];

        $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
        <style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body { font-family: Helvetica,Arial,sans-serif; font-size:11px; color:#1a1a1a; line-height:1.5; }
            .linea-navy { width:100%; height:3px; background:#162660; margin-bottom:18px; }
            .contenido  { padding:0 28px 28px; }
            .titulo-doc { text-align:center; font-size:13px; font-weight:bold; color:#162660; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px; }
            .subtitulo  { text-align:center; font-size:10px; color:#555; margin-bottom:20px; }
            .lugar-fecha { text-align:right; font-size:10.5px; color:#444; margin-bottom:18px; }
            .info-grid  { display:table; width:100%; border-collapse:collapse; margin-bottom:16px; }
            .info-cell  { display:table-cell; vertical-align:top; width:50%; padding:6px 10px; }
            .info-cell strong { color:#162660; display:block; font-size:9px; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px; }
            .kpi-row    { display:table; width:100%; border-collapse:separate; border-spacing:8px; margin-bottom:16px; }
            .kpi-box    { display:table-cell; text-align:center; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:8px 4px; }
            .kpi-val    { font-size:18px; font-weight:bold; color:#162660; display:block; }
            .kpi-lbl    { font-size:8.5px; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; }
            table.asist { width:100%; border-collapse:collapse; font-size:10.5px; }
            table.asist th { background:#162660; color:white; padding:6px 8px; text-align:left; font-size:9.5px; text-transform:uppercase; letter-spacing:0.4px; }
            table.asist td { padding:5px 8px; border-bottom:1px solid #e2e8f0; vertical-align:middle; }
            table.asist tr:nth-child(even) td { background:#f8fafc; }
            .badge { display:inline-block; padding:2px 8px; border-radius:12px; font-size:9px; font-weight:bold; }
            .pie   { margin-top:24px; border-top:1px solid #cbd5e1; padding-top:8px; font-size:8.5px; color:#9ca3af; text-align:center; }
        </style></head><body>';

        if ($b64) {
            $html .= '<div style="width:100%;line-height:0;"><img src="' . $b64 . '" style="width:100%;height:auto;display:block;" alt=""></div>';
        } else {
            $html .= '<div style="width:100%;padding:10px 20px;background:#162660;color:white;font-weight:bold;">Instituto de Salud Pública de Bolívar | SGP</div>';
        }

        $html .= '<div class="linea-navy"></div>';
        $html .= '<div class="contenido">';
        $html .= '<p class="lugar-fecha">Ciudad Bolívar, ' . date('d') . ' de ' . $meses[(int)date('m')] . ' de ' . date('Y') . '</p>';
        $html .= '<p class="titulo-doc">Reporte Individual de Asistencias</p>';
        $html .= '<p class="subtitulo">Período: ' . htmlspecialchars($pasante->periodo_nombre ?? 'N/D') . ' &nbsp;|&nbsp; ' . $desdeF . ' al ' . $hastaF . '</p>';

        $html .= '<table style="width:100%;border-collapse:collapse;border:1px solid #e2e8f0;border-radius:6px;margin-bottom:16px;font-size:10.5px;"><tbody>';
        $html .= '<tr><td style="padding:7px 12px;background:#f1f5f9;font-weight:bold;color:#374151;width:30%;">Nombre</td><td style="padding:7px 12px;">' . $nombreCompleto . '</td><td style="padding:7px 12px;background:#f1f5f9;font-weight:bold;color:#374151;width:25%;">Cédula</td><td style="padding:7px 12px;">V-' . htmlspecialchars($pasante->cedula ?? '—') . '</td></tr>';
        $html .= '<tr><td style="padding:7px 12px;background:#f1f5f9;font-weight:bold;color:#374151;">Departamento</td><td style="padding:7px 12px;">' . htmlspecialchars($pasante->departamento ?? '—') . '</td><td style="padding:7px 12px;background:#f1f5f9;font-weight:bold;color:#374151;">Estado</td><td style="padding:7px 12px;">' . htmlspecialchars($pasante->estado_pasantia ?? '—') . '</td></tr>';
        $html .= '<tr><td style="padding:7px 12px;background:#f1f5f9;font-weight:bold;color:#374151;">Institución</td><td style="padding:7px 12px;" colspan="3">' . htmlspecialchars($pasante->institucion ?? '—') . '</td></tr>';
        $html .= '</tbody></table>';

        // KPIs
        $html .= '<table style="width:100%;border-collapse:separate;border-spacing:8px 0;margin-bottom:16px;"><tr>';
        $html .= '<td style="text-align:center;background:#dcfce7;border:1px solid #bbf7d0;border-radius:6px;padding:8px;"><span style="font-size:18px;font-weight:bold;color:#166534;display:block;">' . $totalPresentes . '</span><span style="font-size:8.5px;color:#166534;text-transform:uppercase;">Días Asistidos</span></td>';
        $html .= '<td style="text-align:center;background:#fee2e2;border:1px solid #fecaca;border-radius:6px;padding:8px;"><span style="font-size:18px;font-weight:bold;color:#991b1b;display:block;">' . $totalAusentes . '</span><span style="font-size:8.5px;color:#991b1b;text-transform:uppercase;">Ausencias</span></td>';
        $html .= '<td style="text-align:center;background:#e0e7ff;border:1px solid #c7d2fe;border-radius:6px;padding:8px;"><span style="font-size:18px;font-weight:bold;color:#3730a3;display:block;">' . $totalJustif . '</span><span style="font-size:8.5px;color:#3730a3;text-transform:uppercase;">Justificados</span></td>';
        $html .= '<td style="text-align:center;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:8px;"><span style="font-size:18px;font-weight:bold;color:#1e3a8a;display:block;">' . $pctAsist . '%</span><span style="font-size:8.5px;color:#64748b;text-transform:uppercase;">Tasa Asist.</span></td>';
        $html .= '</tr></table>';

        // Tabla de registros
        $html .= '<table class="asist"><thead><tr><th>#</th><th>Fecha</th><th>Día</th><th>Hora</th><th>Estado</th><th>Método</th><th>Observación</th></tr></thead><tbody>';

        $diasES = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
        $rowNum = 0;
        foreach ($asistencias as $a) {
            $rowNum++;
            $ts   = strtotime($a->fecha);
            $dia  = $diasES[(int)date('w', $ts)];
            $colores = $estadoColors[$a->estado] ?? ['bg' => '#f1f5f9', 'color' => '#475569'];
            $badge = '<span style="background:' . $colores['bg'] . ';color:' . $colores['color'] . ';padding:2px 8px;border-radius:12px;font-size:9px;font-weight:bold;">' . htmlspecialchars($a->estado) . '</span>';
            $html .= '<tr>';
            $html .= '<td style="color:#94a3b8;font-size:9.5px;">' . $rowNum . '</td>';
            $html .= '<td>' . date('d/m/Y', $ts) . '</td>';
            $html .= '<td style="color:#64748b;">' . $dia . '</td>';
            $html .= '<td>' . ($a->hora_registro ? date('h:i A', strtotime($a->hora_registro)) : '—') . '</td>';
            $html .= '<td>' . $badge . '</td>';
            $html .= '<td style="color:#64748b;">' . htmlspecialchars($a->metodo ?? '—') . '</td>';
            $html .= '<td style="color:#64748b;">' . htmlspecialchars($a->motivo_justificacion ?? '') . '</td>';
            $html .= '</tr>';
        }

        if ($rowNum === 0) {
            $html .= '<tr><td colspan="7" style="text-align:center;padding:20px;color:#94a3b8;font-style:italic;">No hay registros de asistencia en el rango seleccionado.</td></tr>';
        }

        $html .= '</tbody></table>';
        $html .= '<div class="pie">Instituto de Salud Pública del Estado Bolívar &nbsp;|&nbsp; Sistema de Gestión de Pasantías (SGP) &nbsp;|&nbsp; Generado el ' . date('d/m/Y H:i') . '</div>';
        $html .= '</div></body></html>';

        $nombreSafe = preg_replace('/[^A-Za-z0-9_\-]/', '_', trim(($pasante->nombres ?? '') . '_' . ($pasante->apellidos ?? '')));
        $pdf->renderDomPdf($html, 'Asistencias_' . $nombreSafe . '_' . str_replace('-', '', $desde) . '-' . str_replace('-', '', $hasta), false);
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

            // Obtener tipo del período a activar
            $this->db->query("SELECT tipo FROM periodos_academicos WHERE id = :id LIMIT 1");
            $this->db->bind(':id', $id);
            $pTipo = $this->db->single();
            $tipo  = $pTipo->tipo ?? 'Regular';

            // Cerrar solo el período activo del MISMO tipo
            $this->db->query("UPDATE periodos_academicos SET estado = 'Cerrado' WHERE LOWER(estado) = 'activo' AND tipo = :tipo");
            $this->db->bind(':tipo', $tipo);
            $this->db->execute();

            // Activar el período seleccionado
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
    // POST /periodos/eliminar/ID — solo períodos Planificados
    // ─────────────────────────────────────────────────────────────────
    public function eliminar(int $id = 0): void
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

        if ($id <= 0) {
            Session::setFlash('error', 'ID de período inválido.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        $this->db->query("SELECT id, nombre, estado FROM periodos_academicos WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $periodo = $this->db->single();

        if (!$periodo) {
            Session::setFlash('error', 'El período no existe.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        if (!in_array($periodo->estado, ['Planificado', 'Activo', 'Cerrado'], true)) {
            Session::setFlash('error', 'Solo se pueden eliminar períodos en estado Planificado, Activo o Cerrado.');
            header('Location: ' . URLROOT . '/periodos');
            exit;
        }

        // Desvincular pasantes del período antes de eliminarlo
        $this->db->query("UPDATE datos_pasante SET periodo_id = NULL WHERE periodo_id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();

        $this->db->query("DELETE FROM periodos_academicos WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();

        Session::setFlash('success', 'Período «' . $periodo->nombre . '» eliminado correctamente.');
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
