-- ============================================
-- Tabla de Notificaciones
-- ============================================
-- Almacena notificaciones del sistema para cada usuario
-- Soporta notificaciones de diferentes tipos con URLs opcionales

CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL COMMENT 'Tipo: usuario_creado, perfil_actualizado, password_reset, sistema',
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    leida TINYINT(1) DEFAULT 0 COMMENT '0 = No leída, 1 = Leída',
    url VARCHAR(255) NULL COMMENT 'URL opcional para redirigir al hacer clic',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_leida (usuario_id, leida),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar notificaciones de ejemplo para el admin
INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, url) VALUES
(1, 'sistema', 'Bienvenido al Sistema', 'El sistema de notificaciones ha sido activado correctamente', '/dashboard'),
(1, 'sistema', 'Nueva funcionalidad', 'Se ha implementado encriptación de URLs para mayor seguridad', '/users');
