<?php
/**
 * ActividadesController - Módulo Actividades Extras
 *
 * Gestión de grupos/brigadas de servicio comunitario, pasantías cortas
 * y mantenimiento provenientes de instituciones universitarias externas.
 * URL base: /actividades  —  exclusiva para Administradores (rol_id = 1).
 */

declare(strict_types=1);

class ActividadesController extends Controller
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

    // =========================================================
    // GET /actividades — Lista de actividades + KPIs + filtros
    // =========================================================
    public function index(): void
    {
        // KPIs globales
        $this->db->query("SELECT COUNT(*) AS total FROM actividades_extras");
        $kpiTotal = (int)($this->db->single()->total ?? 0);

        $this->db->query("SELECT COUNT(*) AS total FROM actividades_extras WHERE estado = 'Activa'");
        $kpiActivas = (int)($this->db->single()->total ?? 0);

        $this->db->query("SELECT COUNT(*) AS total FROM actividades_extras WHERE estado = 'Finalizada'");
        $kpiFinalizadas = (int)($this->db->single()->total ?? 0);

        $this->db->query("SELECT COUNT(*) AS total FROM actividad_participantes");
        $kpiParticipantes = (int)($this->db->single()->total ?? 0);

        // Lista de actividades con datos relacionados
        $this->db->query("
            SELECT
                a.id,
                a.nombre,
                a.tipo,
                a.estado,
                a.fecha_inicio,
                a.fecha_fin,
                a.descripcion,
                ie.nombre   AS institucion_nombre,
                ie.tipo     AS institucion_tipo,
                CONCAT(COALESCE(dp.nombres,''), ' ', COALESCE(dp.apellidos,'')) AS supervisor_nombre,
                COUNT(DISTINCT ap.id) AS total_participantes
            FROM actividades_extras a
            LEFT JOIN instituciones_externas ie ON ie.id = a.institucion_id
            LEFT JOIN datos_personales dp       ON dp.usuario_id = a.supervisor_id
            LEFT JOIN actividad_participantes ap ON ap.actividad_id = a.id
            GROUP BY a.id
            ORDER BY a.created_at DESC
        ");
        $actividades = $this->db->resultSet();

        // Instituciones activas para el modal de creación
        $this->db->query("SELECT id, nombre, tipo FROM instituciones_externas WHERE activo = 1 ORDER BY nombre ASC");
        $instituciones = $this->db->resultSet();

        // Supervisores (usuarios rol=2)
        $this->db->query("
            SELECT u.id, CONCAT(COALESCE(dp.nombres,''), ' ', COALESCE(dp.apellidos,'')) AS nombre_completo
            FROM usuarios u
            LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
            WHERE u.rol_id = 2 AND u.estado = 'activo'
            ORDER BY COALESCE(dp.apellidos, u.correo) ASC
        ");
        $supervisores = $this->db->resultSet();

        $this->view('actividades/index', [
            'kpiTotal'          => $kpiTotal,
            'kpiActivas'        => $kpiActivas,
            'kpiFinalizadas'    => $kpiFinalizadas,
            'kpiParticipantes'  => $kpiParticipantes,
            'actividades'       => $actividades,
            'instituciones'     => $instituciones,
            'supervisores'      => $supervisores,
        ]);
    }

    // =========================================================
    // GET /actividades/ver/{id} — Detalle de actividad
    // =========================================================
    public function ver(int $id): void
    {
        // Actividad principal
        $this->db->query("
            SELECT
                a.*,
                ie.nombre   AS institucion_nombre,
                ie.tipo     AS institucion_tipo,
                CONCAT(COALESCE(dp.nombres,''), ' ', COALESCE(dp.apellidos,'')) AS supervisor_nombre
            FROM actividades_extras a
            LEFT JOIN instituciones_externas ie ON ie.id = a.institucion_id
            LEFT JOIN datos_personales dp       ON dp.usuario_id = a.supervisor_id
            WHERE a.id = :id
            LIMIT 1
        ");
        $this->db->bind(':id', $id);
        $actividad = $this->db->single();

        if (!$actividad) {
            Session::setFlash('error', 'Actividad no encontrada.');
            header('Location: ' . URLROOT . '/actividades');
            exit;
        }

        // Participantes
        $this->db->query("
            SELECT * FROM actividad_participantes
            WHERE actividad_id = :id
            ORDER BY apellidos ASC, nombres ASC
        ");
        $this->db->bind(':id', $id);
        $participantes = $this->db->resultSet();

        // Asistencias del mes actual (para precarga)
        $mesActual = date('Y-m');
        $this->db->query("
            SELECT
                aa.participante_id,
                aa.fecha,
                aa.estado,
                aa.notas
            FROM actividad_asistencias aa
            WHERE aa.actividad_id = :id
              AND DATE_FORMAT(aa.fecha, '%Y-%m') = :mes
            ORDER BY aa.fecha ASC
        ");
        $this->db->bind(':id', $id);
        $this->db->bind(':mes', $mesActual);
        $asistenciasMes = $this->db->resultSet();

        // Asistencias indexadas por participante_id + fecha para JS
        $asistenciasMap = [];
        foreach ($asistenciasMes as $row) {
            $asistenciasMap[$row->participante_id][$row->fecha] = $row->estado;
        }

        // KPI asistencia global
        $this->db->query("
            SELECT
                COUNT(*) AS total,
                SUM(estado = 'Presente')    AS presentes,
                SUM(estado = 'Ausente')     AS ausentes,
                SUM(estado = 'Justificado') AS justificados
            FROM actividad_asistencias
            WHERE actividad_id = :id
        ");
        $this->db->bind(':id', $id);
        $statsAsistencia = $this->db->single();

        // Días activa
        $diasActiva = 0;
        if ($actividad->fecha_inicio) {
            $fechaFin = $actividad->fecha_fin ? new DateTime($actividad->fecha_fin) : new DateTime();
            $diasActiva = (int)(new DateTime($actividad->fecha_inicio))->diff($fechaFin)->days;
        }

        // % asistencia
        $pctAsistencia = 0;
        if (!empty($statsAsistencia->total) && (int)$statsAsistencia->total > 0) {
            $pctAsistencia = round(((int)$statsAsistencia->presentes / (int)$statsAsistencia->total) * 100);
        }

        // Instituciones para edición
        $this->db->query("SELECT id, nombre FROM instituciones_externas WHERE activo = 1 ORDER BY nombre ASC");
        $instituciones = $this->db->resultSet();

        // Supervisores para edición
        $this->db->query("
            SELECT u.id, CONCAT(COALESCE(dp.nombres,''), ' ', COALESCE(dp.apellidos,'')) AS nombre_completo
            FROM usuarios u
            LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
            WHERE u.rol_id = 2 AND u.estado = 'activo'
            ORDER BY COALESCE(dp.apellidos, u.correo) ASC
        ");
        $supervisores = $this->db->resultSet();

        $this->view('actividades/ver', [
            'actividad'      => $actividad,
            'participantes'  => $participantes,
            'asistenciasMap' => $asistenciasMap,
            'statsAsistencia'=> $statsAsistencia,
            'diasActiva'     => $diasActiva,
            'pctAsistencia'  => $pctAsistencia,
            'instituciones'  => $instituciones,
            'supervisores'   => $supervisores,
        ]);
    }

    // =========================================================
    // POST /actividades/crear
    // =========================================================
    public function crear(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/actividades');
            exit;
        }

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de seguridad inválido. Intenta de nuevo.');
            header('Location: ' . URLROOT . '/actividades');
            exit;
        }

        $nombre       = trim($_POST['nombre'] ?? '');
        $tipo         = trim($_POST['tipo'] ?? 'Servicio Comunitario');
        $instId       = !empty($_POST['institucion_id']) ? (int)$_POST['institucion_id'] : null;
        $supervisorId = !empty($_POST['supervisor_id']) ? (int)$_POST['supervisor_id'] : null;
        $descripcion  = trim($_POST['descripcion'] ?? '');
        $fechaInicio  = trim($_POST['fecha_inicio'] ?? '');
        $fechaFin     = !empty($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : null;

        if (empty($nombre) || empty($fechaInicio)) {
            Session::setFlash('error', 'El nombre y la fecha de inicio son obligatorios.');
            header('Location: ' . URLROOT . '/actividades');
            exit;
        }

        $tiposValidos = ['Servicio Comunitario', 'Pasantía Corta', 'Mantenimiento', 'Otro'];
        if (!in_array($tipo, $tiposValidos)) {
            $tipo = 'Servicio Comunitario';
        }

        $this->db->query("
            INSERT INTO actividades_extras
                (nombre, tipo, institucion_id, supervisor_id, descripcion, fecha_inicio, fecha_fin, estado)
            VALUES
                (:nombre, :tipo, :inst_id, :sup_id, :desc, :fi, :ff, 'Activa')
        ");
        $this->db->bind(':nombre',  $nombre);
        $this->db->bind(':tipo',    $tipo);
        $this->db->bind(':inst_id', $instId);
        $this->db->bind(':sup_id',  $supervisorId);
        $this->db->bind(':desc',    $descripcion ?: null);
        $this->db->bind(':fi',      $fechaInicio);
        $this->db->bind(':ff',      $fechaFin);
        $this->db->execute();

        Session::setFlash('success', "Actividad \"{$nombre}\" creada correctamente.");
        header('Location: ' . URLROOT . '/actividades');
        exit;
    }

    // =========================================================
    // POST /actividades/editar
    // =========================================================
    public function editar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/actividades');
            exit;
        }

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de seguridad inválido. Intenta de nuevo.');
            header('Location: ' . URLROOT . '/actividades');
            exit;
        }

        $id           = (int)($_POST['id'] ?? 0);
        $nombre       = trim($_POST['nombre'] ?? '');
        $tipo         = trim($_POST['tipo'] ?? 'Servicio Comunitario');
        $instId       = !empty($_POST['institucion_id']) ? (int)$_POST['institucion_id'] : null;
        $supervisorId = !empty($_POST['supervisor_id']) ? (int)$_POST['supervisor_id'] : null;
        $descripcion  = trim($_POST['descripcion'] ?? '');
        $fechaInicio  = trim($_POST['fecha_inicio'] ?? '');
        $fechaFin     = !empty($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : null;
        $estado       = trim($_POST['estado'] ?? 'Activa');

        if ($id <= 0 || empty($nombre) || empty($fechaInicio)) {
            Session::setFlash('error', 'Datos incompletos.');
            header('Location: ' . URLROOT . '/actividades');
            exit;
        }

        $estadosValidos = ['Activa', 'Finalizada', 'Cancelada'];
        if (!in_array($estado, $estadosValidos)) $estado = 'Activa';

        $this->db->query("
            UPDATE actividades_extras
            SET nombre = :nombre, tipo = :tipo, institucion_id = :inst_id,
                supervisor_id = :sup_id, descripcion = :desc,
                fecha_inicio = :fi, fecha_fin = :ff, estado = :estado
            WHERE id = :id
        ");
        $this->db->bind(':nombre',  $nombre);
        $this->db->bind(':tipo',    $tipo);
        $this->db->bind(':inst_id', $instId);
        $this->db->bind(':sup_id',  $supervisorId);
        $this->db->bind(':desc',    $descripcion ?: null);
        $this->db->bind(':fi',      $fechaInicio);
        $this->db->bind(':ff',      $fechaFin);
        $this->db->bind(':estado',  $estado);
        $this->db->bind(':id',      $id);
        $this->db->execute();

        Session::setFlash('success', 'Actividad actualizada correctamente.');
        header('Location: ' . URLROOT . '/actividades/ver/' . $id);
        exit;
    }

    // =========================================================
    // POST /actividades/agregarParticipante  (AJAX → JSON)
    // =========================================================
    public function agregarParticipante(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        // CSRF desde header X-CSRF-TOKEN o campo POST
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
        if (!Session::validateCsrfToken($token)) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
            exit;
        }

        $actividadId = (int)($_POST['actividad_id'] ?? 0);
        $nombres     = trim($_POST['nombres'] ?? '');
        $apellidos   = trim($_POST['apellidos'] ?? '');
        $cedula      = trim($_POST['cedula'] ?? '');
        $carrera     = trim($_POST['carrera'] ?? '');
        $telefono    = trim($_POST['telefono'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');

        if ($actividadId <= 0 || empty($nombres) || empty($apellidos) || empty($cedula)) {
            echo json_encode(['success' => false, 'message' => 'Nombres, apellidos y cédula son obligatorios.']);
            exit;
        }

        // Verificar que la actividad existe
        $this->db->query("SELECT id FROM actividades_extras WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $actividadId);
        if (!$this->db->single()) {
            echo json_encode(['success' => false, 'message' => 'Actividad no encontrada.']);
            exit;
        }

        // Cédula duplicada en la misma actividad
        $this->db->query("
            SELECT id FROM actividad_participantes
            WHERE actividad_id = :aid AND cedula = :cedula LIMIT 1
        ");
        $this->db->bind(':aid',    $actividadId);
        $this->db->bind(':cedula', $cedula);
        if ($this->db->single()) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un participante con esa cédula en esta actividad.']);
            exit;
        }

        $this->db->query("
            INSERT INTO actividad_participantes
                (actividad_id, nombres, apellidos, cedula, carrera, telefono, observaciones)
            VALUES
                (:aid, :nombres, :apellidos, :cedula, :carrera, :tel, :obs)
        ");
        $this->db->bind(':aid',      $actividadId);
        $this->db->bind(':nombres',  $nombres);
        $this->db->bind(':apellidos',$apellidos);
        $this->db->bind(':cedula',   $cedula);
        $this->db->bind(':carrera',  $carrera ?: null);
        $this->db->bind(':tel',      $telefono ?: null);
        $this->db->bind(':obs',      $observaciones ?: null);
        $this->db->execute();

        // Devolver el nuevo participante para actualizar la tabla sin recargar
        $this->db->query("
            SELECT * FROM actividad_participantes
            WHERE actividad_id = :aid AND cedula = :cedula ORDER BY id DESC LIMIT 1
        ");
        $this->db->bind(':aid',    $actividadId);
        $this->db->bind(':cedula', $cedula);
        $nuevo = $this->db->single();

        echo json_encode([
            'success'      => true,
            'message'      => 'Participante agregado correctamente.',
            'participante' => $nuevo,
        ]);
        exit;
    }

    // =========================================================
    // POST /actividades/registrarAsistencia  (AJAX → JSON)
    // =========================================================
    public function registrarAsistencia(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
        if (!Session::validateCsrfToken($token)) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
            exit;
        }

        $actividadId    = (int)($_POST['actividad_id'] ?? 0);
        $participanteId = (int)($_POST['participante_id'] ?? 0);
        $fecha          = trim($_POST['fecha'] ?? '');
        $estado         = trim($_POST['estado'] ?? 'Presente');
        $notas          = trim($_POST['notas'] ?? '');

        if ($actividadId <= 0 || $participanteId <= 0 || empty($fecha)) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            exit;
        }

        $estadosValidos = ['Presente', 'Ausente', 'Justificado'];
        if (!in_array($estado, $estadosValidos)) {
            echo json_encode(['success' => false, 'message' => 'Estado de asistencia inválido.']);
            exit;
        }

        // Validar formato de fecha
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$dt || $dt->format('Y-m-d') !== $fecha) {
            echo json_encode(['success' => false, 'message' => 'Fecha inválida.']);
            exit;
        }

        // INSERT ... ON DUPLICATE KEY UPDATE
        $this->db->query("
            INSERT INTO actividad_asistencias
                (actividad_id, participante_id, fecha, estado, notas)
            VALUES
                (:aid, :pid, :fecha, :estado, :notas)
            ON DUPLICATE KEY UPDATE
                estado = VALUES(estado),
                notas  = VALUES(notas)
        ");
        $this->db->bind(':aid',    $actividadId);
        $this->db->bind(':pid',    $participanteId);
        $this->db->bind(':fecha',  $fecha);
        $this->db->bind(':estado', $estado);
        $this->db->bind(':notas',  $notas ?: null);
        $this->db->execute();

        echo json_encode([
            'success' => true,
            'message' => "Asistencia registrada: {$estado}",
            'estado'  => $estado,
            'fecha'   => $fecha,
        ]);
        exit;
    }

    // =========================================================
    // GET /actividades/instituciones
    // =========================================================
    public function instituciones(): void
    {
        $this->db->query("SELECT * FROM instituciones_externas ORDER BY nombre ASC");
        $instituciones = $this->db->resultSet();

        $this->view('actividades/instituciones', [
            'instituciones' => $instituciones,
        ]);
    }

    // =========================================================
    // POST /actividades/crearInstitucion
    // =========================================================
    public function crearInstitucion(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/actividades/instituciones');
            exit;
        }

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de seguridad inválido.');
            header('Location: ' . URLROOT . '/actividades/instituciones');
            exit;
        }

        $nombre   = trim($_POST['nombre'] ?? '');
        $tipo     = trim($_POST['tipo'] ?? 'Universidad');
        $contacto = trim($_POST['contacto'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if (empty($nombre)) {
            Session::setFlash('error', 'El nombre de la institución es obligatorio.');
            header('Location: ' . URLROOT . '/actividades/instituciones');
            exit;
        }

        $tiposValidos = ['Universidad', 'Instituto', 'Colegio Técnico', 'Otro'];
        if (!in_array($tipo, $tiposValidos)) $tipo = 'Universidad';

        $this->db->query("
            INSERT INTO instituciones_externas (nombre, tipo, contacto, telefono, activo)
            VALUES (:nombre, :tipo, :contacto, :telefono, 1)
        ");
        $this->db->bind(':nombre',   $nombre);
        $this->db->bind(':tipo',     $tipo);
        $this->db->bind(':contacto', $contacto ?: null);
        $this->db->bind(':telefono', $telefono ?: null);
        $this->db->execute();

        Session::setFlash('success', "Institución \"{$nombre}\" registrada correctamente.");
        header('Location: ' . URLROOT . '/actividades/instituciones');
        exit;
    }

    // =========================================================
    // POST /actividades/toggleInstitucion  (AJAX → JSON)
    // =========================================================
    public function toggleInstitucion(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
        if (!Session::validateCsrfToken($token)) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

        $this->db->query("SELECT activo FROM instituciones_externas WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $inst = $this->db->single();

        if (!$inst) {
            echo json_encode(['success' => false, 'message' => 'Institución no encontrada.']);
            exit;
        }

        $nuevoEstado = $inst->activo ? 0 : 1;
        $this->db->query("UPDATE instituciones_externas SET activo = :estado WHERE id = :id");
        $this->db->bind(':estado', $nuevoEstado);
        $this->db->bind(':id',     $id);
        $this->db->execute();

        echo json_encode([
            'success' => true,
            'activo'  => $nuevoEstado,
            'message' => $nuevoEstado ? 'Institución activada.' : 'Institución desactivada.',
        ]);
        exit;
    }

    // =========================================================
    // GET /actividades/asistenciasFecha  (AJAX → JSON)
    // Devuelve las asistencias de una actividad en una fecha dada
    // =========================================================
    public function asistenciasFecha(): void
    {
        header('Content-Type: application/json');

        $actividadId = (int)($_GET['actividad_id'] ?? 0);
        $fecha       = trim($_GET['fecha'] ?? '');

        if ($actividadId <= 0 || empty($fecha)) {
            echo json_encode(['success' => false, 'message' => 'Parámetros faltantes.']);
            exit;
        }

        $this->db->query("
            SELECT participante_id, estado, notas
            FROM actividad_asistencias
            WHERE actividad_id = :aid AND fecha = :fecha
        ");
        $this->db->bind(':aid',   $actividadId);
        $this->db->bind(':fecha', $fecha);
        $rows = $this->db->resultSet();

        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r->participante_id] = ['estado' => $r->estado, 'notas' => $r->notas];
        }

        echo json_encode(['success' => true, 'data' => $map]);
        exit;
    }
}
