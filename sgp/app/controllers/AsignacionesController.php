<?php
/**
 * AsignacionesController — Gestión de Asignaciones de Pasantes
 *
 * Maneja la URL /asignaciones — exclusiva para Administradores.
 * Permite crear, editar, activar/finalizar y eliminar asignaciones
 * de pasantes a departamentos y tutores.
 *
 * ENDPOINTS:
 *   GET  /asignaciones           → index()    : listado completo
 *   POST /asignaciones/guardar   → guardar()  : crear nueva asignación (AJAX→JSON)
 *   POST /asignaciones/activar   → activar()  : cambiar estado a Activo (AJAX→JSON)
 *   POST /asignaciones/finalizar → finalizar(): cambiar estado a Finalizado (AJAX→JSON)
 *   POST /asignaciones/eliminar  → eliminar() : borrar registro (AJAX→JSON)
 */

declare(strict_types=1);

class AsignacionesController extends Controller
{
    private Database $db;

    public function __construct()
    {
        Session::start();
        AuthMiddleware::require();
        AuthMiddleware::verificarEstado();

        if (!RoleMiddleware::hasAnyRole([1])) {
            RoleMiddleware::redirectToRoleDashboard(Session::get('role_id'));
        }

        $config    = require APPROOT . '/config/config.php';
        $this->db  = new Database($config['db']);
    }

