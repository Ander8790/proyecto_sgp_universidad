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

        // Sobreescribir horas_acumuladas con el valor Pro-Rata real
        if ($pasante) {
            $pasante->horas_acumuladas = $proRata->horas_mostradas;
            $pasante->horas_meta       = $proRata->horas_meta;
        }

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
            'title'      => 'Mi Panel',
            'pasante'    => $pasante,
            'actividades'=> $actividades,
            'user_name'  => Session::get('user_name') ?? 'Pasante',
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
}
