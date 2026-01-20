-- =====================================================
-- Sistema de Gestión de Personal (SGP) - v1.0
-- Base de Datos Normalizada (3FN)
-- =====================================================
-- Charset: utf8mb4
-- Collation: utf8mb4_general_ci
-- Motor: InnoDB (Soporte completo de Foreign Keys)
-- =====================================================

-- Eliminar base de datos si existe y crear nueva
DROP DATABASE IF EXISTS sgp_v1;
CREATE DATABASE sgp_v1 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_general_ci;

USE sgp_v1;

-- =====================================================
-- TABLAS CATÁLOGO (Normalización)
-- =====================================================

-- Tabla: roles
-- Descripción: Catálogo de roles del sistema
CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos iniciales para roles
INSERT INTO roles (nombre) VALUES 
    ('Administrador'),
    ('Tutor'),
    ('Pasante');

-- Tabla: departamentos
-- Descripción: Catálogo de áreas de trabajo
CREATE TABLE departamentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos iniciales para departamentos
INSERT INTO departamentos (nombre, descripcion) VALUES 
    ('Soporte Técnico', 'Atención y resolución de problemas técnicos'),
    ('Redes y Telecomunicaciones', 'Gestión de infraestructura de red'),
    ('Atención al Usuario', 'Servicio de atención y orientación'),
    ('Desarrollo de Software', 'Creación y mantenimiento de sistemas'),
    ('Seguridad Informática', 'Protección de sistemas y datos'),
    ('Base de Datos', 'Administración y optimización de bases de datos');

