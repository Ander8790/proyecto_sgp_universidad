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
    private $asignacionModel;

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
        
        $this->asignacionModel = $this->model('Asignacion');
    }

    // ─────────────────────────────────────────────────────────
    // GET /asignaciones  — Listado
    // ─────────────────────────────────────────────────────────
    public function index(): void
    {
        // Todas las asignaciones obtenidas desde el modelo
        $asignaciones = $this->asignacionModel->getAll();

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
        $autoRellenar = isset($_POST['auto_rellenar']) && $_POST['auto_rellenar'] == '1';

        if ($this->asignacionModel->guardar($pasanteId, $departamentoId, $tutorIdVal, $horasMeta, $fechaInicio, $fechaFin)) {
            
            // -------------------------------------------------------------
            // FASE 1: AUTO-RELLENADO PARA PASANTES TARDÍOS
            // -------------------------------------------------------------
            if ($autoRellenar) {
                // Se usa date() en vez de 'new DateTime(hoy)' o 'date('Y-m-d')' para poder parsear
                $fechaActual = date('Y-m-d');
                if ($fechaInicio < $fechaActual) {
                    try {
                        $inicioObj = new DateTime($fechaInicio);
                        $hoyObj    = new DateTime($fechaActual);
                        
                        while ($inicioObj < $hoyObj) {
                            $diaSemana = (int)$inicioObj->format('N');
                            if ($diaSemana >= 1 && $diaSemana <= 5) { // Lunes a Viernes
                                $fechaLoop = $inicioObj->format('Y-m-d');
                                
                                // Asegurar que no haya registros ese mismo día
                                $this->db->query("SELECT id FROM asistencias WHERE pasante_id = :pid AND fecha = :fecha LIMIT 1");
                                $this->db->bind(':pid', $pasanteId);
                                $this->db->bind(':fecha', $fechaLoop);
                                
                                if (!$this->db->single()) {
                                    // 🚨 CORECCIÓN APLICADA: METODO = 'Manual'
                                    $this->db->query("
                                        INSERT INTO asistencias (pasante_id, fecha, hora_registro, estado, metodo, motivo_justificacion)
                                        VALUES (:pid, :fecha, '08:00:00', 'Justificado', 'Manual', 'Ingreso tardío por trámites administrativos')
                                    ");
                                    $this->db->bind(':pid', $pasanteId);
                                    $this->db->bind(':fecha', $fechaLoop);
                                    $this->db->execute();
                                }
                            }
                            $inicioObj->modify('+1 day');
                        }
                    } catch (Exception $e) {
                        // Ignorar cualquier fallo del loop y continuar
                    }
                }
            }
            
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
                "Has sido asignado a {$deptNombre}. Fecha de inicio: " . date('d/m/Y', strtotime($fechaInicio)) . ".",
                URLROOT . '/perfil'
            );

            echo json_encode([
                'success'   => true,
                'message'   => 'Asignación guardada' . ($autoRellenar ? ' y rellenada' : '') . ' correctamente.',
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

        if ($this->asignacionModel->activar($pasanteId)) {
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

        if ($this->asignacionModel->finalizar($pasanteId)) {
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

        if ($this->asignacionModel->eliminar($pasanteId)) {
            echo json_encode(['success' => true, 'message' => 'Asignación eliminada. El pasante queda sin asignar.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la asignación.']);
        }
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // POST /asignaciones/buscarPasanteAjax  — (Buscador AJAX Modal)
    // ─────────────────────────────────────────────────────────
    public function buscarPasanteAjax(): void
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([]);
            exit;
        }

        $query = trim($_POST['query'] ?? '');
        if (strlen($query) < 2) {
            echo json_encode([]);
            exit;
        }

        $sql = "
            SELECT u.id AS pasante_id, u.cedula, dp.nombres, dp.apellidos, 
                   COALESCE(dpa.institucion_procedencia, 'No especificada') AS institucion_procedencia, 
                   dpa.estado_pasantia
            FROM usuarios u
            JOIN datos_personales dp ON u.id = dp.usuario_id
            LEFT JOIN datos_pasante dpa ON u.id = dpa.usuario_id
            WHERE u.rol_id = 3
              AND (dpa.estado_pasantia IN ('Pendiente', 'Sin Asignar') OR dpa.estado_pasantia IS NULL OR dpa.estado_pasantia = '')
              AND (u.cedula LIKE :q OR dp.nombres LIKE :q OR dp.apellidos LIKE :q)
            LIMIT 10
        ";
        $this->db->query($sql);
        $this->db->bind(':q', "%{$query}%");
        
        echo json_encode($this->db->resultSet());
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // POST /asignaciones/getDetalleAjax  — (Detalle de Modal)
    // ─────────────────────────────────────────────────────────
    public function getDetalleAjax(): void
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        $pasanteId = (int)($_POST['pasante_id'] ?? 0);
        if (!$pasanteId) {
            echo json_encode(['error' => 'ID de pasante inválido']);
            exit;
        }

        $detalle = $this->asignacionModel->getById($pasanteId);
        if ($detalle) {
            echo json_encode($detalle);
        } else {
            echo json_encode(['error' => 'No se encontraron detalles para este pasante']);
        }
        exit;
    }
}
