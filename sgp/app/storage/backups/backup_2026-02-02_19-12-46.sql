-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: proyecto_sgp
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `asignaciones`
--

DROP TABLE IF EXISTS `asignaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asignaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pasante_id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `departamento_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time NOT NULL,
  `horas_totales` int(11) DEFAULT 480,
  `horas_cumplidas` decimal(10,2) DEFAULT 0.00,
  `estado` enum('activo','finalizado','cancelado') DEFAULT 'activo',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_asignacion_departamento` (`departamento_id`),
  KEY `fk_asignacion_pasante_v2` (`pasante_id`),
  KEY `fk_asignacion_tutor_v2` (`tutor_id`),
  CONSTRAINT `fk_asignacion_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_asignacion_pasante_v2` FOREIGN KEY (`pasante_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_asignacion_tutor_v2` FOREIGN KEY (`tutor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignaciones`
--

LOCK TABLES `asignaciones` WRITE;
/*!40000 ALTER TABLE `asignaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `asignaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asistencias`
--

DROP TABLE IF EXISTS `asistencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asistencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asignacion_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time DEFAULT NULL,
  `horas_calculadas` decimal(10,2) DEFAULT 0.00,
  `observacion` text DEFAULT NULL,
  `estado` enum('abierto','cerrado') DEFAULT 'abierto',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_asistencia_asignacion` (`asignacion_id`),
  CONSTRAINT `fk_asistencia_asignacion` FOREIGN KEY (`asignacion_id`) REFERENCES `asignaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencias`
--

