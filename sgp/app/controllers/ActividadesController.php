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

        if (!RoleMiddleware::hasAnyRole([0, 1])) {
            RoleMiddleware::redirectToRoleDashboard(Session::get('role_id'));
        }

        $this->db = Database::getInstance();
    }

    // =========================================================
    // Helper: Merge JSON body into $_POST for fetch() AJAX calls
    // =========================================================
    private function parsePostBody(): void
    {
        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($ct, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            if (!empty($raw)) {
                $json = json_decode($raw, true);
                if (is_array($json)) {
                    $_POST = array_merge($_POST, $json);
                }
            }
        }
    }

    // =========================================================
    // Helper: queries reutilizables
    // =========================================================
    private function getKpis(): array
    {
        $this->db->query("SELECT COUNT(*) AS total FROM datos_pasante WHERE tipo_pasantia = 'Corta'");
        $kpiPasantesCortos = (int)($this->db->single()->total ?? 0);

        $this->db->query("SELECT COUNT(*) AS total FROM datos_pasante WHERE tipo_pasantia = 'Corta' AND estado_pasantia = 'Activo'");
        $kpiCortosActivos = (int)($this->db->single()->total ?? 0);

        $this->db->query("SELECT COUNT(*) AS total FROM actividades_extras WHERE tipo != 'Pasantía Corta'");
        $kpiActividadesCom = (int)($this->db->single()->total ?? 0);

        $this->db->query("SELECT COUNT(*) AS total FROM instituciones WHERE categoria = 'actividad' AND activo = 1");
        $kpiInstituciones = (int)($this->db->single()->total ?? 0);

        $this->db->query("SELECT id, nombre, estado FROM periodos_academicos WHERE tipo = 'Corto' AND estado = 'Activo' LIMIT 1");
        $periodoCortoActivo = $this->db->single();

        return compact('kpiPasantesCortos', 'kpiCortosActivos', 'kpiActividadesCom', 'kpiInstituciones', 'periodoCortoActivo');
    }

    // =========================================================
    // GET /actividades — Hub central con previews + KPIs
    // =========================================================
    public function index(): void
    {
        $kpis = $this->getKpis();

        // Preview: últimas 3 instituciones
        $this->db->query("SELECT id, nombre, tipo, contacto FROM instituciones WHERE categoria = 'actividad' ORDER BY id DESC LIMIT 3");
        $previewInstituciones = $this->db->resultSet();

        // Preview: últimos 3 pasantes cortos
        $this->db->query("
            SELECT u.id, dp.nombres, dp.apellidos,
                   dpa.estado_pasantia, dpa.horas_acumuladas, dpa.horas_meta,
                   ie.nombre AS institucion_nombre
            FROM datos_pasante dpa
            JOIN usuarios u ON u.id = dpa.usuario_id
            LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
            LEFT JOIN instituciones ie ON ie.id = dpa.institucion_id
            WHERE dpa.tipo_pasantia = 'Corta'
            ORDER BY dpa.id DESC LIMIT 3
        ");
        $previewPasantes = $this->db->resultSet();

        // Preview: últimas 2 actividades SC
        $this->db->query("
            SELECT a.id, a.nombre, a.estado, a.fecha_inicio,
                   ie.nombre AS institucion_nombre,
                   COUNT(DISTINCT ap.id) AS total_participantes
            FROM actividades_extras a
            LEFT JOIN instituciones ie ON ie.id = a.institucion_id
            LEFT JOIN actividad_participantes ap ON ap.actividad_id = a.id
            WHERE a.tipo != 'Pasantía Corta'
            GROUP BY a.id ORDER BY a.id DESC LIMIT 2
        ");
        $previewActividades = $this->db->resultSet();

        // Distribución por tipo para donut chart
        $this->db->query("SELECT tipo, COUNT(*) AS total FROM actividades_extras GROUP BY tipo");
        $distribucionTipos = $this->db->resultSet();

        $this->view('actividades/index', array_merge($kpis, [
            'previewInstituciones' => $previewInstituciones,
            'previewPasantes'      => $previewPasantes,
            'previewActividades'   => $previewActividades,
            'distribucionTipos'    => $distribucionTipos,
        ]));
    }

    // =========================================================
    // GET /actividades/instituciones — Vista dedicada
    // =========================================================
    public function instituciones(): void
    {
        $kpis = $this->getKpis();

        $this->db->query("
            SELECT ie.*,
                COUNT(DISTINCT dpa.usuario_id) AS pasantes_activos
            FROM instituciones ie
            LEFT JOIN datos_pasante dpa ON dpa.institucion_id = ie.id AND dpa.estado_pasantia = 'Activo'
            WHERE ie.categoria = 'actividad'
            GROUP BY ie.id
            ORDER BY ie.nombre ASC
        ");
        $instituciones = $this->db->resultSet();

        // Conteo por tipo para donut
        $this->db->query("SELECT tipo, COUNT(*) AS total FROM instituciones WHERE categoria = 'actividad' GROUP BY tipo");
        $porTipo = $this->db->resultSet();

        $csrfToken = Session::generateCsrfToken();

        $this->view('actividades/instituciones', array_merge($kpis, [
            'instituciones' => $instituciones,
            'porTipo'       => $porTipo,
            'csrfToken'     => $csrfToken,
        ]));
    }

    // =========================================================
    // GET /actividades/pasantias — Vista dedicada pasantías cortas
    // =========================================================
    public function pasantias(): void
    {
        $kpis = $this->getKpis();

        // Pasantes cortos completos
        $this->db->query("
            SELECT
                u.id, u.correo, u.cedula, u.estado,
                dp.nombres, dp.apellidos,
                dpa.estado_pasantia, dpa.horas_acumuladas, dpa.horas_meta,
                dpa.fecha_inicio_pasantia, dpa.fecha_fin_estimada,
                d.nombre  AS departamento_nombre,
                ie.nombre AS institucion_nombre,
                CONCAT(COALESCE(tp.nombres,''), ' ', COALESCE(tp.apellidos,'')) AS tutor_nombre,
                pa.nombre AS periodo_nombre
            FROM datos_pasante dpa
            JOIN usuarios u ON u.id = dpa.usuario_id
            LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
            LEFT JOIN departamentos d ON d.id = dpa.departamento_asignado_id
            LEFT JOIN instituciones ie ON ie.id = dpa.institucion_id
            LEFT JOIN usuarios tu ON tu.id = dpa.tutor_id
            LEFT JOIN datos_personales tp ON tp.usuario_id = tu.id
            LEFT JOIN periodos_academicos pa ON pa.id = dpa.periodo_id
            WHERE dpa.tipo_pasantia = 'Corta'
            ORDER BY dp.apellidos ASC
        ");
        $pasantesCortos = $this->db->resultSet();

        // Instituciones activas para modal nuevo pasante
        $this->db->query("SELECT id, nombre FROM instituciones WHERE categoria = 'actividad' AND activo = 1 ORDER BY nombre ASC");
        $instituciones = $this->db->resultSet();

        $this->db->query("
            SELECT u.id, CONCAT(COALESCE(dp.nombres,''), ' ', COALESCE(dp.apellidos,'')) AS nombre_completo
            FROM usuarios u
            LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
            WHERE u.rol_id = 2 AND u.estado = 'activo'
            ORDER BY COALESCE(dp.apellidos, u.correo) ASC
        ");
        $tutores = $this->db->resultSet();

        $this->db->query("SELECT id, nombre FROM departamentos WHERE activo = 1 ORDER BY nombre ASC");
        $departamentos = $this->db->resultSet();

        // Solo períodos Cortos activos/planificados
        $this->db->query("SELECT id, nombre, estado, fecha_inicio, fecha_fin FROM periodos_academicos WHERE tipo = 'Corto' AND estado IN ('Activo','Planificado') ORDER BY id DESC");
        $periodos = $this->db->resultSet();

        // Estadísticas por estado para los KPIs de la vista
        $activos     = count(array_filter($pasantesCortos, fn($p) => ($p->estado_pasantia ?? '') === 'Activo'));
        $finalizados = count(array_filter($pasantesCortos, fn($p) => ($p->estado_pasantia ?? '') === 'Finalizado'));
        $totalH      = array_sum(array_map(fn($p) => (int)($p->horas_acumuladas ?? 0), $pasantesCortos));
        $metaH       = array_sum(array_map(fn($p) => max(1, (int)($p->horas_meta ?? 480)), $pasantesCortos));
        $promPct     = $metaH > 0 ? round($totalH / $metaH * 100) : 0;

        $csrfToken = Session::generateCsrfToken();

        $this->view('actividades/pasantias', array_merge($kpis, [
            'pasantesCortos' => $pasantesCortos,
            'instituciones'  => $instituciones,
            'tutores'        => $tutores,
            'departamentos'  => $departamentos,
            'periodos'       => $periodos,
            'statActivos'    => $activos,
            'statFinalizados'=> $finalizados,
            'statPromPct'    => $promPct,
            'csrfToken'      => $csrfToken,
        ]));
    }

    // =========================================================
    // GET /actividades/servicio — Vista dedicada servicio comunitario
    // =========================================================
    public function servicio(): void
    {
        $kpis = $this->getKpis();

        $this->db->query("
            SELECT
                a.id, a.nombre, a.tipo, a.estado, a.fecha_inicio, a.fecha_fin, a.descripcion,
                ie.nombre AS institucion_nombre,
                COUNT(DISTINCT ap.id) AS total_participantes
            FROM actividades_extras a
            LEFT JOIN instituciones ie ON ie.id = a.institucion_id
            LEFT JOIN actividad_participantes ap ON ap.actividad_id = a.id
            WHERE a.tipo != 'Pasantía Corta'
            GROUP BY a.id
            ORDER BY a.fecha_inicio DESC
        ");
        $actividades = $this->db->resultSet();

        $this->db->query("SELECT id, nombre FROM instituciones WHERE categoria = 'actividad' AND activo = 1 ORDER BY nombre ASC");
        $instituciones = $this->db->resultSet();

        $statActivas    = count(array_filter($actividades, fn($a) => ($a->estado ?? '') === 'Activa'));
        $statFinalizadas= count(array_filter($actividades, fn($a) => ($a->estado ?? '') === 'Finalizada'));
        $statParticip   = array_sum(array_map(fn($a) => (int)($a->total_participantes ?? 0), $actividades));

        $csrfToken = Session::generateCsrfToken();

        $this->view('actividades/servicio', array_merge($kpis, [
            'actividades'     => $actividades,
            'instituciones'   => $instituciones,
            'statActivas'     => $statActivas,
            'statFinalizadas' => $statFinalizadas,
            'statParticip'    => $statParticip,
            'csrfToken'       => $csrfToken,
        ]));
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
            LEFT JOIN instituciones ie ON ie.id = a.institucion_id
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
            $diasActiva = (int) (new DateTime($actividad->fecha_inicio))->diff($fechaFin)->days;
        }

        // % asistencia
        $pctAsistencia = 0;
        if (!empty($statsAsistencia->total) && (int)$statsAsistencia->total > 0) {
            $pctAsistencia = round(((int)$statsAsistencia->presentes / (int)$statsAsistencia->total) * 100);
        }

        // Instituciones para edición
        $this->db->query("SELECT id, nombre FROM instituciones WHERE categoria = 'actividad' AND activo = 1 ORDER BY nombre ASC");
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
        
        $actividadId = $this->db->lastInsertId();

        // Inserción automática de Pasante para Pasantías Cortas (Flujo Unified)
        if ($tipo === 'Pasantía Corta' && !empty($_POST['pasante_cedula']) && !empty($_POST['pasante_nombres'])) {
            $pCedula = trim($_POST['pasante_cedula']);
            $pNombres = trim($_POST['pasante_nombres']);
            $pApellidos = trim($_POST['pasante_apellidos'] ?? '');
            $pCarrera = trim($_POST['pasante_carrera'] ?? '');
            $this->db->query("
                INSERT INTO actividad_participantes
                    (actividad_id, nombres, apellidos, cedula, carrera)
                VALUES
                    (:aid, :nombres, :apellidos, :cedula, :carrera)
            ");
            $this->db->bind(':aid', $actividadId);
            $this->db->bind(':nombres', $pNombres);
            $this->db->bind(':apellidos', $pApellidos);
            $this->db->bind(':cedula', $pCedula);
            $this->db->bind(':carrera', $pCarrera ?: null);
            $this->db->execute();
        }

        Session::setFlash('success', "Actividad \"{$nombre}\" creada. Ahora puedes agregar participantes.");
        if ($tipo === 'Pasantía Corta') {
            header('Location: ' . URLROOT . '/actividades/pasantias');
        } else {
            // Redirigir al detalle para agregar participantes de inmediato
            header('Location: ' . URLROOT . '/actividades/ver/' . $actividadId);
        }
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
        $this->parsePostBody();

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
        $this->parsePostBody();

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
    // POST /actividades/crearInstitucion
    // =========================================================
    public function crearInstitucion(): void
    {
        header('Content-Type: application/json');
        $this->parsePostBody();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
        if (!Session::validateCsrfToken($token)) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
            exit;
        }

        $nombre   = trim($_POST['nombre'] ?? '');
        $tipo     = trim($_POST['tipo'] ?? 'Universidad');
        $contacto = trim($_POST['contacto'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if (empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio.']);
            exit;
        }

        $tiposValidos = ['Universidad', 'Instituto', 'Colegio Técnico', 'Otro'];
        if (!in_array($tipo, $tiposValidos)) $tipo = 'Universidad';

        $this->db->query("
            INSERT INTO instituciones (nombre, tipo, contacto, telefono, activo, categoria)
            VALUES (:nombre, :tipo, :contacto, :telefono, 1, 'actividad')
        ");
        $this->db->bind(':nombre',   $nombre);
        $this->db->bind(':tipo',     $tipo);
        $this->db->bind(':contacto', $contacto ?: null);
        $this->db->bind(':telefono', $telefono ?: null);
        $this->db->execute();

        $id = $this->db->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => "Institución \"{$nombre}\" registrada correctamente.",
            'institucion' => [
                'id'       => $id,
                'nombre'   => $nombre,
                'tipo'     => $tipo,
                'contacto' => $contacto,
                'telefono' => $telefono,
                'activo'   => 1,
            ]
        ]);
        exit;
    }

    // =========================================================
    // POST /actividades/editarInstitucion (AJAX)
    // =========================================================
    public function editarInstitucion(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
            exit;
        }

        $id       = (int)($_POST['id'] ?? 0);
        $nombre   = trim($_POST['nombre'] ?? '');
        $tipo     = trim($_POST['tipo'] ?? 'Universidad');
        $contacto = trim($_POST['contacto'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if ($id <= 0 || empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos o ID inválido.']);
            exit;
        }

        $tiposValidos = ['Universidad', 'Instituto', 'Colegio Técnico', 'Otro'];
        if (!in_array($tipo, $tiposValidos)) $tipo = 'Universidad';

        $this->db->query("
            UPDATE instituciones
            SET nombre = :nombre, tipo = :tipo, contacto = :contacto, telefono = :telefono
            WHERE id = :id AND categoria = 'actividad'
        ");
        $this->db->bind(':id',       $id);
        $this->db->bind(':nombre',   $nombre);
        $this->db->bind(':tipo',     $tipo);
        $this->db->bind(':contacto', $contacto ?: null);
        $this->db->bind(':telefono', $telefono ?: null);
        
        if ($this->db->execute()) {
            echo json_encode([
                'success' => true,
                'institucion' => [
                    'id'       => $id,
                    'nombre'   => $nombre,
                    'tipo'     => $tipo,
                    'contacto' => $contacto,
                    'telefono' => $telefono
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar en la base de datos.']);
        }
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

        $this->db->query("SELECT activo FROM instituciones WHERE id = :id AND categoria = 'actividad' LIMIT 1");
        $this->db->bind(':id', $id);
        $inst = $this->db->single();

        if (!$inst) {
            echo json_encode(['success' => false, 'message' => 'Institución no encontrada.']);
            exit;
        }

        $nuevoEstado = $inst->activo ? 0 : 1;
        $this->db->query("UPDATE instituciones SET activo = :estado WHERE id = :id AND categoria = 'actividad'");
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
    // GET /actividades/participante/{id} — Vista individual pasante
    // =========================================================
    public function participante(int $id): void
    {
        $this->db->query("
            SELECT
                ap.*,
                ae.nombre   AS actividad_nombre,
                ae.tipo     AS actividad_tipo,
                ae.estado   AS actividad_estado,
                ae.id       AS actividad_id,
                ie.nombre   AS institucion_nombre
            FROM actividad_participantes ap
            JOIN actividades_extras ae       ON ae.id = ap.actividad_id
            LEFT JOIN instituciones ie ON ie.id = ae.institucion_id
            WHERE ap.id = :id
            LIMIT 1
        ");
        $this->db->bind(':id', $id);
        $participante = $this->db->single();

        if (!$participante) {
            Session::setFlash('error', 'Participante no encontrado.');
            header('Location: ' . URLROOT . '/actividades');
            exit;
        }

        $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');

        $this->db->query("
            SELECT fecha, estado, notas
            FROM actividad_asistencias
            WHERE participante_id = :pid
              AND YEAR(fecha) = :anio
            ORDER BY fecha ASC
        ");
        $this->db->bind(':pid', $id);
        $this->db->bind(':anio', $anio);
        $registros = $this->db->resultSet();

        $registroMap = [];
        foreach ($registros as $r) {
            $registroMap[$r->fecha] = $r;
        }

        // Stats del año
        $stats = ['P' => 0, 'A' => 0, 'J' => 0, 'laborables' => 0];
        foreach ($registros as $r) {
            $dn = (int)date('N', strtotime($r->fecha));
            if ($dn <= 5) {
                $stats['laborables']++;
                if     ($r->estado === 'Presente')    $stats['P']++;
                elseif ($r->estado === 'Ausente')     $stats['A']++;
                elseif ($r->estado === 'Justificado') $stats['J']++;
            }
        }
        $pct = $stats['laborables'] > 0
            ? round($stats['P'] / $stats['laborables'] * 100, 1)
            : 0.0;

        // Semana (offset 0 = semana actual)
        $semOffset = isset($_GET['sem']) ? (int)$_GET['sem'] : 0;
        $lunes = new DateTime('monday this week');
        if ($semOffset !== 0) $lunes->modify("{$semOffset} week");

        $diasSemana = [];
        for ($i = 0; $i < 5; $i++) {
            $d    = clone $lunes;
            $d->modify("+{$i} day");
            $fStr = $d->format('Y-m-d');
            $diasSemana[] = [
                'fecha'  => $fStr,
                'diaNom' => $d->format('l'),
                'diaNum' => $d->format('d/m'),
                'estado' => $registroMap[$fStr]->estado ?? null,
                'notas'  => $registroMap[$fStr]->notas  ?? null,
            ];
        }
        $viernesLabel = (clone $lunes)->modify('+4 day');
        $baseUrl = URLROOT . '/actividades/participante/' . $id;
        $navSemana = [
            'ant'      => $semOffset - 1,
            'sig'      => $semOffset + 1,
            'ant_url'  => $baseUrl . '?anio=' . $anio . '&sem=' . ($semOffset - 1),
            'sig_url'  => $baseUrl . '?anio=' . $anio . '&sem=' . ($semOffset + 1),
            'semLabel' => $lunes->format('d/m') . ' – ' . $viernesLabel->format('d/m/Y'),
            'label'    => $lunes->format('d/m') . ' – ' . $viernesLabel->format('d/m/Y'),
            'esActual' => $semOffset === 0,
        ];

        // Almanaque anual
        // $grillaMeses[$m][$d] = estado string  (para compatibilidad con la vista desglose mensual)
        // $grillaMesMeta[$m]   = ['nombre','dias'(array objetos),'stats','tiene']  (para el calendario)
        $grillaMeses    = [];   // [m][d] => estado|null  (indexado por día numérico)
        $grillaMesMeta  = [];   // [m] => {nombre, dias[], stats, tiene}
        for ($m = 1; $m <= 12; $m++) {
            $primerDia = new DateTime("{$anio}-{$m}-01");
            $ultimoDia = (int)$primerDia->format('t');
            $inicioSem = (int)$primerDia->format('N') - 1; // 0=Lun

            // Array de celdas para el calendario (incluye huecos)
            $celdas = array_fill(0, $inicioSem, null);
            $mStats = ['P' => 0, 'A' => 0, 'J' => 0];

            for ($d = 1; $d <= $ultimoDia; $d++) {
                $fStr = sprintf('%04d-%02d-%02d', $anio, $m, $d);
                $dn   = (int) (new DateTime($fStr))->format('N');
                $est  = $registroMap[$fStr]->estado ?? null;
                $notas= $registroMap[$fStr]->notas  ?? null;

                $celdas[] = [
                    'fecha'   => $fStr,
                    'dia'     => $d,
                    'estado'  => $est,
                    'notas'   => $notas,
                    'esFinde' => $dn >= 6,
                ];

                // Mapa simple [día] => estado  (para la vista desglose)
                $grillaMeses[$m][$d] = $est;

                if ($est) {
                    $k = substr($est, 0, 1);
                    if (isset($mStats[$k])) $mStats[$k]++;
                }
            }

            $grillaMesMeta[$m] = [
                'nombre' => $primerDia->format('F'),
                'dias'   => $celdas,
                'stats'  => $mStats,
                'tiene'  => array_sum($mStats) > 0,
            ];
        }

        // Años con registros
        $this->db->query("
            SELECT DISTINCT YEAR(fecha) AS anio
            FROM actividad_asistencias
            WHERE participante_id = :pid
            ORDER BY anio DESC
        ");
        $this->db->bind(':pid', $id);
        $aniosRows = $this->db->resultSet();
        $anios = array_map(fn($r) => (int)$r->anio, $aniosRows);
        if (!in_array($anio, $anios)) $anios[] = $anio;
        rsort($anios);

        // Historial completo
        $this->db->query("
            SELECT fecha, estado, notas
            FROM actividad_asistencias
            WHERE participante_id = :pid
            ORDER BY fecha DESC
            LIMIT 100
        ");
        $this->db->bind(':pid', $id);
        $historial = $this->db->resultSet();

        $this->view('actividades/participante', [
            'participante'  => $participante,
            'anio'          => $anio,
            'anios'         => $anios,
            'grillaMeses'   => $grillaMeses,   // [m][d] => estado (para desglose)
            'grillaMesMeta' => $grillaMesMeta, // [m] => {nombre,dias,stats,tiene} (para calendario)
            'stats'         => $stats,
            'pct'           => $pct,
            'diasSemana'    => $diasSemana,
            'navSemana'     => $navSemana,
            'historial'     => $historial,
        ]);
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

    // =========================================================
    // POST /actividades/eliminarInstitucion  (AJAX → JSON)
    // =========================================================
    public function eliminarInstitucion(): void
    {
        header('Content-Type: application/json');
        $this->parsePostBody();

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

        // Verificar que no tenga actividades asociadas
        $this->db->query("SELECT COUNT(*) AS total FROM actividades_extras WHERE institucion_id = :id");
        $this->db->bind(':id', $id);
        $uso = (int)($this->db->single()->total ?? 0);
        if ($uso > 0) {
            echo json_encode(['success' => false, 'message' => "No se puede eliminar: tiene {$uso} actividad(es) asociada(s)."]);
            exit;
        }

        $this->db->query("DELETE FROM instituciones WHERE id = :id AND categoria = 'actividad'");
        $this->db->bind(':id', $id);
        $this->db->execute();

        echo json_encode(['success' => true, 'message' => 'Institución eliminada.']);
        exit;
    }

    // =========================================================
    // POST /actividades/crearPasanteCorto  (AJAX → JSON)
    // =========================================================
    public function crearPasanteCorto(): void
    {
        header('Content-Type: application/json');
        $this->parsePostBody();

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
        if (!Session::validateCsrfToken($token)) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
            exit;
        }

        $nombres      = trim($_POST['nombres']      ?? '');
        $apellidos    = trim($_POST['apellidos']    ?? '');
        $cedula       = trim($_POST['cedula']       ?? '');
        $correo       = trim($_POST['correo']       ?? '');
        $carrera      = trim($_POST['carrera']      ?? '');
        $instId       = !empty($_POST['institucion_id'])  ? (int)$_POST['institucion_id']  : null;
        $deptoId      = !empty($_POST['departamento_id']) ? (int)$_POST['departamento_id'] : null;
        $tutorId      = !empty($_POST['tutor_id'])        ? (int)$_POST['tutor_id']        : null;
        $periodoId    = !empty($_POST['periodo_id'])      ? (int)$_POST['periodo_id']      : null;
        $fechaInicio  = trim($_POST['fecha_inicio'] ?? '');
        $horasMeta    = !empty($_POST['horas_meta']) ? (int)$_POST['horas_meta'] : 480;
        $fechaFin     = !empty($_POST['fecha_fin'])  ? trim($_POST['fecha_fin'])  : null;

        if (empty($nombres) || empty($apellidos) || empty($cedula) || empty($correo) || empty($fechaInicio)) {
            echo json_encode(['success' => false, 'message' => 'Nombres, apellidos, cédula, correo y fecha de inicio son obligatorios.']);
            exit;
        }

        // Verificar duplicados
        $this->db->query("SELECT id FROM usuarios WHERE correo = :correo OR cedula = :cedula LIMIT 1");
        $this->db->bind(':correo', $correo);
        $this->db->bind(':cedula', $cedula);
        if ($this->db->single()) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un usuario con ese correo o cédula.']);
            exit;
        }

        $tempPass = 'Sgp.' . $cedula;
        $hash     = password_hash($tempPass, PASSWORD_DEFAULT);

        try {
            $this->db->beginTransaction();

            $this->db->query("
                INSERT INTO usuarios (correo, password, cedula, rol_id, estado, requiere_cambio_clave)
                VALUES (:correo, :pass, :cedula, 3, 'activo', 1)
            ");
            $this->db->bind(':correo', $correo);
            $this->db->bind(':pass',   $hash);
            $this->db->bind(':cedula', $cedula);
            $this->db->execute();
            $userId = $this->db->lastInsertId();

            $this->db->query("
                INSERT INTO datos_personales (usuario_id, nombres, apellidos, cedula, cargo)
                VALUES (:uid, :nombres, :apellidos, :cedula, :carrera)
            ");
            $this->db->bind(':uid',      $userId);
            $this->db->bind(':nombres',  $nombres);
            $this->db->bind(':apellidos',$apellidos);
            $this->db->bind(':cedula',   $cedula);
            $this->db->bind(':carrera',  $carrera ?: null);
            $this->db->execute();

            $this->db->query("
                INSERT INTO datos_pasante
                    (usuario_id, tipo_pasantia, estado_pasantia, departamento_asignado_id,
                     tutor_id, periodo_id, institucion_id, institucion_procedencia,
                     horas_meta, fecha_inicio_pasantia, fecha_fin_estimada)
                VALUES
                    (:uid, 'Corta', 'Activo', :depto, :tutor, :periodo, :inst, '', :hmeta, :fi, :ff)
            ");
            $this->db->bind(':uid',    $userId);
            $this->db->bind(':depto',  $deptoId);
            $this->db->bind(':tutor',  $tutorId);
            $this->db->bind(':periodo',$periodoId);
            $this->db->bind(':inst',   $instId);
            $this->db->bind(':hmeta',  $horasMeta);
            $this->db->bind(':fi',     $fechaInicio);
            $this->db->bind(':ff',     $fechaFin);
            $this->db->execute();

            $this->db->commit();

            echo json_encode([
                'success'        => true,
                'message'        => "Pasante {$nombres} {$apellidos} registrado correctamente.",
                'temp_password'  => $tempPass,
                'usuario_id'     => $userId,
            ]);
        } catch (Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Error al registrar el pasante: ' . $e->getMessage()]);
        }
        exit;
    }

    // =========================================================
    // POST /actividades/editarPasanteCorto  (AJAX → JSON)
    // =========================================================
    public function editarPasanteCorto(): void
    {
        header('Content-Type: application/json');
        $this->parsePostBody();

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
        if (!Session::validateCsrfToken($token)) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
            exit;
        }

        $userId    = (int)($_POST['usuario_id']      ?? 0);
        $nombres   = trim($_POST['nombres']           ?? '');
        $apellidos = trim($_POST['apellidos']         ?? '');
        $carrera   = trim($_POST['carrera']           ?? '');
        $instId    = !empty($_POST['institucion_id'])  ? (int)$_POST['institucion_id']  : null;
        $deptoId   = !empty($_POST['departamento_id']) ? (int)$_POST['departamento_id'] : null;
        $tutorId   = !empty($_POST['tutor_id'])        ? (int)$_POST['tutor_id']        : null;
        $periodoId = !empty($_POST['periodo_id'])      ? (int)$_POST['periodo_id']      : null;
        $fechaInicio = trim($_POST['fecha_inicio']    ?? '');
        $horasMeta   = !empty($_POST['horas_meta'])   ? (int)$_POST['horas_meta'] : 480;
        $fechaFin    = !empty($_POST['fecha_fin'])    ? trim($_POST['fecha_fin'])  : null;
        $estado      = trim($_POST['estado_pasantia'] ?? 'Activo');

        if ($userId <= 0 || empty($nombres) || empty($apellidos)) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            exit;
        }

        $estadosValidos = ['Activo', 'Finalizado', 'Retirado', 'Pendiente'];
        if (!in_array($estado, $estadosValidos)) $estado = 'Activo';

        $this->db->query("
            UPDATE datos_personales SET nombres = :nombres, apellidos = :apellidos, cargo = :carrera
            WHERE usuario_id = :uid
        ");
        $this->db->bind(':nombres',  $nombres);
        $this->db->bind(':apellidos',$apellidos);
        $this->db->bind(':carrera',  $carrera ?: null);
        $this->db->bind(':uid',      $userId);
        $this->db->execute();

        $this->db->query("
            UPDATE datos_pasante
            SET departamento_asignado_id = :depto, tutor_id = :tutor, periodo_id = :periodo,
                institucion_id = :inst, horas_meta = :hmeta,
                fecha_inicio_pasantia = :fi, fecha_fin_estimada = :ff, estado_pasantia = :estado
            WHERE usuario_id = :uid AND tipo_pasantia = 'Corta'
        ");
        $this->db->bind(':depto',  $deptoId);
        $this->db->bind(':tutor',  $tutorId);
        $this->db->bind(':periodo',$periodoId);
        $this->db->bind(':inst',   $instId);
        $this->db->bind(':hmeta',  $horasMeta);
        $this->db->bind(':fi',     $fechaInicio ?: null);
        $this->db->bind(':ff',     $fechaFin);
        $this->db->bind(':estado', $estado);
        $this->db->bind(':uid',    $userId);
        $this->db->execute();

        echo json_encode(['success' => true, 'message' => 'Pasante actualizado correctamente.']);
        exit;
    }

    // =========================================================
    // POST /actividades/marcarAsistenciaCorto  (AJAX → JSON)
    // =========================================================
    public function marcarAsistenciaCorto(): void
    {
        header('Content-Type: application/json');
        $this->parsePostBody();

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
        if (!Session::validateCsrfToken($token)) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
            exit;
        }

        $pasanteId = (int)($_POST['pasante_id'] ?? 0);
        $fecha     = trim($_POST['fecha']        ?? date('Y-m-d'));
        $estado    = trim($_POST['estado']       ?? 'Presente');
        $motivo    = trim($_POST['motivo']       ?? '');

        if ($pasanteId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Pasante no especificado.']);
            exit;
        }

        $estadosValidos = ['Presente', 'Ausente', 'Justificado', 'Retardo'];
        if (!in_array($estado, $estadosValidos)) {
            echo json_encode(['success' => false, 'message' => 'Estado inválido.']);
            exit;
        }

        // Verificar que es pasante corto
        $this->db->query("SELECT id FROM datos_pasante WHERE usuario_id = :uid AND tipo_pasantia = 'Corta' LIMIT 1");
        $this->db->bind(':uid', $pasanteId);
        if (!$this->db->single()) {
            echo json_encode(['success' => false, 'message' => 'Pasante no encontrado o no es pasantía corta.']);
            exit;
        }

        $this->db->query("
            INSERT INTO asistencias (pasante_id, fecha, hora_registro, estado, metodo, motivo_justificacion)
            VALUES (:pid, :fecha, NOW(), :estado, 'Manual', :motivo)
            ON DUPLICATE KEY UPDATE
                estado = VALUES(estado),
                motivo_justificacion = VALUES(motivo_justificacion),
                hora_registro = VALUES(hora_registro)
        ");
        $this->db->bind(':pid',    $pasanteId);
        $this->db->bind(':fecha',  $fecha);
        $this->db->bind(':estado', $estado);
        $this->db->bind(':motivo', $motivo ?: null);
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
    // GET /actividades/asistenciaCorto  (AJAX → JSON)
    // Devuelve asistencias de un pasante corto en rango dado
    // =========================================================
    public function asistenciaCorto(): void
    {
        header('Content-Type: application/json');

        $pasanteId = (int)($_GET['pasante_id'] ?? 0);
        $modo      = trim($_GET['modo'] ?? 'semanal');   // diaria | semanal | mensual
        $fecha     = trim($_GET['fecha'] ?? date('Y-m-d'));

        if ($pasanteId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Pasante no especificado.']);
            exit;
        }

        if ($modo === 'diaria') {
            $inicio = $fecha;
            $fin    = $fecha;
        } elseif ($modo === 'semanal') {
            $lunes  = date('Y-m-d', strtotime('monday this week', strtotime($fecha)));
            $inicio = $lunes;
            $fin    = date('Y-m-d', strtotime('+4 days', strtotime($lunes)));
        } else { // mensual
            $inicio = date('Y-m-01', strtotime($fecha));
            $fin    = date('Y-m-t',  strtotime($fecha));
        }

        $this->db->query("
            SELECT fecha, hora_registro, estado, motivo_justificacion, metodo, es_auto_fill
            FROM asistencias
            WHERE pasante_id = :pid AND fecha BETWEEN :inicio AND :fin
            ORDER BY fecha ASC
        ");
        $this->db->bind(':pid',    $pasanteId);
        $this->db->bind(':inicio', $inicio);
        $this->db->bind(':fin',    $fin);
        $registros = $this->db->resultSet();

        $map = [];
        foreach ($registros as $r) {
            $map[$r->fecha] = [
                'estado'  => $r->estado,
                'hora'    => $r->hora_registro,
                'metodo'  => $r->metodo,
                'motivo'  => $r->motivo_justificacion,
                'autofill'=> (bool)$r->es_auto_fill,
            ];
        }

        echo json_encode([
            'success'  => true,
            'inicio'   => $inicio,
            'fin'      => $fin,
            'modo'     => $modo,
            'registros'=> $map,
        ]);
        exit;
    }

    // =========================================================
    // GET /actividades/verificarCampo  (AJAX → JSON)
    // Verifica si correo, cédula o teléfono ya existen en BD
    // =========================================================
    public function verificarCampo(): void
    {
        header('Content-Type: application/json');

        $campo = trim($_GET['campo'] ?? '');
        $valor = trim($_GET['valor'] ?? '');

        if (empty($valor) || !in_array($campo, ['correo', 'cedula', 'telefono'])) {
            echo json_encode(['disponible' => true]);
            exit;
        }

        if ($campo === 'telefono') {
            // Verificar en datos_personales
            $this->db->query("SELECT COUNT(*) AS total FROM datos_personales WHERE telefono = :valor LIMIT 1");
            $this->db->bind(':valor', $valor);
        } else {
            // Verificar en tabla usuarios (correo y cedula)
            $this->db->query("SELECT COUNT(*) AS total FROM usuarios WHERE {$campo} = :valor LIMIT 1");
            $this->db->bind(':valor', $valor);
        }

        $res = $this->db->single();
        $existe = (int)($res->total ?? 0) > 0;

        echo json_encode([
            'disponible' => !$existe,
            'campo'      => $campo,
            'mensaje'    => $existe ? "Este {$campo} ya está registrado en el sistema." : '',
        ]);
        exit;
    }

    // =========================================================
    // POST /actividades/eliminar/ID
    // =========================================================
    public function eliminar(int $id = 0): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/actividades/servicio');
            exit;
        }

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de seguridad inválido.');
            header('Location: ' . URLROOT . '/actividades/servicio');
            exit;
        }

        if ($id <= 0) {
            Session::setFlash('error', 'ID de actividad inválido.');
            header('Location: ' . URLROOT . '/actividades/servicio');
            exit;
        }

        // Verificar que existe
        $this->db->query("SELECT id, nombre FROM actividades_extras WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $act = $this->db->single();

        if (!$act) {
            Session::setFlash('error', 'La actividad no existe.');
            header('Location: ' . URLROOT . '/actividades/servicio');
            exit;
        }

        // Eliminar participantes primero (FK constraint)
        $this->db->query("DELETE FROM actividad_participantes WHERE actividad_id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();

        // Eliminar la actividad
        $this->db->query("DELETE FROM actividades_extras WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();

        Session::setFlash('success', 'Actividad «' . $act->nombre . '» eliminada correctamente.');
        header('Location: ' . URLROOT . '/actividades/servicio');
        exit;
    }
}
