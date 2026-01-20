-- =====================================================
-- Actualizar Preguntas de Seguridad (Simplificadas)
-- =====================================================
-- Eliminar acentos y usar solo 3 preguntas simples
-- =====================================================

USE sgp_v1;

-- Desactivar todas las preguntas existentes
UPDATE preguntas_seguridad SET activa = 0;

-- Eliminar preguntas antiguas
DELETE FROM preguntas_seguridad;

-- Insertar las 3 nuevas preguntas sin acentos
INSERT INTO preguntas_seguridad (id, pregunta, activa) VALUES
(1, 'Cual es tu postre favorito?', 1),
(2, 'Cual es tu color favorito?', 1),
(3, 'Cual es el nombre de tu mascota?', 1);

-- Verificar preguntas activas
SELECT id, pregunta, activa FROM preguntas_seguridad;

-- =====================================================
-- Preguntas Activas (3):
-- =====================================================
-- 1. Cual es tu postre favorito?
-- 2. Cual es tu color favorito?
-- 3. Cual es el nombre de tu mascota?
-- =====================================================