LOCK TABLES `asistencias` WRITE;
/*!40000 ALTER TABLE `asistencias` DISABLE KEYS */;
/*!40000 ALTER TABLE `asistencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bitacora`
--

DROP TABLE IF EXISTS `bitacora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bitacora` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL COMMENT 'Usuario que ejecuta la acción',
  `accion` varchar(100) NOT NULL COMMENT 'LOGIN, UPDATE_PROFILE, DELETE, FORMALIZE_INTERN, etc.',
  `tabla_afectada` varchar(50) DEFAULT NULL COMMENT 'Tabla modificada (opcional)',
  `registro_id` int(11) DEFAULT NULL COMMENT 'ID del registro afectado (opcional)',
  `ip_address` varchar(45) NOT NULL COMMENT 'IP del usuario',
  `user_agent` text DEFAULT NULL COMMENT 'Navegador/Dispositivo',
  `detalles` text DEFAULT NULL COMMENT 'JSON con datos adicionales',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_accion` (`accion`),
  KEY `idx_fecha` (`created_at`),
  KEY `idx_tabla` (`tabla_afectada`),
  CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de auditoría de todas las acciones críticas del sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--

LOCK TABLES `bitacora` WRITE;
/*!40000 ALTER TABLE `bitacora` DISABLE KEYS */;
INSERT INTO `bitacora` VALUES (1,1,'UPDATE_PROFILE','datos_personales',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-01-31 07:03:08'),(2,1,'UPDATE_PROFILE','datos_personales',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-01-31 07:04:10'),(3,1,'UPDATE_PROFILE','datos_personales',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-01-31 07:04:19'),(4,9,'UPDATE_PROFILE','datos_personales',9,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-02 01:58:52'),(5,9,'UPDATE_PROFILE','datos_personales',9,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-02 01:59:19'),(6,9,'UPDATE_PROFILE','datos_personales',9,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-02 02:01:14'),(7,9,'UPDATE_PROFILE','datos_personales',9,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-02 02:01:44'),(8,1,'UPDATE_SECURITY_QUESTIONS','usuarios_respuestas',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-02 02:06:02'),(9,9,'UPDATE_PROFILE','datos_personales',9,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-02 03:20:49'),(10,9,'UPDATE_PROFILE','datos_personales',9,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-02 03:30:18');
/*!40000 ALTER TABLE `bitacora` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `datos_pasante`
--

DROP TABLE IF EXISTS `datos_pasante`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datos_pasante` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `institucion_procedencia` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado_pasantia` enum('Pendiente','Activo','Finalizado','Retirado') DEFAULT 'Pendiente' COMMENT 'Estado actual de la pasantía',
  `fecha_inicio_pasantia` date DEFAULT NULL COMMENT 'Fecha de inicio formal de la pasantía',
  `fecha_fin_estimada` date DEFAULT NULL COMMENT 'Fecha estimada de finalización',
  `horas_acumuladas` int(11) DEFAULT 0 COMMENT 'Horas de pasantía cumplidas',
  `horas_meta` int(11) DEFAULT 240 COMMENT 'Horas totales requeridas',
  `departamento_asignado_id` int(11) DEFAULT NULL COMMENT 'Departamento asignado',
  `observaciones` text DEFAULT NULL COMMENT 'Notas adicionales',
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  KEY `departamento_asignado_id` (`departamento_asignado_id`),
  CONSTRAINT `fk_pasante_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datos_pasante`
--

LOCK TABLES `datos_pasante` WRITE;
/*!40000 ALTER TABLE `datos_pasante` DISABLE KEYS */;
INSERT INTO `datos_pasante` VALUES (1,9,'moral y luces','2026-02-02 03:30:18','2026-02-02 03:30:18','Pendiente',NULL,NULL,0,240,NULL,NULL);
/*!40000 ALTER TABLE `datos_pasante` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `datos_personales`
--

DROP TABLE IF EXISTS `datos_personales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datos_personales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `genero` enum('M','F','Otro') DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  UNIQUE KEY `cedula` (`cedula`),
  KEY `idx_cedula` (`cedula`),
  CONSTRAINT `fk_personales_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datos_personales`
--

LOCK TABLES `datos_personales` WRITE;
/*!40000 ALTER TABLE `datos_personales` DISABLE KEYS */;
INSERT INTO `datos_personales` VALUES (1,1,'12345678','Administrador','del Sistema','jefe de soporte tecnico','04146785679','los proceres','M','2026-01-15','2026-01-10 00:02:19','2026-01-31 07:04:19'),(2,2,'11111111','Carlos','Tutor',NULL,'0414-1234567',NULL,'M',NULL,'2026-01-10 00:02:19','2026-01-10 00:02:19'),(5,4,'30342975','jose luis','gomezlo',NULL,'04148965723','los proceres','M','2000-06-10','2026-01-10 05:27:05','2026-01-10 05:27:05'),(6,5,'0000000000','Yurimar','Del Carmen',NULL,'0000000000','Por definir','M','2000-01-01','2026-01-11 22:25:54','2026-01-11 22:25:54'),(8,8,'15020928','petra','prieto',NULL,'04241234567','los proceres','F','2014-01-06','2026-01-14 05:18:55','2026-01-14 18:17:25'),(9,9,'30342978','albert','rodriguez',NULL,'04148965723','la sabanita 2','M','2000-01-01','2026-01-17 12:25:12','2026-02-02 02:01:44'),(11,11,'15020929','Yarima','Del Carmen',NULL,NULL,NULL,NULL,NULL,'2026-01-30 23:31:18','2026-01-30 23:31:18'),(12,12,'20242114','maria','yepez',NULL,NULL,NULL,NULL,NULL,'2026-02-01 05:12:46','2026-02-01 05:12:46'),(13,13,'28708388','delmary','Nuevo',NULL,NULL,NULL,NULL,NULL,'2026-02-01 20:07:11','2026-02-01 20:07:11');
/*!40000 ALTER TABLE `datos_personales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departamentos`
--

DROP TABLE IF EXISTS `departamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departamentos`
--

LOCK TABLES `departamentos` WRITE;
/*!40000 ALTER TABLE `departamentos` DISABLE KEYS */;
INSERT INTO `departamentos` VALUES (1,'Soporte Técnico','Mantenimiento preventivo y correctivo de equipos',1,'2026-01-31 04:51:24'),(2,'Redes y Telecomunicaciones','Gestión de infraestructura de red y conectividad',1,'2026-01-31 04:51:24'),(3,'Atención al Usuario','Recepción de incidencias y soporte de primer nivel',1,'2026-01-31 04:51:24'),(4,'Reparaciones Electrónicas','Diagnóstico y reparación de hardware a nivel de componente',1,'2026-01-31 04:51:24');
/*!40000 ALTER TABLE `departamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'ID del usuario que recibe la notificación (admin)',
  `tipo` varchar(50) DEFAULT 'general' COMMENT 'Tipo: general, unlock_request, etc.',
  `mensaje` text NOT NULL COMMENT 'Mensaje de la notificación',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Datos adicionales en formato JSON' CHECK (json_valid(`metadata`)),
  `leida` tinyint(1) DEFAULT 0 COMMENT '0 = No leída, 1 = Leída',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de creación',
  PRIMARY KEY (`id`),
  KEY `idx_user_leida` (`user_id`,`leida`),
  KEY `idx_tipo_created` (`tipo`,`created_at`),
  KEY `idx_metadata_created` (`metadata`(100),`created_at`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
INSERT INTO `notificaciones` VALUES (1,1,'unlock_request','El usuario Juan Pérez (juan@example.com) ha solicitado un reseteo de cuenta por olvido de respuestas de seguridad.','{\"user_id\": 5, \"user_email\": \"juan@example.com\", \"user_name\": \"Juan Pérez\", \"request_type\": \"unlock_account\"}',1,'2026-01-30 06:03:50');
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preguntas_seguridad`
--

DROP TABLE IF EXISTS `preguntas_seguridad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `preguntas_seguridad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pregunta` text NOT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preguntas_seguridad`
--

LOCK TABLES `preguntas_seguridad` WRITE;
/*!40000 ALTER TABLE `preguntas_seguridad` DISABLE KEYS */;
INSERT INTO `preguntas_seguridad` VALUES (1,'Cual es tu postre favorito?',1,'2026-01-10 00:02:19'),(2,'Cual es tu color favorito?',1,'2026-01-10 00:02:19'),(3,'Cual es el nombre de tu mascota?',1,'2026-01-10 00:02:19'),(4,'¿Cuál es el nombre de tu padre?',1,'2026-01-30 05:02:15'),(5,'¿En qué ciudad se conocieron tus padres?',1,'2026-01-30 05:02:15'),(6,'¿Cuál fue el nombre de tu primera escuela?',1,'2026-01-30 05:02:15'),(7,'¿Cuál es el nombre de tu madre?',1,'2026-01-30 05:02:15'),(8,'¿Cuál es tu personaje histórico favorito?',1,'2026-01-30 05:02:15'),(9,'¿Cuál es la marca de tu primer vehículo?',1,'2026-01-30 05:02:15'),(10,'¿Cuál es tu comida favorita?',1,'2026-01-30 05:02:15');
/*!40000 ALTER TABLE `preguntas_seguridad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrador','Acceso total al sistema','2026-01-10 00:02:19'),(2,'Tutor','Supervisi??n de pasantes','2026-01-10 00:02:19'),(3,'Pasante','Usuario b??sico del sistema','2026-01-10 00:02:19');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `correo` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `rol_id` int(11) NOT NULL,
  `departamento_id` int(11) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default.png',
  `requiere_cambio_clave` tinyint(1) DEFAULT 1,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`),
  KEY `departamento_id` (`departamento_id`),
  KEY `idx_correo` (`correo`),
  KEY `idx_rol` (`rol_id`),
  KEY `idx_estado` (`estado`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'admin@sgp.local','$2y$10$X6FrcqzhY8agHsYPh9.wVO4Mga9FhsDXKgbX.4mXHwNhlh2rvM2yC',1,NULL,'default.png',0,'activo','2026-01-10 00:02:19','2026-01-11 23:06:29'),(2,'tutor@sgp.local','$2y$10$TxrLZz9BSpdfUFZSH127qu6h0c/GW83ujwnnA4DqEkEvMjyrHCw2S',2,NULL,'default.png',0,'activo','2026-01-10 00:02:19','2026-01-12 01:36:35'),(4,'luisgabrielyanez9@gmail.com','$2y$10$suzP73.i87XCpegPe8U4I.EKJMTziXRd5kWfFZVvP13HijTCJgnO2',3,NULL,'default.png',0,'activo','2026-01-10 05:18:37','2026-01-20 04:38:36'),(5,'yurimarprieto.79@gmail.com','$2y$10$Ga.8N.cmbEnxUIO08Jc1OupJT/HVvcJXI63.lEGk3G3JQcIk4PP76',3,NULL,'default.png',0,'inactivo','2026-01-11 22:25:54','2026-01-12 01:44:56'),(8,'petra.79@GMAIL.COM','$2y$10$jNMYV9MG7.k3dzR3ajRMEOO9iUgw7KKII1SjF9LGoD5Ngf0Vf8MiS',2,NULL,'default.png',1,'activo','2026-01-14 04:21:10','2026-02-02 16:36:22'),(9,'albert@gmail.com','$2y$10$xQg8s9puOmRpvfAqCkFWPOQKaYRTqKOdI/NYRGYCYEnKhWP5Dq4qu',3,NULL,'default.png',0,'activo','2026-01-17 12:25:11','2026-01-17 12:25:11'),(11,'yurima@sgp.local','$2y$10$QrTasd0TeXvkXbGpgKeROOvyn3WeHc6ejV6gSyLWig1NtsUW2AMwe',3,NULL,'default.png',0,'activo','2026-01-30 23:31:18','2026-01-30 23:31:18'),(12,'mariayepez@sgp.local','$2y$10$EgRnlUwlJIIcQXCDAQeYQOi/yj/OdntzLpoduHAO0/SjgUtliaIb.',3,NULL,'default.png',0,'activo','2026-02-01 05:12:46','2026-02-01 05:12:46'),(13,'delmaryguzman@gmail.com','$2y$10$34JGLe.CxtwtAM8SnMXyV.Zu/MVhj4lhP7B7wNZfs66aWBS3QojHu',1,NULL,'default.png',0,'activo','2026-02-01 20:07:11','2026-02-01 20:13:03'),(14,'ortiz9@gmail.com','$2y$10$xrm3mgtQVm1Nf74R0JnbQeHuDkI7h9o8UUZGlApKqekIc.mq3Vd7W',1,NULL,'default.png',1,'activo','2026-02-02 18:12:30','2026-02-02 18:12:30');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios_respuestas`
--

DROP TABLE IF EXISTS `usuarios_respuestas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios_respuestas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `respuesta_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_question` (`usuario_id`,`pregunta_id`),
  KEY `pregunta_id` (`pregunta_id`),
  CONSTRAINT `usuarios_respuestas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `usuarios_respuestas_ibfk_2` FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas_seguridad` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios_respuestas`
--

LOCK TABLES `usuarios_respuestas` WRITE;
/*!40000 ALTER TABLE `usuarios_respuestas` DISABLE KEYS */;
INSERT INTO `usuarios_respuestas` VALUES (1,4,3,'$2y$10$xFGprh.0upQAJelJoXCJ5uon7.EDrbGYq0Uo/Vr6j.ceQWGAN685C','2026-01-10 05:18:37'),(2,4,1,'$2y$10$S4LfO25gdPAkJ1cNmaWZDeR6WAh2CiQasUn0YeXB2JDjxAdbkDAHS','2026-01-10 05:18:37'),(3,4,2,'$2y$10$VwMPdeqg8JlvS9RijviRc.NQtAzUeBdc0VMMO9lJ6xuhLgG2AxCVK','2026-01-10 05:18:38'),(4,5,3,'$2y$10$7ry4F6QZdx1/LiAvzFbVNO4V580B9CH/1jcCdljMHOB4ccE8wviXm','2026-01-11 22:25:54'),(5,5,2,'$2y$10$Vbfvz0iOJAL9kC/XGpPFfOSrPdR9cy0VBx70.GPG73wToU9fBrdWa','2026-01-11 22:25:54'),(6,5,1,'$2y$10$eMlCZ9OXCBRpDRD43Jww1uawqEVym00oF2P0Sgtm1xKvAk5mh3vw6','2026-01-11 22:25:54'),(10,8,2,'$2y$10$BARKo9Q9ZdfxBI9aQmyYLOuH4LSMerqaqIBCcVH9FqSF3dE0vnLFW','2026-01-14 18:17:25'),(11,8,3,'$2y$10$0pkWdFRsD/F2qnc/OoNz3edi9cRVhdr0nYwmgKWf1X5NT8kAFa6rK','2026-01-14 18:17:25'),(12,8,1,'$2y$10$x9Z/l9wby.DxHztF5hxlweK/lgQioxWVzC4rU3PaTkdRLNnWGzqly','2026-01-14 18:17:25'),(13,9,2,'$2y$10$WMMtN/3kHpy0nyTQRp6rLO1ieDbVS1536XmicE.k/9S3vgYcKceOK','2026-01-17 12:25:12'),(14,9,1,'$2y$10$WZ.fpXuthYBKK8GXk2JCoOlpnb5BCTJFdOtIGADvrFDIKOjUFyOuK','2026-01-17 12:25:12'),(15,9,3,'$2y$10$KxQ2abWJ/ZvUelhXAvg5wuklX7tOB/iXrbjjYEHd/U4vvmAv3FNxi','2026-01-17 12:25:12'),(16,12,1,'$2y$10$30V8dqKNPN5F/PmWbT0JYe0vnJRW77ZV0mpwQQefHlpISL9b.tO5.','2026-02-01 05:12:46'),(17,12,10,'$2y$10$sx1/K4HvOgW9GK79DEqkTe/9fXzPpqyNoURhjFZIGXhHNYHLaxN.y','2026-02-01 05:12:46'),(18,12,9,'$2y$10$gpv0Fx12lCXNHT/keQSXROqbSYRKkd0dd8TpMwJ/3OHhpPiXZeRma','2026-02-01 05:12:46'),(19,13,1,'$2y$10$9brrF4Se5VyH.9GY0lfP2OvWOJjlJRsOc8mWq07l3TXhMDl.LyTSS','2026-02-01 20:07:11'),(20,13,2,'$2y$10$FmkTggeXhaNYp264VcBoxuf4ptNEi8kbT5a2dThX6/prUhP07EHu.','2026-02-01 20:07:11'),(21,13,3,'$2y$10$X5gAZqGOuqz59qjXSNmS3umZUp3Jx0XZ8ISj7.Bx6c0b1lAS/7mxW','2026-02-01 20:07:11'),(22,1,1,'$2y$10$8ThOERlFSNC4FEMkEr4LhOfQ24DO7ZfZX4nJ6291jlvK4atNjhyZi','2026-02-02 02:06:02'),(23,1,2,'$2y$10$AsXfeRp/18K3JHC7M9dZF.K2MbFP21M1rmfPyEb6bekcIn9AZEr7O','2026-02-02 02:06:02'),(24,1,10,'$2y$10$zsVPdu0Gs2/zzxdujwQ7OuSE0kF77Mrnu4fA8.N30xUfJFi07hF4q','2026-02-02 02:06:02');
/*!40000 ALTER TABLE `usuarios_respuestas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-02 14:12:46
