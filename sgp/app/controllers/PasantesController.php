<?php
/**
 * PasantesController - Vista Admin: Gestión de Pasantes
 *
 * Maneja la URL /pasantes — exclusiva para Administradores.
 * No confundir con PasanteController (rol pasante, URL /pasante).
 */

declare(strict_types=1);

class PasantesController extends Controller
{
    private $pasanteModel;
    private Database $db;

    public function __construct()
    {
        Session::start();
        AuthMiddleware::require();

        // Solo administradores
        if (!RoleMiddleware::hasAnyRole([1])) {
            RoleMiddleware::redirectToRoleDashboard(Session::get('role_id'));
        }

        require_once APPROOT . '/models/PasanteModel.php';
        $this->pasanteModel = new PasanteModel();

        $config   = require APPROOT . '/config/config.php';
        $this->db = new Database($config['db']);
    }

    /**
     * GET /pasantes
     * Lista todos los pasantes del sistema para el administrador.
     */
    public function index(): void
    {
        $pasantes = $this->pasanteModel->getAll();

        // Contadores para KPI cards
        $total      = count($pasantes);
        $enCurso    = count(array_filter($pasantes, fn($p) => ($p->estado_pasantia ?? '') === 'Activo'));
        $pendientes = count(array_filter($pasantes, fn($p) => ($p->estado_pasantia ?? '') === 'Pendiente'));
        $culminados = count(array_filter($pasantes, fn($p) => ($p->estado_pasantia ?? '') === 'Finalizado'));

        // Departamentos activos para el modal de asignación
        $this->db->query("SELECT id, nombre FROM departamentos WHERE activo = 1 ORDER BY nombre ASC");
        $departamentos = $this->db->resultSet();

        // Tutores activos para el modal
        $this->db->query("
            SELECT u.id, dp.nombres, dp.apellidos
            FROM usuarios u
            LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
            WHERE u.rol_id = 2 AND u.estado = 'activo'
            ORDER BY IFNULL(dp.apellidos, u.correo) ASC
        ");
        $tutores = $this->db->resultSet();

        $this->view('pasantes/index', [
            'pasantes'     => $pasantes,
            'total'        => $total,
            'enCurso'      => $enCurso,
            'pendientes'   => $pendientes,
            'culminados'   => $culminados,
            'departamentos'=> $departamentos,
            'tutores'      => $tutores,
        ]);
    }

    /**
     * AJAX: Consulta Rápida de Pasante (Bento Box UI)
     * GET /pasantes/consultaRapida?q=query
     */
    public function consultaRapida(): void
    {
        header('Content-Type: application/json');
        $q = trim($_GET['q'] ?? '');

        if (strlen($q) < 3) {
            echo json_encode(['success' => false, 'message' => 'Escribe al menos 3 caracteres.']);
            exit;
        }

        $this->db->query("
            SELECT
                u.id, u.cedula, u.correo, u.rol_id,
                dp.nombres, dp.apellidos, dp.telefono,
                dpa.institucion_procedencia,
                dpa.departamento_asignado_id, dpa.tutor_id,
                COALESCE(dpa.estado_pasantia, 'Sin Asignar') AS estado_pasantia,
                COALESCE(dpa.horas_acumuladas, 0)            AS horas_acumuladas,
                COALESCE(dpa.horas_meta, 0)                  AS horas_meta,
                dpa.fecha_inicio_pasantia                    AS fecha_inicio,
                dpa.fecha_fin_estimada,
                d.nombre                                     AS departamento_nombre,
                CONCAT(tup.nombres, ' ', tup.apellidos)      AS tutor_nombre,
                u.estado AS estado_usuario
            FROM usuarios u
            LEFT JOIN datos_personales dp  ON dp.usuario_id  = u.id
            LEFT JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos    d   ON d.id = dpa.departamento_asignado_id
            LEFT JOIN usuarios         tu  ON tu.id = dpa.tutor_id
            LEFT JOIN datos_personales tup ON tup.usuario_id = tu.id
            WHERE u.rol_id = 3 AND (u.cedula LIKE :q OR dp.nombres LIKE :q OR dp.apellidos LIKE :q)
            LIMIT 1
        ");
        $this->db->bind(':q', "%$q%");
        $result = $this->db->single();

        echo json_encode(['success' => !!$result, 'data' => $result]);
        exit;
    }

    /**
     * AJAX: Resetear PIN del Pasante para el Kiosco
     * POST /pasantes/resetPin
     */
    public function resetPin(): void
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $pasanteId = (int)($_POST['pasante_id'] ?? 0);

        if (!$pasanteId) {
            echo json_encode(['success' => false, 'message' => 'ID de pasante inválido.']);
            exit;
        }

        // Generar un nuevo PIN aleatorio de 4 dígitos
        $nuevoPin = str_pad((string)mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

        // Guardar el PIN en texto plano en la tabla usuarios (así lo maneja KioscoController)
        $this->db->query("UPDATE usuarios SET pin_asistencia = :pin WHERE id = :id AND rol_id = 3");
        $this->db->bind(':pin', $nuevoPin);
        $this->db->bind(':id', $pasanteId);
        
        if ($this->db->execute()) {
            AuditModel::log('RESET_PIN', "Se reseteó el PIN del pasante ID: $pasanteId");
            echo json_encode([
                'success' => true, 
                'message' => 'PIN reseteado correctamente.',
                'nuevo_pin' => $nuevoPin
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Error al resetear el PIN en la base de datos.'
            ]);
        }
        exit;
    }

    /**
     * AJAX: Cambiar Estado de Pasantía
     * POST /pasantes/cambiarEstado
     */
    public function cambiarEstado(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $pasanteId = (int)($_POST['pasante_id'] ?? 0);
        $estado    = trim($_POST['estado'] ?? '');

        if (!$pasanteId) {
            echo json_encode(['success' => false, 'message' => 'ID de pasante inválido.']);
            exit;
        }

        $estadosPermitidos = ['Pendiente', 'Activo', 'Finalizado', 'Retirado'];
        if (!in_array($estado, $estadosPermitidos)) {
            echo json_encode(['success' => false, 'message' => 'Estado no válido.']);
            exit;
        }

        $this->db->query("UPDATE datos_pasante SET estado_pasantia = :estado WHERE usuario_id = :id");
        $this->db->bind(':estado', $estado);
        $this->db->bind(':id', $pasanteId);

        if ($this->db->execute()) {
            // --- Notificar al Pasante ---
            require_once APPROOT . '/models/NotificationModel.php';
            $notificationModel = new NotificationModel($this->db);
            $notificationModel->create(
                $pasanteId,
                'cambio_estado',
                'Actualización de Estado',
                "El estado de tu pasantía ha cambiado a: {$estado}.",
                URLROOT . '/perfil'
            );

            AuditModel::log('CHANGE_PASANTE_STATUS', "Pasante ID $pasanteId → Estado: $estado");
            echo json_encode([
                'success' => true,
                'message' => "Estado actualizado a '$estado'."
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el estado.'
            ]);
        }
        exit;
    }
}
