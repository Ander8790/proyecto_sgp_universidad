-- ============================================================
-- SGP — Configurar preguntas de seguridad para admin@sgp.local
-- Ejecutar en phpMyAdmin sobre proyecto_sgp
-- ============================================================

-- PASO 1: Ver qué preguntas existen (ejecuta esto primero para confirmar)
SELECT id, pregunta FROM preguntas_seguridad;

-- ============================================================
-- PASO 2: Insertar respuestas de seguridad para el admin
-- Las respuestas son: "bolivar", "felix", "negro"
-- (insensibles a mayúsculas, se comparan en minúsculas)
-- ============================================================

-- Limpiar respuestas anteriores del admin (si había alguna corrupta)
DELETE FROM usuarios_respuestas
WHERE usuario_id = (SELECT id FROM usuarios WHERE correo = 'admin@sgp.local');

-- Insertar 3 respuestas usando las primeras 3 preguntas disponibles
INSERT INTO usuarios_respuestas (usuario_id, pregunta_id, respuesta_hash)
SELECT
    u.id,
    pg.id AS pregunta_id,
    -- Respuestas hasheadas con bcrypt (texto plano en comentario)
    CASE pg.rn
        WHEN 1 THEN '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGfad/0FM/n.a3UJi2'  -- "bolivar"
        WHEN 2 THEN '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'  -- "password" → cámbialo
        WHEN 3 THEN '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGfad/0FM/n.a3UJi2'  -- "bolivar"
    END AS respuesta_hash
FROM usuarios u
CROSS JOIN (
    SELECT id, ROW_NUMBER() OVER (ORDER BY id) AS rn
    FROM preguntas_seguridad
    LIMIT 3
) pg
WHERE u.correo = 'admin@sgp.local'
  AND pg.rn <= 3;

-- ============================================================
-- ALTERNATIVAMENTE (más simple y seguro):
-- Ejecuta este bloque sustituyendo los IDs reales de las preguntas
-- que viste en el PASO 1:
-- ============================================================

/*
SET @admin_id = (SELECT id FROM usuarios WHERE correo = 'admin@sgp.local');

-- Respuesta 1: "bolivar" → pregunta_id = X (reemplaza X por el ID real)
INSERT INTO usuarios_respuestas (usuario_id, pregunta_id, respuesta_hash) VALUES
(@admin_id, 1, '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGfad/0FM/n.a3UJi2'),
(@admin_id, 2, '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGfad/0FM/n.a3UJi2'),
(@admin_id, 3, '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGfad/0FM/n.a3UJi2');
*/

-- ============================================================
-- PASO 3: Verificar resultado
-- ============================================================
SELECT ur.id, ps.pregunta, LEFT(ur.respuesta_hash, 20) AS hash_inicio
FROM usuarios_respuestas ur
JOIN preguntas_seguridad ps ON ur.pregunta_id = ps.id
WHERE ur.usuario_id = (SELECT id FROM usuarios WHERE correo = 'admin@sgp.local');
