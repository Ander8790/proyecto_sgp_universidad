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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignaciones`
--

LOCK TABLES `asignaciones` WRITE;
/*!40000 ALTER TABLE `asignaciones` DISABLE KEYS */;
INSERT INTO `asignaciones` VALUES (1,4,8,1,'2026-01-15','2026-07-15','08:00:00','12:00:00',1440,120.00,'activo',NULL,'2026-03-15 16:20:39','2026-03-15 16:20:39'),(2,26,8,2,'2026-01-15','2026-07-15','08:00:00','12:00:00',1440,450.00,'activo',NULL,'2026-03-15 16:20:39','2026-03-15 16:20:39'),(3,29,2,3,'2026-01-15','2026-07-15','08:00:00','12:00:00',1440,1200.00,'activo',NULL,'2026-03-15 16:20:39','2026-03-15 16:20:39'),(4,12,2,4,'2026-01-15','2026-07-15','08:00:00','12:00:00',1440,50.00,'activo',NULL,'2026-03-15 16:20:39','2026-03-15 16:20:39');
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
  `pasante_id` int(11) DEFAULT NULL,
  `asignacion_id` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora_registro` time DEFAULT NULL,
  `metodo` enum('Kiosco','Manual') DEFAULT 'Kiosco',
  `motivo_justificacion` text DEFAULT NULL,
  `ruta_evidencia` varchar(255) DEFAULT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `horas_calculadas` decimal(10,2) DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  `estado` enum('abierto','cerrado','Presente','Justificado','Ausente') DEFAULT 'Presente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_asistencia_asignacion` (`asignacion_id`),
  KEY `idx_asistencias_fecha` (`fecha`),
  KEY `idx_asistencias_pasante` (`pasante_id`),
  KEY `idx_asistencias_estado` (`estado`),
  KEY `idx_asis_fecha` (`fecha`),
  KEY `idx_asis_pasante_fecha` (`pasante_id`,`fecha`),
  KEY `idx_asis_estado` (`estado`),
  KEY `idx_asis_pasante_estado` (`pasante_id`,`estado`),
  CONSTRAINT `fk_asistencia_asignacion` FOREIGN KEY (`asignacion_id`) REFERENCES `asignaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencias`
--

