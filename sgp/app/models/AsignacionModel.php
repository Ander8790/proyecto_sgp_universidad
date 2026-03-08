<?php
/**
 * AsignacionModel - Gestión de Asignaciones de Pasantes
 * 
 * Encapsula la lógica relacionada con asignar pasantes a departamentos y tutores,
 * actuando sobre las tablas de `datos_pasante` y relacionadas.
 */
class AsignacionModel {
    private $db;

    public function __construct() {
        $config = require '../app/config/config.php';
        $this->db = new Database($config['db']);
    }

    /**
     * Obtener todas las asignaciones (activos, pendientes, sin asignar, etc)
     */
    public function getAll() {
        $this->db->query("
            SELECT
                u.id                AS pasante_id,
                u.cedula,
                dp.nombres,
                dp.apellidos,
                dpa.institucion_procedencia,
                COALESCE(dpa.estado_pasantia, 'Sin Asignar') AS estado_pasantia,
                COALESCE(dpa.horas_acumuladas, 0)            AS horas_acumuladas,
                COALESCE(dpa.horas_meta, 1440)               AS horas_meta,
                dpa.fecha_inicio_pasantia,
                dpa.fecha_fin_estimada,
                d.id               AS departamento_id,
                d.nombre           AS departamento_nombre,
                tu.id              AS tutor_id,
                tup.nombres        AS tutor_nombres,
                tup.apellidos      AS tutor_apellidos
            FROM usuarios u
            LEFT JOIN datos_personales dp  ON dp.usuario_id  = u.id
            LEFT JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos    d   ON d.id = dpa.departamento_asignado_id
            LEFT JOIN usuarios         tu  ON tu.id = dpa.tutor_id
            LEFT JOIN datos_personales tup ON tup.usuario_id = tu.id
            WHERE u.rol_id = 3 AND u.estado = 'activo'
            ORDER BY
                FIELD(COALESCE(dpa.estado_pasantia,'Sin Asignar'),
                      'Activo','Pendiente','Sin Asignar','Finalizado','Retirado'),
                IFNULL(dp.apellidos, u.correo) ASC
        ");
        return $this->db->resultSet();
    }

    /**
     * Obtener una asignación específica por pasante_id
     */
    public function getById($pasanteId) {
        $this->db->query("
            SELECT
                u.id                AS pasante_id,
                u.cedula,
                dp.nombres,
                dp.apellidos,
                dpa.institucion_procedencia,
                COALESCE(dpa.estado_pasantia, 'Sin Asignar') AS estado_pasantia,
                COALESCE(dpa.horas_acumuladas, 0)            AS horas_acumuladas,
                COALESCE(dpa.horas_meta, 1440)               AS horas_meta,
                dpa.fecha_inicio_pasantia,
                dpa.fecha_fin_estimada,
                d.nombre           AS departamento_nombre,
                tup.nombres        AS tutor_nombres,
                tup.apellidos      AS tutor_apellidos
            FROM usuarios u
            LEFT JOIN datos_personales dp  ON dp.usuario_id  = u.id
            LEFT JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos    d   ON d.id = dpa.departamento_asignado_id
            LEFT JOIN usuarios         tu  ON tu.id = dpa.tutor_id
            LEFT JOIN datos_personales tup ON tup.usuario_id = tu.id
            WHERE u.id = :uid
            LIMIT 1
        ");
        $this->db->bind(':uid', $pasanteId);
        return $this->db->single();
    }

    /**
     * Guardar/Actualizar una asignación (UPSERT)
     */
    public function guardar($pasanteId, $departamentoId, $tutorIdVal, $horasMeta, $fechaInicio, $fechaFin) {
        $this->db->query("
            INSERT INTO datos_pasante
                (usuario_id, departamento_asignado_id, tutor_id, horas_meta,
                 fecha_inicio_pasantia, fecha_fin_estimada, estado_pasantia)
            VALUES
                (:uid, :dept_id, :tutor_id, :horas_meta, :fecha_inicio, :fecha_fin, 'Pendiente')
            ON DUPLICATE KEY UPDATE
                departamento_asignado_id = VALUES(departamento_asignado_id),
                tutor_id                 = VALUES(tutor_id),
                horas_meta               = VALUES(horas_meta),
                fecha_inicio_pasantia    = VALUES(fecha_inicio_pasantia),
                fecha_fin_estimada       = VALUES(fecha_fin_estimada)
        ");
        $this->db->bind(':uid',          $pasanteId);
        $this->db->bind(':dept_id',      $departamentoId);
        $this->db->bind(':tutor_id',     $tutorIdVal);
        $this->db->bind(':horas_meta',   $horasMeta);
        $this->db->bind(':fecha_inicio', $fechaInicio);
        $this->db->bind(':fecha_fin',    $fechaFin);

        return $this->db->execute();
    }

    /**
     * Activar una pasantía
     */
    public function activar($pasanteId) {
        $this->db->query("
            UPDATE datos_pasante SET estado_pasantia = 'Activo' WHERE usuario_id = :uid
        ");
        $this->db->bind(':uid', $pasanteId);
        return $this->db->execute();
    }

    /**
     * Finalizar una pasantía
     */
    public function finalizar($pasanteId) {
        $this->db->query("
            UPDATE datos_pasante SET estado_pasantia = 'Finalizado' WHERE usuario_id = :uid
        ");
        $this->db->bind(':uid', $pasanteId);
        return $this->db->execute();
    }

    /**
     * Eliminar una asignación (resetear)
     */
    public function eliminar($pasanteId) {
        $this->db->query("
            UPDATE datos_pasante
            SET departamento_asignado_id = NULL,
                tutor_id     = NULL,
                estado_pasantia          = 'Sin Asignar',
                fecha_inicio_pasantia    = NULL,
                fecha_fin_estimada       = NULL
            WHERE usuario_id = :uid
        ");
        $this->db->bind(':uid', $pasanteId);
        return $this->db->execute();
    }
}
