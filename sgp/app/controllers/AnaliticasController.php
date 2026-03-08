<?php
/**
 * AnaliticasController — Métricas y Estadísticas del Sistema
 *
 * Provee datos reales desde la DB para el módulo de Analíticas.
 * Acceso: solo Administrador (role_id = 1)
 */

declare(strict_types=1);

class AnaliticasController extends Controller
{
    private Database $db;

    public function __construct()
    {
        Session::start();
        CacheControl::noCache();
        AuthMiddleware::require();
        AuthMiddleware::verificarEstado();

        if (!RoleMiddleware::hasAnyRole([1])) {
            RoleMiddleware::redirectToRoleDashboard(Session::get('role_id'));
        }

        $config   = require APPROOT . '/config/config.php';
        $this->db = new Database($config['db']);
    }

    public function index(): void
    {
        // ── KPI 1: Tasa de asistencia (últimos 30 días) ──────────────
        $this->db->query("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN estado = 'Presente'    THEN 1 ELSE 0 END) AS presentes,
                SUM(CASE WHEN estado = 'Justificado' THEN 1 ELSE 0 END) AS justificados,
                SUM(CASE WHEN estado = 'Ausente'     THEN 1 ELSE 0 END) AS ausentes
            FROM asistencias
            WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        $asist = $this->db->single();
        $totalAsistencias = (int)($asist->total ?? 0);
        $kpiTasa = $totalAsistencias > 0
            ? round(($asist->presentes / $totalAsistencias) * 100, 1)
            : 0;

        // ── KPI 2: Pasantes activos ───────────────────────────────────
        $this->db->query("SELECT COUNT(*) AS total FROM datos_pasante WHERE estado_pasantia = 'Activo'");
        $kpiActivos = (int)($this->db->single()->total ?? 0);

        // ✅ KPI 3: Total horas Pro-Rata (COUNT asistencias válidas * 8)
        $this->db->query("
            SELECT COALESCE(SUM(dias_validos), 0) * 8 AS total
            FROM (
                SELECT COUNT(*) AS dias_validos
                FROM asistencias
                WHERE estado IN ('Presente', 'Justificado')
                GROUP BY pasante_id
            ) sub
        ");
        $kpiHoras = (int)($this->db->single()->total ?? 0);

        // ✅ KPI 4: Promedio de progreso Pro-Rata (%) sobre meta 1440h
        $this->db->query("
            SELECT ROUND(AVG(progreso_pct), 1) AS promedio
            FROM (
                SELECT
                    LEAST(100, ROUND((COUNT(*) * 8 / 1440) * 100, 1)) AS progreso_pct
                FROM asistencias a
                INNER JOIN datos_pasante dpa ON dpa.usuario_id = a.pasante_id
                WHERE a.estado IN ('Presente', 'Justificado')
                  AND dpa.estado_pasantia = 'Activo'
                GROUP BY a.pasante_id
            ) sub
        ");
        $kpiProgreso = (float)($this->db->single()->promedio ?? 0);

        // ── Asistencias por mes (año actual) ─────────────────────────
        $this->db->query("
            SELECT
                MONTH(fecha) AS mes,
                SUM(CASE WHEN estado = 'Presente'    THEN 1 ELSE 0 END) AS presentes,
                SUM(CASE WHEN estado = 'Justificado' THEN 1 ELSE 0 END) AS justificados,
                SUM(CASE WHEN estado = 'Ausente'     THEN 1 ELSE 0 END) AS ausentes
            FROM asistencias
            WHERE YEAR(fecha) = YEAR(CURDATE())
            GROUP BY MONTH(fecha)
            ORDER BY mes
        ");
        $asistencias_mes = $this->db->resultSet();

        // Rellenar 12 meses
        $seriesPresentes    = array_fill(0, 12, 0);
        $seriesJustificados = array_fill(0, 12, 0);
        $seriesAusentes     = array_fill(0, 12, 0);
        foreach ($asistencias_mes as $row) {
            $idx = (int)$row->mes - 1;
            $seriesPresentes[$idx]    = (int)$row->presentes;
            $seriesJustificados[$idx] = (int)$row->justificados;
            $seriesAusentes[$idx]     = (int)$row->ausentes;
        }

        // ── Pasantes por departamento ─────────────────────────────────
        $this->db->query("
            SELECT d.nombre AS departamento, COUNT(*) AS total
            FROM datos_pasante dpa
            JOIN departamentos d ON d.id = dpa.departamento_asignado_id
            WHERE dpa.estado_pasantia IN ('Activo', 'Pendiente')
            GROUP BY d.id
            ORDER BY total DESC
        ");
        $porDepartamento = $this->db->resultSet();

        // ── Heatmap: asistencias por día del mes actual ───────────────
        $this->db->query("
            SELECT DAY(fecha) AS dia, COUNT(*) AS total
            FROM asistencias
            WHERE YEAR(fecha) = YEAR(CURDATE()) AND MONTH(fecha) = MONTH(CURDATE())
            GROUP BY DAY(fecha)
            ORDER BY dia
        ");
        $heatmapData = $this->db->resultSet();
        $heatmap = array_fill(1, 31, 0);
        foreach ($heatmapData as $h) {
            $heatmap[(int)$h->dia] = (int)$h->total;
        }

        // ✅ TOP 5 Pasantes Destacados (Pro-Rata: progreso = COUNT asistencias válidas * 8 / 1440)
        $this->db->query("
            SELECT
                dp.nombres,
                dp.apellidos,
                u.cedula,
                d.nombre                                                AS departamento,
                COUNT(a.id) * 8                                         AS horas_acumuladas,
                1440                                                    AS horas_meta,
                ROUND(LEAST(100, COUNT(a.id) * 8 / 1440 * 100), 1)    AS progreso,
                ROUND(AVG(e.promedio_final), 2)                        AS prom_eval,
                COUNT(DISTINCT e.id)                                   AS num_eval,
                -- Score: 60% evaluación + 40% progreso Pro-Rata
                ROUND(
                    (COALESCE(AVG(e.promedio_final), 0) / 5 * 60) +
                    (LEAST(100, COUNT(a.id) * 8 / 1440 * 100) / 100 * 40)
                , 1) AS score_total
            FROM datos_pasante dpa
            JOIN usuarios         u  ON u.id  = dpa.usuario_id
            JOIN datos_personales dp ON dp.usuario_id = dpa.usuario_id
            LEFT JOIN departamentos d ON d.id = dpa.departamento_asignado_id
            LEFT JOIN asistencias   a ON a.pasante_id = dpa.usuario_id
                                     AND a.estado IN ('Presente', 'Justificado')
            LEFT JOIN evaluaciones  e ON e.pasante_id = dpa.usuario_id
            WHERE dpa.estado_pasantia = 'Activo'
            GROUP BY dpa.usuario_id
            ORDER BY score_total DESC, progreso DESC
            LIMIT 5
        ");
        $top5Pasantes = $this->db->resultSet();

        // ── Tendencia horas acumuladas (evolución mensual estimada) ───
        $this->db->query("
            SELECT
                MONTH(a.fecha) AS mes,
                COUNT(DISTINCT a.pasante_id) * 8 AS horas_estimadas
            FROM asistencias a
            WHERE YEAR(a.fecha) = YEAR(CURDATE()) AND a.estado = 'Presente'
            GROUP BY MONTH(a.fecha)
            ORDER BY mes
        ");
        $tendenciaHoras = $this->db->resultSet();
        $seriesHorasMes = array_fill(0, 12, 0);
        foreach ($tendenciaHoras as $row) {
            $seriesHorasMes[(int)$row->mes - 1] = (int)$row->horas_estimadas;
        }

        $this->view('analiticas/index', [
            'title'             => 'Analíticas del Sistema',
            'kpiTasa'           => $kpiTasa,
            'kpiActivos'        => $kpiActivos,
            'kpiHoras'          => $kpiHoras,
            'kpiProgreso'       => $kpiProgreso,
            'seriesPresentes'   => $seriesPresentes,
            'seriesJustificados'=> $seriesJustificados,
            'seriesAusentes'    => $seriesAusentes,
            'porDepartamento'   => $porDepartamento,
            'heatmap'           => array_values($heatmap),
            'top5Pasantes'      => $top5Pasantes,
            'seriesHorasMes'    => $seriesHorasMes,
            'totalAsistencias'  => $totalAsistencias,
        ]);
    }
}
