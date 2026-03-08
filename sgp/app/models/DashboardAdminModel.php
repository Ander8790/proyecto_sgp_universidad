<?php
/**
 * DashboardAdminModel - Lógica de datos para el Dashboard del Administrador
 * 
 * Contiene todas las consultas para extraer las métricas y KPIs del Dashboard,
 * aislando completamente el SQL del Controlador.
 */
class DashboardAdminModel
{
    private $db;

    public function __construct()
    {
        $config = require '../app/config/config.php';
        $this->db = new Database($config['db']);
    }

    public function getTotalActivos(): int
    {
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM datos_pasante
            WHERE estado_pasantia = 'Activo'
        ");
        return (int)($this->db->single()->total ?? 0);
    }

    public function getPendientesAsignar(): int
    {
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM usuarios u
            LEFT JOIN datos_pasante dp ON u.id = dp.usuario_id
            WHERE u.rol_id = 3 AND (dp.estado_pasantia = 'Sin Asignar' OR dp.estado_pasantia IS NULL)
        ");
        return (int)($this->db->single()->total ?? 0);
    }

    public function getTotalTutores(): int
    {
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM usuarios
            WHERE rol_id = 2 AND estado = 'activo'
        ");
        return (int)($this->db->single()->total ?? 0);
    }

    public function getAsistenciasHoy(): int
    {
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM asistencias
            WHERE fecha = CURDATE() AND estado = 'Presente'
        ");
        return (int)($this->db->single()->total ?? 0);
    }

    public function getFaltasHoy(): int
    {
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM asistencias
            WHERE fecha = CURDATE() AND estado IN ('Ausente', 'Falta')
        ");
        return (int)($this->db->single()->total ?? 0);
    }

