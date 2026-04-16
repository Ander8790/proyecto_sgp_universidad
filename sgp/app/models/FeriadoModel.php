<?php
/**
 * FeriadoModel — Gestión de Días Feriados
 *
 * PROPÓSITO:
 *   Permite al administrador gestionar los días no laborables del año
 *   para que el sistema de asistencias los excluya del auto-fill de ausencias.
 *
 * REGLAS DE NEGOCIO:
 *   - Tabla: dias_feriados (id, fecha, nombre, tipo, aplica_departamento_id, created_at)
 *   - tipo ENUM: 'Nacional', 'Regional', 'Institucional'
 *   - No se permiten fechas duplicadas
 *   - Los feriados pasados no se eliminan (protección de auditoría histórica)
 *
 * @version 1.1
 */
declare(strict_types=1);

class FeriadoModel
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    // =========================================================
    // CONSULTAS DE LECTURA
    // =========================================================

    /**
     * Retorna todos los feriados ordenados por fecha ascendente.
     * Opcionalmente filtra solo los del año indicado.
     */
    public function obtenerTodos(?int $anio = null): array
    {
        if ($anio !== null) {
            $this->db->query("
                SELECT id, fecha, nombre, tipo, created_at
                FROM   dias_feriados
                WHERE  YEAR(fecha) = :anio
                ORDER  BY fecha ASC
            ");
            $this->db->bind(':anio', $anio);
        } else {
            $this->db->query("
                SELECT id, fecha, nombre, tipo, created_at
                FROM   dias_feriados
                ORDER  BY fecha ASC
            ");
        }
        return $this->db->resultSet() ?: [];
    }

    /**
     * Obtiene los feriados del año actual y el siguiente
     * para mostrar en la vista de configuración.
     */
    public function obtenerVigentes(): array
    {
        $anioActual = (int) date('Y');
        $this->db->query("
            SELECT id, fecha, nombre, tipo, created_at
            FROM   dias_feriados
            WHERE  YEAR(fecha) >= :anio
            ORDER  BY fecha ASC
            LIMIT  200
        ");
        $this->db->bind(':anio', $anioActual);
        return $this->db->resultSet() ?: [];
    }

    /**
     * Verifica si una fecha ya está registrada como feriado.
     */
    public function existeFecha(string $fecha): bool
    {
        $this->db->query("SELECT COUNT(*) AS total FROM dias_feriados WHERE fecha = :fecha");
        $this->db->bind(':fecha', $fecha);
        $row = $this->db->single();
        return (int)($row->total ?? 0) > 0;
    }

    /**
     * Total de feriados registrados en el año indicado.
     */
    public function contarPorAnio(int $anio): int
    {
        $this->db->query("SELECT COUNT(*) AS total FROM dias_feriados WHERE YEAR(fecha) = :anio");
        $this->db->bind(':anio', $anio);
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    // =========================================================
    // OPERACIONES DE ESCRITURA
    // =========================================================

    /**
     * Registra un nuevo feriado.
     *
     * @param string $fecha   Formato Y-m-d
     * @param string $nombre  Descripción corta (ej. "Día de la Independencia")
     * @param string $tipo    Nacional | Regional | Institucional
     * @return bool
     */
    public function crear(string $fecha, string $nombre, string $tipo = 'Nacional'): bool
    {
        $tiposValidos = ['Nacional', 'Regional', 'Institucional'];
        if (!in_array($tipo, $tiposValidos, true)) {
            $tipo = 'Nacional';
        }

        $this->db->query("
            INSERT INTO dias_feriados (fecha, nombre, tipo, created_at)
            VALUES (:fecha, :nombre, :tipo, NOW())
        ");
        $this->db->bind(':fecha',  $fecha);
        $this->db->bind(':nombre', $nombre);
        $this->db->bind(':tipo',   $tipo);
        return $this->db->execute();
    }

    /**
     * Elimina un feriado por ID.
     * Solo se permite eliminar feriados futuros (protección de auditoría histórica).
     *
     * @return bool  true si se eliminó, false si era pasado o no existía
     */
    public function eliminar(int $id): bool
    {
        $this->db->query("DELETE FROM dias_feriados WHERE id = :id AND fecha >= CURDATE()");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Devuelve un feriado específico por ID.
     */
    public function obtenerPorId(int $id)
    {
        $this->db->query("SELECT * FROM dias_feriados WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Actualiza el nombre y tipo de un feriado (la fecha no se modifica).
     * Aplica tanto a feriados pasados como futuros.
     */
    public function actualizar(int $id, string $nombre, string $tipo): bool
    {
        $tiposValidos = ['Nacional', 'Regional', 'Institucional'];
        if (!in_array($tipo, $tiposValidos, true)) {
            $tipo = 'Nacional';
        }

        $this->db->query("
            UPDATE dias_feriados
            SET    nombre = :nombre,
                   tipo   = :tipo
            WHERE  id     = :id
        ");
        $this->db->bind(':nombre', $nombre);
        $this->db->bind(':tipo',   $tipo);
        $this->db->bind(':id',     $id);
        return $this->db->execute();
    }
}