LOCK TABLES `asistencias` WRITE;
/*!40000 ALTER TABLE `asistencias` DISABLE KEYS */;
INSERT INTO `asistencias` VALUES (1,4,1,'2026-03-02',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:11:45','2026-03-15 16:20:39'),(2,4,1,'2026-03-03',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:11:45','2026-03-15 16:20:39'),(3,4,1,'2026-03-04',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:11:45','2026-03-15 16:20:39'),(4,4,1,'2026-03-05',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:11:45','2026-03-15 16:20:39'),(5,26,2,'2026-03-02',NULL,'Kiosco',NULL,NULL,'08:05:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:11:45','2026-03-15 16:20:39'),(6,26,2,'2026-03-03',NULL,'Manual',NULL,NULL,NULL,NULL,0.00,NULL,'Ausente','2026-03-15 16:11:45','2026-03-15 16:20:39'),(7,26,2,'2026-03-04',NULL,'Manual','Reposo médico sellado por el IVSS',NULL,NULL,NULL,0.00,NULL,'Justificado','2026-03-15 16:11:45','2026-03-15 16:20:39'),(8,29,3,'2026-03-02',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:11:45','2026-03-15 16:20:39'),(9,29,3,'2026-03-03',NULL,'Manual',NULL,NULL,NULL,NULL,0.00,NULL,'Ausente','2026-03-15 16:11:45','2026-03-15 16:20:39'),(10,29,3,'2026-03-04',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:11:45','2026-03-15 16:20:39'),(11,4,1,'2026-03-15',NULL,'Kiosco',NULL,NULL,'08:00:00',NULL,0.00,NULL,'Presente','2026-03-15 16:15:25','2026-03-15 16:20:39'),(13,29,3,'2026-03-15',NULL,'Kiosco',NULL,NULL,'08:45:00',NULL,0.00,NULL,'Presente','2026-03-15 16:15:25','2026-03-15 16:20:39'),(14,4,1,'2026-03-09',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(15,26,2,'2026-03-09',NULL,'Kiosco',NULL,NULL,'08:15:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(16,29,3,'2026-03-09',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(17,12,4,'2026-03-09',NULL,'Manual',NULL,NULL,NULL,NULL,0.00,NULL,'Ausente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(18,4,1,'2026-03-10',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(19,26,2,'2026-03-10',NULL,'Manual',NULL,NULL,NULL,NULL,0.00,NULL,'Justificado','2026-03-15 16:17:42','2026-03-15 16:20:39'),(20,29,3,'2026-03-10',NULL,'Kiosco',NULL,NULL,'08:05:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(21,12,4,'2026-03-10',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(22,4,1,'2026-03-11',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(23,26,2,'2026-03-11',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(24,29,3,'2026-03-11',NULL,'Manual',NULL,NULL,NULL,NULL,0.00,NULL,'Ausente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(25,12,4,'2026-03-11',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(26,4,1,'2026-03-12',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(27,26,2,'2026-03-12',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(28,29,3,'2026-03-12',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(29,12,4,'2026-03-12',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(30,4,1,'2026-03-13',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(31,26,2,'2026-03-13',NULL,'Kiosco',NULL,NULL,'08:30:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(32,29,3,'2026-03-13',NULL,'Kiosco',NULL,NULL,'08:00:00','12:00:00',4.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(33,12,4,'2026-03-13',NULL,'Manual',NULL,NULL,NULL,NULL,0.00,NULL,'Justificado','2026-03-15 16:17:42','2026-03-15 16:20:39'),(34,12,4,'2026-03-15',NULL,'Kiosco',NULL,NULL,'08:00:00',NULL,0.00,NULL,'Presente','2026-03-15 16:17:42','2026-03-15 16:20:39'),(35,4,NULL,'2026-03-16','13:19:21','Kiosco',NULL,NULL,NULL,NULL,NULL,NULL,'Presente','2026-03-16 17:19:21','2026-03-16 17:19:21'),(36,9,NULL,'2026-03-16','16:08:14','Manual',NULL,NULL,NULL,NULL,NULL,NULL,'Presente','2026-03-16 20:08:14','2026-03-16 20:08:14'),(37,26,NULL,'2026-03-16','16:08:23','Manual',NULL,NULL,NULL,NULL,NULL,NULL,'Presente','2026-03-16 20:08:23','2026-03-16 20:08:23'),(38,30,NULL,'2026-03-16','16:08:29','Manual',NULL,NULL,NULL,NULL,NULL,NULL,'Presente','2026-03-16 20:08:29','2026-03-16 20:08:29');
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
) ENGINE=InnoDB AUTO_INCREMENT=533 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de auditoría de todas las acciones críticas del sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--

LOCK TABLES `bitacora` WRITE;
/*!40000 ALTER TABLE `bitacora` DISABLE KEYS */;
INSERT INTO `bitacora` VALUES (491,1,'TOGGLE_USER_STATUS','usuarios',5,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"nuevo_estado\":\"activo\"}','2026-03-15 16:30:28'),(492,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 16:48:15'),(493,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 16:49:42'),(495,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 17:39:33'),(496,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 18:08:22'),(497,31,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 18:08:48'),(498,31,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 18:14:13'),(499,32,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 18:14:54'),(500,32,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 18:55:51'),(501,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 18:56:19'),(502,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 19:07:45'),(503,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 19:09:14'),(504,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 19:50:31'),(505,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 20:10:05'),(506,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 20:20:30'),(507,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 20:25:54'),(508,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 21:59:38'),(509,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 23:10:21'),(510,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-15 23:38:11'),(511,31,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-16 00:29:49'),(512,31,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-16 00:56:19'),(513,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-16 01:06:07'),(514,1,'RESET_PASSWORD','usuarios',31,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"motivo\":\"Solicitud admin\"}','2026-03-16 01:07:09'),(515,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-16 01:07:20'),(516,31,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-16 01:08:08'),(517,31,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-16 01:20:57'),(518,31,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-16 01:48:08'),(519,31,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-16 01:58:19'),(520,31,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-16 01:58:31'),(521,31,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-16 02:15:43'),(522,31,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-03-16 02:23:32'),(523,1,'LOGIN',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0',NULL,'2026-03-16 16:48:15'),(525,1,'LOGIN',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0',NULL,'2026-03-16 17:16:38'),(526,1,'LOGOUT',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0',NULL,'2026-03-16 17:16:42'),(527,1,'LOGIN',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0',NULL,'2026-03-16 17:17:54'),(528,1,'RESET_PIN','Se reseteó el PIN del pasante ID: 4',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0',NULL,'2026-03-16 17:19:03'),(529,1,'LOGOUT',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0',NULL,'2026-03-16 17:24:21'),(530,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36',NULL,'2026-03-16 20:04:03'),(531,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36',NULL,'2026-03-16 20:48:33'),(532,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36',NULL,'2026-03-16 20:51:59');
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
  `horas_meta` int(11) DEFAULT 1440 COMMENT 'Horas totales requeridas',
  `departamento_asignado_id` int(11) DEFAULT NULL COMMENT 'Departamento asignado',
  `tutor_id` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL COMMENT 'Notas adicionales',
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  KEY `departamento_asignado_id` (`departamento_asignado_id`),
  KEY `fk_dp_tutor` (`tutor_id`),
  KEY `idx_datos_pasante_estado` (`estado_pasantia`),
  KEY `idx_datos_pasante_fecha_fin` (`fecha_fin_estimada`),
  KEY `idx_dp_estado_pasantia` (`estado_pasantia`),
  KEY `idx_dp_usuario_id` (`usuario_id`),
  KEY `idx_dp_depto_asignado` (`departamento_asignado_id`),
  CONSTRAINT `fk_dp_tutor` FOREIGN KEY (`tutor_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pasante_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datos_pasante`
--

LOCK TABLES `datos_pasante` WRITE;
/*!40000 ALTER TABLE `datos_pasante` DISABLE KEYS */;
INSERT INTO `datos_pasante` VALUES (21,4,'1','2026-03-15 16:29:12','2026-03-15 18:00:22','Activo','2026-03-02',NULL,120,1440,1,8,NULL),(22,26,'2','2026-03-15 16:29:12','2026-03-15 18:00:22','Activo','2026-03-02',NULL,450,1440,2,8,NULL),(23,29,'2','2026-03-15 16:29:12','2026-03-16 20:09:43','Finalizado','2026-03-02',NULL,1200,1440,3,2,NULL),(24,12,'2','2026-03-15 16:29:12','2026-03-15 18:00:22','Activo','2026-03-09',NULL,50,1440,4,2,NULL),(25,5,'Sin especificar','2026-03-15 16:35:27','2026-03-15 16:35:27','Retirado','2025-09-01',NULL,0,1440,NULL,NULL,NULL),(26,9,'Sin especificar','2026-03-15 16:35:27','2026-03-15 18:00:22','Activo','2026-01-13',NULL,0,1440,1,2,NULL),(27,11,'Sin especificar','2026-03-15 16:35:27','2026-03-15 18:00:22','Activo','2026-01-13',NULL,0,1440,2,2,NULL),(28,15,'Sin especificar','2026-03-15 16:35:27','2026-03-15 18:00:22','Activo','2026-01-13',NULL,0,1440,2,2,NULL),(29,17,'Sin especificar','2026-03-15 16:35:27','2026-03-15 18:00:22','Activo','2026-01-13',NULL,0,1440,3,2,NULL),(30,23,'Sin especificar','2026-03-15 16:35:27','2026-03-15 18:00:22','Activo','2026-01-13',NULL,0,1440,3,2,NULL),(31,30,'Sin especificar','2026-03-15 16:35:27','2026-03-15 18:00:22','Activo','2026-03-02',NULL,0,1440,1,2,NULL);
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
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `genero` enum('M','F','Otro') DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  KEY `idx_dp_usuario_id` (`usuario_id`),
  CONSTRAINT `fk_personales_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datos_personales`
--

LOCK TABLES `datos_personales` WRITE;
/*!40000 ALTER TABLE `datos_personales` DISABLE KEYS */;
INSERT INTO `datos_personales` VALUES (38,4,'José Luis','Gómez',NULL,NULL,'M',NULL,'2026-03-15 16:29:12','2026-03-15 16:29:12'),(39,26,'Isabel','Gutiérrez',NULL,NULL,'F',NULL,'2026-03-15 16:29:12','2026-03-15 16:29:12'),(40,29,'Wilfredo','Rivas',NULL,NULL,'M',NULL,'2026-03-15 16:29:12','2026-03-15 16:29:12'),(41,12,'María','Yépez',NULL,NULL,'F',NULL,'2026-03-15 16:29:12','2026-03-15 16:29:12'),(42,5,'Yurimar','Prieto',NULL,NULL,NULL,NULL,'2026-03-15 16:35:27','2026-03-15 16:35:27'),(43,9,'Albert','González',NULL,NULL,NULL,NULL,'2026-03-15 16:35:27','2026-03-15 16:35:27'),(44,11,'Yurima','Rodríguez',NULL,NULL,NULL,NULL,'2026-03-15 16:35:27','2026-03-15 16:35:27'),(45,15,'Albert','Ramírez',NULL,NULL,NULL,NULL,'2026-03-15 16:35:27','2026-03-15 16:35:27'),(46,17,'Gabriel','Pérez',NULL,NULL,NULL,NULL,'2026-03-15 16:35:27','2026-03-15 16:35:27'),(47,23,'Yarimar','Salazar',NULL,NULL,NULL,NULL,'2026-03-15 16:35:27','2026-03-15 16:35:27'),(48,30,'José','Lozada',NULL,NULL,NULL,NULL,'2026-03-15 16:35:27','2026-03-15 16:35:27'),(49,1,'Administrador','del Sistema','Jefe de Sistema','0414-6785600','M','2012-02-19','2026-03-15 17:39:15','2026-03-15 17:39:15'),(50,2,'Carlos','Medina','Tutor de Pasantías',NULL,NULL,NULL,'2026-03-15 18:00:22','2026-03-15 18:00:22'),(51,8,'Petra','González','Tutor de Pasantías',NULL,NULL,NULL,'2026-03-15 18:00:22','2026-03-15 18:00:22'),(52,31,'Tutor','Atención','asistente','0414-8965888','F','2000-01-27','2026-03-15 18:07:23','2026-03-16 01:10:18'),(53,32,'Tutor','Reparaciones',NULL,'0414-8965757','F','2005-03-25','2026-03-15 18:07:23','2026-03-15 18:43:29');
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
INSERT INTO `departamentos` VALUES (1,'Soporte Técnico','Mantenimiento de equipos y atención nivel 1',1,'2026-03-15 15:55:00'),(2,'Redes y Telecomunicaciones','Infraestructura de red y servidores',1,'2026-03-15 15:55:00'),(3,'Atención al Usuario','Recepción de incidencias',1,'2026-03-15 15:55:00'),(4,'Reparaciones Electrónicas','Laboratorio de hardware',1,'2026-03-15 15:55:00');
/*!40000 ALTER TABLE `departamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evaluaciones`
--

DROP TABLE IF EXISTS `evaluaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pasante_id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `fecha_evaluacion` date NOT NULL,
  `lapso_academico` varchar(100) DEFAULT NULL,
  `criterio_iniciativa` int(11) NOT NULL,
  `criterio_interes` int(11) NOT NULL,
  `criterio_conocimiento` int(11) NOT NULL,
  `criterio_analisis` int(11) NOT NULL,
  `criterio_comunicacion` int(11) NOT NULL,
  `criterio_aprendizaje` int(11) NOT NULL,
  `criterio_companerismo` int(11) NOT NULL,
  `criterio_cooperacion` int(11) NOT NULL,
  `criterio_puntualidad` int(11) NOT NULL,
  `criterio_presentacion` int(11) NOT NULL,
  `criterio_desarrollo` int(11) NOT NULL,
  `criterio_analisis_res` int(11) NOT NULL,
  `criterio_conclusiones` int(11) NOT NULL,
  `criterio_recomendacion` int(11) NOT NULL,
  `promedio_final` decimal(4,2) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pasante_id` (`pasante_id`),
  KEY `idx_eval_pasante_id` (`pasante_id`),
  CONSTRAINT `evaluaciones_ibfk_1` FOREIGN KEY (`pasante_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evaluaciones`
--

LOCK TABLES `evaluaciones` WRITE;
/*!40000 ALTER TABLE `evaluaciones` DISABLE KEYS */;
INSERT INTO `evaluaciones` VALUES (1,9,31,'2026-01-01',NULL,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5.00,NULL,'2026-03-16 20:36:47');
/*!40000 ALTER TABLE `evaluaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instituciones`
--

DROP TABLE IF EXISTS `instituciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `instituciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `ubicacion` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo` enum('Escuela Técnica','Universidad') DEFAULT 'Escuela Técnica',
  `direccion` text DEFAULT NULL,
  `representante_academico` varchar(150) DEFAULT NULL COMMENT 'Profesor encargado de los pasantes de esta institución',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instituciones`
--

LOCK TABLES `instituciones` WRITE;
/*!40000 ALTER TABLE `instituciones` DISABLE KEYS */;
INSERT INTO `instituciones` VALUES (1,'E.T.C Juan Bautista González',NULL,'2026-03-15 15:55:00','',NULL,NULL),(2,'Colegio Fe y Alegría \"José María Vélaz\"',NULL,'2026-03-15 15:55:00','',NULL,NULL),(3,'E.T. Luis María Olaso',NULL,'2026-03-15 15:55:00','',NULL,NULL);
/*!40000 ALTER TABLE `instituciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intentos_acceso`
--

DROP TABLE IF EXISTS `intentos_acceso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `intentos_acceso` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `direccion_ip` varchar(45) DEFAULT NULL,
  `correo` varchar(255) NOT NULL,
  `intentos` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `bloqueado_hasta` datetime DEFAULT NULL,
  `ultimo_intento` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ip_email` (`direccion_ip`,`correo`),
  KEY `idx_blocked` (`bloqueado_hasta`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intentos_acceso`
--

LOCK TABLES `intentos_acceso` WRITE;
/*!40000 ALTER TABLE `intentos_acceso` DISABLE KEYS */;
INSERT INTO `intentos_acceso` VALUES (6,'::1','tutor.reparacion@sgp.local',1,NULL,'2026-03-15 21:04:50');
/*!40000 ALTER TABLE `intentos_acceso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_attempts` (
  `id` int(10) unsigned NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `blocked_until` datetime DEFAULT NULL,
  `last_attempt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ip_email` (`ip_address`,`email`),
  KEY `idx_blocked` (`blocked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL DEFAULT 'info',
  `titulo` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `url` varchar(500) DEFAULT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_usuario_leida` (`usuario_id`,`leida`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
INSERT INTO `notificaciones` VALUES (1,1,'success','¡Sistema de Notificaciones Activado!','El sistema de notificaciones ha sido configurado correctamente. Ahora recibirás actualizaciones importantes del sistema.',NULL,1,'2026-02-08 16:44:55'),(2,1,'info','Bienvenido al SGP','Gracias por usar el Sistema de Gestión de Pasantes. Explora todas las funcionalidades disponibles.','/dashboard',1,'2026-02-08 16:44:55'),(3,1,'warning','Actualiza tu perfil','Completa tu información de perfil para aprovechar todas las funcionalidades del sistema.','/perfil/ver',1,'2026-02-08 16:44:55'),(4,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',1,'2026-02-28 04:31:31'),(10,8,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',0,'2026-02-28 04:31:31'),(11,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',1,'2026-02-28 04:31:57'),(17,8,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',0,'2026-02-28 04:31:57'),(18,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',1,'2026-02-28 04:35:22'),(24,8,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',0,'2026-02-28 04:35:22'),(25,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',1,'2026-02-28 04:35:47'),(31,8,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',0,'2026-02-28 04:35:47'),(32,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',1,'2026-02-28 04:39:21'),(38,8,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',0,'2026-02-28 04:39:21'),(39,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',1,'2026-02-28 04:39:27'),(45,8,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',0,'2026-02-28 04:39:27'),(46,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',1,'2026-02-28 04:40:03'),(52,8,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',0,'2026-02-28 04:40:03'),(53,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',1,'2026-02-28 04:41:14'),(59,8,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',0,'2026-02-28 04:41:14'),(60,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',1,'2026-02-28 04:49:49'),(66,8,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',0,'2026-02-28 04:49:49'),(67,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',1,'2026-02-28 04:52:49'),(73,8,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',0,'2026-02-28 04:52:49'),(74,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',1,'2026-02-28 04:57:48'),(80,8,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',0,'2026-02-28 04:57:48'),(81,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',1,'2026-02-28 16:56:10'),(87,8,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',0,'2026-02-28 16:56:10'),(88,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',1,'2026-02-28 17:04:41'),(94,8,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',0,'2026-02-28 17:04:41'),(95,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',1,'2026-03-02 02:03:10'),(101,8,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/pasantes',0,'2026-03-02 02:03:10'),(102,9,'cambio_estado','Actualización de Estado','El estado de tu pasantía ha cambiado a: Pendiente.','http://localhost/proyecto_sgp/sgp/public/perfil',0,'2026-03-02 03:14:03'),(103,9,'cambio_estado','Actualización de Estado','El estado de tu pasantía ha cambiado a: Activo.','http://localhost/proyecto_sgp/sgp/public/perfil',0,'2026-03-02 03:14:09'),(104,15,'cambio_estado','Actualización de Estado','El estado de tu pasantía ha cambiado a: Activo.','http://localhost/proyecto_sgp/sgp/public/perfil',0,'2026-03-02 03:17:10'),(105,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin',1,'2026-03-02 12:45:08'),(111,8,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin',0,'2026-03-02 12:45:08'),(112,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin',1,'2026-03-02 13:15:24'),(118,8,'solicitud_pin','Solicitud de reseteo de PIN','El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin',0,'2026-03-02 13:15:24'),(119,12,'asignacion_nueva','Nueva Asignación de Pasantía','Has sido asignado a Atención al Usuario. Fecha de inicio: 2026-03-02.','http://localhost/proyecto_sgp/sgp/public/perfil',0,'2026-03-02 13:36:47'),(120,29,'cambio_estado','Actualización de Estado','El estado de tu pasantía ha cambiado a: Activo.','http://localhost/proyecto_sgp/sgp/public/perfil',0,'2026-03-02 13:48:40'),(121,1,'solicitud_pin','Solicitud de reseteo de PIN','El pasante wilfredo rivas (V-30587335) ha olvidado su PIN y solicita un reseteo.','http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin',1,'2026-03-02 13:50:23'),(127,12,'asignacion_nueva','Nueva Asignación de Pasantía','Has sido asignado a Atención al Usuario. Fecha de inicio: 2026-03-03.','http://localhost/proyecto_sgp/sgp/public/perfil',0,'2026-03-03 01:12:03'),(128,12,'cambio_estado','Actualización de Estado','El estado de tu pasantía ha cambiado a: Activo.','http://localhost/proyecto_sgp/sgp/public/perfil',0,'2026-03-03 01:13:16'),(129,30,'asignacion_nueva','Nueva Asignación de Pasantía','Has sido asignado a Atención al Usuario. Fecha de inicio: 10/03/2026.','http://localhost/proyecto_sgp/sgp/public/perfil',0,'2026-03-10 01:25:21'),(130,30,'asignacion_nueva','Nueva Asignación de Pasantía','Has sido asignado a Soporte Técnico. Fecha de inicio: 10/03/2026.','http://localhost/proyecto_sgp/sgp/public/perfil',0,'2026-03-10 02:07:27'),(131,17,'asignacion_nueva','Nueva Asignación de Pasantía','Has sido asignado a Redes y Telecomunicaciones. Fecha de inicio: 03/02/2026.','http://localhost/proyecto_sgp/sgp/public/perfil',0,'2026-03-10 15:42:35'),(132,5,'asignacion_nueva','Nueva Asignación de Pasantía','Has sido asignado a Atención al Usuario. Fecha de inicio: 12/02/2026.','http://localhost/proyecto_sgp/sgp/public/perfil',0,'2026-03-10 16:44:57'),(133,5,'asignacion_nueva','Nueva Asignación de Pasantía','Has sido asignado a Atención al Usuario. Fecha de inicio: 01/01/2026.','http://localhost/proyecto_sgp/sgp/public/perfil',0,'2026-03-12 15:31:27'),(134,9,'evaluacion_nueva','Nueva Evaluación','Tutor Atención ha registrado tu evaluación con un promedio de 5/5.','http://localhost/proyecto_sgp/sgp/public/perfil',0,'2026-03-16 20:36:47');
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
INSERT INTO `roles` VALUES (1,'Administrador','Acceso total al sistema','2026-01-10 00:02:19'),(2,'Tutor','Supervisor de pasantes','2026-01-10 00:02:19'),(3,'Pasante','Usuario b??sico del sistema','2026-01-10 00:02:19');
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
  `cedula` varchar(20) DEFAULT NULL,
  `correo` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `pin_asistencia` varchar(255) DEFAULT NULL,
  `rol_id` int(11) NOT NULL,
  `departamento_id` int(11) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default.png',
  `requiere_cambio_clave` tinyint(1) DEFAULT 1,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`),
  UNIQUE KEY `cedula` (`cedula`),
  KEY `departamento_id` (`departamento_id`),
  KEY `idx_correo` (`correo`),
  KEY `idx_rol` (`rol_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_usuarios_rol_estado` (`rol_id`,`estado`),
  KEY `idx_usr_rol_id` (`rol_id`),
  KEY `idx_usr_rol_estado` (`rol_id`,`estado`),
  CONSTRAINT `fk_usuario_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'12345678','admin@sgp.local','$2y$10$ricUBjOy9yUvAlU3NOzwz.N9Rdx5HxCPhRKIs37oLFPa561Esyvtu',NULL,1,1,'default.png',0,'activo','2026-01-10 00:02:19','2026-03-04 17:36:17'),(2,'30342971','tutor@sgp.local','$2y$10$TxrLZz9BSpdfUFZSH127qu6h0c/GW83ujwnnA4DqEkEvMjyrHCw2S',NULL,2,1,'default.png',0,'activo','2026-01-10 00:02:19','2026-03-15 18:07:23'),(4,'30342975','luisgabrielyanez9@gmail.com','$2y$10$mti9s2fD8FXPSDJxiCULH.zVtl.pI6r4FUFnuYhgV1JuN7v7hTQLi','$2y$10$pDcfgtwn9Br.HYTjz6X3ie3eMhzvFvaWuHg7uBjfYnofQBbNj2MPe',3,1,'default.png',0,'activo','2026-01-10 05:18:37','2026-03-16 17:19:03'),(5,'30342972','yurimarprieto.79@gmail.com','$2y$10$Ga.8N.cmbEnxUIO08Jc1OupJT/HVvcJXI63.lEGk3G3JQcIk4PP76',NULL,3,NULL,'default.png',0,'activo','2026-01-11 22:25:54','2026-03-15 16:30:28'),(8,'30342973','petra.79@GMAIL.COM','$2y$10$x3mj9u/wybL.KxEGbcy9reLgLq0eDbAktp2Icso7DT4lDnJ5dUspO',NULL,2,2,'default.png',0,'activo','2026-01-14 04:21:10','2026-03-15 18:07:23'),(9,'30342974','albert@gmail.com','$2y$10$xQg8s9puOmRpvfAqCkFWPOQKaYRTqKOdI/NYRGYCYEnKhWP5Dq4qu',NULL,3,1,'default.png',0,'activo','2026-01-17 12:25:11','2026-03-06 02:25:09'),(11,'30342976','yurima@sgp.local','$2y$10$QrTasd0TeXvkXbGpgKeROOvyn3WeHc6ejV6gSyLWig1NtsUW2AMwe',NULL,3,2,'default.png',0,'activo','2026-01-30 23:31:18','2026-03-06 02:25:09'),(12,'30342977','mariayepez@sgp.local','$2y$10$EgRnlUwlJIIcQXCDAQeYQOi/yj/OdntzLpoduHAO0/SjgUtliaIb.','$2y$10$82/hv7k3aSCwtcdAqPMyd.61orOKLsr4cmb7wqY1s0QLS0ZptNRpG',3,4,'default.png',0,'activo','2026-02-01 05:12:46','2026-03-06 02:25:09'),(15,'30342979','albertr@gmail.commm','$2y$10$UEL4uEPCMpzZNdZVlmHJzeOiGKW7EsDJNcAAXnWWlULTdLBON27l6',NULL,3,2,'default.png',0,'activo','2026-02-05 17:28:20','2026-03-06 02:25:09'),(17,'31342972','gabriel@sgp.local','$2y$10$WBiy3Ht2kZVph6HN6G44z.yrhifxZp6dJzhTyWiOPVPK6ApUVqf0W','$2y$10$ULjfODQXwxTQKq8QzOPMK.KgEXEFhf2JcHC7eRoSduhaar1dwVlCO',3,3,'default.png',0,'activo','2026-02-21 01:15:04','2026-03-10 15:50:33'),(23,'15020928','yarimar.79@gmail.com','$2y$10$UliJoserAEXjticcnhqwmO1jZ22tZ4Wp6CHHFYooGacap26r352ja','4301',3,3,'default.png',0,'activo','2026-02-21 23:44:21','2026-03-06 02:25:09'),(26,'28694068','isabelgutierrez@gmail.com','$2y$10$qA0DWToBZFFdKui75QQPvu/lkQqJbrnsFn5vfp8Sjr5QM6tRZ5oaO','$2y$10$g71tpRB9mwzuQnwiUkkXw.5wiBfAWKPzuHohT1ew2uQcRQmjhm1K6',3,2,'default.png',0,'activo','2026-02-23 00:01:43','2026-03-15 16:17:42'),(29,'30587335','wilfredo2004@gmail.com','$2y$10$Xrvyh873CnsNme0w.f935e81rVt65rSsLySGQe6skOqTng6sG76Oe','$2y$10$XOUDGnmfgWpM.2Abpp7EH.BolS7jA6.qtSwKKe9hqnTLTHaHuMf5.',3,3,'default.png',0,'activo','2026-03-02 13:43:03','2026-03-15 16:17:42'),(30,'30123321','joselozada2025@gmail.com','$2y$10$h6qYCLcB/mTBrRlDX.EvDe/hv6fOCFlgV/T72JmDDmxOadmnzsKTG','$2y$10$W4Bv2p4twt.vziwWKq6XPOCqNFwT8CIAqypkO9EU9NPP/kWL4RRZW',3,1,'default.png',0,'activo','2026-03-09 21:12:45','2026-03-15 18:00:22'),(31,'00000003','tutor.atencion@sgp.local','$2y$10$tQUTXJEGO26awVAftlZ.muLiTy26q5Z708mQDV6bYBR541jlAkHWi',NULL,2,3,'default.png',0,'activo','2026-03-15 18:07:23','2026-03-16 01:10:18'),(32,'00000004','tutor.reparaciones@sgp.local','$2y$10$bGNdf1Z1Fci57BnIKweQqegPFW1Ij5WGjP/gZQ.y60OmXXTio6fn.',NULL,2,4,'default.png',0,'activo','2026-03-15 18:07:23','2026-03-15 18:43:29');
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
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios_respuestas`
--

LOCK TABLES `usuarios_respuestas` WRITE;
/*!40000 ALTER TABLE `usuarios_respuestas` DISABLE KEYS */;
INSERT INTO `usuarios_respuestas` VALUES (1,4,3,'$2y$10$xFGprh.0upQAJelJoXCJ5uon7.EDrbGYq0Uo/Vr6j.ceQWGAN685C','2026-01-10 05:18:37'),(2,4,1,'$2y$10$S4LfO25gdPAkJ1cNmaWZDeR6WAh2CiQasUn0YeXB2JDjxAdbkDAHS','2026-01-10 05:18:37'),(3,4,2,'$2y$10$VwMPdeqg8JlvS9RijviRc.NQtAzUeBdc0VMMO9lJ6xuhLgG2AxCVK','2026-01-10 05:18:38'),(4,5,3,'$2y$10$7ry4F6QZdx1/LiAvzFbVNO4V580B9CH/1jcCdljMHOB4ccE8wviXm','2026-01-11 22:25:54'),(5,5,2,'$2y$10$Vbfvz0iOJAL9kC/XGpPFfOSrPdR9cy0VBx70.GPG73wToU9fBrdWa','2026-01-11 22:25:54'),(6,5,1,'$2y$10$eMlCZ9OXCBRpDRD43Jww1uawqEVym00oF2P0Sgtm1xKvAk5mh3vw6','2026-01-11 22:25:54'),(13,9,2,'$2y$10$WMMtN/3kHpy0nyTQRp6rLO1ieDbVS1536XmicE.k/9S3vgYcKceOK','2026-01-17 12:25:12'),(14,9,1,'$2y$10$WZ.fpXuthYBKK8GXk2JCoOlpnb5BCTJFdOtIGADvrFDIKOjUFyOuK','2026-01-17 12:25:12'),(15,9,3,'$2y$10$KxQ2abWJ/ZvUelhXAvg5wuklX7tOB/iXrbjjYEHd/U4vvmAv3FNxi','2026-01-17 12:25:12'),(16,12,1,'$2y$10$30V8dqKNPN5F/PmWbT0JYe0vnJRW77ZV0mpwQQefHlpISL9b.tO5.','2026-02-01 05:12:46'),(17,12,10,'$2y$10$sx1/K4HvOgW9GK79DEqkTe/9fXzPpqyNoURhjFZIGXhHNYHLaxN.y','2026-02-01 05:12:46'),(18,12,9,'$2y$10$gpv0Fx12lCXNHT/keQSXROqbSYRKkd0dd8TpMwJ/3OHhpPiXZeRma','2026-02-01 05:12:46'),(36,8,10,'$2y$10$Xf0LB66wIZR8qqD3YwvRxODyNWGM.B32tnTgmtzIWQDIqvwGG2LBy','2026-02-03 06:31:36'),(37,8,2,'$2y$10$GkJc34Vtn0dK1i2PkxLVOeUJlnLkQBEfIXExWqpgzZQ0HgQ4HNOoi','2026-02-03 06:31:36'),(38,8,9,'$2y$10$l/W/tJqKNb.rzu.bIJWpauf262JeqfTbotrGS3Kl7FvlESr7s4kQK','2026-02-03 06:31:36'),(42,15,1,'$2y$10$VxaL8xANLbbDt4Ei4j9v5eVy36QJgHCFCwAx8PbISOFAaNqXrFNPi','2026-02-05 17:34:13'),(43,15,2,'$2y$10$.uVXv9sVZsh3MpKGE1.hPerfC5nGQU7FqeHrL28Jn9/01rQQwRd1q','2026-02-05 17:34:13'),(44,15,3,'$2y$10$yRbqbo8hJ/HuzdUpV4iLceDbRIItC686WaOuMbhJpCj8b4lq5rXie','2026-02-05 17:34:13'),(63,23,10,'$2y$10$av/1y8o77VP9zhsRg9l86.kZ8.PYrfowGM/E9oNLNZfROi2UjXP3e','2026-02-21 23:47:49'),(64,23,1,'$2y$10$C6LzOnBiqV3GoCycoDL/0.YlNUFdfXbZ6iX0GedxeV.lowxj4v6yu','2026-02-21 23:47:49'),(65,23,9,'$2y$10$YiEp1SsUs1KgCjdwxMF5Pe0Sr1XCdyDA2.zXYHBlR70R0j0V01oPS','2026-02-21 23:47:49'),(75,26,1,'$2y$10$PuiRjvAL2Vk4BXiLpD6cS.PV6jVVRVkvf.VZ/hGHwsh/TRCgkAxOW','2026-02-23 00:07:19'),(76,26,2,'$2y$10$nAsTkmFUzjH/9rtA.xxp8uJ6cYn5vpMnH6N8Sa1Xq7953AY7ljroO','2026-02-23 00:07:19'),(77,26,10,'$2y$10$AKtyqmtb6W3niGt6RE.So.YvecgSuSKsUJem4NZgZjqQzPjWmrRs.','2026-02-23 00:07:19'),(90,1,1,'$2y$10$sAQIahFxNeG5rCOocP6oaefaVNv9zRIRa9zwMlJ.rlb84MfAceGkq','2026-02-28 15:23:16'),(91,1,2,'$2y$10$Xjc/.X4wT2Cunp6UIKsN6uC8vH8bW9qhvDFp1tMQPMZqLzuGkMoL.','2026-02-28 15:23:16'),(92,1,3,'$2y$10$c9CoYh1poQ8oRR.jSE5M5ueXDSDQPISLjfb0NSfHDsibyT.HHHFJm','2026-02-28 15:23:16'),(108,32,1,'$2y$10$O.KT.LlaJcRt3nGzmjQjluXA1SAzZ/X5dKqSvH7JNOyBy1npjwtYq','2026-03-15 18:43:29'),(109,32,3,'$2y$10$Ph3BXFu.yuuOScTtk0Lg2egmMID70tZumWbXdowl95jqxFAx5kj8G','2026-03-15 18:43:29'),(110,32,2,'$2y$10$vCqDHgHtm/7X5GY4G9OmDeMpWD/rseTUd7D5Pkw7aeOeVjuoDw652','2026-03-15 18:43:30'),(114,31,1,'$2y$10$VzaBRlSagnXryWsE5r2gpOl5DpK.kzvO8PmGUe.eVuVHlBcFyYA/.','2026-03-16 01:10:18'),(115,31,3,'$2y$10$rhs009lDYSDiWA0ZVfgiqewr.yOkvgpXoxs0JayGa0q06JEHKcPWO','2026-03-16 01:10:18'),(116,31,2,'$2y$10$fIS7u8Cyz96QOCZWmBKBfuBg14s6HMiGccqXSsJHRAr.n.taV/g96','2026-03-16 01:10:18');
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

-- Dump completed on 2026-03-16 17:12:27
