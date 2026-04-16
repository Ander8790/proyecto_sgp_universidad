<?php
/**
 * AutoFillService — Demonio de Relleno de Asistencias
 * ======================================================
 * Detecta días laborables sin marcaje en asignaciones activas
 * y los rellena como "Presente" para mantener la integridad del
 * conteo de 180 días.
 *
 * REGLAS DE NEGOCIO aplicadas:
 *  - Siempre suma 8 horas (horas_calculadas = 8.00)
 *  - Nunca rellena fines de semana (sáb/dom)
 *  - Nunca rellena días marcados en la tabla dias_feriados
 *  - Solo rellena hasta "ayer" — el día actual queda abierto
 *  - No sobreescribe registros existentes (cualquier estado)
 *  - Registra metodo = 'AutoFill' para distinguirlos de marcajes reales
 */
class AutoFillService
{
    /** Minutos de margen al inicio del día para considerar que "ya inició" */
    private const DIAS_ATRAS_MAX = 365; // límite de seguridad: no rellenar más de 1 año atrás

    /**
     * Punto de entrada principal del demonio.
     *
     * @param  Database $db       Instancia de la BD (singleton del sistema)
     * @param  bool     $dryRun   Si true: solo simula sin insertar
     * @return array              Estadísticas de la ejecución
     */
    public static function ejecutar(Database $db, bool $dryRun = false): array
    {
        $stats = [
            'revisados'          => 0,
            'pasantes_afectados' => 0,
            'dias_rellenos'      => 0,
            'dias_omitidos'      => 0,
            'errores'            => 0,
            'detalle'            => [],
            'dry_run'            => $dryRun,
        ];

        $ayer = date('Y-m-d', strtotime('-1 day'));
        $limiteAtras = date('Y-m-d', strtotime("-" . self::DIAS_ATRAS_MAX . " days"));

        // ── 1. Cargar todos los feriados en un set indexado por fecha ─────────
        $feriados = self::cargarFeriados($db);

        // ── 2. Obtener todas las asignaciones activas con fecha_inicio ────────
        $db->query("
            SELECT a.id            AS asignacion_id,
                   a.pasante_id,
                   a.hora_entrada,
                   a.hora_salida,
                   a.fecha_inicio,
                   COALESCE(a.fecha_fin, CURDATE()) AS fecha_fin,
                   dp.nombres,
                   dp.apellidos
            FROM   asignaciones a
            JOIN   datos_personales dp ON dp.usuario_id = a.pasante_id
            WHERE  a.estado = 'activo'
              AND  a.fecha_inicio <= :ayer
            ORDER BY a.pasante_id
        ");
        $db->bind(':ayer', $ayer);
        $asignaciones = $db->resultSet();

        if (empty($asignaciones)) {
            $stats['detalle'][] = 'No hay asignaciones activas.';
            return $stats;
        }

        foreach ($asignaciones as $asig) {
            $stats['revisados']++;

            $fechaDesde = max($asig->fecha_inicio, $limiteAtras);
            $fechaHasta = min($asig->fecha_fin, $ayer);

            if ($fechaDesde > $fechaHasta) {
                continue;
            }

            try {
                // ── 3. Obtener días que ya tienen registro ─────────────────────
                $db->query("
                    SELECT fecha FROM asistencias
                    WHERE  pasante_id    = :pid
                      AND  asignacion_id = :aid
                      AND  fecha BETWEEN :desde AND :hasta
                ");
                $db->bind(':pid',   $asig->pasante_id);
                $db->bind(':aid',   $asig->asignacion_id);
                $db->bind(':desde', $fechaDesde);
                $db->bind(':hasta', $fechaHasta);
                $rows = $db->resultSet();

                $existentes = [];
                foreach ($rows as $r) {
                    $existentes[$r->fecha] = true;
                }

                // ── 4. Generar días laborables en el rango ─────────────────────
                $diasLaborables = self::generarDiasLaborables($fechaDesde, $fechaHasta, $feriados);
                $diasARellenar  = array_filter($diasLaborables, fn($d) => !isset($existentes[$d]));

                if (empty($diasARellenar)) {
                    continue;
                }

                // ── 5. Insertar registros auto-fill (en bloque) ────────────────
                if (!$dryRun) {
                    $db->beginTransaction();

                    foreach ($diasARellenar as $dia) {
                        $db->query("
                            INSERT INTO asistencias
                                (pasante_id, asignacion_id, fecha, hora_registro,
                                 metodo, hora_entrada, hora_salida,
                                 horas_calculadas, estado, observacion, created_at)
                            VALUES
                                (:pid, :aid, :fecha, :hora_reg,
                                 'AutoFill', :hora_ent, :hora_sal,
                                 8.00, 'Presente',
                                 'Generado automáticamente por el sistema (sin marcaje detectado)',
                                 :created)
                        ");
                        $db->bind(':pid',      $asig->pasante_id);
                        $db->bind(':aid',      $asig->asignacion_id);
                        $db->bind(':fecha',    $dia);
                        $db->bind(':hora_reg', $asig->hora_entrada);
                        $db->bind(':hora_ent', $asig->hora_entrada);
                        $db->bind(':hora_sal', $asig->hora_salida);
                        $db->bind(':created',  $dia . ' 23:59:00');
                        $db->execute();
                    }

                    // ── 6. Actualizar horas_cumplidas en la asignacion ─────────
                    $db->query("
                        UPDATE asignaciones
                        SET    horas_cumplidas = (
                                   SELECT COUNT(*) * 8
                                   FROM   asistencias
                                   WHERE  asignacion_id = :aid
                                )
                        WHERE  id = :aid2
                    ");
                    $db->bind(':aid',  $asig->asignacion_id);
                    $db->bind(':aid2', $asig->asignacion_id);
                    $db->execute();

                    $db->commit();
                }

                $cantRellenados = count($diasARellenar);
                $stats['dias_rellenos']      += $cantRellenados;
                $stats['dias_omitidos']      += count($existentes);
                $stats['pasantes_afectados'] += 1;
                $stats['detalle'][]           = [
                    'pasante'     => $asig->nombres . ' ' . $asig->apellidos,
                    'pasante_id'  => $asig->pasante_id,
                    'rellenos'    => $cantRellenados,
                    'primer_dia'  => reset($diasARellenar),
                    'ultimo_dia'  => end($diasARellenar),
                ];

            } catch (\Throwable $e) {
                if (!$dryRun) {
                    try { $db->rollBack(); } catch (\Throwable $_) {}
                }
                $stats['errores']++;
                $stats['detalle'][] = [
                    'error'      => true,
                    'pasante_id' => $asig->pasante_id,
                    'mensaje'    => $e->getMessage(),
                ];
            }
        }

        return $stats;
    }

    /**
     * Carga todos los feriados de la BD en un array indexado por fecha.
     * Devuelve: ['2026-01-01' => 'Año Nuevo', ...]
     */
    public static function cargarFeriados(Database $db): array
    {
        $db->query("SELECT fecha, nombre FROM dias_feriados ORDER BY fecha");
        $rows = $db->resultSet();

        $set = [];
        foreach ($rows as $r) {
            $set[$r->fecha] = $r->nombre;
        }
        return $set;
    }

    /**
     * Genera la lista de días laborables (lunes–viernes, sin feriados)
     * entre dos fechas inclusive.
     *
     * @param  string $desde    Fecha inicio 'Y-m-d'
     * @param  string $hasta    Fecha fin    'Y-m-d'
     * @param  array  $feriados Set indexado ['Y-m-d' => nombre]
     * @return string[]
     */
    public static function generarDiasLaborables(string $desde, string $hasta, array $feriados): array
    {
        $dias   = [];
        $cursor = new \DateTime($desde);
        $fin    = new \DateTime($hasta);

        while ($cursor <= $fin) {
            $dow  = (int) $cursor->format('N'); // 1=Lun … 7=Dom
            $date = $cursor->format('Y-m-d');

            if ($dow < 6 && !isset($feriados[$date])) {
                $dias[] = $date;
            }
            $cursor->modify('+1 day');
        }

        return $dias;
    }

    /**
     * Verifica si una fecha específica es laborable.
     */
    public static function esLaborable(string $fecha, array $feriados): bool
    {
        $dow = (int)(new \DateTime($fecha))->format('N');
        return $dow < 6 && !isset($feriados[$fecha]);
    }
}
