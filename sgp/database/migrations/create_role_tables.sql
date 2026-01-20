-- =====================================================
-- SGP - Migration: Role-Specific Profile Tables
-- Version: 1.0 MVP
-- =====================================================

USE sgp_v1;

-- =====================================================
-- Table: datos_pasante
-- Description: Profile data for Liceo Técnico students
-- =====================================================

CREATE TABLE IF NOT EXISTS datos_pasante (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL UNIQUE,
    
    -- Liceo Técnico Information
    institucion_procedencia VARCHAR(150) NOT NULL COMMENT 'Nombre del Liceo',
    grado_anio VARCHAR(20) NOT NULL COMMENT 'Ej: 6to Año',
    mencion VARCHAR(50) DEFAULT 'Informática' COMMENT 'Mención del estudiante',
    periodo_pasantias VARCHAR(50) COMMENT 'Ej: 2024-2025',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Key
    CONSTRAINT fk_datos_pasante_usuario 
        FOREIGN KEY (usuario_id) 
        REFERENCES usuarios(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
        
    -- Indexes
    INDEX idx_institucion (institucion_procedencia),
    INDEX idx_periodo (periodo_pasantias)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Datos específicos de pasantes de Liceo Técnico';

-- =====================================================
-- Table: datos_tutor
-- Description: Profile data for tutors/staff (SIMPLIFIED)
-- =====================================================

CREATE TABLE IF NOT EXISTS datos_tutor (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL UNIQUE,
    
    -- Essential Staff Information
    departamento_id INT UNSIGNED NULL COMMENT 'Departamento asignado',
    cargo VARCHAR(100) NOT NULL COMMENT 'Ej: Coordinador, Supervisor',
    extension_telefonica VARCHAR(20) COMMENT 'Extensión interna',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_datos_tutor_usuario 
        FOREIGN KEY (usuario_id) 
        REFERENCES usuarios(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
        
    CONSTRAINT fk_datos_tutor_departamento 
        FOREIGN KEY (departamento_id) 
        REFERENCES departamentos(id) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE,
        
    -- Indexes
    INDEX idx_departamento (departamento_id),
    INDEX idx_cargo (cargo)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Datos específicos de tutores/personal';

-- =====================================================
-- Verification Queries
-- =====================================================

-- Show created tables
SHOW TABLES LIKE 'datos_%';

-- Show table structures
DESCRIBE datos_pasante;
DESCRIBE datos_tutor;

-- Verify foreign keys
SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'sgp_v1' 
  AND TABLE_NAME IN ('datos_pasante', 'datos_tutor')
  AND REFERENCED_TABLE_NAME IS NOT NULL;

SELECT 'Migration completed successfully' AS status;
