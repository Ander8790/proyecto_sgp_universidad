-- ============================================================
-- FASE 1: Database Fix & Cleanup — Matemática Pro-Rata SGP
-- Ejecutar en phpMyAdmin o desde consola MySQL (proyecto_sgp)
-- ============================================================

-- 1A. Limpiar estados inválidos: '' → 'Pendiente'
--     Razón: el ENUM no acepta cadena vacía; 'Pendiente' es el estado neutro correcto.
UPDATE datos_pasante
SET estado_pasantia = 'Pendiente'
WHERE estado_pasantia = '' OR estado_pasantia IS NULL;

-- 1B. Corregir la meta por defecto de horas: 240 → 1440
--     Razón: la jornada oficial es 180 días hábiles × 8 h = 1440 h.
--     Sólo afecta registros que aún tengan el valor incorrecto de 240.
ALTER TABLE datos_pasante
    ALTER COLUMN horas_meta SET DEFAULT 1440;

UPDATE datos_pasante
SET horas_meta = 1440
WHERE horas_meta = 240;

-- 1C. (Recomendado) Poner a cero horas_acumuladas — ya no se usa para el cálculo.
--     El campo queda como "legacy / archivado". El sistema leerá asistencias en adelante.
--     DESCOMENTA si deseas limpiar completamente el campo:
-- UPDATE datos_pasante SET horas_acumuladas = 0;

-- Verificación rápida post-ejecución:
SELECT estado_pasantia, COUNT(*) AS total
FROM datos_pasante
GROUP BY estado_pasantia;

SELECT horas_meta, COUNT(*) AS total
FROM datos_pasante
GROUP BY horas_meta;
