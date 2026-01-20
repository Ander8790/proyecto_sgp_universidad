-- =====================================================
-- SGP MASTER DATABASE - CLEAN SLATE v3.0
-- =====================================================
-- Estrategia: Borrado total y reconstrucción normalizada
-- Charset: utf8mb4_general_ci
-- Autor: Sistema SGP
-- Fecha: 2026-01-09
-- =====================================================

-- PASO 1: ELIMINAR TABLAS EXISTENTES (CLEAN SLATE)
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS usuarios_respuestas;
DROP TABLE IF EXISTS datos_academicos;
DROP TABLE IF EXISTS datos_personales;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS preguntas_seguridad;
DROP TABLE IF EXISTS departamentos;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- PASO 2: CREAR BASE DE DATOS (SI NO EXISTE)
-- =====================================================

CREATE DATABASE IF NOT EXISTS sgp_v1 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci;

USE sgp_v1;

-- =====================================================
-- PASO 3: ESTRUCTURA NORMALIZADA (3NF)
-- =====================================================

-- Tabla: roles
-- =====================================================
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla: departamentos
-- =====================================================
CREATE TABLE departamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla: preguntas_seguridad
-- =====================================================
CREATE TABLE preguntas_seguridad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta TEXT NOT NULL,
    activa TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla: usuarios (CORE)
-- =====================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    correo VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    departamento_id INT NULL,
    avatar VARCHAR(255) DEFAULT 'default.png',
    requiere_cambio_clave TINYINT(1) DEFAULT 1,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL,
    
    INDEX idx_correo (correo),
    INDEX idx_rol (rol_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla: datos_personales (1:1 con usuarios)
-- =====================================================
CREATE TABLE datos_personales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL UNIQUE,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    genero ENUM('M', 'F', 'Otro'),
    fecha_nacimiento DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    
    INDEX idx_cedula (cedula)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla: datos_academicos (1:1 con usuarios)
-- =====================================================
CREATE TABLE datos_academicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL UNIQUE,
    institucion_procedencia VARCHAR(200) NOT NULL,
    carrera VARCHAR(200) NOT NULL,
    nivel_academico ENUM('Técnico', 'Universitario', 'Postgrado') DEFAULT 'Universitario',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla: usuarios_respuestas (Seguridad)
-- =====================================================
CREATE TABLE usuarios_respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    pregunta_id INT NOT NULL,
    respuesta_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (pregunta_id) REFERENCES preguntas_seguridad(id) ON DELETE RESTRICT,
    
    UNIQUE KEY unique_user_question (usuario_id, pregunta_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- PASO 4: DATOS SEMILLA (SEEDERS)
-- =====================================================

-- Catálogo: Roles
-- =====================================================
INSERT INTO roles (id, nombre, descripcion) VALUES
(1, 'Administrador', 'Acceso total al sistema'),
(2, 'Tutor', 'Supervisión de pasantes'),
(3, 'Pasante', 'Usuario básico del sistema');

-- Catálogo: Departamentos ISP
-- =====================================================
INSERT INTO departamentos (id, nombre, descripcion) VALUES
(1, 'Soporte Técnico', 'Atención y soporte técnico a usuarios'),
(2, 'Redes y Telecomunicaciones', 'Gestión de infraestructura de red'),
(3, 'Atención al Usuario', 'Servicio al cliente y atención directa'),
(4, 'Reparaciones Electrónicas', 'Mantenimiento y reparación de equipos'),
(5, 'Sin Asignar', 'Departamento temporal para usuarios sin asignación');

-- Catálogo: Preguntas de Seguridad
-- =====================================================
INSERT INTO preguntas_seguridad (id, pregunta, activa) VALUES
(1, 'Cual es tu postre favorito?', 1),
(2, 'Cual es tu color favorito?', 1),
(3, 'Cual es el nombre de tu mascota?', 1);

-- =====================================================
-- USUARIOS DE PRUEBA (Clave: Sgp.123*)
-- =====================================================
-- Hash generado: password_hash('Sgp.123*', PASSWORD_BCRYPT)
-- =====================================================

-- Usuario 1: Administrador (Completo)
INSERT INTO usuarios (id, correo, password, rol_id, departamento_id, requiere_cambio_clave, estado) VALUES
(1, 'admin@sgp.local', '$2y$10$xwCcDMHXaKGfAOpi2puiaPfzia9NGBGghfyiq4c2', 1, NULL, 0, 'activo');

INSERT INTO datos_personales (usuario_id, cedula, nombres, apellidos, telefono, genero) VALUES
(1, '00000000', 'Administrador', 'del Sistema', '0000-0000000', 'M');

-- Usuario 2: Tutor (Completo, Dpto: Redes)
INSERT INTO usuarios (id, correo, password, rol_id, departamento_id, requiere_cambio_clave, estado) VALUES
(2, 'tutor@sgp.local', '$2y$10$xwCcDMHXaKGfAOpi2puiaPfzia9NGBGghfyiq4c2', 2, 2, 0, 'activo');

INSERT INTO datos_personales (usuario_id, cedula, nombres, apellidos, telefono, genero) VALUES
(2, '11111111', 'Carlos', 'Tutor', '0414-1234567', 'M');

-- Usuario 3: Pasante (Incompleto - Para probar flujo de onboarding)
INSERT INTO usuarios (id, correo, password, rol_id, departamento_id, requiere_cambio_clave, estado) VALUES
(3, 'pasante@sgp.local', '$2y$10$xwCcDMHXaKGfAOpi2puiaPfzia9NGBGghfyiq4c2', 3, NULL, 1, 'activo');

-- NOTA: El pasante NO tiene datos_personales ni departamento asignado
-- Esto permite probar el flujo completo de "La Jaula"

-- =====================================================
-- PASO 5: VERIFICACIÓN
-- =====================================================

SELECT 'Base de datos SGP v3.0 creada exitosamente' as mensaje,
       (SELECT COUNT(*) FROM roles) as total_roles,
       (SELECT COUNT(*) FROM departamentos) as total_departamentos,
       (SELECT COUNT(*) FROM preguntas_seguridad) as total_preguntas,
       (SELECT COUNT(*) FROM usuarios) as total_usuarios;

-- =====================================================
-- CREDENCIALES DE PRUEBA
-- =====================================================
-- Email: admin@sgp.local | Clave: Sgp.123* (Ya cambiada)
-- Email: tutor@sgp.local | Clave: Sgp.123* (Ya cambiada)
-- Email: pasante@sgp.local | Clave: Sgp.123* (DEBE cambiar)
-- =====================================================
