-- ================================================================
-- SGP — FASE 1: Migración de Base de Datos
-- Cohortes, Feriados y Representantes Institucionales
-- Versión: 1.0 | Fecha: 2026-03-31
-- Idempotente: seguro para ejecutar múltiples veces
-- ================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------
-- 1. TABLA: periodos_academicos
--    Gestión de cohortes para segmentar pasantes por año escolar
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `periodos_academicos` (
    `id`           int(11)      NOT NULL AUTO_INCREMENT,
    `nombre`       varchar(100) NOT NULL             COMMENT 'Ej: 2025-2026',
    `fecha_inicio` date         NOT NULL,
    `fecha_fin`    date         NOT NULL,
    `estado`       enum('activo','cerrado') NOT NULL DEFAULT 'activo',
    `descripcion`  text         DEFAULT NULL,
    `created_at`   timestamp    NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Cohortes / Periodos Académicos de Pasantía';

-- ----------------------------------------------------------------
-- 2. TABLA: dias_feriados
--    Días no laborables: nacionales, regionales e institucionales
--    El Demonio Auto-Fill los excluye al rellenar asistencias
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dias_feriados` (
    `id`                     int(11)      NOT NULL AUTO_INCREMENT,
    `fecha`                  date         NOT NULL,
    `nombre`                 varchar(150) NOT NULL,
    `tipo`                   enum('Nacional','Regional','Institucional') NOT NULL DEFAULT 'Nacional',
    `aplica_departamento_id` int(11)      DEFAULT NULL COMMENT 'NULL = aplica a todos los departamentos',
    `created_at`             timestamp    NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_feriado_fecha_tipo` (`fecha`, `tipo`),
    KEY `idx_fecha` (`fecha`),
    CONSTRAINT `fk_feriado_depto` FOREIGN KEY (`aplica_departamento_id`)
        REFERENCES `departamentos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Días no laborables. Usados por el Demonio Auto-Fill para no generar asistencias en estos días';

-- ----------------------------------------------------------------
-- 3. ALTER TABLE instituciones
--    Agregar datos del representante/tutor académico
--    El registro automático del pasante podrá pre-llenar estos datos
-- ----------------------------------------------------------------
ALTER TABLE `instituciones`
    ADD COLUMN IF NOT EXISTS `representante_nombre`   varchar(150) DEFAULT NULL AFTER `tipo`,
    ADD COLUMN IF NOT EXISTS `representante_cargo`    varchar(100) DEFAULT NULL AFTER `representante_nombre`,
    ADD COLUMN IF NOT EXISTS `representante_correo`   varchar(100) DEFAULT NULL AFTER `representante_cargo`,
    ADD COLUMN IF NOT EXISTS `representante_telefono` varchar(20)  DEFAULT NULL AFTER `representante_correo`;

-- ----------------------------------------------------------------
-- 4. ALTER TABLE datos_pasante
--    Vincular pasante a un periodo académico e institución formal
-- ----------------------------------------------------------------
ALTER TABLE `datos_pasante`
    ADD COLUMN IF NOT EXISTS `periodo_id`     int(11) DEFAULT NULL AFTER `id`,
    ADD COLUMN IF NOT EXISTS `institucion_id` int(11) DEFAULT NULL AFTER `institucion_procedencia`;

-- Índices para las nuevas columnas FK
CREATE INDEX IF NOT EXISTS `idx_dp_periodo`     ON `datos_pasante` (`periodo_id`);
CREATE INDEX IF NOT EXISTS `idx_dp_institucion` ON `datos_pasante` (`institucion_id`);

-- FK: datos_pasante → periodos_academicos (condicional)
SET @fk_periodo = (
    SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'datos_pasante'
      AND CONSTRAINT_NAME = 'fk_dp_periodo'
);
SET @sql_fk1 = IF(@fk_periodo = 0,
    'ALTER TABLE datos_pasante ADD CONSTRAINT fk_dp_periodo FOREIGN KEY (periodo_id) REFERENCES periodos_academicos(id) ON DELETE SET NULL',
    'SELECT "fk_dp_periodo ya existe — omitido" AS info'
);
PREPARE _s1 FROM @sql_fk1; EXECUTE _s1; DEALLOCATE PREPARE _s1;

-- FK: datos_pasante → instituciones (condicional)
SET @fk_inst = (
    SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'datos_pasante'
      AND CONSTRAINT_NAME = 'fk_dp_institucion'
);
SET @sql_fk2 = IF(@fk_inst = 0,
    'ALTER TABLE datos_pasante ADD CONSTRAINT fk_dp_institucion FOREIGN KEY (institucion_id) REFERENCES instituciones(id) ON DELETE SET NULL',
    'SELECT "fk_dp_institucion ya existe — omitido" AS info'
);
PREPARE _s2 FROM @sql_fk2; EXECUTE _s2; DEALLOCATE PREPARE _s2;

-- ----------------------------------------------------------------
-- 5. DATOS INICIALES: Periodo académico activo
-- ----------------------------------------------------------------
INSERT IGNORE INTO `periodos_academicos` (`nombre`, `fecha_inicio`, `fecha_fin`, `estado`, `descripcion`)
VALUES ('2025-2026', '2025-09-01', '2026-08-31', 'activo', 'Primer periodo académico del sistema SGP');

-- ----------------------------------------------------------------
-- 6. DATOS INICIALES: Feriados nacionales y regionales
--    Venezuela — Bolívar — ciclo 2025/2026
-- ----------------------------------------------------------------

-- Feriados 2025 (segundo semestre escolar)
INSERT IGNORE INTO `dias_feriados` (`fecha`, `nombre`, `tipo`) VALUES
    ('2025-10-12', 'Día de la Resistencia Indígena',              'Nacional'),
    ('2025-11-17', 'Aniversario de Ciudad Bolívar',               'Regional'),
    ('2025-12-08', 'Inmaculada Concepción',                       'Nacional'),
    ('2025-12-17', 'Muerte del Libertador Simón Bolívar',         'Nacional'),
    ('2025-12-24', 'Nochebuena',                                  'Nacional'),
    ('2025-12-25', 'Navidad',                                     'Nacional'),
    ('2025-12-31', 'Fin de Año',                                  'Nacional');

-- Feriados 2026 (primer semestre escolar)
INSERT IGNORE INTO `dias_feriados` (`fecha`, `nombre`, `tipo`) VALUES
    ('2026-01-01', 'Año Nuevo',                                   'Nacional'),
    ('2026-01-06', 'Día de Reyes',                                'Nacional'),
    ('2026-02-12', 'Día de la Juventud',                          'Nacional'),
    ('2026-02-16', 'Carnaval — Lunes',                            'Nacional'),
    ('2026-02-17', 'Carnaval — Martes',                           'Nacional'),
    ('2026-03-19', 'San José',                                    'Nacional'),
    ('2026-03-30', 'Lunes Santo — Semana Santa',                  'Nacional'),
    ('2026-03-31', 'Martes Santo — Semana Santa',                 'Nacional'),
    ('2026-04-01', 'Miércoles Santo — Semana Santa',              'Nacional'),
    ('2026-04-02', 'Jueves Santo — Semana Santa',                 'Nacional'),
    ('2026-04-03', 'Viernes Santo — Semana Santa',                'Nacional'),
    ('2026-04-19', 'Declaración de Independencia',                'Nacional'),
    ('2026-05-01', 'Día del Trabajador',                          'Nacional'),
    ('2026-06-24', 'Batalla de Carabobo / Día del Ejército',      'Nacional'),
    ('2026-07-05', 'Día de la Independencia',                     'Nacional'),
    ('2026-07-24', 'Natalicio del Libertador Simón Bolívar',      'Nacional'),
    ('2026-08-03', 'Aniversario fundación Ciudad Bolívar',        'Regional');

-- ----------------------------------------------------------------
-- 7. DATOS INICIALES: Universidades de Bolívar
-- ----------------------------------------------------------------
INSERT IGNORE INTO `instituciones` (`nombre`, `tipo`, `ubicacion`) VALUES
    ('Universidad de Oriente (UDO) - Núcleo Bolívar',                          'Universidad', 'Ciudad Bolívar, Bolívar'),
    ('Universidad Nacional Experimental de Guayana (UNEG)',                     'Universidad', 'Puerto Ordaz, Bolívar'),
    ('Instituto Universitario de Tecnología del Estado Bolívar (IUTEB)',        'Universidad', 'Ciudad Bolívar, Bolívar'),
    ('Universidad Gran Mariscal de Ayacucho (UGMA) - Sede Bolívar',            'Universidad', 'Ciudad Bolívar, Bolívar'),
    ('Instituto Universitario de Tecnología Rodolfo Loero Arismendi (IUTIRLA)','Universidad', 'Puerto Ordaz, Bolívar'),
    ('E.T.C. Felipe Guevara Rojas',                                             'Escuela Técnica', 'Ciudad Bolívar, Bolívar'),
    ('E.T.I. Andrés Eloy Blanco',                                              'Escuela Técnica', 'Ciudad Bolívar, Bolívar');

-- ----------------------------------------------------------------
-- 8. Vincular pasantes existentes al periodo activo
-- ----------------------------------------------------------------
UPDATE `datos_pasante`
SET `periodo_id` = (SELECT `id` FROM `periodos_academicos` WHERE `estado` = 'activo' LIMIT 1)
WHERE `periodo_id` IS NULL;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Migración Fase 1 completada exitosamente.' AS resultado;
