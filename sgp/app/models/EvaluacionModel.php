<?php
/**
 * EvaluacionModel - Gestión de Evaluaciones de Pasantes
 * 
 * Encapsula la lógica para interactuar con la tabla `evaluaciones`.
 */
class EvaluacionModel {
    private $db;

    public function __construct() {
        $config = require '../app/config/config.php';
        $this->db = Database::getInstance(); // SGP-FIX-v2 [6/2.1] aplicado
    }

    /**
     * Obtener todas las evaluaciones
     */
    public function getAll() {
        $this->db->query("
            SELECT 
                e.*,
                dp.nombres   AS pasante_nombres,
                dp.apellidos AS pasante_apellidos,
                u.cedula     AS pasante_cedula,
                tp.nombres   AS tutor_nombres,
                tp.apellidos AS tutor_apellidos
            FROM evaluaciones e
            LEFT JOIN datos_personales dp ON dp.usuario_id = e.pasante_id
            LEFT JOIN usuarios         u  ON u.id = e.pasante_id
            LEFT JOIN datos_personales tp ON tp.usuario_id = e.tutor_id
            ORDER BY e.created_at DESC
        ");
        return $this->db->resultSet();
    }

    /**
     * Guardar una nueva evaluación
     */
    public function guardar($pasanteId, $tutorId, $fecha, $lapso, $promedio, $obs, $valores, $criterios) {
        $this->db->query("
            INSERT INTO evaluaciones 
                (pasante_id, tutor_id, fecha_evaluacion, lapso_academico,
                 criterio_iniciativa, criterio_interes, criterio_conocimiento,
                 criterio_analisis, criterio_comunicacion, criterio_aprendizaje,
                 criterio_companerismo, criterio_cooperacion, criterio_puntualidad,
                 criterio_presentacion, criterio_desarrollo, criterio_analisis_res,
                 criterio_conclusiones, criterio_recomendacion,
                 promedio_final, observaciones)
            VALUES 
                (:pasante, :tutor, :fecha, :lapso,
                 :c1, :c2, :c3, :c4, :c5, :c6, :c7, :c8, :c9, :c10, :c11, :c12, :c13, :c14,
                 :promedio, :obs)
        ");

        $this->db->bind(':pasante', $pasanteId);
        $this->db->bind(':tutor',   $tutorId);
        $this->db->bind(':fecha',   $fecha);
        $this->db->bind(':lapso',   $lapso ?: null);
        
        $i = 1;
        foreach ($criterios as $c) {
            $this->db->bind(":c{$i}", $valores[$c]);
            $i++;
        }
        
        $this->db->bind(':promedio', $promedio);
        $this->db->bind(':obs',      $obs ?: null);

        return $this->db->execute();
    }
}
