<?php
/**
 * PasanteModel - Gestión de Datos de Pasantes
 *
 * ARQUITECTURA NORMALIZADA (3NF):
 *   - `usuarios`        : autenticación (correo, password, pin, rol, estado)
 *   - `datos_personales`: biografía (cedula, nombres, apellidos, cargo, teléfono)
 *   - `datos_pasante`   : lógica académica (horas, estado_pasantia, departamento)
 *
 * @version 4.0 — Arquitectura Normalizada con JOINs
 */
class PasanteModel
{
    private $db;

    public function __construct()
    {
        $config = require '../app/config/config.php';
        $this->db = Database::getInstance(); // SGP-FIX-v2 [6/2.1] aplicado
    }

    // ────────────────────────────────────────────────────────────────
    // CÁLCULO DE FECHAS — DÍAS HÁBILES
    // ────────────────────────────────────────────────────────────────

    /**
     * Calcular Fecha Fin Estimada saltando Sábados y Domingos.
     *
     * @param string $fecha_inicio  Formato Y-m-d
     * @param int    $dias_habiles  Por defecto 180 (≈ 9 meses laborales)
     * @return string Fecha fin en formato Y-m-d
     */
    public function calcularFechaFin(string $fecha_inicio, int $dias_habiles = 180): string
    {
        $feriados = [];
        $fecha    = new DateTime($fecha_inicio);
        $conteo   = 0;

        while ($conteo < $dias_habiles) {
            $fecha->modify('+1 day');
            $diaSemana = (int)$fecha->format('N'); // 1=Lun … 7=Dom

            if ($diaSemana >= 6) continue;
            if (in_array($fecha->format('Y-m-d'), $feriados)) continue;

            $conteo++;
        }

        return $fecha->format('Y-m-d');
    }

    // ────────────────────────────────────────────────────────────────
    // CONSULTAS
    // ────────────────────────────────────────────────────────────────

