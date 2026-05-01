-- ============================================================
-- MIGRATION: SuperAdmin + Sistema de Permisos Granulares
-- SGP | Ejecutar UNA SOLA VEZ en phpMyAdmin
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

-- PASO 1: Rol SuperAdmin
INSERT IGNORE INTO `roles` (`id`, `nombre`, `descripcion`, `created_at`)
VALUES (0, 'SuperAdministrador', 'Control total. Gestor de permisos de todos los usuarios.', NOW());

-- PASO 2: Usuario SuperAdmin (contraseña temporal: Sgp.99999999)
INSERT IGNORE INTO `usuarios`
  (`id`,`cedula`,`correo`,`password`,`rol_id`,`avatar`,`requiere_cambio_clave`,`estado`,`created_at`,`updated_at`)
VALUES (
  99,'99999999','superadmin@sgp.local',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  0,'default.png',1,'activo',NOW(),NOW()
);

-- PASO 3: Perfil datos_personales del SuperAdmin
INSERT IGNORE INTO `datos_personales` (`usuario_id`,`cedula`,`nombres`,`apellidos`)
VALUES (99,'99999999','Super','Administrador');

-- PASO 4: Respuestas de seguridad (respuestas: "sgp", "sistema", "azul")
INSERT IGNORE INTO `usuarios_respuestas` (`usuario_id`,`pregunta_id`,`respuesta_hash`,`created_at`) VALUES
(99,1,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW()),
(99,2,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW()),
(99,3,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW());

