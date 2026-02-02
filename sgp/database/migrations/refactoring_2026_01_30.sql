-- =====================================================
-- MIGRACIÓN 1: Verificar y Crear Tabla preguntas_seguridad
-- =====================================================
-- Fecha: 2026-01-30
-- Propósito: Asegurar que existe la tabla de preguntas de seguridad
-- para el sistema de recuperación de contraseñas
-- =====================================================

-- Crear tabla si no existe
CREATE TABLE IF NOT EXISTS preguntas_seguridad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta TEXT NOT NULL,
    activa TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar preguntas de seguridad (solo si la tabla está vacía)
INSERT INTO preguntas_seguridad (pregunta, activa)
SELECT * FROM (
    SELECT '¿Cuál es tu postre favorito?' as pregunta, 1 as activa UNION ALL
    SELECT '¿Cuál es tu color favorito?', 1 UNION ALL
    SELECT '¿Cuál es el nombre de tu mascota?', 1 UNION ALL
    SELECT '¿En qué ciudad naciste?', 1 UNION ALL
    SELECT '¿Cuál es tu película favorita?', 1
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM preguntas_seguridad LIMIT 1);

-- Verificación: Mostrar preguntas activas
SELECT 
    id, 
    pregunta, 
    activa,
    created_at
FROM preguntas_seguridad 
WHERE activa = 1
ORDER BY id;

-- =====================================================
-- MIGRACIÓN 2: Agregar Campo 'cargo' a datos_personales
-- =====================================================
-- Fecha: 2026-01-30
-- Propósito: Permitir que Administradores y Tutores registren
-- su cargo institucional (ej: "Analista de Soporte")
-- =====================================================

-- Verificar si la columna ya existe
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'datos_personales' 
  AND COLUMN_NAME = 'cargo';

-- Si la consulta anterior NO devuelve resultados, ejecutar:
ALTER TABLE datos_personales 
ADD COLUMN cargo VARCHAR(100) NULL AFTER apellidos;

-- Verificación final: Confirmar que la columna existe
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'datos_personales' 
  AND COLUMN_NAME = 'cargo';

-- =====================================================
-- RESULTADO ESPERADO
-- =====================================================
-- 1. Tabla preguntas_seguridad creada con 5 preguntas activas
-- 2. Columna 'cargo' agregada a datos_personales (VARCHAR(100), NULL)
-- =====================================================