    /**
     * Obtener todos los pasantes (rol_id = 3) con datos de las 3 tablas.
     *
     * JOIN con datos_personales (cedula, nombres, apellidos)
     * JOIN con datos_pasante    (horas_acumuladas, estado_pasantia)
     *
     * @return array Lista de pasantes como objetos stdClass
     */
    public function getAll(int $periodoId = 0): array
    {
        $periodoWhere = $periodoId > 0 ? 'AND (dpa.periodo_id = :periodo_id OR dpa.periodo_id IS NULL)' : '';

        $this->db->query("
            SELECT
                u.id,
                u.cedula,
                dp.nombres,
                dp.apellidos,
                dp.cargo,
                u.correo,
                dp.telefono                                 AS telefono,
                u.estado                                     AS activo,
                u.pin_asistencia,
                -- Lógica de negocio: datos académicos viven en datos_pasante
                COALESCE(dpa.estado_pasantia, 'Sin Asignar') AS estado_pasantia,
                COALESCE(dpa.horas_acumuladas, 0)            AS horas_acumuladas,
                COALESCE(dpa.horas_meta, 1440)               AS horas_meta,
                dpa.fecha_inicio_pasantia                    AS fecha_inicio,
                dpa.fecha_fin_estimada                       AS fecha_fin_estimada,
                dpa.institucion_procedencia,
                u.created_at                                             AS fecha_registro,
                d.nombre                                                 AS departamento_nombre,
                COALESCE(inst.nombre, dpa.institucion_procedencia)       AS institucion_nombre,
                inst.representante_nombre                                AS institucion_representante,
                pa.nombre                                                AS periodo_nombre,
                CASE
                    WHEN COALESCE(dpa.horas_meta, 1440) > 0
                    THEN ROUND(
                        (COALESCE(dpa.horas_acumuladas, 0) / COALESCE(dpa.horas_meta, 1440)) * 100,
                    2)
                    ELSE 0
                END AS progreso_porcentaje
            FROM usuarios u
            LEFT JOIN datos_personales dp  ON dp.usuario_id  = u.id
            LEFT JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos    d   ON d.id = dpa.departamento_asignado_id
            LEFT JOIN instituciones    inst ON inst.id = dpa.institucion_procedencia
            LEFT JOIN periodos_academicos pa ON pa.id = dpa.periodo_id
            WHERE u.rol_id = 3 {$periodoWhere}
            ORDER BY
                FIELD(COALESCE(dpa.estado_pasantia, 'Sin Asignar'), 'Sin Asignar', 'Activo', 'Finalizado'),
                IFNULL(dp.apellidos, u.correo) ASC
        ");

        if ($periodoId > 0) {
            $this->db->bind(':periodo_id', $periodoId);
        }

        return $this->db->resultSet();
    }

    /**
     * Obtener un pasante por su ID de usuario.
     *
     * @param int $usuarioId
     * @return object|null stdClass con todos los datos o null
     */
    public function getByUsuarioId(int $usuarioId): ?object
    {
        $this->db->query("
            SELECT
                u.id,
                u.cedula,
                dp.nombres,
                dp.apellidos,
                dp.cargo,
                u.correo,
                dp.telefono                           AS telefono,
                dp.genero                             AS genero,
                dp.fecha_nacimiento,
                u.estado                               AS activo,
                u.pin_asistencia,
                COALESCE(dpa.estado_pasantia, 'Sin Asignar') AS estado_pasantia,
                COALESCE(dpa.horas_acumuladas, 0)            AS horas_acumuladas,
                COALESCE(dpa.horas_meta, 1440)               AS horas_meta,
                dpa.fecha_inicio_pasantia                    AS fecha_inicio,
                dpa.fecha_fin_estimada                       AS fecha_fin_estimada,
                dpa.institucion_procedencia,
                u.created_at  AS fecha_registro,
                d.nombre      AS departamento_nombre,
                COALESCE(inst.nombre, dpa.institucion_procedencia) AS institucion_nombre,
                inst.representante_nombre AS institucion_representante,
                pa.nombre AS periodo_nombre,
                CASE
                    WHEN COALESCE(dpa.horas_meta, 1440) > 0
                    THEN ROUND(
                        (COALESCE(dpa.horas_acumuladas, 0) / COALESCE(dpa.horas_meta, 1440)) * 100,
                    2)
                    ELSE 0
                END AS progreso_porcentaje
            FROM usuarios u
            LEFT JOIN datos_personales dp  ON dp.usuario_id  = u.id
            LEFT JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos    d   ON d.id = dpa.departamento_asignado_id
            LEFT JOIN instituciones    inst ON inst.id = dpa.institucion_procedencia
            LEFT JOIN periodos_academicos pa ON pa.id = dpa.periodo_id
            WHERE u.id = :uid AND u.rol_id = 3
            LIMIT 1
        ");

        $this->db->bind(':uid', $usuarioId);
        $result = $this->db->single();

        return $result ?: null;
    }

    // ────────────────────────────────────────────────────────────────
    // ASIGNACIÓN
    // ────────────────────────────────────────────────────────────────

    /**
     * Asignar pasante a un departamento y marcar la pasantía como Activa.
     *
     * ESTRATEGIA: Actualiza (o crea) el registro en datos_pasante.
     * Si el pasante aún no tiene fila en datos_pasante, la inserta (UPSERT).
     *
     * @param int    $pasanteId
     * @param int    $departamentoId
     * @param string $fechaInicio     Y-m-d
     * @return bool
     */
    public function asignar(int $pasanteId, int $departamentoId, string $fechaInicio): bool
    {
        $fechaFin = $this->calcularFechaFin($fechaInicio, 180);

        // UPSERT: si ya existe fila en datos_pasante → UPDATE, si no → INSERT
        $this->db->query("
            INSERT INTO datos_pasante
                (usuario_id, departamento_asignado_id, fecha_inicio_pasantia, fecha_fin_estimada, estado_pasantia)
            VALUES
                (:uid, :dept_id, :fecha_inicio, :fecha_fin, 'Activo')
            ON DUPLICATE KEY UPDATE
                departamento_asignado_id = VALUES(departamento_asignado_id),
                fecha_inicio_pasantia    = VALUES(fecha_inicio_pasantia),
                fecha_fin_estimada       = VALUES(fecha_fin_estimada),
                estado_pasantia          = 'Activo'
        ");

        $this->db->bind(':uid',          $pasanteId);
        $this->db->bind(':dept_id',      $departamentoId);
        $this->db->bind(':fecha_inicio', $fechaInicio);
        $this->db->bind(':fecha_fin',    $fechaFin);

        return $this->db->execute();
    }

    /**
     * Sumar horas acumuladas a un pasante.
     *
     * @param int $pasanteId
     * @param int $horas
     * @return bool
     */
    public function sumarHoras(int $pasanteId, int $horas): bool
    {
        $this->db->query("
            UPDATE datos_pasante
            SET horas_acumuladas = horas_acumuladas + :horas
            WHERE usuario_id = :pasante_id
        ");

        $this->db->bind(':horas',      $horas);
        $this->db->bind(':pasante_id', $pasanteId);

        return $this->db->execute();
    }

    /**
     * Marcar pasantía como Finalizada.
     *
     * @param int $pasanteId
     * @return bool
     */
    public function finalizar(int $pasanteId): bool
    {
        $this->db->query("
            UPDATE datos_pasante
            SET estado_pasantia = 'Finalizado'
            WHERE usuario_id = :pasante_id
        ");

        $this->db->bind(':pasante_id', $pasanteId);

        return $this->db->execute();
    }
}
