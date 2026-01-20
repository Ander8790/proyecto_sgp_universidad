-- =====================================================
-- Script de Verificación y Creación Rápida
-- =====================================================
-- Este script verifica si existe sgp_v1 y crea el admin
-- =====================================================

-- Verificar si existe la base de datos
SELECT SCHEMA_NAME 
FROM INFORMATION_SCHEMA.SCHEMATA 
WHERE SCHEMA_NAME = 'sgp_v1';

-- Si no existe, ejecuta primero: sgp_v1.sql
-- Si ya existe, continúa con este script:

USE sgp_v1;

-- Verificar si existe el usuario admin
SELECT * FROM usuarios WHERE correo = 'admin@sgp.local';

-- Si NO existe, ejecutar este INSERT:
INSERT IGNORE INTO usuarios (
    rol_id, 
    departamento_id, 
    perfil_completado, 
    activo,
    correo, 
    password, 
    nombre_completo, 
    cedula, 
    telefono
) VALUES (
    1, -- Administrador
    NULL,
    1,
    1,
    'admin@sgp.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123
    'Administrador del Sistema',
    '00000000',
    '0000-0000000'
);

-- Verificar inserción
SELECT 
    id,
    nombre_completo,
    correo,
    rol_id,
    created_at
FROM usuarios
WHERE correo = 'admin@sgp.local';

-- =====================================================
-- CREDENCIALES:
-- Email: admin@sgp.local
-- Contraseña: admin123
-- =====================================================
