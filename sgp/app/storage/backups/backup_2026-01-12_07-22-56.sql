-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sgp_v1
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
-- Table structure for table `datos_academicos`
--

DROP TABLE IF EXISTS `datos_academicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datos_academicos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `institucion_procedencia` varchar(200) NOT NULL,
  `carrera` varchar(200) NOT NULL,
  `nivel_academico` enum('T??cnico','Universitario','Postgrado') DEFAULT 'Universitario',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `datos_academicos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datos_academicos`
--

LOCK TABLES `datos_academicos` WRITE;
/*!40000 ALTER TABLE `datos_academicos` DISABLE KEYS */;
/*!40000 ALTER TABLE `datos_academicos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `datos_pasante`
--

DROP TABLE IF EXISTS `datos_pasante`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datos_pasante` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `institucion_procedencia` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datos_pasante`
--

LOCK TABLES `datos_pasante` WRITE;
/*!40000 ALTER TABLE `datos_pasante` DISABLE KEYS */;
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
  CONSTRAINT `datos_personales_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datos_personales`
--

LOCK TABLES `datos_personales` WRITE;
/*!40000 ALTER TABLE `datos_personales` DISABLE KEYS */;
INSERT INTO `datos_personales` VALUES (1,1,'00000000','Administrador','del Sistema','0000-0000000',NULL,'M',NULL,'2026-01-10 00:02:19','2026-01-10 00:02:19'),(2,2,'11111111','Carlos','Tutor','0414-1234567',NULL,'M',NULL,'2026-01-10 00:02:19','2026-01-10 00:02:19'),(5,4,'30342975','jose luis','gomezlo','04148965723','los proceres','M','2000-06-10','2026-01-10 05:27:05','2026-01-10 05:27:05'),(6,5,'0000000000','Yurimar','Del Carmen','0000000000','Por definir','M','2000-01-01','2026-01-11 22:25:54','2026-01-11 22:25:54');
/*!40000 ALTER TABLE `datos_personales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `datos_tutor`
--

DROP TABLE IF EXISTS `datos_tutor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datos_tutor` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `departamento_id` int(10) unsigned DEFAULT NULL,
  `cargo` varchar(100) NOT NULL,
  `extension_telefonica` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datos_tutor`
--

LOCK TABLES `datos_tutor` WRITE;
/*!40000 ALTER TABLE `datos_tutor` DISABLE KEYS */;
/*!40000 ALTER TABLE `datos_tutor` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departamentos`
--

LOCK TABLES `departamentos` WRITE;
/*!40000 ALTER TABLE `departamentos` DISABLE KEYS */;
INSERT INTO `departamentos` VALUES (1,'Soporte T??cnico','Atenci??n y soporte t??cnico a usuarios',1,'2026-01-10 00:02:19'),(2,'Redes y Telecomunicaciones','Gesti??n de infraestructura de red',1,'2026-01-10 00:02:19'),(3,'Atenci??n al Usuario','Servicio al cliente y atenci??n directa',1,'2026-01-10 00:02:19'),(4,'Reparaciones Electr??nicas','Mantenimiento y reparaci??n de equipos',1,'2026-01-10 00:02:19'),(5,'Sin Asignar','Departamento temporal para usuarios sin asignaci??n',1,'2026-01-10 00:02:19');
/*!40000 ALTER TABLE `departamentos` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preguntas_seguridad`
--

LOCK TABLES `preguntas_seguridad` WRITE;
/*!40000 ALTER TABLE `preguntas_seguridad` DISABLE KEYS */;
INSERT INTO `preguntas_seguridad` VALUES (1,'Cual es tu postre favorito?',1,'2026-01-10 00:02:19'),(2,'Cual es tu color favorito?',1,'2026-01-10 00:02:19'),(3,'Cual es el nombre de tu mascota?',1,'2026-01-10 00:02:19');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'admin@sgp.local','$2y$10$X6FrcqzhY8agHsYPh9.wVO4Mga9FhsDXKgbX.4mXHwNhlh2rvM2yC',1,NULL,'default.png',0,'activo','2026-01-10 00:02:19','2026-01-11 23:06:29'),(2,'tutor@sgp.local','$2y$10$TxrLZz9BSpdfUFZSH127qu6h0c/GW83ujwnnA4DqEkEvMjyrHCw2S',2,2,'default.png',0,'activo','2026-01-10 00:02:19','2026-01-12 01:36:35'),(3,'pasante@sgp.local','.AaOHdvMeASON21Kaf3kU1wED3xilN3/3hehTUFgM1q',3,NULL,'default.png',0,'activo','2026-01-10 00:02:19','2026-01-10 03:49:56'),(4,'luisgabrielyanez9@gmail.com','$2y$10$UNpBq6vRmUvoJ4QJFaAWge7dHP0mLOV.GW01OlC6.Pv3qTj8DLHOG',3,NULL,'default.png',0,'activo','2026-01-10 05:18:37','2026-01-10 05:39:41'),(5,'yurimarprieto.79@gmail.com','$2y$10$Ga.8N.cmbEnxUIO08Jc1OupJT/HVvcJXI63.lEGk3G3JQcIk4PP76',3,NULL,'default.png',0,'inactivo','2026-01-11 22:25:54','2026-01-12 01:44:56'),(6,'maria.rodriguez@sgp.local','$2y$10$wqPfHjgfgn4GiANMdE616O9GtzhUE.8qZ.zlyZhHfl9Srl8NbgiPe',3,NULL,'default.png',0,'activo','2026-01-12 02:58:08','2026-01-12 03:01:45');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios_respuestas`
--

LOCK TABLES `usuarios_respuestas` WRITE;
/*!40000 ALTER TABLE `usuarios_respuestas` DISABLE KEYS */;
INSERT INTO `usuarios_respuestas` VALUES (1,4,3,'$2y$10$xFGprh.0upQAJelJoXCJ5uon7.EDrbGYq0Uo/Vr6j.ceQWGAN685C','2026-01-10 05:18:37'),(2,4,1,'$2y$10$S4LfO25gdPAkJ1cNmaWZDeR6WAh2CiQasUn0YeXB2JDjxAdbkDAHS','2026-01-10 05:18:37'),(3,4,2,'$2y$10$VwMPdeqg8JlvS9RijviRc.NQtAzUeBdc0VMMO9lJ6xuhLgG2AxCVK','2026-01-10 05:18:38'),(4,5,3,'$2y$10$7ry4F6QZdx1/LiAvzFbVNO4V580B9CH/1jcCdljMHOB4ccE8wviXm','2026-01-11 22:25:54'),(5,5,2,'$2y$10$Vbfvz0iOJAL9kC/XGpPFfOSrPdR9cy0VBx70.GPG73wToU9fBrdWa','2026-01-11 22:25:54'),(6,5,1,'$2y$10$eMlCZ9OXCBRpDRD43Jww1uawqEVym00oF2P0Sgtm1xKvAk5mh3vw6','2026-01-11 22:25:54');
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

-- Dump completed on 2026-01-12  2:22:57
