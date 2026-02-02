<?php
/**
 * PasanteModel - Gestión de Datos de Pasantes
 * 
 * PROPÓSITO EDUCATIVO:
 * Este modelo maneja EXCLUSIVAMENTE los datos específicos del rol Pasante.
 * Trabaja con la tabla `datos_pasante` que tiene relación 1:1 con `usuarios`.
 * 
 * IMPORTANTE:
 * Después de la refactorización, la columna `institucion_procedencia`
 * está DIRECTAMENTE en `datos_pasante` (ya no en datos_academicos).
 * 
 * @author Sistema SGP
 * @version 2.0 (Post-Refactorización)
 */

class PasanteModel
{
    private $db;

    public function __construct()
    {
        $config = require '../app/config/config.php';
        $this->db = new Database($config['db']);
    }

    /**
     * Obtener Datos Completos de un Pasante
     * 
     * PROPÓSITO:
     * Retornar toda la información de un pasante específico
     * combinando datos de: usuarios, datos_personales y datos_pasante.
     * 
     * DEFENSA ACADÉMICA:
     * "Profesor, uso un solo JOIN para obtener todos los datos en una
     * consulta. Esto es más eficiente que hacer múltiples consultas
     * separadas (evita el problema N+1)."
     * 
     * @param int $usuarioId ID del usuario
     * @return array|null Datos del pasante o null si no existe
     */
    public function getByUsuarioId(int $usuarioId): ?array
    {
        $this->db->query("
            SELECT 
                u.id,
                u.correo,
                u.activo,
                u.created_at as fecha_registro,
                dp.cedula,
                dp.nombres,
                dp.apellidos,
                dp.telefono,
                dp.direccion,
                dp.genero,
                dp.fecha_nacimiento,
                dpa.institucion_procedencia,
                dpa.carrera,
                dpa.semestre,
                dpa.cargo,
                dpa.estado_pasantia,
                dpa.fecha_inicio_pasantia,
                dpa.fecha_fin_estimada,
                dpa.horas_acumuladas,
                dpa.horas_meta,
                dpa.departamento_asignado_id,
                dpa.observaciones,
                dept.nombre as departamento_nombre
            FROM usuarios u
            INNER JOIN datos_personales dp ON u.id = dp.usuario_id
            LEFT JOIN datos_pasante dpa ON u.id = dpa.usuario_id
            LEFT JOIN departamentos dept ON dpa.departamento_asignado_id = dept.id
            WHERE u.id = :usuario_id AND u.role_id = 3
            LIMIT 1
        ");
        
        $this->db->bind(':usuario_id', $usuarioId);
        
        $result = $this->db->single();
        
        return $result ? (array) $result : null;
    }

    /**
     * Crear Registro de Pasante
     * 
     * PROPÓSITO:
     * Insertar datos iniciales del pasante en la tabla datos_pasante.
     * Se llama después de crear el usuario y datos_personales.
     * 
     * @param array $data Datos del pasante
     * @return bool True si se creó exitosamente
     */
    public function create(array $data): bool
    {
        $this->db->query("
            INSERT INTO datos_pasante (
                usuario_id,
                institucion_procedencia,
                carrera,
                semestre,
                cargo,
                estado_pasantia,
                horas_meta
            ) VALUES (
                :usuario_id,
                :institucion,
                :carrera,
                :semestre,
                :cargo,
                'Pendiente',
                240
            )
        ");
        
        $this->db->bind(':usuario_id', $data['usuario_id']);
        $this->db->bind(':institucion', $data['institucion_procedencia'] ?? null);
        $this->db->bind(':carrera', $data['carrera'] ?? null);
        $this->db->bind(':semestre', $data['semestre'] ?? null);
        $this->db->bind(':cargo', $data['cargo'] ?? null);
        
        return $this->db->execute();
    }

    /**
     * Actualizar Estado de Pasantía (Formalización)
     * 
     * PROPÓSITO:
     * Cambiar el estado de un pasante de "Pendiente" a "Activo"
     * y registrar fecha de inicio, departamento asignado.
     * 
     * DEFENSA ACADÉMICA:
     * "Profesor, esta actualización es atómica. Si falla cualquier
     * parte, se revierte todo gracias a las transacciones de MySQL.
     * Además, registro la acción en la bitácora para trazabilidad."
     * 
     * @param int $usuarioId ID del usuario
     * @param array $data Datos de formalización
     * @return bool True si se actualizó exitosamente
     */
    public function formalizar(int $usuarioId, array $data): bool
    {
        // Calcular fecha fin estimada (6 meses después)
        $fechaInicio = $data['fecha_inicio'];
        $fechaFin = date('Y-m-d', strtotime($fechaInicio . ' + 6 months'));
        
        $this->db->query("
            UPDATE datos_pasante
            SET 
                estado_pasantia = 'Activo',
                fecha_inicio_pasantia = :fecha_inicio,
                fecha_fin_estimada = :fecha_fin,
                departamento_asignado_id = :departamento_id,
                institucion_procedencia = COALESCE(:institucion, institucion_procedencia)
            WHERE usuario_id = :usuario_id
        ");
        
        $this->db->bind(':fecha_inicio', $fechaInicio);
        $this->db->bind(':fecha_fin', $fechaFin);
        $this->db->bind(':departamento_id', $data['departamento_id']);
        $this->db->bind(':institucion', $data['institucion_procedencia'] ?? null);
        $this->db->bind(':usuario_id', $usuarioId);
        
        return $this->db->execute();
    }

    /**
     * Actualizar Horas Acumuladas
     * 
     * @param int $usuarioId ID del usuario
     * @param int $horas Horas a sumar
     * @return bool True si se actualizó exitosamente
     */
    public function actualizarHoras(int $usuarioId, int $horas): bool
    {
        $this->db->query("
            UPDATE datos_pasante
            SET horas_acumuladas = horas_acumuladas + :horas
            WHERE usuario_id = :usuario_id
        ");
        
        $this->db->bind(':horas', $horas);
        $this->db->bind(':usuario_id', $usuarioId);
        
        return $this->db->execute();
    }

    /**
     * Obtener Todos los Pasantes
     * 
     * @return array Lista de pasantes
     */
    public function getAll(): array
    {
        $this->db->query("
            SELECT 
                u.id,
                u.correo,
                u.activo,
                dp.cedula,
                dp.nombres,
                dp.apellidos,
                dpa.institucion_procedencia,
                dpa.estado_pasantia,
                dpa.horas_acumuladas,
                dpa.horas_meta,
                dept.nombre as departamento_nombre,
                CASE 
                    WHEN dpa.horas_meta > 0 THEN ROUND((dpa.horas_acumuladas / dpa.horas_meta) * 100, 2)
                    ELSE 0
                END as progreso_porcentaje
            FROM usuarios u
            INNER JOIN datos_personales dp ON u.id = dp.usuario_id
            LEFT JOIN datos_pasante dpa ON u.id = dpa.usuario_id
            LEFT JOIN departamentos dept ON dpa.departamento_asignado_id = dept.id
            WHERE u.role_id = 3
            ORDER BY dpa.estado_pasantia ASC, dp.apellidos ASC
        ");
        
        return $this->db->resultSet();
    }
}
