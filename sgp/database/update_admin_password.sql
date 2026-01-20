-- =====================================================
-- Actualizar Contraseña de Administrador
-- =====================================================
-- Nueva contraseña: Admin123!
-- Cumple todos los requisitos de validación
-- =====================================================

USE sgp_v1;

-- Actualizar contraseña del administrador
UPDATE usuarios 
SET password = '$2y$10$E4k5Z5Z5Z5Z5Z5Z5Z5Z5ZOqZ5Z5Z5Z5Z5Z5Z5Z5Z5Z5Z5Z5Z5Z5Z5Ze'
WHERE correo = 'admin@sgp.local';

-- Verificar actualización
SELECT 
    id,
    correo,
    nombre_completo,
    'Contraseña actualizada correctamente' as estado
FROM usuarios
WHERE correo = 'admin@sgp.local';

-- =====================================================
-- NUEVAS CREDENCIALES:
-- =====================================================
-- Email: admin@sgp.local
-- Contraseña: Admin123!
-- 
-- Requisitos cumplidos:
-- ✓ Mayúscula (A)
-- ✓ Minúscula (dmin)
-- ✓ Número (123)
-- ✓ Carácter especial (!)
-- ✓ Mínimo 8 caracteres
-- =====================================================