-- Tabla: preguntas_seguridad
-- Descripción: Catálogo de preguntas para recuperación de contraseña
CREATE TABLE preguntas_seguridad (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pregunta TEXT NOT NULL,
    activa TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos iniciales para preguntas de seguridad
INSERT INTO preguntas_seguridad (pregunta) VALUES 
    ('¿Cuál es el nombre de tu primera mascota?'),
    ('¿En qué ciudad naciste?'),
    ('¿Cuál es el nombre de tu mejor amigo de la infancia?'),
    ('¿Cuál es tu comida favorita?'),
    ('¿Cuál fue el nombre de tu primera escuela?');

-- =====================================================
-- TABLA PRINCIPAL: usuarios
-- =====================================================

CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Relaciones (Foreign Keys)
    rol_id INT UNSIGNED NOT NULL DEFAULT 3 COMMENT 'FK a roles, Default: Pasante (3)',
    departamento_id INT UNSIGNED NULL COMMENT 'FK a departamentos, Asignado por tutor',
    
    -- Control de flujo
    perfil_completado TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Indica si completó datos personales',
    activo TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Estado del usuario',
    
    -- Datos de autenticación
    correo VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt',
    
    -- Datos personales (Nullable hasta completar perfil)
    nombre_completo VARCHAR(150) NOT NULL,
    cedula VARCHAR(20) UNIQUE NULL,
    telefono VARCHAR(20) NULL,
    institucion_procedencia VARCHAR(150) NULL,
    
    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices para optimización
    INDEX idx_correo (correo),
    INDEX idx_cedula (cedula),
    INDEX idx_rol (rol_id),
    INDEX idx_departamento (departamento_id),
    
    -- Foreign Keys con integridad referencial
    CONSTRAINT fk_usuarios_rol 
        FOREIGN KEY (rol_id) 
        REFERENCES roles(id) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE,
        
    CONSTRAINT fk_usuarios_departamento 
        FOREIGN KEY (departamento_id) 
        REFERENCES departamentos(id) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE
        
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABLA: usuarios_respuestas
-- Descripción: Almacena respuestas de seguridad encriptadas
-- =====================================================

CREATE TABLE usuarios_respuestas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Relaciones
    usuario_id INT UNSIGNED NOT NULL,
    pregunta_id INT UNSIGNED NOT NULL,
    
    -- Respuesta encriptada (bcrypt)
    respuesta_hash VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt de la respuesta normalizada',
    
    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_usuario (usuario_id),
    
    -- Constraint: Un usuario solo puede tener una respuesta por pregunta
    UNIQUE KEY unique_usuario_pregunta (usuario_id, pregunta_id),
    
    -- Foreign Keys
    CONSTRAINT fk_respuestas_usuario 
        FOREIGN KEY (usuario_id) 
        REFERENCES usuarios(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
        
    CONSTRAINT fk_respuestas_pregunta 
        FOREIGN KEY (pregunta_id) 
        REFERENCES preguntas_seguridad(id) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE
        
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- DATOS DE PRUEBA (Usuario Administrador por defecto)
-- =====================================================

-- Insertar usuario administrador
-- Contraseña: admin123 (Hash bcrypt)
INSERT INTO usuarios (
    rol_id, 
    departamento_id, 
    perfil_completado, 
    correo, 
    password, 
    nombre_completo, 
    cedula, 
    telefono
) VALUES (
    1, -- Administrador
    NULL, -- Sin departamento específico
    1, -- Perfil completado
    'admin@sgp.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123
    'Administrador del Sistema',
    '00000000',
    '0000-0000000'
);

-- =====================================================
-- VISTAS ÚTILES (Opcional)
-- =====================================================

-- Vista: usuarios_completos
-- Descripción: Muestra usuarios con información de rol y departamento
CREATE VIEW vista_usuarios_completos AS
SELECT 
    u.id,
    u.nombre_completo,
    u.correo,
    u.cedula,
    u.telefono,
    u.institucion_procedencia,
    r.nombre AS rol,
    d.nombre AS departamento,
    u.perfil_completado,
    u.activo,
    u.created_at
FROM usuarios u
INNER JOIN roles r ON u.rol_id = r.id
LEFT JOIN departamentos d ON u.departamento_id = d.id;

-- =====================================================
-- PROCEDIMIENTOS ALMACENADOS (Opcional - Avanzado)
-- =====================================================

DELIMITER $$

-- Procedimiento: Verificar si usuario puede acceder al dashboard
CREATE PROCEDURE sp_verificar_acceso_usuario(
    IN p_usuario_id INT UNSIGNED,
    OUT p_puede_acceder TINYINT(1),
    OUT p_mensaje VARCHAR(255)
)
BEGIN
    DECLARE v_rol_id INT UNSIGNED;
    DECLARE v_perfil_completado TINYINT(1);
    DECLARE v_departamento_id INT UNSIGNED;
    
    -- Obtener datos del usuario
    SELECT rol_id, perfil_completado, departamento_id
    INTO v_rol_id, v_perfil_completado, v_departamento_id
    FROM usuarios
    WHERE id = p_usuario_id AND activo = 1;
    
    -- Verificar si es Pasante (rol_id = 3)
    IF v_rol_id = 3 THEN
        -- Verificar perfil completado
        IF v_perfil_completado = 0 THEN
            SET p_puede_acceder = 0;
            SET p_mensaje = 'Debe completar su perfil';
        -- Verificar asignación de departamento
        ELSEIF v_departamento_id IS NULL THEN
            SET p_puede_acceder = 0;
            SET p_mensaje = 'Esperando asignación de departamento';
        ELSE
            SET p_puede_acceder = 1;
            SET p_mensaje = 'Acceso permitido';
        END IF;
    ELSE
        -- Administrador o Tutor: acceso directo
        SET p_puede_acceder = 1;
        SET p_mensaje = 'Acceso permitido';
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- INFORMACIÓN DE LA BASE DE DATOS
-- =====================================================

SELECT 
    'Base de datos SGP v1.0 creada exitosamente' AS mensaje,
    (SELECT COUNT(*) FROM roles) AS total_roles,
    (SELECT COUNT(*) FROM departamentos) AS total_departamentos,
    (SELECT COUNT(*) FROM preguntas_seguridad) AS total_preguntas,
    (SELECT COUNT(*) FROM usuarios) AS total_usuarios;
