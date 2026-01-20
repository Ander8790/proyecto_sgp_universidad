DROP DATABASE IF EXISTS sgp_db;
CREATE DATABASE IF NOT EXISTS sgp_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sgp_db;

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT IGNORE INTO roles (id, name) VALUES 
(1, 'Administrador'),
(2, 'Tutor'),
(3, 'Pasante');

CREATE TABLE IF NOT EXISTS security_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT IGNORE INTO security_questions (id, question) VALUES 
(1, '¿Cuál es el nombre de tu primera mascota?'),
(2, '¿Cuál es tu postre favorito?'),
(3, '¿Cuál es tu color favorito?');

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_security_answers (
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_hash VARCHAR(255) NOT NULL,
    PRIMARY KEY (user_id, question_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES security_questions(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Default Admin (password: admin123)
INSERT IGNORE INTO users (role_id, email, password, name) VALUES 
(1, 'admin@sgp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Principal');