    // ─────────────────────────────────────────────────────────
    // GET /asignaciones  — Listado
    // ─────────────────────────────────────────────────────────
    public function index(): void
    {
        // Todas las asignaciones (desde datos_pasante que es la tabla de asignación)
        $this->db->query("
            SELECT
                u.id                AS pasante_id,
                u.cedula,
                dp.nombres,
                dp.apellidos,
                dpa.institucion_procedencia,
                COALESCE(dpa.estado_pasantia, 'Sin Asignar') AS estado_pasantia,
                COALESCE(dpa.horas_acumuladas, 0)            AS horas_acumuladas,
                COALESCE(dpa.horas_meta, 1440)               AS horas_meta,
                dpa.fecha_inicio_pasantia,
                dpa.fecha_fin_estimada,
                d.id               AS departamento_id,
                d.nombre           AS departamento_nombre,
                tu.id              AS tutor_id,
                tup.nombres        AS tutor_nombres,
                tup.apellidos      AS tutor_apellidos
            FROM usuarios u
            LEFT JOIN datos_personales dp  ON dp.usuario_id  = u.id
            LEFT JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos    d   ON d.id = dpa.departamento_asignado_id
            LEFT JOIN usuarios         tu  ON tu.id = dpa.tutor_id
            LEFT JOIN datos_personales tup ON tup.usuario_id = tu.id
            WHERE u.rol_id = 3 AND u.estado = 'activo'
            ORDER BY
                FIELD(COALESCE(dpa.estado_pasantia,'Sin Asignar'),
                      'Activo','Pendiente','Sin Asignar','Finalizado','Retirado'),
                IFNULL(dp.apellidos, u.correo) ASC
        ");
        $asignaciones = $this->db->resultSet();

        // Departamentos para el select del modal
        $this->db->query("SELECT id, nombre FROM departamentos WHERE activo = 1 ORDER BY nombre ASC");
        $departamentos = $this->db->resultSet();

        // Tutores activos para el select del modal
        $this->db->query("
            SELECT u.id, dp.nombres, dp.apellidos, d.nombre AS departamento_nombre
            FROM usuarios u
            LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
            LEFT JOIN departamentos    d  ON d.id = u.departamento_id
            WHERE u.rol_id = 2 AND u.estado = 'activo'
            ORDER BY IFNULL(dp.apellidos, u.correo) ASC
        ");
        $tutores = $this->db->resultSet();

        // KPIs
        $activos    = count(array_filter($asignaciones, fn($a) => $a->estado_pasantia === 'Activo'));
        $pendientes = count(array_filter($asignaciones, fn($a) => in_array($a->estado_pasantia, ['Pendiente','Sin Asignar'])));
        $sinAsignar = count(array_filter($asignaciones, fn($a) => $a->estado_pasantia === 'Sin Asignar'));
        $finalizados= count(array_filter($asignaciones, fn($a) => $a->estado_pasantia === 'Finalizado'));

        $this->view('asignaciones/index', [
            'asignaciones' => $asignaciones,
            'departamentos'=> $departamentos,
            'tutores'      => $tutores,
            'activos'      => $activos,
            'pendientes'   => $pendientes,
            'sinAsignar'   => $sinAsignar,
            'finalizados'  => $finalizados,
            'total'        => count($asignaciones),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // POST /asignaciones/guardar  — Crear/Actualizar asignación (AJAX)
    // ─────────────────────────────────────────────────────────
    public function guardar(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $pasanteId      = (int)($_POST['pasante_id']      ?? 0);
        $departamentoId = (int)($_POST['departamento_id'] ?? 0);
        $tutorId        = (int)($_POST['tutor_id']        ?? 0);
        $horasMeta      = (int)($_POST['horas_meta']      ?? 1440);
        $fechaInicio    = trim($_POST['fecha_inicio']     ?? '');

        if (!$pasanteId || !$departamentoId || !$fechaInicio) {
            echo json_encode(['success' => false, 'message' => 'Pasante, departamento y fecha de inicio son obligatorios.']);
            exit;
        }

        // Calcular fecha fin (días hábiles L-V)
        try {
            $inicio  = new DateTime($fechaInicio);
            $diasTotal = (int)ceil($horasMeta / 8);
            $fin     = clone $inicio;
            $conteo  = 0;
            while ($conteo < $diasTotal) {
                $fin->modify('+1 day');
                $dow = (int)$fin->format('N');
                if ($dow < 6) $conteo++;
            }
            $fechaFin = $fin->format('Y-m-d');
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Fecha de inicio inválida.']);
            exit;
        }

        $tutorIdVal = $tutorId > 0 ? $tutorId : null;

        // UPSERT en datos_pasante
        $this->db->query("
            INSERT INTO datos_pasante
                (usuario_id, departamento_asignado_id, tutor_id, horas_meta,
                 fecha_inicio_pasantia, fecha_fin_estimada, estado_pasantia)
            VALUES
                (:uid, :dept_id, :tutor_id, :horas_meta, :fecha_inicio, :fecha_fin, 'Pendiente')
            ON DUPLICATE KEY UPDATE
                departamento_asignado_id = VALUES(departamento_asignado_id),
                tutor_id                 = VALUES(tutor_id),
                horas_meta               = VALUES(horas_meta),
                fecha_inicio_pasantia    = VALUES(fecha_inicio_pasantia),
                fecha_fin_estimada       = VALUES(fecha_fin_estimada)
        ");
        $this->db->bind(':uid',          $pasanteId);
        $this->db->bind(':dept_id',      $departamentoId);
        $this->db->bind(':tutor_id',     $tutorIdVal);
        $this->db->bind(':horas_meta',   $horasMeta);
        $this->db->bind(':fecha_inicio', $fechaInicio);
        $this->db->bind(':fecha_fin',    $fechaFin);

        if ($this->db->execute()) {
            // --- Notificar al Pasante ---
            require_once APPROOT . '/models/NotificationModel.php';
            $notificationModel = new NotificationModel($this->db);
            $this->db->query("SELECT nombre FROM departamentos WHERE id = :did");
            $this->db->bind(':did', $departamentoId);
            $deptInfo = $this->db->single();
            $deptNombre = $deptInfo ? $deptInfo->nombre : 'un departamento';
            
            $notificationModel->create(
                $pasanteId,
                'asignacion_nueva',
                'Nueva Asignación de Pasantía',
                "Has sido asignado a {$deptNombre}. Fecha de inicio: {$fechaInicio}.",
                URLROOT . '/perfil'
            );

            echo json_encode([
                'success'   => true,
                'message'   => 'Asignación guardada correctamente.',
                'fecha_fin' => $fechaFin,
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar la asignación en la base de datos.']);
        }
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // POST /asignaciones/activar  — Activar pasantía (AJAX)
    // ─────────────────────────────────────────────────────────
    public function activar(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $pasanteId = (int)($_POST['pasante_id'] ?? 0);
        if (!$pasanteId) {
            echo json_encode(['success' => false, 'message' => 'ID de pasante inválido.']);
            exit;
        }

        $this->db->query("
            UPDATE datos_pasante SET estado_pasantia = 'Activo' WHERE usuario_id = :uid
        ");
        $this->db->bind(':uid', $pasanteId);

        if ($this->db->execute()) {
            echo json_encode(['success' => true, 'message' => 'Pasantía activada. El pasante puede registrar asistencia en el Kiosco.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo activar la pasantía.']);
        }
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // POST /asignaciones/finalizar  — Finalizar pasantía (AJAX)
    // ─────────────────────────────────────────────────────────
    public function finalizar(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $pasanteId = (int)($_POST['pasante_id'] ?? 0);
        if (!$pasanteId) {
            echo json_encode(['success' => false, 'message' => 'ID de pasante inválido.']);
            exit;
        }

        $this->db->query("
            UPDATE datos_pasante SET estado_pasantia = 'Finalizado' WHERE usuario_id = :uid
        ");
        $this->db->bind(':uid', $pasanteId);

        if ($this->db->execute()) {
            echo json_encode(['success' => true, 'message' => 'Pasantía marcada como Finalizada.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo finalizar la pasantía.']);
        }
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // POST /asignaciones/eliminar  — Eliminar asignación (AJAX)
    // ─────────────────────────────────────────────────────────
    public function eliminar(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $pasanteId = (int)($_POST['pasante_id'] ?? 0);
        if (!$pasanteId) {
            echo json_encode(['success' => false, 'message' => 'ID de pasante inválido.']);
            exit;
        }

        $this->db->query("
            UPDATE datos_pasante
            SET departamento_asignado_id = NULL,
                tutor_id     = NULL,
                estado_pasantia          = 'Sin Asignar',
                fecha_inicio_pasantia    = NULL,
                fecha_fin_estimada       = NULL
            WHERE usuario_id = :uid
        ");
        $this->db->bind(':uid', $pasanteId);

        if ($this->db->execute()) {
            echo json_encode(['success' => true, 'message' => 'Asignación eliminada. El pasante queda sin asignar.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la asignación.']);
        }
        exit;
    }
}
