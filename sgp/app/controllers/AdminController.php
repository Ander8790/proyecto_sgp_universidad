<?php
/**
 * AdminController — Panel de Control del Administrador (Rol ID = 1)
 *
 * Obtiene métricas reales de la BD para poblar el dashboard.
 */

declare(strict_types=1);

class AdminController extends Controller
{
    private Database $db;

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

        $config   = require APPROOT . '/config/config.php';
        $this->db = new Database($config['db']);
    }

    /**
     * GET /admin
     * Dashboard principal con métricas reales del sistema.
     */
    public function index(): void
    {
        // ── KPI 1: Total Pasantes Activos ────────────────────────
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM datos_pasante
            WHERE estado_pasantia = 'Activo'
        ");
        $totalActivos = (int)($this->db->single()->total ?? 0);

        // ── KPI 2: Total Tutores Activos ─────────────────────────
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM usuarios
            WHERE rol_id = 2 AND estado = 'activo'
        ");
        $totalTutores = (int)($this->db->single()->total ?? 0);

        // ── KPI 3: Asistencias hoy ───────────────────────────────
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM asistencias
            WHERE fecha = CURDATE() AND estado = 'Presente'
        ");
        $asistenciasHoy = (int)($this->db->single()->total ?? 0);

        // ── KPI 4: Total Instituciones ───────────────────────────
        // Contamos las instituciones distintas registradas en datos_pasante
        $this->db->query("
            SELECT COUNT(DISTINCT institucion_procedencia) AS total
            FROM datos_pasante
            WHERE institucion_procedencia IS NOT NULL AND institucion_procedencia != ''
        ");
        $totalInstituciones = (int)($this->db->single()->total ?? 0);

        // ── Actividad reciente (últimas 5 asistencias) ───────────
        $this->db->query("
            SELECT
                a.id,
                a.fecha,
                a.hora_registro,
                a.estado,
                a.metodo,
                dp.nombres,
                dp.apellidos,
                u.cedula
            FROM asistencias a
            INNER JOIN usuarios         u  ON u.id  = a.pasante_id
            LEFT  JOIN datos_personales dp ON dp.usuario_id = a.pasante_id
            ORDER BY a.fecha DESC, a.hora_registro DESC
            LIMIT 5
        ");
        $actividadReciente = $this->db->resultSet();

        // ── Pasantes por estado (para gráfico radial) ────────────
        $this->db->query("
            SELECT estado_pasantia, COUNT(*) AS cantidad
            FROM datos_pasante
            GROUP BY estado_pasantia
        ");
        $estadosPasantes = $this->db->resultSet();

        // ── Nuevos pasantes por mes (año actual) ─────────────────
        $this->db->query("
            SELECT MONTH(u.created_at) AS mes, COUNT(*) AS cantidad
            FROM usuarios u
            WHERE u.rol_id = 3 AND YEAR(u.created_at) = YEAR(CURDATE())
            GROUP BY MONTH(u.created_at)
            ORDER BY mes ASC
        ");
        $registrosMensuales = $this->db->resultSet();

        // Rellenar los 12 meses (0 si no hay datos)
        $datosMensuales = array_fill(1, 12, 0);
        foreach ($registrosMensuales as $r) {
            $datosMensuales[(int)$r->mes] = (int)$r->cantidad;
        }

        $this->view('admin/dashboard', [
            'user_name'          => Session::get('user_name') ?? 'Administrador',
            'role'               => 'Administrador',
            'totalActivos'       => $totalActivos,
            'totalTutores'       => $totalTutores,
            'asistenciasHoy'     => $asistenciasHoy,
            'totalInstituciones' => $totalInstituciones,
            'actividadReciente'  => $actividadReciente,
            'estadosPasantes'    => $estadosPasantes,
            'datosMensuales'     => array_values($datosMensuales), // índices 0–11
        ]);
    }
}
