-- =====================================================
-- Migration: Add Cargo Field to datos_personales
-- Date: 2026-01-15
-- Description: Adds cargo (job title) field to personal data table
-- =====================================================

USE sgp_v1;

-- Add cargo column after apellidos
ALTER TABLE datos_personales 
ADD COLUMN cargo VARCHAR(100) NULL AFTER apellidos;

-- Verify the change
DESCRIBE datos_personales;

SELECT 'Migration completed: cargo field added successfully' AS status;