    public function getTotalInstituciones(): int
    {
        $this->db->query("
            SELECT COUNT(DISTINCT institucion_procedencia) AS total
            FROM datos_pasante
            WHERE institucion_procedencia IS NOT NULL AND institucion_procedencia != ''
        ");
        return (int)($this->db->single()->total ?? 0);
    }

    public function getActividadReciente(int $limit = 5): array
    {
        $this->db->query("
            SELECT
                a.id,
                a.fecha,
                a.hora_registro,
                a.estado,
                a.metodo,
                dp.nombres,
                dp.apellidos,
                u.cedula,
                u.id AS usuario_id
            FROM asistencias a
            INNER JOIN usuarios u  ON u.id  = a.pasante_id
            LEFT  JOIN datos_personales dp ON dp.usuario_id = a.pasante_id
            ORDER BY a.fecha DESC, a.hora_registro DESC
            LIMIT :limit
        ");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getEstadosPasantes(): array
    {
        $this->db->query("
            SELECT COALESCE(estado_pasantia, 'Sin Asignar') AS estado_pasantia, COUNT(*) AS cantidad
            FROM datos_pasante
            GROUP BY COALESCE(estado_pasantia, 'Sin Asignar')
        ");
        return $this->db->resultSet();
    }

    public function getRegistrosMensuales(): array
    {
        $this->db->query("
            SELECT MONTH(u.created_at) AS mes, COUNT(*) AS cantidad
            FROM usuarios u
            WHERE u.rol_id = 3 AND YEAR(u.created_at) = YEAR(CURDATE())
            GROUP BY MONTH(u.created_at)
            ORDER BY mes ASC
        ");
        
        $registros = $this->db->resultSet();
        $datosMensuales = array_fill(1, 12, 0);
        
        foreach ($registros as $r) {
            $datosMensuales[(int)$r->mes] = (int)$r->cantidad;
        }
        
        return array_values($datosMensuales); // Retorna array 0-indexado
    }

    // ==========================================
    // MÉTRICAS PARA GRÁFICAS (Data Binding Real)
    // ==========================================

    public function getAsistenciaDiaria(): array
    {
        // Últimos 5 días hábiles
        $this->db->query("
            SELECT 
                DATE_FORMAT(fecha, '%w') AS dia_num, /* 1=Lun, 5=Vie */
                SUM(estado = 'Presente') AS p,
                SUM(estado IN ('Ausente', 'Falta') OR estado IS NULL) AS f,
                SUM(estado = 'Justificado') AS j
            FROM asistencias
            WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
              AND DATE_FORMAT(fecha, '%w') BETWEEN 1 AND 5
            GROUP BY fecha
            ORDER BY fecha ASC
            LIMIT 5
        ");
        $results = $this->db->resultSet();
        
        $mapaDias = ['1'=>'Lun', '2'=>'Mar', '3'=>'Mié', '4'=>'Jue', '5'=>'Vie'];
        $data = ['cat' => [], 'p' => [], 'f' => [], 'j' => []];
        
        foreach ($results as $r) {
            $data['cat'][] = $mapaDias[$r->dia_num] ?? '?';
            $data['p'][]   = (int)$r->p;
            $data['f'][]   = (int)$r->f;
            $data['j'][]   = (int)$r->j;
        }
        
        // Si no hay datos, devolver vacíos limpios en lugar de arrays desajustados
        if (empty($data['cat'])) {
            $data = ['cat' => ['Lun','Mar','Mié','Jue','Vie'], 'p' => [0,0,0,0,0], 'f' => [0,0,0,0,0], 'j' => [0,0,0,0,0]];
        }
        
        return $data;
    }

    public function getAsistenciaSemanal(): array
    {
        // Últimas 4 semanas
        $this->db->query("
            SELECT 
                WEEK(fecha, 1) AS semana_num,
                SUM(estado = 'Presente') AS p,
                SUM(estado IN ('Ausente', 'Falta') OR estado IS NULL) AS f,
                SUM(estado = 'Justificado') AS j
            FROM asistencias
            WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 28 DAY)
            GROUP BY WEEK(fecha, 1)
            ORDER BY semana_num ASC
            LIMIT 4
        ");
        $results = $this->db->resultSet();
        $data = ['cat' => [], 'p' => [], 'f' => [], 'j' => []];
        $count = 1;
        
        foreach ($results as $r) {
            $data['cat'][] = 'Sem ' . $count++;
            $data['p'][]   = (int)$r->p;
            $data['f'][]   = (int)$r->f;
            $data['j'][]   = (int)$r->j;
        }
        
        if (empty($data['cat'])) {
            $data = ['cat' => ['Sem 1','Sem 2','Sem 3','Sem 4'], 'p' => [0,0,0,0], 'f' => [0,0,0,0], 'j' => [0,0,0,0]];
        }
        return $data;
    }

    public function getAsistenciaMensual(): array
    {
        // Últimos 6 meses del año actual
        $this->db->query("
            SELECT 
                MONTH(fecha) AS mes_num,
                SUM(estado = 'Presente') AS p,
                SUM(estado IN ('Ausente', 'Falta') OR estado IS NULL) AS f,
                SUM(estado = 'Justificado') AS j
            FROM asistencias
            WHERE YEAR(fecha) = YEAR(CURDATE())
            GROUP BY MONTH(fecha)
            ORDER BY mes_num ASC
            LIMIT 6
        ");
        $results = $this->db->resultSet();
        $mapaMeses = [1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr', 5=>'May', 6=>'Jun', 7=>'Jul', 8=>'Ago', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dic'];
        $data = ['cat' => [], 'p' => [], 'f' => [], 'j' => []];
        
        foreach ($results as $r) {
            $data['cat'][] = $mapaMeses[$r->mes_num] ?? '?';
            $data['p'][]   = (int)$r->p;
            $data['f'][]   = (int)$r->f;
            $data['j'][]   = (int)$r->j;
        }
        
        if (empty($data['cat'])) {
            $data = ['cat' => ['Ene','Feb','Mar','Abr','May'], 'p' => [0,0,0,0,0], 'f' => [0,0,0,0,0], 'j' => [0,0,0,0,0]];
        }
        return $data;
    }

    public function getAsistenciaPorDepartamento(): array
    {
        // % de asistencias por departamento (Presentes / Total * 100)
        $this->db->query("
            SELECT 
                d.nombre AS departamento,
                COUNT(a.id) AS total_registros,
                SUM(a.estado = 'Presente') AS total_presentes
            FROM datos_pasante dpa
            JOIN departamentos d ON d.id = dpa.departamento_asignado_id
            LEFT JOIN asistencias a ON a.pasante_id = dpa.usuario_id
            GROUP BY d.id, d.nombre
            HAVING total_registros > 0
            ORDER BY total_presentes DESC
            LIMIT 4
        ");
        
        $results = $this->db->resultSet();
        $data = ['labels' => [], 'series' => []];
        
        foreach ($results as $r) {
            $data['labels'][] = htmlspecialchars($r->departamento);
            // Evitar división por cero preventivamente aunque con HAVING > 0 es seguro
            $porcentaje = ($r->total_registros > 0) ? ROUND(($r->total_presentes / $r->total_registros) * 100) : 0;
            $data['series'][] = (int)$porcentaje;
        }
        
        // Si no hay pasantes o no hay registros, pasar un mockup vacío real (zeros)
        if (empty($data['labels'])) {
            $data = ['labels' => ['General'], 'series' => [0]];
        }
        
        return $data;
    }

    public function getAlertasPendientes(int $limit = 5): array
    {
        // Pasantes sin asignar o pasantías a 7 días o menos de vencer
        $this->db->query("
            SELECT 
                u.id AS usuario_id,
                dp.nombres,
                dp.apellidos,
                COALESCE(dpa.estado_pasantia, 'Sin Asignar') AS estado,
                dpa.fecha_fin_estimada
            FROM usuarios u
            LEFT JOIN datos_personales dp ON u.id = dp.usuario_id
            LEFT JOIN datos_pasante dpa ON u.id = dpa.usuario_id
            WHERE u.rol_id = 3 
              AND u.estado = 'activo'
              AND (
                  dpa.estado_pasantia = 'Sin Asignar' 
                  OR dpa.estado_pasantia IS NULL
                  OR (dpa.estado_pasantia = 'Activo' AND dpa.fecha_fin_estimada <= DATE_ADD(CURDATE(), INTERVAL 7 DAY))
              )
            ORDER BY 
                CASE WHEN dpa.estado_pasantia = 'Sin Asignar' OR dpa.estado_pasantia IS NULL THEN 1 ELSE 2 END ASC,
                dpa.fecha_fin_estimada ASC
            LIMIT :limit
        ");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
}
