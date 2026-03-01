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
  `pasante_id` int(11) DEFAULT NULL,
  `asignacion_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_registro` time DEFAULT NULL,
  `metodo` enum('Kiosco','Manual') DEFAULT 'Kiosco',
  `motivo_justificacion` text DEFAULT NULL,
  `ruta_evidencia` varchar(255) DEFAULT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time DEFAULT NULL,
  `horas_calculadas` decimal(10,2) DEFAULT 0.00,
  `observacion` text DEFAULT NULL,
  `estado` enum('abierto','cerrado','Presente','Justificado','Ausente') DEFAULT 'Presente',
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
) ENGINE=InnoDB AUTO_INCREMENT=167 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de auditoría de todas las acciones críticas del sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--

LOCK TABLES `bitacora` WRITE;
/*!40000 ALTER TABLE `bitacora` DISABLE KEYS */;
INSERT INTO `bitacora` VALUES (1,1,'UPDATE_PROFILE','datos_personales',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-01-31 07:03:08'),(2,1,'UPDATE_PROFILE','datos_personales',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-01-31 07:04:10'),(3,1,'UPDATE_PROFILE','datos_personales',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-01-31 07:04:19'),(4,9,'UPDATE_PROFILE','datos_personales',9,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-02 01:58:52'),(5,9,'UPDATE_PROFILE','datos_personales',9,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-02 01:59:19'),(6,9,'UPDATE_PROFILE','datos_personales',9,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-02 02:01:14'),(7,9,'UPDATE_PROFILE','datos_personales',9,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-02 02:01:44'),(8,1,'UPDATE_SECURITY_QUESTIONS','usuarios_respuestas',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-02 02:06:02'),(9,9,'UPDATE_PROFILE','datos_personales',9,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-02 03:20:49'),(10,9,'UPDATE_PROFILE','datos_personales',9,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-02 03:30:18'),(11,15,'UPDATE_PROFILE','datos_personales',15,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-05 17:34:59'),(12,1,'UPDATE_PROFILE','datos_personales',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-11 17:33:07'),(13,1,'UPDATE_PROFILE','datos_personales',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-11 18:28:22'),(14,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-13 23:24:23'),(15,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-13 23:38:19'),(16,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-14 00:50:57'),(17,8,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-14 00:51:15'),(18,8,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-14 00:57:32'),(19,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-14 00:58:14'),(20,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-14 00:58:25'),(21,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 01:19:04'),(22,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 01:19:35'),(23,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 01:26:14'),(24,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 01:28:14'),(25,9,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 01:28:26'),(26,9,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 01:29:02'),(27,9,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 01:37:39'),(28,9,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 01:38:08'),(29,8,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 01:38:20'),(30,8,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 01:38:55'),(31,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 01:39:29'),(32,1,'LOGIN',NULL,NULL,'192.168.0.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-16 16:32:16'),(33,1,'LOGOUT',NULL,NULL,'192.168.0.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-16 16:32:50'),(34,1,'LOGIN',NULL,NULL,'192.168.0.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-16 16:34:06'),(35,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 16:34:25'),(36,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 16:37:10'),(37,1,'CREATE_USER','usuarios',NULL,'192.168.0.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','{\"cedula\":\"30939460\",\"rol_id\":1,\"email\":\"catiremanuel170@gmail.com\"}','2026-02-16 16:41:12'),(38,16,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 16:42:39'),(39,1,'LOGOUT',NULL,NULL,'192.168.0.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-16 16:44:36'),(40,16,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 17:14:17'),(41,16,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 17:19:22'),(42,16,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 17:19:33'),(43,16,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 17:23:50'),(44,16,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-16 17:29:24'),(45,1,'LOGIN',NULL,NULL,'192.168.0.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-16 18:43:25'),(46,1,'LOGOUT',NULL,NULL,'192.168.0.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-16 18:44:26'),(47,1,'LOGIN',NULL,NULL,'192.168.0.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-16 21:13:09'),(48,1,'LOGOUT',NULL,NULL,'192.168.0.100','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',NULL,'2026-02-16 21:14:26'),(49,16,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-17 02:08:24'),(50,16,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-17 03:15:12'),(51,16,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-17 23:05:26'),(52,16,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-17 23:19:08'),(53,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-17 23:22:41'),(54,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-18 02:13:40'),(56,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-18 18:43:32'),(57,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-18 18:46:29'),(58,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-18 19:04:02'),(59,1,'LOGIN',NULL,NULL,'10.36.0.40','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36',NULL,'2026-02-18 19:05:50'),(60,1,'LOGOUT',NULL,NULL,'10.36.0.40','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36',NULL,'2026-02-18 19:07:45'),(61,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-18 19:29:03'),(62,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-18 20:00:31'),(63,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-18 22:51:17'),(64,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-18 23:00:06'),(65,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-18 23:00:45'),(66,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-18 23:01:54'),(67,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-18 23:03:54'),(68,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-18 23:23:11'),(69,1,'LOGIN',NULL,NULL,'192.168.0.104','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36',NULL,'2026-02-19 00:21:58'),(70,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 00:40:53'),(71,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 00:41:23'),(72,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 00:41:45'),(73,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 00:57:34'),(74,1,'DELETE_USER','usuarios',5,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"tipo\":\"soft_delete\"}','2026-02-19 02:19:29'),(75,1,'TOGGLE_USER_STATUS','usuarios',16,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"nuevo_estado\":\"inactivo\"}','2026-02-19 02:41:32'),(76,1,'TOGGLE_USER_STATUS','usuarios',16,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"nuevo_estado\":\"activo\"}','2026-02-19 02:41:38'),(77,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 14:34:45'),(78,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 14:40:05'),(79,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 14:44:03'),(80,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 14:59:27'),(81,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 14:59:56'),(82,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 15:00:55'),(83,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 15:02:13'),(84,1,'UPDATE_PROFILE','datos_personales',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-19 15:02:41'),(85,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 15:11:23'),(86,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 17:06:48'),(87,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 17:09:49'),(88,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 17:50:31'),(89,1,'UPDATE_PROFILE','datos_personales',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-19 17:52:03'),(90,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 17:52:12'),(91,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-19 22:55:54'),(92,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-20 01:26:40'),(93,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 00:55:57'),(94,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 00:56:16'),(95,17,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 01:15:30'),(96,17,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 01:18:08'),(97,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 01:18:22'),(98,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 01:19:38'),(99,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 02:35:05'),(100,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 13:46:56'),(101,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 14:59:17'),(102,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 14:59:32'),(103,1,'CREATE_USER','usuarios',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"cedula\":\"11729897\",\"rol_id\":3,\"email\":\"luisrafaelyanez@gmail.com\"}','2026-02-21 15:18:41'),(104,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 15:19:12'),(105,18,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 15:20:10'),(106,18,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 15:50:27'),(107,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 15:50:59'),(108,1,'UPDATE_PROFILE','datos_personales',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-21 15:52:46'),(109,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 15:52:54'),(110,1,'LOGIN',NULL,NULL,'192.168.0.101','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36',NULL,'2026-02-21 16:36:02'),(111,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 16:36:45'),(112,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 16:44:12'),(113,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 16:53:48'),(114,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 17:05:47'),(115,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 17:51:00'),(116,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 17:57:17'),(117,19,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 17:59:34'),(118,19,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 18:00:07'),(119,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 18:00:16'),(120,1,'TOGGLE_USER_STATUS','usuarios',19,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"nuevo_estado\":\"inactivo\"}','2026-02-21 18:00:39'),(121,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 18:00:53'),(123,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 19:23:02'),(124,1,'TOGGLE_USER_STATUS','usuarios',19,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"nuevo_estado\":\"activo\"}','2026-02-21 19:24:17'),(125,1,'UPDATE_USER','usuarios',19,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"email\",\"rol_id\",\"departamento_id\",\"cedula\",\"datos_personales\"]}','2026-02-21 19:25:57'),(126,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 19:26:06'),(127,19,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 19:26:52'),(128,19,'CREATE_USER','usuarios',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"cedula\":\"12345676\",\"rol_id\":1,\"email\":\"luciayanez@gmail.com\"}','2026-02-21 19:40:28'),(129,19,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 19:40:53'),(130,21,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 19:41:25'),(131,21,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 20:18:17'),(132,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 20:18:39'),(133,1,'CREATE_USER','usuarios',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"cedula\":\"22334455\",\"rol_id\":3,\"email\":\"luciana@gmail.com\"}','2026-02-21 20:25:10'),(134,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 20:25:19'),(135,22,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 20:25:56'),(136,22,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 21:17:08'),(137,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 21:17:31'),(138,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 21:17:45'),(139,22,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 23:09:28'),(140,22,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 23:10:30'),(141,22,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 23:16:23'),(142,22,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 23:37:27'),(143,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 23:43:02'),(144,1,'CREATE_USER','usuarios',23,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"cedula\":\"15020928\",\"rol_id\":3,\"email\":\"yarimar.79@gmail.com\",\"nombre_completo\":\"yarimar prieto\"}','2026-02-21 23:44:21'),(145,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 23:44:49'),(146,23,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-21 23:45:21'),(147,23,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-22 03:30:30'),(148,1,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-22 03:30:37'),(149,1,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-22 05:17:36'),(150,24,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-22 14:49:38'),(151,24,'LOGOUT',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-22 16:55:51'),(152,25,'LOGIN',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,'2026-02-22 16:57:04'),(153,25,'UPDATE_PROFILE','datos_personales',25,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-22 17:08:32'),(154,25,'UPDATE_PROFILE','datos_personales',25,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}','2026-02-22 19:07:02'),(155,1,'LOGIN',NULL,NULL,'192.168.0.101','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36',NULL,'2026-02-22 21:58:47'),(156,1,'LOGOUT',NULL,NULL,'192.168.0.101','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36',NULL,'2026-02-22 22:01:35'),(157,1,'LOGIN',NULL,NULL,'192.168.0.101','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36',NULL,'2026-02-22 23:01:00'),(158,1,'LOGOUT',NULL,NULL,'192.168.0.101','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36',NULL,'2026-02-22 23:10:09'),(159,1,'LOGIN',NULL,NULL,'192.168.0.101','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36',NULL,'2026-02-22 23:57:23'),(160,1,'LOGOUT',NULL,NULL,'192.168.0.101','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36',NULL,'2026-02-22 23:59:50'),(161,1,'LOGIN',NULL,NULL,'192.168.0.101','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36',NULL,'2026-02-23 00:00:27'),(162,1,'CREATE_USER','usuarios',26,'192.168.0.101','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36','{\"cedula\":\"28694068\",\"rol_id\":3,\"email\":\"isabelgutierrez@gmail.com\",\"nombre_completo\":\"Isabel Gutierrez\"}','2026-02-23 00:01:43'),(163,1,'LOGOUT',NULL,NULL,'192.168.0.101','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36',NULL,'2026-02-23 00:03:29'),(164,26,'LOGIN',NULL,NULL,'192.168.0.101','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36',NULL,'2026-02-23 00:04:04'),(165,26,'LOGOUT',NULL,NULL,'192.168.0.101','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36',NULL,'2026-02-23 00:08:25'),(166,1,'LOGIN',NULL,NULL,'192.168.0.101','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36',NULL,'2026-02-23 00:10:25');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datos_pasante`
--

LOCK TABLES `datos_pasante` WRITE;
/*!40000 ALTER TABLE `datos_pasante` DISABLE KEYS */;
INSERT INTO `datos_pasante` VALUES (1,9,'moral y luces','2026-02-02 03:30:18','2026-02-02 03:30:18','Pendiente',NULL,NULL,0,240,NULL,NULL),(2,15,'jdhdhd','2026-02-05 17:34:59','2026-02-05 17:34:59','Pendiente',NULL,NULL,0,240,NULL,NULL),(3,18,'','2026-02-21 15:27:33','2026-02-21 15:27:33','',NULL,NULL,0,1440,NULL,NULL),(4,22,'','2026-02-21 20:29:26','2026-02-21 20:29:26','',NULL,NULL,0,1440,NULL,NULL),(5,23,'2','2026-02-21 23:47:49','2026-02-21 23:47:49','',NULL,NULL,0,1440,NULL,NULL),(6,25,'1','2026-02-22 17:07:56','2026-02-22 17:07:56','',NULL,NULL,0,1440,NULL,NULL),(9,26,'2','2026-02-23 00:07:19','2026-02-23 00:07:19','',NULL,NULL,0,1440,NULL,NULL);
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
  CONSTRAINT `fk_personales_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datos_personales`
--

LOCK TABLES `datos_personales` WRITE;
/*!40000 ALTER TABLE `datos_personales` DISABLE KEYS */;
INSERT INTO `datos_personales` VALUES (1,1,'Administrador','del Sistema','jefe de soporte tecnico','0414-6785678','M','2026-01-15','2026-01-10 00:02:19','2026-02-21 20:02:59'),(2,2,'Carlos','Tutor',NULL,'0414-1234567','M',NULL,'2026-01-10 00:02:19','2026-01-10 00:02:19'),(5,4,'jose luis','gomezlo',NULL,'04148965723','M','2000-06-10','2026-01-10 05:27:05','2026-02-21 20:02:59'),(6,5,'Yurimar','Del Carmen',NULL,'0000000000','M','2000-01-01','2026-01-11 22:25:54','2026-02-21 20:02:59'),(8,8,'petra','prieto',NULL,'04241234567','F','2014-01-06','2026-01-14 05:18:55','2026-02-21 20:02:59'),(9,9,'albert','rodriguez',NULL,'04148965723','M','2000-01-01','2026-01-17 12:25:12','2026-02-21 20:02:59'),(11,11,'Yarima','Del Carmen',NULL,NULL,NULL,NULL,'2026-01-30 23:31:18','2026-01-30 23:31:18'),(12,12,'maria','yepez',NULL,NULL,NULL,NULL,'2026-02-01 05:12:46','2026-02-01 05:12:46'),(13,13,'delmary','Nuevo',NULL,NULL,NULL,NULL,'2026-02-01 20:07:11','2026-02-01 20:07:11'),(14,15,'Albert','Rodriguez',NULL,'04148965723','M','2026-02-05','2026-02-05 17:28:20','2026-02-21 20:01:46'),(15,17,'gabriel','prieto',NULL,NULL,NULL,NULL,'2026-02-21 01:15:05','2026-02-21 01:15:05'),(16,19,'gabrielucho','prieto',NULL,NULL,NULL,NULL,'2026-02-21 17:58:49','2026-02-21 17:58:49'),(17,21,'lucia','yanez','asistente','04148965728','','2010-02-01','2026-02-21 19:44:44','2026-02-21 19:44:44'),(18,22,'','',NULL,'0414-6785609','F','2009-05-21','2026-02-21 20:29:26','2026-02-21 20:29:26'),(19,23,'yarimar','prieto',NULL,'04128729021','M','2012-02-22','2026-02-21 23:44:21','2026-02-21 23:47:49'),(21,24,'pedro','Nuevo',NULL,NULL,NULL,NULL,'2026-02-22 14:49:12','2026-02-22 14:49:12'),(22,25,'pedro','paez',NULL,'04128728021','M','2012-02-22','2026-02-22 16:56:49','2026-02-22 17:07:56'),(24,26,'Isabel','Gutierrez',NULL,'04160904308','F','2002-10-07','2026-02-23 00:01:43','2026-02-23 00:07:19');
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
INSERT INTO `departamentos` VALUES (1,'Soporte Técnico','Mantenimiento preventivo y correctivo de equipos de computación.',1,'2026-02-19 14:54:35'),(2,'Redes y Telecomunicaciones','Gestión de infraestructura de red, servidores y conectividad.',1,'2026-02-19 14:54:35'),(3,'Reparaciones Electrónicas','Diagnóstico avanzado y reparación de componentes electrónicos.',1,'2026-02-19 14:54:35'),(4,'Atención al Usuario','Recepción de equipos y gestión de solicitudes de servicio.',1,'2026-02-19 14:54:35');
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
  CONSTRAINT `evaluaciones_ibfk_1` FOREIGN KEY (`pasante_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evaluaciones`
--

LOCK TABLES `evaluaciones` WRITE;
/*!40000 ALTER TABLE `evaluaciones` DISABLE KEYS */;
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instituciones`
--

LOCK TABLES `instituciones` WRITE;
/*!40000 ALTER TABLE `instituciones` DISABLE KEYS */;
INSERT INTO `instituciones` VALUES (1,'E.T.C Juan Bautista González',NULL,'2026-02-19 14:54:35','Escuela Técnica','3FJH+2F8, Cdad. Bolívar 8001, Bolívar'),(2,'Colegio Fe y Alegría \"José María Vélaz\"',NULL,'2026-02-19 14:54:35','Escuela Técnica','3F7C+XPR, Cdad. Bolívar 8001, Bolívar');
/*!40000 ALTER TABLE `instituciones` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
INSERT INTO `notificaciones` VALUES (1,1,'success','¡Sistema de Notificaciones Activado!','El sistema de notificaciones ha sido configurado correctamente. Ahora recibirás actualizaciones importantes del sistema.',NULL,1,'2026-02-08 16:44:55'),(2,1,'info','Bienvenido al SGP','Gracias por usar el Sistema de Gestión de Pasantes. Explora todas las funcionalidades disponibles.','/dashboard',1,'2026-02-08 16:44:55'),(3,1,'warning','Actualiza tu perfil','Completa tu información de perfil para aprovechar todas las funcionalidades del sistema.','/perfil/ver',1,'2026-02-08 16:44:55');
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
  CONSTRAINT `fk_usuario_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'12345678','admin@sgp.local','$2y$10$X6FrcqzhY8agHsYPh9.wVO4Mga9FhsDXKgbX.4mXHwNhlh2rvM2yC',NULL,1,NULL,'default.png',0,'activo','2026-01-10 00:02:19','2026-02-21 19:12:30'),(2,'30342971','tutor@sgp.local','$2y$10$TxrLZz9BSpdfUFZSH127qu6h0c/GW83ujwnnA4DqEkEvMjyrHCw2S',NULL,2,NULL,'default.png',0,'activo','2026-01-10 00:02:19','2026-02-21 19:22:00'),(4,'30342975','luisgabrielyanez9@gmail.com','$2y$10$suzP73.i87XCpegPe8U4I.EKJMTziXRd5kWfFZVvP13HijTCJgnO2',NULL,3,NULL,'default.png',0,'activo','2026-01-10 05:18:37','2026-02-21 19:22:00'),(5,'30342972','yurimarprieto.79@gmail.com','$2y$10$Ga.8N.cmbEnxUIO08Jc1OupJT/HVvcJXI63.lEGk3G3JQcIk4PP76',NULL,3,NULL,'default.png',0,'inactivo','2026-01-11 22:25:54','2026-02-21 19:22:00'),(8,'30342973','petra.79@GMAIL.COM','$2y$10$x3mj9u/wybL.KxEGbcy9reLgLq0eDbAktp2Icso7DT4lDnJ5dUspO',NULL,2,NULL,'default.png',0,'activo','2026-01-14 04:21:10','2026-02-21 19:22:00'),(9,'30342974','albert@gmail.com','$2y$10$xQg8s9puOmRpvfAqCkFWPOQKaYRTqKOdI/NYRGYCYEnKhWP5Dq4qu',NULL,3,NULL,'default.png',0,'activo','2026-01-17 12:25:11','2026-02-21 19:22:00'),(11,'30342976','yurima@sgp.local','$2y$10$QrTasd0TeXvkXbGpgKeROOvyn3WeHc6ejV6gSyLWig1NtsUW2AMwe',NULL,3,NULL,'default.png',0,'activo','2026-01-30 23:31:18','2026-02-21 19:22:00'),(12,'30342977','mariayepez@sgp.local','$2y$10$EgRnlUwlJIIcQXCDAQeYQOi/yj/OdntzLpoduHAO0/SjgUtliaIb.',NULL,3,NULL,'default.png',0,'activo','2026-02-01 05:12:46','2026-02-21 19:22:00'),(13,'303429758','delmaryguzman@gmail.com','$2y$10$34JGLe.CxtwtAM8SnMXyV.Zu/MVhj4lhP7B7wNZfs66aWBS3QojHu',NULL,1,NULL,'default.png',0,'activo','2026-02-01 20:07:11','2026-02-21 19:22:00'),(14,NULL,'ortiz9@gmail.com','$2y$10$xrm3mgtQVm1Nf74R0JnbQeHuDkI7h9o8UUZGlApKqekIc.mq3Vd7W',NULL,1,NULL,'default.png',1,'activo','2026-02-02 18:12:30','2026-02-02 18:12:30'),(15,'30342979','albertr@gmail.commm','$2y$10$UEL4uEPCMpzZNdZVlmHJzeOiGKW7EsDJNcAAXnWWlULTdLBON27l6',NULL,3,NULL,'default.png',0,'activo','2026-02-05 17:28:20','2026-02-21 19:22:00'),(16,'31341971','catiremanuel170@gmail.com','$2y$10$DbKDoFu5uCYFV6IS6hb7K.0V/Sc7VK44STCxb0662ekKGTD1yNf.2',NULL,1,NULL,'default.png',0,'activo','2026-02-16 16:41:12','2026-02-21 19:22:00'),(17,'31342972','gabriel@sgp.local','$2y$10$WBiy3Ht2kZVph6HN6G44z.yrhifxZp6dJzhTyWiOPVPK6ApUVqf0W',NULL,3,NULL,'default.png',0,'activo','2026-02-21 01:15:04','2026-02-21 19:22:00'),(18,NULL,'luisrafaelyanez@gmail.com','$2y$10$4E9I6m7vcCVRfsn8zjXYCeDIeIla.lvyStKenNECSo5MQ88kq2riS','1234',3,NULL,'default.png',0,'activo','2026-02-21 15:18:41','2026-02-21 15:27:33'),(19,'12345677','gabrielucho@gmail.com','$2y$10$7oyfFcb5L8x0Zbi.d7xaLODkzJTpSnsvBdDY0/uJryIiQ/9gqDMzi',NULL,1,NULL,'default.png',0,'activo','2026-02-21 17:58:48','2026-02-21 19:25:57'),(21,'12345676','luciayanez@gmail.com','$2y$10$yCayWuRfNhxVN32aIDqm2eFpKjYw.H3W8yQI/LovotMTX49UVCQie',NULL,1,1,'default.png',0,'activo','2026-02-21 19:40:28','2026-02-21 19:44:44'),(22,'22334455','luciana@gmail.com','$2y$10$w86sVRJHENZNM/8TOq7d2e.7LKNvGEd7urWFn0WKtCT5Gbwlu3666','1234',3,NULL,'default.png',0,'activo','2026-02-21 20:25:10','2026-02-21 23:09:08'),(23,'15020928','yarimar.79@gmail.com','$2y$10$UliJoserAEXjticcnhqwmO1jZ22tZ4Wp6CHHFYooGacap26r352ja','1234',3,NULL,'default.png',0,'activo','2026-02-21 23:44:21','2026-02-21 23:47:49'),(24,NULL,'pedro@sgp.local','$2y$10$7FU5Ot6GKqpoyIi.sGPez.h0Kmmmil9PGcMJ/v269u3gZ/6VkOJzu',NULL,3,NULL,'default.png',0,'activo','2026-02-22 14:49:11','2026-02-22 14:49:11'),(25,NULL,'pedropaez@sgp.local','$2y$10$cWUBFoGPpmhYbPUDsAOqK.RLCWt48slTTAmO6MguizqEj9pumM//O','1111',3,NULL,'default.png',0,'activo','2026-02-22 16:56:48','2026-02-22 17:07:56'),(26,'28694068','isabelgutierrez@gmail.com','$2y$10$qA0DWToBZFFdKui75QQPvu/lkQqJbrnsFn5vfp8Sjr5QM6tRZ5oaO','2020',3,NULL,'default.png',0,'activo','2026-02-23 00:01:43','2026-02-23 00:07:19');
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
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios_respuestas`
--

LOCK TABLES `usuarios_respuestas` WRITE;
/*!40000 ALTER TABLE `usuarios_respuestas` DISABLE KEYS */;
INSERT INTO `usuarios_respuestas` VALUES (1,4,3,'$2y$10$xFGprh.0upQAJelJoXCJ5uon7.EDrbGYq0Uo/Vr6j.ceQWGAN685C','2026-01-10 05:18:37'),(2,4,1,'$2y$10$S4LfO25gdPAkJ1cNmaWZDeR6WAh2CiQasUn0YeXB2JDjxAdbkDAHS','2026-01-10 05:18:37'),(3,4,2,'$2y$10$VwMPdeqg8JlvS9RijviRc.NQtAzUeBdc0VMMO9lJ6xuhLgG2AxCVK','2026-01-10 05:18:38'),(4,5,3,'$2y$10$7ry4F6QZdx1/LiAvzFbVNO4V580B9CH/1jcCdljMHOB4ccE8wviXm','2026-01-11 22:25:54'),(5,5,2,'$2y$10$Vbfvz0iOJAL9kC/XGpPFfOSrPdR9cy0VBx70.GPG73wToU9fBrdWa','2026-01-11 22:25:54'),(6,5,1,'$2y$10$eMlCZ9OXCBRpDRD43Jww1uawqEVym00oF2P0Sgtm1xKvAk5mh3vw6','2026-01-11 22:25:54'),(13,9,2,'$2y$10$WMMtN/3kHpy0nyTQRp6rLO1ieDbVS1536XmicE.k/9S3vgYcKceOK','2026-01-17 12:25:12'),(14,9,1,'$2y$10$WZ.fpXuthYBKK8GXk2JCoOlpnb5BCTJFdOtIGADvrFDIKOjUFyOuK','2026-01-17 12:25:12'),(15,9,3,'$2y$10$KxQ2abWJ/ZvUelhXAvg5wuklX7tOB/iXrbjjYEHd/U4vvmAv3FNxi','2026-01-17 12:25:12'),(16,12,1,'$2y$10$30V8dqKNPN5F/PmWbT0JYe0vnJRW77ZV0mpwQQefHlpISL9b.tO5.','2026-02-01 05:12:46'),(17,12,10,'$2y$10$sx1/K4HvOgW9GK79DEqkTe/9fXzPpqyNoURhjFZIGXhHNYHLaxN.y','2026-02-01 05:12:46'),(18,12,9,'$2y$10$gpv0Fx12lCXNHT/keQSXROqbSYRKkd0dd8TpMwJ/3OHhpPiXZeRma','2026-02-01 05:12:46'),(19,13,1,'$2y$10$9brrF4Se5VyH.9GY0lfP2OvWOJjlJRsOc8mWq07l3TXhMDl.LyTSS','2026-02-01 20:07:11'),(20,13,2,'$2y$10$FmkTggeXhaNYp264VcBoxuf4ptNEi8kbT5a2dThX6/prUhP07EHu.','2026-02-01 20:07:11'),(21,13,3,'$2y$10$X5gAZqGOuqz59qjXSNmS3umZUp3Jx0XZ8ISj7.Bx6c0b1lAS/7mxW','2026-02-01 20:07:11'),(22,1,1,'$2y$10$8ThOERlFSNC4FEMkEr4LhOfQ24DO7ZfZX4nJ6291jlvK4atNjhyZi','2026-02-02 02:06:02'),(23,1,2,'$2y$10$AsXfeRp/18K3JHC7M9dZF.K2MbFP21M1rmfPyEb6bekcIn9AZEr7O','2026-02-02 02:06:02'),(24,1,10,'$2y$10$zsVPdu0Gs2/zzxdujwQ7OuSE0kF77Mrnu4fA8.N30xUfJFi07hF4q','2026-02-02 02:06:02'),(36,8,10,'$2y$10$Xf0LB66wIZR8qqD3YwvRxODyNWGM.B32tnTgmtzIWQDIqvwGG2LBy','2026-02-03 06:31:36'),(37,8,2,'$2y$10$GkJc34Vtn0dK1i2PkxLVOeUJlnLkQBEfIXExWqpgzZQ0HgQ4HNOoi','2026-02-03 06:31:36'),(38,8,9,'$2y$10$l/W/tJqKNb.rzu.bIJWpauf262JeqfTbotrGS3Kl7FvlESr7s4kQK','2026-02-03 06:31:36'),(42,15,1,'$2y$10$VxaL8xANLbbDt4Ei4j9v5eVy36QJgHCFCwAx8PbISOFAaNqXrFNPi','2026-02-05 17:34:13'),(43,15,2,'$2y$10$.uVXv9sVZsh3MpKGE1.hPerfC5nGQU7FqeHrL28Jn9/01rQQwRd1q','2026-02-05 17:34:13'),(44,15,3,'$2y$10$yRbqbo8hJ/HuzdUpV4iLceDbRIItC686WaOuMbhJpCj8b4lq5rXie','2026-02-05 17:34:13'),(45,16,1,'$2y$10$RiKlsorGYbfiGUbEc.69pe2r6AiTq1i19zJpOPFjtAHtPXLGRp9eG','2026-02-17 02:13:13'),(46,16,2,'$2y$10$7zRG8OkR/YrTXaiBB0EMyuI9nc0G9K4MGXi0B9CX1OmmzH5yqxjB.','2026-02-17 02:13:14'),(47,16,10,'$2y$10$PJLsBuTJ/32.r9IsrL6KGuxfU1rIacXr8b4NvFuChTat2otoqD7Hq','2026-02-17 02:13:14'),(48,17,10,'$2y$10$30p6BqPa0fBRqcPTVCb89ugmbbSCOS.P6KULKPQNTgEQ4G/6o6Cp6','2026-02-21 01:15:05'),(49,17,1,'$2y$10$m.s6X2.x6QEg2Awb8EpIeOe2ARDC2GOzhZP271AjTVqqavr2dXoeu','2026-02-21 01:15:05'),(50,17,9,'$2y$10$6tSTXiWUtiC6sRORCrtsUOYIsh5wVH4dBXqhYpACs2u97i/dUlgPm','2026-02-21 01:15:05'),(51,18,1,'$2y$10$S/6UF7VV.UKVmLggJIxWBeCkD.UbSi4pALUBVZHXvXrO2m0DIxERG','2026-02-21 15:27:33'),(52,18,10,'$2y$10$2BIEdEDJDkSYYE6U5pORXummecgC1sC3jIOlcY8c2rVsh51oLgVoy','2026-02-21 15:27:33'),(53,18,9,'$2y$10$XQjSzWPV0bSFHo7IgEybIegB.fxazw23vfqt5iB3AgZlkIOHAT3Ae','2026-02-21 15:27:33'),(54,19,1,'$2y$10$coQd5a7QKHNb6jdtgaJsb.iHVr6XMvf01BlZfzYCtZJKunFn01AP2','2026-02-21 17:58:48'),(55,19,10,'$2y$10$yrm0opwl99uiIyc/nC9HC.hFe3fSvduzpRXOIyenvN79DZAsD.xmy','2026-02-21 17:58:48'),(56,19,3,'$2y$10$hapRWiabsWiutTz5hQAO5eikFSPsdGZ2I.H/J6gFL72Zo4OMAB2Py','2026-02-21 17:58:49'),(57,21,10,'$2y$10$bVXQZGjqq9BP/3llIOjOnOwKJKcVWuvg3uQTVQnD/vnU8HRb52IAa','2026-02-21 19:44:44'),(58,21,2,'$2y$10$3szXub/KLpMQ2pMJxRC4a.RVBwj2lx3MTfgsW9Q9r1/2J6vRfcuAu','2026-02-21 19:44:44'),(59,21,9,'$2y$10$4GFRpIkWe.hWfIe.ZPrSKOxPXP68ZwMLrXj37Kkh5msIbxenmGWIG','2026-02-21 19:44:44'),(60,22,10,'$2y$10$ZqghPE7J.LKuSXqXREPNe.2y1neNzwZusj8DWyOhcWJK.yAOCbSQm','2026-02-21 20:29:26'),(61,22,1,'$2y$10$cK0/CSMHuYu7VA17flQx8OKovgSV9jCr/XV1fWd6vaEtbkjQKIXgi','2026-02-21 20:29:26'),(62,22,9,'$2y$10$IdHwCgIAACnIiwGLBNJvteGVZeNIo1Ld2z8WxYYx4LBgdY4A/aP1W','2026-02-21 20:29:26'),(63,23,10,'$2y$10$av/1y8o77VP9zhsRg9l86.kZ8.PYrfowGM/E9oNLNZfROi2UjXP3e','2026-02-21 23:47:49'),(64,23,1,'$2y$10$C6LzOnBiqV3GoCycoDL/0.YlNUFdfXbZ6iX0GedxeV.lowxj4v6yu','2026-02-21 23:47:49'),(65,23,9,'$2y$10$YiEp1SsUs1KgCjdwxMF5Pe0Sr1XCdyDA2.zXYHBlR70R0j0V01oPS','2026-02-21 23:47:49'),(66,24,1,'$2y$10$Lz9bRhOY08NWoGqBg0THWe9I0qN6XK8rF84yhxAfFV84MLUWYsUDa','2026-02-22 14:49:11'),(67,24,10,'$2y$10$lEORk7SV5Q8fyQkqg5Tu6u7MKvgY6vdEIJSYAhSb1hATE7WGL6OxC','2026-02-22 14:49:12'),(68,24,2,'$2y$10$MUU13uLTrY8V/OdmIhoNpe5vA4zkzTgFNxf41EljrOSXqI6YkQ0Qq','2026-02-22 14:49:12'),(75,26,1,'$2y$10$PuiRjvAL2Vk4BXiLpD6cS.PV6jVVRVkvf.VZ/hGHwsh/TRCgkAxOW','2026-02-23 00:07:19'),(76,26,2,'$2y$10$nAsTkmFUzjH/9rtA.xxp8uJ6cYn5vpMnH6N8Sa1Xq7953AY7ljroO','2026-02-23 00:07:19'),(77,26,10,'$2y$10$AKtyqmtb6W3niGt6RE.So.YvecgSuSKsUJem4NZgZjqQzPjWmrRs.','2026-02-23 00:07:19');
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

-- Dump completed on 2026-02-22 20:24:21
