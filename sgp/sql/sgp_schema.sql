-- ============================================
-- BASE DE DATOS: SGP
-- Sistema de Gestión de Pasantes
-- Instituto de Salud Pública del Estado Bolívar
-- ============================================

CREATE DATABASE IF NOT EXISTS sgp
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE sgp;

-- =========================
-- TABLA: roles
-- =========================
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(150) NOT NULL
);

-- =========================
-- TABLA: users
-- =========================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_users_role
        FOREIGN KEY (role_id)
        REFERENCES roles(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- =========================
-- TABLA: security_questions
-- =========================
CREATE TABLE IF NOT EXISTS security_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL UNIQUE
);

-- =========================
-- TABLA: user_security_answers
-- =========================
CREATE TABLE IF NOT EXISTS user_security_answers (
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_hash VARCHAR(255) NOT NULL,

    PRIMARY KEY (user_id, question_id),

    CONSTRAINT fk_answers_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_answers_question
        FOREIGN KEY (question_id)
        REFERENCES security_questions(id)
        ON DELETE CASCADE
);

-- =========================
-- DATOS INICIALES
-- =========================

-- Roles del sistema
INSERT INTO roles (name, description) VALUES
('Administrador', 'Control total del sistema'),
('Tutor', 'Gestión de pasantes y asistencias'),
('Pasante', 'Visualización de información personal')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Preguntas de seguridad (predefinidas)
INSERT INTO security_questions (question) VALUES
('¿Cuál es el nombre de tu primera mascota?'),
('¿En qué ciudad naciste?'),
('¿Cuál es tu comida favorita?'),
('¿Nombre de tu escuela primaria?'),
('¿Cuál es tu color favorito?')
ON DUPLICATE KEY UPDATE question = VALUES(question);
