-- ============================================================
-- MIGRACIÓN FASE 2: Retardos + Auto-Fill + Feriados
-- Ejecutar una sola vez contra la BD del proyecto SGP
-- ============================================================

-- 0. Asegurar que asignacion_id permita NULL (el módulo Kiosco ya no lo usa)
--    Si la columna ya es nullable esta sentencia no hace nada dañino.
ALTER TABLE `asistencias`
    MODIFY COLUMN `asignacion_id` INT(11) NULL DEFAULT NULL
        COMMENT 'FK legada — nullable desde Fase 2';

-- También eliminar la FK si aún existe (puede fallar si ya fue eliminada; ignorar error)
ALTER TABLE `asistencias` DROP FOREIGN KEY IF EXISTS `fk_asistencia_asignacion`;

-- 1. Agregar columna es_retardo a tabla asistencias
ALTER TABLE `asistencias`
    ADD COLUMN IF NOT EXISTS `es_retardo` TINYINT(1) NOT NULL DEFAULT 0
        COMMENT '1 si hora_registro > 09:00:00 y estado=Presente' AFTER `estado`;

-- 2. Agregar columna es_auto_fill para identificar ausentes generados automáticamente
--    (NO penalizan Pro-Rata, sirven para diferenciarlos de ausencias reales)
ALTER TABLE `asistencias`
    ADD COLUMN IF NOT EXISTS `es_auto_fill` TINYINT(1) NOT NULL DEFAULT 0
        COMMENT '1 = generado automáticamente por el sistema (ausente silencioso)' AFTER `es_retardo`;

-- 3. NOTA: La tabla de feriados ya existe como `dias_feriados` con datos cargados.
--    No se crea una tabla nueva. El sistema usa dias_feriados para el auto-fill.

-- 5. Backfill: marcar como retardo los registros Presente con hora > 09:00 ya existentes
UPDATE `asistencias`
SET `es_retardo` = 1
WHERE `estado` = 'Presente'
  AND `hora_registro` > '09:00:00'
  AND `es_retardo` = 0;
