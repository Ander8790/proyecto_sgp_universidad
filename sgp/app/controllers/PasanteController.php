<?php
/**
 * PasanteController — Gestión de Pasantes
 *
 * ARQUITECTURA POST-SANEAMIENTO (Fase 1):
 * Todo centralizado en la tabla `usuarios`. Sin JOINs con datos_personales
 * ni datos_pasante (tablas eliminadas).
 *
 * RUTAS:
 *   GET  /pasantes           → index()      Lista admin con modal
 *   GET  /pasantes/show/ID   → show()       Kardex individual
 *   POST /pasantes/asignar   → asignar()    JSON — asignar departamento + fechas
 *   POST /pasantes/finalizar_pasantia → finalizarPasantia() JSON — marcar como finalizado
 *
 * @version 3.0 — Tabla Única
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
        $this->db = new Database($config['db']);

        require_once '../app/models/PasanteModel.php';
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
            'title'   => 'Kardex — ' . ($pasante->nombres ?? '') . ' ' . ($pasante->apellidos ?? ''),
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

        $userId = (int)Session::get('user_id');
        $pasante = $this->pasanteModel->getByUsuarioId($userId);

        // Fetch recent activities for the dashboard feed
        $this->db->query("
            SELECT a.*, d.nombre as departamento_nombre
            FROM asistencias a
            JOIN datos_pasante dp ON a.pasante_id = dp.usuario_id
            LEFT JOIN departamentos d ON dp.departamento_asignado_id = d.id
            WHERE a.pasante_id = :uid
            ORDER BY a.fecha DESC, a.hora_registro DESC
            LIMIT 5
        ");
        $this->db->bind(':uid', $userId);
        $actividades = $this->db->resultSet();

        $this->view('pasante/dashboard', [
            'title'   => 'Mi Panel',
            'pasante' => $pasante,
            'actividades' => $actividades,
            'user_name' => Session::get('user_name') ?? 'Pasante'
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

        $this->db->query("
            SELECT * FROM asistencias 
            WHERE pasante_id = :uid 
            ORDER BY fecha DESC, hora_registro DESC
        ");
        $this->db->bind(':uid', $userId);
        $asistencias = $this->db->resultSet();

        $this->view('pasante/asistencia', [
            'title' => 'Mi Asistencia',
            'pasante' => $pasante,
            'asistencias' => $asistencias
        ]);
    }

    // ────────────────────────────────────────────────────────────────
    // ENDPOINTS JSON — MODAL DE ASIGNACIÓN (FASE 5)
    // ────────────────────────────────────────────────────────────────

    /**
     * Asignar Pasante — El Modal Definitivo.
     *
     * Recibe por POST:
     *   - pasante_id    int
     *   - departamento_id int
     *   - fecha_inicio  string Y-m-d  (por defecto: hoy)
     *
     * Calcula fecha_fin_estimada usando 180 días hábiles (lun-vie).
     * Hace un único UPDATE atómico en usuarios.
     * Devuelve JSON { success, message, fecha_fin_estimada }.
     */
    public function asignar(): void
    {
        header('Content-Type: application/json');

        // Solo el Admin puede asignar
        if (Session::get('role_id') != 1) {
            echo json_encode(['success' => false, 'message' => 'No autorizado.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        // Captura y saneamiento de datos
        $pasanteId      = (int)($_POST['pasante_id']     ?? 0);
        $departamentoId = (int)($_POST['departamento_id'] ?? 0);
        $fechaInicio    = trim($_POST['fecha_inicio']     ?? '');

        // Fecha por defecto: hoy
        if (!$fechaInicio) {
            $fechaInicio = date('Y-m-d');
        }

        // Validaciones básicas
        if ($pasanteId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de pasante inválido.']);
            exit;
        }

        if ($departamentoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Debes seleccionar un departamento.']);
            exit;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)) {
            echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido. Use AAAA-MM-DD.']);
            exit;
        }

        // Calcular fecha fin (180 días hábiles)
        $fechaFin = $this->pasanteModel->calcularFechaFin($fechaInicio, 180);

        // Ejecutar asignación
        $ok = $this->pasanteModel->asignar($pasanteId, $departamentoId, $fechaInicio);

        if ($ok) {
            // Bitácora
            try {
                require_once '../app/models/AuditModel.php';
                AuditModel::log('ASIGNAR_PASANTE', 'usuarios', $pasanteId, [
                    'departamento_id' => $departamentoId,
                    'fecha_inicio'    => $fechaInicio,
                    'fecha_fin'       => $fechaFin,
                    'admin_id'        => Session::get('user_id'),
                ]);
            } catch (Throwable $e) {
                // Bitácora falla silenciosamente — la asignación ya se guardó
                error_log('⚠️ AuditModel::log falló: ' . $e->getMessage());
            }

            // Formatear fecha fin para mostrar en el modal
            $fechaFinFormato = (new DateTime($fechaFin))->format('d/m/Y');

            echo json_encode([
                'success'             => true,
                'message'             => '✅ Pasante asignado exitosamente.',
                'fecha_fin_estimada'  => $fechaFin,
                'fecha_fin_formato'   => $fechaFinFormato,
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar la asignación. Verifica que el pasante exista.',
            ]);
        }

        exit;
    }

    /**
     * Finalizar Pasantía — marcar como Finalizado.
     *
     * Recibe por POST: pasante_id
     * Devuelve JSON { success, message }
     */
    public function finalizar_pasantia(): void
    {
        header('Content-Type: application/json');

        if (Session::get('role_id') != 1) {
            echo json_encode(['success' => false, 'message' => 'No autorizado.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $pasanteId = (int)($_POST['pasante_id'] ?? 0);

        if ($pasanteId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de pasante inválido.']);
            exit;
        }

        $ok = $this->pasanteModel->finalizar($pasanteId);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? '✅ Pasantía marcada como Finalizada.' : 'Error al actualizar el estado.',
        ]);

        exit;
    }
}
