<?php
/**
 * TutorController — Panel del Tutor
 * Métricas reales desde datos_pasante.tutor_id
 */

class TutorController extends Controller {

    private $db;

    public function __construct() {
        CacheControl::noCache();
        AuthMiddleware::require();
        AuthMiddleware::verificarEstado();
        RoleMiddleware::requireRole(2);

        $config = require '../app/config/config.php';
        $this->db = new Database($config['db']);
    }

    /** Dashboard del Tutor */
    public function index(): void {
        Session::start();
        $tutorId = (int)Session::get('user_id');

        // KPI 1: Total de pasantes asignados a este tutor
        $this->db->query("SELECT COUNT(*) AS total FROM datos_pasante WHERE tutor_id = :tid");
        $this->db->bind(':tid', $tutorId);
        $totalPasantes = (int)($this->db->single()->total ?? 0);

        // KPI 2: Pasantes activos
        $this->db->query("SELECT COUNT(*) AS total FROM datos_pasante WHERE tutor_id = :tid AND estado_pasantia = 'Activo'");
        $this->db->bind(':tid', $tutorId);
        $pasantesActivos = (int)($this->db->single()->total ?? 0);

        // KPI 3: Pasantes activos sin evaluación (pendientes)
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM datos_pasante dpa
            LEFT JOIN evaluaciones e ON e.pasante_id = dpa.usuario_id
            WHERE dpa.tutor_id = :tid AND dpa.estado_pasantia = 'Activo' AND e.id IS NULL
        ");
        $this->db->bind(':tid', $tutorId);
        $evaluacionesPendientes = (int)($this->db->single()->total ?? 0);

        // KPI 4: Horas supervisadas totales
        $this->db->query("SELECT COALESCE(SUM(horas_acumuladas), 0) AS total FROM datos_pasante WHERE tutor_id = :tid");
        $this->db->bind(':tid', $tutorId);
        $horasSupervisadas = (int)($this->db->single()->total ?? 0);

        // Lista de pasantes con progreso y promedio de evaluaciones
        $this->db->query("
            SELECT
                dpa.usuario_id              AS pasante_id,
                dp.nombres,
                dp.apellidos,
                u.cedula,
                d.nombre                    AS departamento,
                dpa.horas_acumuladas,
                dpa.horas_meta,
                dpa.estado_pasantia,
                dpa.institucion_procedencia AS institucion,
                dpa.fecha_inicio_pasantia   AS fecha_inicio,
                AVG(e.promedio_final)       AS promedio_eval
            FROM datos_pasante dpa
            INNER JOIN usuarios          u  ON u.id  = dpa.usuario_id
            LEFT  JOIN datos_personales  dp ON dp.usuario_id = dpa.usuario_id
            LEFT  JOIN departamentos     d  ON d.id  = dpa.departamento_asignado_id
            LEFT  JOIN evaluaciones      e  ON e.pasante_id  = dpa.usuario_id
            WHERE dpa.tutor_id = :tid
            GROUP BY dpa.usuario_id
            ORDER BY
                FIELD(dpa.estado_pasantia, 'Activo','Pendiente','Sin Asignar','Finalizado','Retirado'),
                dp.apellidos ASC
            LIMIT 20
        ");
        $this->db->bind(':tid', $tutorId);
        $misPasantes = $this->db->resultSet();

        $this->view('tutor/dashboard', [
            'title'                  => 'Panel de Tutor',
            'role'                   => 'Tutor',
            'user_name'              => Session::get('user_name'),
            'totalPasantes'          => $totalPasantes,
            'pasantesActivos'        => $pasantesActivos,
            'evaluacionesPendientes' => $evaluacionesPendientes,
            'horasSupervisadas'      => $horasSupervisadas,
            'misPasantes'            => $misPasantes,
        ]);
    }
}
