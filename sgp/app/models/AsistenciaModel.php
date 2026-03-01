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
}
