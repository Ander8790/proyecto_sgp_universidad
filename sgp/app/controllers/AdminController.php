<?php
/**
 * AdminController — Panel de Control del Administrador (Rol ID = 1)
 *
 * Obtiene métricas reales de la BD para poblar el dashboard usando Clean MVC.
 */

declare(strict_types=1);

class AdminController extends Controller
{
    private $dashboardModel;

    public function __construct()
    {
        Session::start();
        AuthMiddleware::require();
        AuthMiddleware::verificarEstado();

        // 🔒 NUEVO: Protección Anti-Caché estricta
        CacheControl::noCache();

        if (!RoleMiddleware::hasAnyRole([1])) {
            RoleMiddleware::redirectToRoleDashboard(Session::get('role_id'));
        }

        // Instanciar el modelo con el método heredado del Controller base
        $this->dashboardModel = $this->model('DashboardAdminModel');
    }

    /**
     * GET /admin
     * Dashboard principal con métricas reales del sistema a través del Modelo.
     */
    public function index(): void
    {
        // Traer métricas base en una sola consulta
        $kpis = $this->dashboardModel->getKpiTotales();

        // Traer y estructurar la data cruzada para la vista y el Data Bridge JSON
        $data = [
            'user_name'          => Session::get('user_name') ?? 'Administrador',
            'role'               => 'Administrador',
            'totalActivos'       => $kpis->totalActivos,
            'pendientesAsignar'  => $kpis->pendientesAsignar,
            'asistenciasHoy'     => $kpis->asistenciasHoy,
            'faltasHoy'          => $kpis->faltasHoy,
            'totalTutores'       => $kpis->totalTutores,
            'totalInstituciones' => $kpis->totalInstituciones,
            'actividadReciente'  => $this->dashboardModel->getActividadReciente(8),
            'alertas_pendientes' => $this->dashboardModel->getAlertasPendientes(6),
            'departamentos'      => $this->dashboardModel->getDepartamentosParaAsignacion(),
            'tutores'            => $this->dashboardModel->getTutoresParaAsignacion(),
            
            // ── NUEVO: DATA BRIDGE (Gráficas Dinámicas) ──
            'metricas_graficos' => [
                'asistenciaDiaria'        => $this->dashboardModel->getAsistenciaDiaria(),
                'asistenciaSemanal'       => $this->dashboardModel->getAsistenciaSemanal(),
                'asistenciaMensual'       => $this->dashboardModel->getAsistenciaMensual(),
                'asistenciaDepartamento'  => $this->dashboardModel->getAsistenciaPorDepartamento(),
            ]
        ];

        $this->view('admin/dashboard', $data);
    }
}
