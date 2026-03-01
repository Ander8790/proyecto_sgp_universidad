<?php
class ReportesController extends Controller {
    private Database $db;

    public function __construct() {
        Session::start();
        AuthMiddleware::require();
        AuthMiddleware::verificarEstado();

        $config   = require APPROOT . '/config/config.php';
        $this->db = new Database($config['db']);
    }

    public function index() {
        $data = ['title' => 'Informes y Reportes'];
        $this->view('reportes/index', $data);
    }

    /**
     * AJAX (POST): Retorna el listado de pasantes estructurado en JSON.
     */
    public function exportarPasantes() {
        header('Content-Type: application/json');
        try {
            $estado = $_POST['estado'] ?? 'todos';
            $depto  = $_POST['departamento_id'] ?? 'todos';

            $sql = "
                SELECT 
                    u.cedula,
                    CONCAT(dp.nombres, ' ', dp.apellidos) as nombre_completo,
                    dp.telefono,
                    u.correo,
                    dpa.institucion_procedencia,
                    dept.nombre as departamento_nombre,
                    dpa.estado_pasantia,
                    dpa.horas_acumuladas,
                    dpa.horas_meta
                FROM usuarios u
                JOIN datos_personales dp ON u.id = dp.usuario_id
                JOIN datos_pasante dpa ON u.id = dpa.usuario_id
                LEFT JOIN departamentos dept ON dpa.departamento_asignado_id = dept.id
                WHERE u.rol_id = 3
            ";

            if ($estado !== 'todos') {
                $sql .= " AND LOWER(dpa.estado_pasantia) = :estado";
            }
            if ($depto !== 'todos') {
                $sql .= " AND dpa.departamento_asignado_id = :depto";
            }

            $sql .= " ORDER BY dp.apellidos ASC";

            $this->db->query($sql);

            if ($estado !== 'todos') $this->db->bind(':estado', strtolower($estado));
            if ($depto !== 'todos') $this->db->bind(':depto', $depto);

            $resultados = $this->db->resultSet();

            echo json_encode([
                'success' => true,
                'data' => $resultados
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX (POST): Retorna el historial de asistencias (diario, semanal, mensual).
     */
    public function exportarAsistencias() {
        header('Content-Type: application/json');
        try {
            $inicio = $_POST['fecha_inicio'] ?? date('Y-m-d');
            $fin    = $_POST['fecha_fin'] ?? date('Y-m-d');
            $depto  = $_POST['departamento_id'] ?? 'todos';

            $sql = "
                SELECT 
                    a.fecha,
                    a.hora_entrada,
                    a.hora_salida,
                    a.estado,
                    a.horas_calculadas,
                    u.cedula,
                    CONCAT(dp.nombres, ' ', dp.apellidos) as pasante_nombre,
                    dept.nombre as departamento
                FROM asistencias a
                JOIN usuarios u ON a.pasante_id = u.id
                JOIN datos_personales dp ON u.id = dp.usuario_id
                JOIN datos_pasante dpa ON u.id = dpa.usuario_id
                LEFT JOIN departamentos dept ON dpa.departamento_asignado_id = dept.id
                WHERE a.fecha BETWEEN :inicio AND :fin
            ";

            if ($depto !== 'todos') {
                $sql .= " AND dpa.departamento_asignado_id = :depto";
            }

            $sql .= " ORDER BY a.fecha DESC, dp.apellidos ASC";

            $this->db->query($sql);
            $this->db->bind(':inicio', $inicio);
            $this->db->bind(':fin', $fin);
            if ($depto !== 'todos') $this->db->bind(':depto', $depto);

            $resultados = $this->db->resultSet();
            
            echo json_encode([
                'success' => true,
                'data' => $resultados
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
