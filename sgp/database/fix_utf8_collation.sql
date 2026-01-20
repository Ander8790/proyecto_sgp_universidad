-- ====================================================
-- SCRIPT: Corregir Cotejamiento UTF-8 para Acentos
-- ====================================================
-- Este script corrige el cotejamiento de la base de datos
-- para soportar correctamente caracteres con acentos

-- 1. Cambiar cotejamiento de la base de datos
ALTER DATABASE sgp CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- 2. Cambiar cotejamiento de la tabla de usuarios
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- 3. Cambiar cotejamiento de la tabla de preguntas de seguridad
ALTER TABLE security_questions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- 4. Cambiar cotejamiento de la tabla de respuestas de seguridad
ALTER TABLE user_security_answers CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- 5. Verificar cambios
SHOW TABLE STATUS WHERE Name IN ('users', 'security_questions', 'user_security_answers');

-- 6. Verificar columnas específicas
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CHARACTER_SET_NAME,
    COLLATION_NAME
FROM 
    INFORMATION_SCHEMA.COLUMNS
WHERE 
    TABLE_SCHEMA = 'sgp'
    AND TABLE_NAME IN ('users', 'security_questions', 'user_security_answers')
    AND DATA_TYPE IN ('varchar', 'text', 'char');
