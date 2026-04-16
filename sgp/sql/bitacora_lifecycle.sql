-- ============================================================
-- SGP — Migración: Sistema de Ciclo de Vida de Bitácora
-- Ejecutar en phpMyAdmin sobre la base de datos `proyecto_sgp`
-- ============================================================

-- ------------------------------------------------------------
-- 1. ÍNDICES DE PERFORMANCE (críticos para filtros por fecha)
-- ------------------------------------------------------------
ALTER TABLE `bitacora`
    ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`),
    ADD INDEX IF NOT EXISTS `idx_accion`     (`accion`),
    ADD INDEX IF NOT EXISTS `idx_usuario_id` (`usuario_id`);

-- ------------------------------------------------------------
-- 2. TABLA DE HISTÓRICO (almacén frío de registros archivados)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bitacora_historico` (
    `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `bitacora_id`     INT UNSIGNED  NOT NULL COMMENT 'ID original en tabla bitacora',
    `usuario_id`      INT UNSIGNED  NULL,
    `accion`          VARCHAR(100)  NOT NULL,
    `tabla_afectada`  VARCHAR(100)  NULL,
    `registro_id`     INT UNSIGNED  NULL,
    `ip_address`      VARCHAR(45)   NOT NULL DEFAULT '',
    `user_agent`      TEXT          NULL,
    `detalles`        JSON          NULL,
    `created_at`      DATETIME      NOT NULL COMMENT 'Timestamp original del evento',
    `archivado_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Cuando fue archivado',
    PRIMARY KEY (`id`),
    KEY `idx_bitacora_id`  (`bitacora_id`),
    KEY `idx_created_at`   (`created_at`),
    KEY `idx_archivado_at` (`archivado_at`),
    KEY `idx_accion`       (`accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registros de bitácora archivados (almacén frío — sólo lectura)';

-- ------------------------------------------------------------
-- 3. STORED PROCEDURE DE PURGA CON ARCHIVADO SEGURO
--    Parámetros (días de retención por categoría):
--      p_dias_criticos  → LOGIN/LOGOUT/PASSWORD_CHANGE/CREATE_USER (default 365)
--      p_dias_operacion → resto de acciones (default 90)
--      p_ejecutado_por  → usuario_id del admin que dispara (0 = automático)
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS `spPurgarBitacora`;

DELIMITER $$

CREATE PROCEDURE `spPurgarBitacora`(
    IN p_dias_criticos  INT,
    IN p_dias_operacion INT,
    IN p_ejecutado_por  INT
)
BEGIN
    DECLARE v_archivados INT DEFAULT 0;
    DECLARE v_purgados   INT DEFAULT 0;

    -- Acciones de seguridad crítica (retención larga)
    SET @acciones_criticas = 'LOGIN,LOGOUT,PASSWORD_CHANGE,CREATE_USER,DELETE_USER,RESET_PASSWORD,AUDIT_PURGE';

    -- PASO 1: Archivar registros que superaron su período de retención
    INSERT INTO bitacora_historico
        (bitacora_id, usuario_id, accion, tabla_afectada, registro_id,
         ip_address, user_agent, detalles, created_at)
    SELECT b.id, b.usuario_id, b.accion, b.tabla_afectada, b.registro_id,
           b.ip_address, b.user_agent, b.detalles, b.created_at
    FROM bitacora b
    WHERE
        -- Acciones críticas: retención de N días largos
        (FIND_IN_SET(b.accion, @acciones_criticas) > 0
            AND b.created_at < DATE_SUB(NOW(), INTERVAL p_dias_criticos DAY))
        OR
        -- Acciones operacionales/rutina: retención corta
        (FIND_IN_SET(b.accion, @acciones_criticas) = 0
            AND b.created_at < DATE_SUB(NOW(), INTERVAL p_dias_operacion DAY));

    SET v_archivados = ROW_COUNT();

    -- PASO 2: Eliminar de bitacora activa los que ya fueron archivados
    DELETE b FROM bitacora b
    INNER JOIN bitacora_historico h ON h.bitacora_id = b.id;

    SET v_purgados = ROW_COUNT();

    -- PASO 3: Auto-registrar la purga ejecutada (trazabilidad del mantenimiento)
    IF v_archivados > 0 THEN
        INSERT INTO bitacora (usuario_id, accion, tabla_afectada, detalles, ip_address)
        VALUES (
            p_ejecutado_por,
            'AUDIT_PURGE',
            'bitacora',
            JSON_OBJECT(
                'dias_criticos',  p_dias_criticos,
                'dias_operacion', p_dias_operacion,
                'archivados',     v_archivados,
                'purgados',       v_purgados,
                'modo',           IF(p_ejecutado_por = 0, 'automatico', 'manual')
            ),
            'SISTEMA'
        );
    END IF;

    -- Retornar resumen
    SELECT v_archivados AS archivados, v_purgados AS purgados;
END$$

DELIMITER ;

-- ------------------------------------------------------------
-- 4. MYSQL EVENT — Purga automática semanal (domingos 2:00 AM)
--    PREREQUISITO: SET GLOBAL event_scheduler = ON;
--                  En my.ini: [mysqld] → event_scheduler=ON
-- ------------------------------------------------------------
DROP EVENT IF EXISTS `evt_purga_bitacora_semanal`;

CREATE EVENT `evt_purga_bitacora_semanal`
ON SCHEDULE EVERY 1 WEEK
STARTS (
    DATE_ADD(
        DATE_ADD(CURDATE(), INTERVAL (7 - WEEKDAY(CURDATE())) DAY),
        INTERVAL 2 HOUR
    )
)
ON COMPLETION PRESERVE
ENABLE
COMMENT 'Purga automática de bitácora: críticos 365d, operacionales 90d'
DO
    CALL spPurgarBitacora(365, 90, 0);

-- ------------------------------------------------------------
-- 5. Activar el scheduler de MySQL (si no está activo)
-- ------------------------------------------------------------
SET GLOBAL event_scheduler = ON;