-- PASO 5: Tabla modulos_sistema
CREATE TABLE IF NOT EXISTS `modulos_sistema` (
  `id`        int(11) NOT NULL AUTO_INCREMENT,
  `clave`     varchar(80) NOT NULL,
  `nombre`    varchar(100) NOT NULL,
  `icono`     varchar(60) DEFAULT 'ti-circle',
  `ruta_base` varchar(100) DEFAULT NULL,
  `grupo`     varchar(60) DEFAULT 'General',
  `rol_base`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Admin 2=Tutor',
  `activo`    tinyint(1) DEFAULT 1,
  `orden`     int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `modulos_sistema` (`clave`,`nombre`,`icono`,`ruta_base`,`grupo`,`rol_base`,`orden`) VALUES
('gestion_usuarios',   'Gestión de Usuarios',   'ti-users',             '/users',        'Administración',1, 1),
('gestion_pasantes',   'Gestión de Pasantes',   'ti-user-check',        '/pasantes',     'Pasantías',     1, 2),
('asistencias',        'Asistencias',           'ti-calendar-check',    '/asistencias',  'Pasantías',     1, 3),
('asignaciones',       'Asignaciones',          'ti-arrows-transfer',   '/asignaciones', 'Pasantías',     1, 4),
('evaluaciones_admin', 'Evaluaciones',          'ti-clipboard-check',   '/evaluaciones', 'Pasantías',     1, 5),
('reportes',           'Reportes',              'ti-file-analytics',    '/reportes',     'Informes',      1, 6),
('analiticas',         'Analíticas',            'ti-chart-bar',         '/analiticas',   'Informes',      1, 7),
('backup',             'Respaldos BD',          'ti-database-export',   '/backup',       'Sistema',       1, 8),
('bitacora',           'Bitácora',              'ti-activity',          '/bitacora',     'Sistema',       1, 9),
('configuracion',      'Configuración',         'ti-settings',          '/configuracion','Sistema',       1,10),
('periodos',           'Períodos Académicos',   'ti-calendar',          '/periodos',     'Académico',     1,11),
('actividades_extras', 'Actividades Extras',    'ti-star',              '/actividades',  'Académico',     1,12),
('mis_pasantes',       'Mis Pasantes (Tutor)',  'ti-user-check',        '/tutor',        'Tutor',         2,13),
('evaluaciones_tutor', 'Evaluaciones (Tutor)',  'ti-clipboard-check',   '/evaluaciones', 'Tutor',         2,14);

-- PASO 6: Tabla acciones_modulo
CREATE TABLE IF NOT EXISTS `acciones_modulo` (
  `id`        int(11) NOT NULL AUTO_INCREMENT,
  `modulo_id` int(11) NOT NULL,
  `clave`     varchar(100) NOT NULL,
  `nombre`    varchar(100) NOT NULL,
  `tipo`      enum('ver','crear','editar','eliminar','exportar','configurar') DEFAULT 'ver',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_clave_accion` (`clave`),
  CONSTRAINT `fk_accion_modulo` FOREIGN KEY (`modulo_id`) REFERENCES `modulos_sistema`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `acciones_modulo` (`modulo_id`,`clave`,`nombre`,`tipo`) VALUES
(1,'ver_usuarios','Ver Usuarios','ver'),
(1,'crear_usuario','Crear Usuario','crear'),
(1,'editar_usuario','Editar Usuario','editar'),
(1,'desactivar_usuario','Desactivar Usuario','eliminar'),
(2,'ver_pasantes','Ver Pasantes','ver'),
(2,'editar_pasante','Editar Pasante','editar'),
(2,'exportar_pasantes','Exportar Lista','exportar'),
(3,'ver_asistencias','Ver Asistencias','ver'),
(3,'modificar_asistencia','Modificar Asistencia','editar'),
(3,'exportar_asistencias','Exportar Asistencias','exportar'),
(4,'ver_asignaciones','Ver Asignaciones','ver'),
(4,'crear_asignacion','Crear Asignación','crear'),
(4,'editar_asignacion','Editar Asignación','editar'),
(5,'ver_evaluaciones','Ver Evaluaciones','ver'),
(5,'exportar_evaluacion','Exportar PDF Evaluación','exportar'),
(6,'ver_reportes','Ver Reportes','ver'),
(6,'exportar_reporte','Exportar Reporte','exportar'),
(7,'ver_analiticas','Ver Analíticas','ver'),
(8,'ver_backup','Ver Backups','ver'),
(8,'crear_backup','Crear Backup','crear'),
(8,'descargar_backup','Descargar Backup','exportar'),
(9,'ver_bitacora','Ver Bitácora','ver'),
(9,'exportar_bitacora','Exportar Bitácora','exportar'),
(10,'ver_configuracion','Ver Configuración','ver'),
(10,'editar_configuracion','Editar Configuración','editar'),
(11,'ver_periodos','Ver Períodos','ver'),
(11,'crear_periodo','Crear Período','crear'),
(12,'ver_actividades','Ver Actividades','ver'),
(12,'crear_actividad','Crear Actividad','crear'),
(13,'ver_mis_pasantes','Ver Mis Pasantes','ver'),
(14,'registrar_evaluacion','Registrar Evaluación','crear'),
(14,'ver_mis_evaluaciones','Ver Mis Evaluaciones','ver');

-- PASO 7: Tabla permisos_rol (defaults por rol)
CREATE TABLE IF NOT EXISTS `permisos_rol` (
  `id`         int(11) NOT NULL AUTO_INCREMENT,
  `rol_id`     int(11) NOT NULL,
  `accion_id`  int(11) NOT NULL,
  `habilitado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rol_accion` (`rol_id`,`accion_id`),
  CONSTRAINT `fk_permiso_accion` FOREIGN KEY (`accion_id`) REFERENCES `acciones_modulo`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Defaults Admin (rol=1): todas las acciones de módulos rol_base=1
INSERT IGNORE INTO `permisos_rol` (`rol_id`,`accion_id`,`habilitado`)
SELECT 1, a.id, 1 FROM `acciones_modulo` a
JOIN `modulos_sistema` m ON a.modulo_id = m.id WHERE m.rol_base = 1;

-- Defaults Tutor (rol=2): acciones de módulos rol_base=2
INSERT IGNORE INTO `permisos_rol` (`rol_id`,`accion_id`,`habilitado`)
SELECT 2, a.id, 1 FROM `acciones_modulo` a
JOIN `modulos_sistema` m ON a.modulo_id = m.id WHERE m.rol_base = 2;

-- PASO 8: Tabla usuario_permisos (overrides individuales por usuario)
CREATE TABLE IF NOT EXISTS `usuario_permisos` (
  `id`          int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id`  int(11) NOT NULL,
  `accion_id`   int(11) NOT NULL,
  `habilitado`  tinyint(1) NOT NULL,
  `otorgado_por`int(11) NOT NULL COMMENT 'ID del SuperAdmin que configuró el permiso',
  `created_at`  timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at`  timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuario_accion` (`usuario_id`,`accion_id`),
  CONSTRAINT `fk_up_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_up_accion`  FOREIGN KEY (`accion_id`)  REFERENCES `acciones_modulo`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- PASO 9: FK faltantes (corrección de integridad referencial)
ALTER TABLE `evaluaciones`
  ADD CONSTRAINT `fk_evaluacion_tutor`
  FOREIGN KEY (`tutor_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE;

ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notificacion_usuario`
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE;

ALTER TABLE `asistencias`
  ADD CONSTRAINT `fk_asistencia_pasante`
  FOREIGN KEY (`pasante_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- Verificación rápida:
SELECT 'modulos_sistema' tabla, COUNT(*) total FROM modulos_sistema
UNION ALL SELECT 'acciones_modulo', COUNT(*) FROM acciones_modulo
UNION ALL SELECT 'permisos_rol',    COUNT(*) FROM permisos_rol
UNION ALL SELECT 'usuario_permisos',COUNT(*) FROM usuario_permisos
UNION ALL SELECT 'superadmin_user', COUNT(*) FROM usuarios WHERE rol_id=0;
