<?php
/**
 * AsistenciaModel — Consultas SQL para el módulo de Asistencias
 *
 * ARQUITECTURA NORMALIZADA (3NF):
 *   - `usuarios`          : autenticación (correo, password, pin, rol, estado)
 *   - `datos_personales`  : biografía (cedula, nombres, apellidos)
 *   - `datos_pasante`     : lógica académica (horas, estado_pasantia, departamento)
 *   - `departamentos`     : catálogo de departamentos
 *   - `asistencias`       : registros de marcaje (fecha, hora, estado, método)
 *
 * @version 1.0 — Módulo de Consulta Rápida (Auditoría)
 */
class AsistenciaModel
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    // ==========================================
    // CONSULTAS SQL PARA EL MODAL DE AUDITORÍA
    // ==========================================

    /**
     * Busca pasantes activos en tiempo real (Autocompletado).
     * Filtra por cédula, nombres o apellidos.
     *
     * @param string $query Término de búsqueda del usuario
     * @return array Lista de coincidencias (máx. 5)
     */
    public function buscarPasanteLive(string $query): array
    {
        $this->db->query("
            SELECT u.id AS pasante_id, u.cedula, dp.nombres, dp.apellidos,
                   d.nombre AS departamento_nombre
            FROM   usuarios u
            INNER JOIN datos_personales dp  ON dp.usuario_id  = u.id
            LEFT  JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
            LEFT  JOIN departamentos    d   ON d.id = dpa.departamento_asignado_id
            WHERE  u.rol_id = 3
              AND  u.estado = 'activo'
              AND  COALESCE(dpa.estado_pasantia, 'Sin Asignar') = 'Activo'
              AND  (u.cedula LIKE :busqueda OR dp.nombres LIKE :busqueda OR dp.apellidos LIKE :busqueda)
            ORDER BY IFNULL(dp.apellidos, u.correo) ASC
            LIMIT 5
        ");

        $this->db->bind(':busqueda', '%' . $query . '%');
        return $this->db->resultSet();
    }

    /**
     * Extrae los datos base del pasante y sus fechas de pasantía
     * para el Anillo de Progreso del Modal.
     *
     * @param int $id ID del usuario/pasante
     * @return object|false Perfil del pasante o false si no existe
     */
    public function obtenerPerfilParaAuditoria(int $id)
    {
        $this->db->query("
            SELECT u.id AS pasante_id, u.cedula,
                   dp.nombres, dp.apellidos,
                   d.nombre AS departamento_nombre,
                   dpa.fecha_inicio_pasantia AS fecha_inicio,
                   dpa.fecha_fin_estimada    AS fecha_fin
            FROM   usuarios u
            INNER JOIN datos_personales dp  ON dp.usuario_id  = u.id
            LEFT  JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
            LEFT  JOIN departamentos    d   ON d.id = dpa.departamento_asignado_id
            WHERE  u.id = :id
            LIMIT 1
        ");

        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Extrae todo el historial de marcajes de un pasante específico,
     * ordenado por fecha descendente para la línea de tiempo.
     *
     * @param int $id ID del usuario/pasante
     * @return array Lista completa de registros de asistencia
     */
    /**
     * Extrae todo el historial de marcajes de un pasante específico,
     * ordenado por fecha descendente para la línea de tiempo.
     *
     * @param int $id ID del usuario/pasante
     * @return array Lista completa de registros de asistencia
     */
    public function obtenerHistorialCompletoPasante(int $id): array
    {
        $this->db->query("
            SELECT id, fecha, hora_registro, estado, metodo, motivo_justificacion
            FROM   asistencias
            WHERE  pasante_id = :id
            ORDER  BY fecha DESC, hora_registro DESC
        ");

        $this->db->bind(':id', $id);
        return $this->db->resultSet();
    }

    // ==========================================
    // MATEMÁTICA PRO-RATA (Opción B — Cálculo Dinámico)
    // Fuente de Verdad: tabla 'asistencias'. Jornada: 8h/día. Meta: 1440h.
    // ==========================================

    /**
     * Calcula el progreso Pro-Rata de un pasante individual.
     *
     * Se cuentan SOLO los días con estado 'Presente' o 'Justificado'
     * y se multiplican por la jornada oficial de 8 horas.
     * El porcentaje se calcula sobre la meta institucional de 1440 horas.
     *
     * @param int $pasanteId ID del usuario/pasante
     * @param int $horasMeta  Meta en horas (default: 1440 = 180 días × 8h)
     * @return object { dias_presentes, horas_mostradas, horas_meta, porcentaje }
     */
    public function calcularProgresoProRata(int $pasanteId, int $horasMeta = 1440): object
    {
        $this->db->query("
            SELECT COUNT(*) AS dias_validos
            FROM   asistencias
            WHERE  pasante_id = :pid
              AND  estado IN ('Presente', 'Justificado')
        ");
        $this->db->bind(':pid', $pasanteId);
        $resultado = $this->db->single();

        $diasValidos   = (int)($resultado->dias_validos ?? 0);
        $horasMostradas = $diasValidos * 8;
        $porcentaje    = $horasMeta > 0
            ? min(100, round(($horasMostradas / $horasMeta) * 100, 1))
            : 0;

        return (object)[
            'dias_presentes'  => $diasValidos,
            'horas_mostradas' => $horasMostradas,
            'horas_meta'      => $horasMeta,
            'porcentaje'      => $porcentaje,
        ];
    }

    /**
     * Calcula el progreso Pro-Rata para TODOS los pasantes activos.
     * Útil para inyectar en listados, analíticas y vistas admin.
     *
     * @return array Keyed by pasante_id → { dias_presentes, horas_mostradas, porcentaje }
     */
    public function getProgresoTodosActivos(): array
    {
        $this->db->query("
            SELECT
                a.pasante_id,
                COUNT(*) AS dias_validos
            FROM asistencias a
            INNER JOIN datos_pasante dpa ON dpa.usuario_id = a.pasante_id
            WHERE a.estado IN ('Presente', 'Justificado')
              AND dpa.estado_pasantia = 'Activo'
            GROUP BY a.pasante_id
        ");
        $filas = $this->db->resultSet();

        $mapa = [];
        foreach ($filas as $fila) {
            $diasValidos    = (int)$fila->dias_validos;
            $horasMostradas = $diasValidos * 8;
            $mapa[(int)$fila->pasante_id] = (object)[
                'dias_presentes'  => $diasValidos,
                'horas_mostradas' => $horasMostradas,
                'porcentaje'      => min(100, round(($horasMostradas / 1440) * 100, 1)),
            ];
        }
        return $mapa;
    }

    // ==========================================
    // FASE 2: MATEMÁTICA DE CALENDARIO (Cohortes)
    // ==========================================

    /**
     * Calcula las "horas esperadas" según el calendario transcurrido desde la fecha de inicio.
     * Ignora sábados y domingos.
     *
     * @param string $fechaInicio Fecha de inicio (Y-m-d)
     * @param int $horasMeta Total de horas (ej. 1440)
     * @return object Data con días transcurridos y horas calendario.
     */
    public function calcularProgresoPorCalendario(string $fechaInicio, int $horasMeta = 1440): object
    {
        try {
            $fInicio = new DateTime($fechaInicio);
            $fHoy    = new DateTime(date('Y-m-d')); // Día de hoy puntual sin horas
        } catch (Exception $e) {
            return (object)[
                'dias_habiles_transcurridos' => 0,
                'horas_calendario'           => 0,
                'porcentaje_calendario'      => 0,
                'dias_habiles_restantes'     => 0
            ];
        }
        
        // Si la pasantía aún no empieza (en el futuro), todo está en 0
        if ($fInicio > $fHoy) {
            return (object)[
                'dias_habiles_transcurridos' => 0,
                'horas_calendario'           => 0,
                'porcentaje_calendario'      => 0,
                'dias_habiles_restantes'     => max(0, $horasMeta / 8)
            ];
        }
        
        $diasHabiles = 0;
        $fechaCursor = clone $fInicio;
        
        while ($fechaCursor <= $fHoy) {
            $dow = (int)$fechaCursor->format('N');
            if ($dow < 6) { // Lunes a Viernes
                $diasHabiles++;
            }
            $fechaCursor->modify('+1 day');
        }
        
        $horasCalendario = $diasHabiles * 8;
        if ($horasCalendario > $horasMeta) {
            $horasCalendario = $horasMeta;
        }
        
        $porcentaje = ($horasMeta > 0) ? min(100, round(($horasCalendario / $horasMeta) * 100, 1)) : 0;
        
        return (object)[
            'dias_habiles_transcurridos' => $diasHabiles,
            'horas_calendario'           => $horasCalendario,
            'porcentaje_calendario'      => $porcentaje,
            'dias_habiles_restantes'     => max(0, ($horasMeta / 8) - $diasHabiles)
        ];
    }
}

