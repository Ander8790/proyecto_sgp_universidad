-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-03-2026 a las 00:37:16
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `proyecto_sgp`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaciones`
--

CREATE TABLE `asignaciones` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencias`
--

INSERT INTO `asistencias` (`id`, `pasante_id`, `asignacion_id`, `fecha`, `hora_registro`, `metodo`, `motivo_justificacion`, `ruta_evidencia`, `hora_entrada`, `hora_salida`, `horas_calculadas`, `observacion`, `estado`, `created_at`, `updated_at`) VALUES
(1, 22, NULL, '2026-02-25', '02:43:22', 'Manual', NULL, NULL, NULL, NULL, NULL, NULL, 'Ausente', '2026-02-25 01:43:22', '2026-02-25 03:07:42'),
(2, 4, NULL, '2026-02-25', '04:09:03', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-02-25 03:09:03', '2026-02-25 03:09:03'),
(3, 26, NULL, '2026-02-25', '04:11:55', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-02-25 03:11:55', '2026-02-25 03:11:55'),
(4, 4, NULL, '2026-02-26', '06:01:02', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-02-26 05:01:02', '2026-02-26 05:01:02'),
(5, 4, NULL, '2026-02-28', '05:33:29', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-02-28 04:33:29', '2026-02-28 04:33:29'),
(6, 4, NULL, '2026-03-02', '13:44:23', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-02 12:44:23', '2026-03-02 12:44:23'),
(7, 29, NULL, '2026-03-02', '14:49:21', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-02 13:49:21', '2026-03-02 13:49:21'),
(8, 12, NULL, '2026-03-03', '02:13:52', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-03 01:13:52', '2026-03-03 01:13:52'),
(9, 4, NULL, '2026-03-03', '17:13:48', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-03 16:13:48', '2026-03-03 16:13:48'),
(10, 26, NULL, '2026-03-03', '17:17:47', 'Manual', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-03 16:17:47', '2026-03-03 16:17:47'),
(11, 4, NULL, '2026-02-16', '08:05:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(12, 9, NULL, '2026-02-16', '08:10:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(13, 11, NULL, '2026-02-16', '08:00:00', 'Manual', NULL, NULL, NULL, NULL, NULL, NULL, 'Ausente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(14, 15, NULL, '2026-02-17', '07:55:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(15, 17, NULL, '2026-02-17', '08:12:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(16, 23, NULL, '2026-02-17', '09:00:00', 'Manual', NULL, NULL, NULL, NULL, NULL, NULL, 'Justificado', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(17, 12, NULL, '2026-02-18', '08:00:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(18, 26, NULL, '2026-02-18', '08:15:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(19, 29, NULL, '2026-02-18', '08:30:00', 'Manual', NULL, NULL, NULL, NULL, NULL, NULL, 'Ausente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(20, 4, NULL, '2026-02-19', '07:50:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(21, 11, NULL, '2026-02-19', '08:05:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(22, 15, NULL, '2026-02-19', '08:00:00', 'Manual', NULL, NULL, NULL, NULL, NULL, NULL, 'Justificado', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(23, 17, NULL, '2026-02-20', '08:10:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(24, 26, NULL, '2026-02-20', '08:00:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(25, 29, NULL, '2026-02-20', '08:02:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(26, 4, NULL, '2026-02-23', '08:00:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(27, 9, NULL, '2026-02-23', '08:00:00', 'Manual', NULL, NULL, NULL, NULL, NULL, NULL, 'Ausente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(28, 11, NULL, '2026-02-23', '07:58:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(29, 12, NULL, '2026-02-24', '08:05:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(30, 15, NULL, '2026-02-24', '08:20:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(31, 17, NULL, '2026-02-24', '08:00:00', 'Manual', NULL, NULL, NULL, NULL, NULL, NULL, 'Justificado', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(32, 23, NULL, '2026-02-25', '07:55:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(33, 26, NULL, '2026-02-25', '08:05:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(34, 29, NULL, '2026-02-25', '08:00:00', 'Manual', NULL, NULL, NULL, NULL, NULL, NULL, 'Ausente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(35, 4, NULL, '2026-02-26', '08:10:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(36, 9, NULL, '2026-02-26', '08:00:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(37, 11, NULL, '2026-02-26', '08:00:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(38, 12, NULL, '2026-02-27', '08:05:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(39, 17, NULL, '2026-02-27', '08:00:00', 'Manual', NULL, NULL, NULL, NULL, NULL, NULL, 'Justificado', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(40, 29, NULL, '2026-02-27', '07:50:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(41, 4, NULL, '2026-03-02', '08:00:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(42, 9, NULL, '2026-03-02', '08:01:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(43, 11, NULL, '2026-03-02', '08:00:00', 'Manual', NULL, NULL, NULL, NULL, NULL, NULL, 'Ausente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(44, 15, NULL, '2026-03-02', '07:55:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(45, 12, NULL, '2026-03-03', '08:10:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(46, 17, NULL, '2026-03-03', '08:00:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(47, 23, NULL, '2026-03-03', '08:00:00', 'Manual', NULL, NULL, NULL, NULL, NULL, NULL, 'Justificado', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(48, 26, NULL, '2026-03-03', '07:50:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(49, 4, NULL, '2026-03-04', '08:00:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(50, 11, NULL, '2026-03-04', '08:02:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(51, 29, NULL, '2026-03-04', '08:00:00', 'Manual', NULL, NULL, NULL, NULL, NULL, NULL, 'Ausente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(52, 9, NULL, '2026-03-05', '08:00:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(53, 15, NULL, '2026-03-05', '07:58:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09'),
(54, 26, NULL, '2026-03-05', '08:05:00', 'Kiosco', NULL, NULL, NULL, NULL, NULL, NULL, 'Presente', '2026-03-06 02:25:09', '2026-03-06 02:25:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'Usuario que ejecuta la acción',
  `accion` varchar(100) NOT NULL COMMENT 'LOGIN, UPDATE_PROFILE, DELETE, FORMALIZE_INTERN, etc.',
  `tabla_afectada` varchar(50) DEFAULT NULL COMMENT 'Tabla modificada (opcional)',
  `registro_id` int(11) DEFAULT NULL COMMENT 'ID del registro afectado (opcional)',
  `ip_address` varchar(45) NOT NULL COMMENT 'IP del usuario',
  `user_agent` text DEFAULT NULL COMMENT 'Navegador/Dispositivo',
  `detalles` text DEFAULT NULL COMMENT 'JSON con datos adicionales',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de auditoría de todas las acciones críticas del sistema';

--
-- Volcado de datos para la tabla `bitacora`
--

INSERT INTO `bitacora` (`id`, `usuario_id`, `accion`, `tabla_afectada`, `registro_id`, `ip_address`, `user_agent`, `detalles`, `created_at`) VALUES
(1, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-01-31 07:03:08'),
(2, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-01-31 07:04:10'),
(3, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-01-31 07:04:19'),
(4, 9, 'UPDATE_PROFILE', 'datos_personales', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-02 01:58:52'),
(5, 9, 'UPDATE_PROFILE', 'datos_personales', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-02 01:59:19'),
(6, 9, 'UPDATE_PROFILE', 'datos_personales', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-02 02:01:14'),
(7, 9, 'UPDATE_PROFILE', 'datos_personales', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-02 02:01:44'),
(8, 1, 'UPDATE_SECURITY_QUESTIONS', 'usuarios_respuestas', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-02 02:06:02'),
(9, 9, 'UPDATE_PROFILE', 'datos_personales', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-02 03:20:49'),
(10, 9, 'UPDATE_PROFILE', 'datos_personales', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-02 03:30:18'),
(11, 15, 'UPDATE_PROFILE', 'datos_personales', 15, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-05 17:34:59'),
(12, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-11 17:33:07'),
(13, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-11 18:28:22'),
(14, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-13 23:24:23'),
(15, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-13 23:38:19'),
(16, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-14 00:50:57'),
(17, 8, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-14 00:51:15'),
(18, 8, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-14 00:57:32'),
(19, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-14 00:58:14'),
(20, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-14 00:58:25'),
(21, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 01:19:04'),
(22, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 01:19:35'),
(23, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 01:26:14'),
(24, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 01:28:14'),
(25, 9, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 01:28:26'),
(26, 9, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 01:29:02'),
(27, 9, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 01:37:39'),
(28, 9, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 01:38:08'),
(29, 8, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 01:38:20'),
(30, 8, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 01:38:55'),
(31, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 01:39:29'),
(32, 1, 'LOGIN', NULL, NULL, '192.168.0.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-16 16:32:16'),
(33, 1, 'LOGOUT', NULL, NULL, '192.168.0.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-16 16:32:50'),
(34, 1, 'LOGIN', NULL, NULL, '192.168.0.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-16 16:34:06'),
(35, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 16:34:25'),
(36, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 16:37:10'),
(37, 1, 'CREATE_USER', 'usuarios', NULL, '192.168.0.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"cedula\":\"30939460\",\"rol_id\":1,\"email\":\"catiremanuel170@gmail.com\"}', '2026-02-16 16:41:12'),
(38, 16, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 16:42:39'),
(39, 1, 'LOGOUT', NULL, NULL, '192.168.0.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-16 16:44:36'),
(40, 16, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 17:14:17'),
(41, 16, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 17:19:22'),
(42, 16, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 17:19:33'),
(43, 16, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 17:23:50'),
(44, 16, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-16 17:29:24'),
(45, 1, 'LOGIN', NULL, NULL, '192.168.0.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-16 18:43:25'),
(46, 1, 'LOGOUT', NULL, NULL, '192.168.0.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-16 18:44:26'),
(47, 1, 'LOGIN', NULL, NULL, '192.168.0.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-16 21:13:09'),
(48, 1, 'LOGOUT', NULL, NULL, '192.168.0.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, '2026-02-16 21:14:26'),
(49, 16, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-17 02:08:24'),
(50, 16, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-17 03:15:12'),
(51, 16, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-17 23:05:26'),
(52, 16, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-17 23:19:08'),
(53, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-17 23:22:41'),
(54, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-18 02:13:40'),
(56, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-18 18:43:32'),
(57, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-18 18:46:29'),
(58, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-18 19:04:02'),
(59, 1, 'LOGIN', NULL, NULL, '10.36.0.40', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-02-18 19:05:50'),
(60, 1, 'LOGOUT', NULL, NULL, '10.36.0.40', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-02-18 19:07:45'),
(61, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-18 19:29:03'),
(62, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-18 20:00:31'),
(63, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-18 22:51:17'),
(64, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-18 23:00:06'),
(65, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-18 23:00:45'),
(66, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-18 23:01:54'),
(67, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-18 23:03:54'),
(68, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-18 23:23:11'),
(69, 1, 'LOGIN', NULL, NULL, '192.168.0.104', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-02-19 00:21:58'),
(70, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 00:40:53'),
(71, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 00:41:23'),
(72, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 00:41:45'),
(73, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 00:57:34'),
(74, 1, 'DELETE_USER', 'usuarios', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"tipo\":\"soft_delete\"}', '2026-02-19 02:19:29'),
(75, 1, 'TOGGLE_USER_STATUS', 'usuarios', 16, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"nuevo_estado\":\"inactivo\"}', '2026-02-19 02:41:32'),
(76, 1, 'TOGGLE_USER_STATUS', 'usuarios', 16, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"nuevo_estado\":\"activo\"}', '2026-02-19 02:41:38'),
(77, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 14:34:45'),
(78, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 14:40:05'),
(79, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 14:44:03'),
(80, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 14:59:27'),
(81, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 14:59:56'),
(82, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 15:00:55'),
(83, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 15:02:13'),
(84, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-19 15:02:41'),
(85, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 15:11:23'),
(86, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 17:06:48'),
(87, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 17:09:49'),
(88, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 17:50:31'),
(89, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-19 17:52:03'),
(90, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 17:52:12'),
(91, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-19 22:55:54'),
(92, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-20 01:26:40'),
(93, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 00:55:57'),
(94, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 00:56:16'),
(95, 17, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 01:15:30'),
(96, 17, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 01:18:08'),
(97, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 01:18:22'),
(98, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 01:19:38'),
(99, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 02:35:05'),
(100, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 13:46:56'),
(101, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 14:59:17'),
(102, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 14:59:32'),
(103, 1, 'CREATE_USER', 'usuarios', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"cedula\":\"11729897\",\"rol_id\":3,\"email\":\"luisrafaelyanez@gmail.com\"}', '2026-02-21 15:18:41'),
(104, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 15:19:12'),
(107, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 15:50:59'),
(108, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"direccion\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-21 15:52:46'),
(109, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 15:52:54'),
(110, 1, 'LOGIN', NULL, NULL, '192.168.0.101', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-02-21 16:36:02'),
(111, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 16:36:45'),
(112, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 16:44:12'),
(113, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 16:53:48'),
(114, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 17:05:47'),
(115, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 17:51:00'),
(116, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 17:57:17'),
(117, 19, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 17:59:34'),
(118, 19, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 18:00:07'),
(119, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 18:00:16'),
(120, 1, 'TOGGLE_USER_STATUS', 'usuarios', 19, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"nuevo_estado\":\"inactivo\"}', '2026-02-21 18:00:39'),
(121, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 18:00:53'),
(123, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 19:23:02'),
(124, 1, 'TOGGLE_USER_STATUS', 'usuarios', 19, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"nuevo_estado\":\"activo\"}', '2026-02-21 19:24:17'),
(125, 1, 'UPDATE_USER', 'usuarios', 19, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"email\",\"rol_id\",\"departamento_id\",\"cedula\",\"datos_personales\"]}', '2026-02-21 19:25:57'),
(126, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 19:26:06'),
(127, 19, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 19:26:52'),
(128, 19, 'CREATE_USER', 'usuarios', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"cedula\":\"12345676\",\"rol_id\":1,\"email\":\"luciayanez@gmail.com\"}', '2026-02-21 19:40:28'),
(129, 19, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 19:40:53'),
(130, 21, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 19:41:25'),
(131, 21, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 20:18:17'),
(132, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 20:18:39'),
(133, 1, 'CREATE_USER', 'usuarios', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"cedula\":\"22334455\",\"rol_id\":3,\"email\":\"luciana@gmail.com\"}', '2026-02-21 20:25:10'),
(134, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 20:25:19'),
(137, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 21:17:31'),
(138, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 21:17:45'),
(143, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 23:43:02'),
(144, 1, 'CREATE_USER', 'usuarios', 23, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"cedula\":\"15020928\",\"rol_id\":3,\"email\":\"yarimar.79@gmail.com\",\"nombre_completo\":\"yarimar prieto\"}', '2026-02-21 23:44:21'),
(145, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 23:44:49'),
(146, 23, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 23:45:21'),
(147, 23, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-22 03:30:30'),
(148, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-22 03:30:37'),
(149, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-22 05:17:36'),
(155, 1, 'LOGIN', NULL, NULL, '192.168.0.101', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-02-22 21:58:47'),
(156, 1, 'LOGOUT', NULL, NULL, '192.168.0.101', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-02-22 22:01:35'),
(157, 1, 'LOGIN', NULL, NULL, '192.168.0.101', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-02-22 23:01:00'),
(158, 1, 'LOGOUT', NULL, NULL, '192.168.0.101', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-02-22 23:10:09'),
(159, 1, 'LOGIN', NULL, NULL, '192.168.0.101', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-02-22 23:57:23'),
(160, 1, 'LOGOUT', NULL, NULL, '192.168.0.101', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-02-22 23:59:50'),
(161, 1, 'LOGIN', NULL, NULL, '192.168.0.101', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-02-23 00:00:27'),
(162, 1, 'CREATE_USER', 'usuarios', 26, '192.168.0.101', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '{\"cedula\":\"28694068\",\"rol_id\":3,\"email\":\"isabelgutierrez@gmail.com\",\"nombre_completo\":\"Isabel Gutierrez\"}', '2026-02-23 00:01:43'),
(163, 1, 'LOGOUT', NULL, NULL, '192.168.0.101', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-02-23 00:03:29'),
(164, 26, 'LOGIN', NULL, NULL, '192.168.0.101', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-02-23 00:04:04'),
(165, 26, 'LOGOUT', NULL, NULL, '192.168.0.101', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-02-23 00:08:25'),
(166, 1, 'LOGIN', NULL, NULL, '192.168.0.101', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-02-23 00:10:25'),
(167, 1, 'TOGGLE_USER_STATUS', 'usuarios', 26, '192.168.0.101', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"nuevo_estado\":\"inactivo\"}', '2026-02-23 00:34:55'),
(168, 1, 'TOGGLE_USER_STATUS', 'usuarios', 26, '192.168.0.101', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"nuevo_estado\":\"activo\"}', '2026-02-23 00:35:24'),
(171, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 13:22:00'),
(172, 1, 'RESET_PASSWORD', 'usuarios', 27, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"motivo\":\"Solicitud admin\"}', '2026-02-23 13:23:47'),
(173, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 13:24:33'),
(175, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 13:36:01'),
(176, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 16:28:10'),
(177, 1, 'RESET_PASSWORD', 'usuarios', 27, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"motivo\":\"Solicitud admin\"}', '2026-02-23 16:28:38'),
(178, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 16:30:28'),
(181, 8, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 16:32:17'),
(182, 8, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 16:32:56'),
(183, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 16:57:45'),
(184, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 17:14:47'),
(185, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 17:16:00'),
(186, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 17:18:58'),
(187, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 17:20:00'),
(188, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 17:20:40'),
(189, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 17:30:20'),
(190, 1, 'UPDATE_USER', 'usuarios', 26, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"email\",\"rol_id\",\"departamento_id\",\"cedula\",\"datos_personales\"]}', '2026-02-23 17:30:29'),
(191, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 17:36:26'),
(192, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 17:39:04'),
(193, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 17:42:58'),
(194, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 18:12:28'),
(195, 1, 'UPDATE_USER', 'usuarios', 26, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"email\",\"rol_id\",\"departamento_id\",\"cedula\",\"datos_personales\"]}', '2026-02-23 18:12:53'),
(196, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 18:14:31'),
(197, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 18:29:45'),
(198, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 20:50:04'),
(199, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 20:50:11'),
(200, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 22:43:11'),
(201, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 23:24:01'),
(202, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-23 23:24:35'),
(203, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-24 17:13:13'),
(204, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-24 18:04:30'),
(205, 1, 'CREATE_USER', 'usuarios', 28, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"cedula\":\"28995588\",\"rol_id\":1,\"email\":\"carlos@gmail.com\",\"nombre_completo\":\"carlos garcia\"}', '2026-02-24 18:05:30'),
(206, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-24 18:06:00'),
(207, 28, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-24 18:06:27'),
(208, 28, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-24 18:14:58'),
(209, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-25 01:42:52'),
(210, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-25 02:41:12'),
(211, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-25 02:43:17'),
(212, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-25 02:53:12'),
(213, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-25 02:54:02'),
(214, 1, 'RESET_PIN', 'Se reseteó el PIN del pasante ID: 4', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-25 03:08:44'),
(215, 1, 'RESET_PIN', 'Se reseteó el PIN del pasante ID: 26', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-25 03:10:55'),
(216, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-25 03:13:04'),
(217, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-25 22:38:39'),
(218, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-26 00:27:52'),
(219, 17, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-26 00:28:20'),
(220, 17, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-26 01:32:47'),
(221, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-26 01:33:06'),
(222, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-26 01:43:11'),
(223, 1, 'TOGGLE_USER_STATUS', 'usuarios', 28, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"nuevo_estado\":\"inactivo\"}', '2026-02-26 02:02:08'),
(224, 1, 'TOGGLE_USER_STATUS', 'usuarios', 28, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"nuevo_estado\":\"activo\"}', '2026-02-26 02:02:14'),
(225, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-27 21:37:56'),
(226, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-27 21:50:59'),
(227, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-27 22:13:02'),
(228, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-27 22:36:15'),
(229, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-27 22:36:44'),
(230, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-27 22:37:48'),
(231, 1, 'UPDATE_USER', 'usuarios', 28, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"email\",\"rol_id\",\"departamento_id\",\"cedula\",\"datos_personales\"]}', '2026-02-27 22:37:58'),
(232, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 00:36:28'),
(233, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 00:44:07'),
(234, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 00:45:44'),
(235, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 01:28:00'),
(236, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 01:28:08'),
(237, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:28:34'),
(238, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 01:28:41'),
(239, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 01:32:10'),
(240, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:32:26'),
(241, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 01:35:48'),
(242, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 01:36:33'),
(243, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 01:37:27'),
(244, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:34'),
(245, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:35'),
(246, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:39'),
(247, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:40'),
(248, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:40'),
(249, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:40'),
(250, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:40'),
(251, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:44'),
(252, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:44'),
(253, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:44'),
(254, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:45'),
(255, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:45'),
(256, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:45'),
(257, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:45'),
(258, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:45'),
(259, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:45'),
(260, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:46');
INSERT INTO `bitacora` (`id`, `usuario_id`, `accion`, `tabla_afectada`, `registro_id`, `ip_address`, `user_agent`, `detalles`, `created_at`) VALUES
(261, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:46'),
(262, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:54'),
(263, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:58'),
(264, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:58'),
(265, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:58'),
(266, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:58'),
(267, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:59'),
(268, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:59'),
(269, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:59'),
(270, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:59'),
(271, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:59'),
(272, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:45:59'),
(273, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:46:06'),
(274, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:46:06'),
(275, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:46:06'),
(276, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:46:06'),
(277, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:48:58'),
(278, 1, 'UPDATE_PROFILE', 'datos_personales', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"campos_actualizados\":[\"nombres\",\"apellidos\",\"telefono\",\"genero\",\"fecha_nacimiento\",\"cargo\"]}', '2026-02-28 01:49:03'),
(279, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 03:23:53'),
(280, 1, 'RESET_PIN', 'Se reseteó el PIN del pasante ID: 4', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 04:33:06'),
(281, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 14:18:39'),
(282, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 14:37:11'),
(283, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 14:37:45'),
(284, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 14:48:29'),
(285, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 15:14:02'),
(286, 1, 'RESET_PIN', 'Se reseteó el PIN del pasante ID: 23', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 15:15:35'),
(287, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 15:16:33'),
(288, 4, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 15:19:00'),
(289, 4, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 15:19:12'),
(290, 13, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 15:19:27'),
(291, 13, 'RESET_PASSWORD', 'usuarios', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"motivo\":\"Solicitud admin\"}', '2026-02-28 15:20:52'),
(292, 13, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 15:20:57'),
(293, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 15:21:23'),
(294, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 15:23:23'),
(295, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 15:23:32'),
(296, 1, 'RESET_PIN', 'Se reseteó el PIN del pasante ID: 4', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 17:05:31'),
(297, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 18:37:01'),
(298, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 18:38:26'),
(299, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 19:15:41'),
(300, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 19:17:08'),
(301, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 19:18:55'),
(302, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 19:19:09'),
(303, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 19:19:48'),
(304, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 19:23:08'),
(305, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 19:23:17'),
(306, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 19:29:32'),
(307, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 19:31:30'),
(308, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 19:31:46'),
(309, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 19:33:02'),
(310, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 19:34:03'),
(311, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 19:36:42'),
(312, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 19:39:26'),
(313, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 19:56:41'),
(314, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 20:02:45'),
(315, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 20:03:02'),
(316, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 20:03:22'),
(317, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-28 20:33:02'),
(318, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-01 14:31:52'),
(319, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-01 14:57:27'),
(320, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-01 14:57:34'),
(321, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-01 14:57:40'),
(322, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-01 14:57:45'),
(323, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-01 14:57:57'),
(324, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-01 14:58:04'),
(325, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-01 14:58:17'),
(326, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-01 14:58:57'),
(327, 9, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-01 14:59:42'),
(328, 9, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-01 15:00:01'),
(329, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-01 15:00:36'),
(330, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-01 23:54:01'),
(331, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 00:03:01'),
(332, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 00:03:19'),
(333, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 00:03:58'),
(334, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 00:06:11'),
(335, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 00:06:39'),
(336, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 00:07:13'),
(337, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 00:07:43'),
(338, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 00:12:58'),
(339, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 00:13:13'),
(340, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 00:20:06'),
(341, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 00:20:37'),
(342, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 00:45:33'),
(343, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 00:45:53'),
(344, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 00:46:21'),
(345, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 01:23:51'),
(346, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 01:24:09'),
(347, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 01:24:19'),
(348, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 01:45:57'),
(349, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 02:02:56'),
(351, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 02:04:08'),
(352, 1, 'RESET_PIN', 'Se reseteó el PIN del pasante ID: 4', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 02:04:50'),
(353, 1, 'CHANGE_PASANTE_STATUS', 'Pasante ID 9 → Estado: Pendiente', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 03:14:03'),
(354, 1, 'CHANGE_PASANTE_STATUS', 'Pasante ID 9 → Estado: Activo', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 03:14:09'),
(355, 1, 'CHANGE_PASANTE_STATUS', 'Pasante ID 15 → Estado: Activo', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 03:17:10'),
(356, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 12:38:53'),
(357, 1, 'RESET_PIN', 'Se reseteó el PIN del pasante ID: 4', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 12:44:02'),
(358, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 13:13:01'),
(359, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 13:13:53'),
(360, 1, 'RESET_PIN', 'Se reseteó el PIN del pasante ID: 4', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 13:16:18'),
(361, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 13:38:40'),
(362, 29, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 13:43:30'),
(363, 29, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 13:45:35'),
(364, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 13:45:59'),
(365, 1, 'CHANGE_PASANTE_STATUS', 'Pasante ID 29 → Estado: Activo', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 13:48:40'),
(366, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 18:13:59'),
(367, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-02 18:20:48'),
(368, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-03 01:06:12'),
(369, 1, 'RESET_PIN', 'Se reseteó el PIN del pasante ID: 12', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-03 01:08:48'),
(370, 1, 'CHANGE_PASANTE_STATUS', 'Pasante ID 12 → Estado: Activo', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-03 01:13:16'),
(371, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-03 01:15:48'),
(372, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-03 01:50:01'),
(373, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-03 16:10:46'),
(374, 1, 'RESET_PIN', 'Se reseteó el PIN del pasante ID: 4', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-03 16:13:38'),
(375, 1, 'RESET_PIN', 'Se reseteó el PIN del pasante ID: 29', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-03 16:18:51'),
(376, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-03 16:21:32'),
(377, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-04 02:00:45'),
(379, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-04 02:21:15'),
(380, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-04 02:22:55'),
(382, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-04 17:32:14'),
(383, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-04 17:35:17'),
(384, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-04 17:36:34'),
(385, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-04 17:36:48'),
(386, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-04 17:37:21'),
(387, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-04 17:38:40'),
(388, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-04 17:39:26'),
(389, 1, 'RESET_PIN', 'Se reseteó el PIN del pasante ID: 29', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-04 17:39:51'),
(390, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-04 17:42:02'),
(392, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-05 19:20:01'),
(393, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-05 21:40:29'),
(394, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-05 23:21:50'),
(395, 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-03-06 10:14:39'),
(396, 1, 'TOGGLE_USER_STATUS', 'usuarios', 29, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"nuevo_estado\":\"inactivo\"}', '2026-03-06 11:40:37'),
(397, 1, 'TOGGLE_USER_STATUS', 'usuarios', 29, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '{\"nuevo_estado\":\"activo\"}', '2026-03-06 11:40:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos_pasante`
--

CREATE TABLE `datos_pasante` (
  `id` int(10) UNSIGNED NOT NULL,
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
  `tutor_id` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL COMMENT 'Notas adicionales'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `datos_pasante`
--

INSERT INTO `datos_pasante` (`id`, `usuario_id`, `institucion_procedencia`, `created_at`, `updated_at`, `estado_pasantia`, `fecha_inicio_pasantia`, `fecha_fin_estimada`, `horas_acumuladas`, `horas_meta`, `departamento_asignado_id`, `tutor_id`, `observaciones`) VALUES
(1, 9, 'moral y luces', '2026-02-02 03:30:18', '2026-03-06 02:25:09', 'Activo', NULL, NULL, 0, 240, 1, NULL, NULL),
(2, 15, 'jdhdhd', '2026-02-05 17:34:59', '2026-03-06 02:25:09', 'Activo', NULL, NULL, 0, 240, 2, NULL, NULL),
(5, 23, '2', '2026-02-21 23:47:49', '2026-03-06 02:25:09', '', NULL, NULL, 0, 1440, 3, NULL, NULL),
(9, 26, '2', '2026-02-23 00:07:19', '2026-03-03 16:17:47', 'Activo', '2026-02-24', '2026-11-03', 16, 1440, 4, 8, NULL),
(11, 11, '', '2026-02-25 02:11:49', '2026-02-25 02:11:49', 'Pendiente', '2026-02-24', '2026-11-03', 0, 1440, 2, 8, NULL),
(12, 4, '', '2026-02-25 02:55:43', '2026-03-06 02:25:09', 'Activo', '2026-02-24', '2026-11-03', 40, 1440, 1, 8, NULL),
(14, 17, '2', '2026-02-26 00:29:00', '2026-03-06 02:25:09', '', NULL, NULL, 0, 1440, 3, NULL, NULL),
(15, 12, '', '2026-03-02 13:36:47', '2026-03-03 01:13:52', 'Activo', '2026-03-03', '2026-11-10', 8, 1440, 4, 8, NULL),
(16, 29, '1', '2026-03-02 13:45:20', '2026-03-06 02:25:09', 'Activo', NULL, NULL, 8, 1440, 4, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos_personales`
--

CREATE TABLE `datos_personales` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `genero` enum('M','F','Otro') DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `datos_personales`
--

INSERT INTO `datos_personales` (`id`, `usuario_id`, `nombres`, `apellidos`, `cargo`, `telefono`, `genero`, `fecha_nacimiento`, `created_at`, `updated_at`) VALUES
(1, 1, 'Administrador', 'del Sistema', NULL, '0414-6785600', 'M', '2012-02-19', '2026-01-10 00:02:19', '2026-02-28 15:23:16'),
(2, 2, 'Carlos', 'Tutor', NULL, '0414-1234567', 'M', NULL, '2026-01-10 00:02:19', '2026-01-10 00:02:19'),
(5, 4, 'jose luis', 'gomezlo', NULL, '04148965723', 'M', '2000-06-10', '2026-01-10 05:27:05', '2026-02-21 20:02:59'),
(6, 5, 'Yurimar', 'Del Carmen', NULL, '0000000000', 'M', '2000-01-01', '2026-01-11 22:25:54', '2026-02-21 20:02:59'),
(8, 8, 'petra', 'prieto', NULL, '04241234567', 'F', '2014-01-06', '2026-01-14 05:18:55', '2026-02-21 20:02:59'),
(9, 9, 'albert', 'rodriguez', NULL, '04148965723', 'M', '2000-01-01', '2026-01-17 12:25:12', '2026-02-21 20:02:59'),
(11, 11, 'Yarima', 'Del Carmen', NULL, NULL, NULL, NULL, '2026-01-30 23:31:18', '2026-01-30 23:31:18'),
(12, 12, 'maria', 'yepez', NULL, NULL, NULL, NULL, '2026-02-01 05:12:46', '2026-02-01 05:12:46'),
(13, 13, 'delmary', 'Nuevo', NULL, '0414-6785666', 'F', '2011-10-05', '2026-02-01 20:07:11', '2026-02-28 15:20:06'),
(14, 15, 'Albert', 'Rodriguez', NULL, '04148965723', 'M', '2026-02-05', '2026-02-05 17:28:20', '2026-02-21 20:01:46'),
(15, 17, 'gabriel', 'prieto', NULL, '04164445678', 'M', '2004-02-19', '2026-02-21 01:15:05', '2026-02-26 00:29:00'),
(16, 19, 'gabrielucho', 'prieto', NULL, NULL, NULL, NULL, '2026-02-21 17:58:49', '2026-02-21 17:58:49'),
(17, 21, 'lucia', 'yanez', 'asistente', '04148965728', '', '2010-02-01', '2026-02-21 19:44:44', '2026-02-21 19:44:44'),
(19, 23, 'yarimar', 'prieto', NULL, '04128729021', 'M', '2012-02-22', '2026-02-21 23:44:21', '2026-02-21 23:47:49'),
(24, 26, 'Isabel', 'Gutierrez', NULL, '04160904308', 'F', '2002-10-07', '2026-02-23 00:01:43', '2026-02-23 18:12:53'),
(28, 28, 'carlo', 'garcia', NULL, '04134445678', 'M', '2012-02-08', '2026-02-24 18:05:30', '2026-02-27 22:37:58'),
(34, 29, 'wilfredo', 'rivas', NULL, '04164421182', 'M', '2012-03-02', '2026-03-02 13:43:04', '2026-03-02 13:45:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `departamentos`
--

INSERT INTO `departamentos` (`id`, `nombre`, `descripcion`, `activo`, `created_at`) VALUES
(1, 'Soporte Técnico', 'Mantenimiento preventivo y correctivo de equipos de computación.', 1, '2026-02-19 14:54:35'),
(2, 'Redes y Telecomunicaciones', 'Gestión de infraestructura de red, servidores y conectividad.', 1, '2026-02-19 14:54:35'),
(3, 'Reparaciones Electrónicas', 'Diagnóstico avanzado y reparación de componentes electrónicos.', 1, '2026-02-19 14:54:35'),
(4, 'Atención al Usuario', 'Recepción de equipos y gestión de solicitudes de servicio.', 1, '2026-02-19 14:54:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluaciones`
--

CREATE TABLE `evaluaciones` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instituciones`
--

CREATE TABLE `instituciones` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `ubicacion` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo` enum('Escuela Técnica','Universidad') DEFAULT 'Escuela Técnica',
  `direccion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `instituciones`
--

INSERT INTO `instituciones` (`id`, `nombre`, `ubicacion`, `created_at`, `tipo`, `direccion`) VALUES
(1, 'E.T.C Juan Bautista González', NULL, '2026-02-19 14:54:35', 'Escuela Técnica', '3FJH+2F8, Cdad. Bolívar 8001, Bolívar'),
(2, 'Colegio Fe y Alegría \"José María Vélaz\"', NULL, '2026-02-19 14:54:35', 'Escuela Técnica', '3F7C+XPR, Cdad. Bolívar 8001, Bolívar'),
(5, 'E.T. Luis Maria Olaso \"Fe y Alegria\"', NULL, '2026-02-26 03:40:26', 'Escuela Técnica', 'Dirección: 3CCP+2FJ, Cdad. Bolívar 8001, Bolívar');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intentos_acceso`
--

CREATE TABLE `intentos_acceso` (
  `id` int(10) UNSIGNED NOT NULL,
  `direccion_ip` varchar(45) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `intentos` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ultimo_intento` datetime NOT NULL DEFAULT current_timestamp(),
  `bloqueado_hasta` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL DEFAULT 'info',
  `titulo` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `url` varchar(500) DEFAULT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_id`, `tipo`, `titulo`, `mensaje`, `url`, `leida`, `created_at`) VALUES
(1, 1, 'success', '¡Sistema de Notificaciones Activado!', 'El sistema de notificaciones ha sido configurado correctamente. Ahora recibirás actualizaciones importantes del sistema.', NULL, 1, '2026-02-08 16:44:55'),
(2, 1, 'info', 'Bienvenido al SGP', 'Gracias por usar el Sistema de Gestión de Pasantes. Explora todas las funcionalidades disponibles.', '/dashboard', 1, '2026-02-08 16:44:55'),
(3, 1, 'warning', 'Actualiza tu perfil', 'Completa tu información de perfil para aprovechar todas las funcionalidades del sistema.', '/perfil/ver', 1, '2026-02-08 16:44:55'),
(4, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 1, '2026-02-28 04:31:31'),
(5, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:31:31'),
(6, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:31:31'),
(7, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:31:31'),
(8, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:31:31'),
(9, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:31:31'),
(10, 8, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:31:31'),
(11, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 1, '2026-02-28 04:31:57'),
(12, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:31:57'),
(13, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:31:57'),
(14, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:31:57'),
(15, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:31:57'),
(16, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:31:57'),
(17, 8, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:31:57'),
(18, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 1, '2026-02-28 04:35:22'),
(19, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:35:22'),
(20, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:35:22'),
(21, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:35:22'),
(22, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:35:22'),
(23, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:35:22'),
(24, 8, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:35:22'),
(25, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 1, '2026-02-28 04:35:47'),
(26, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:35:47'),
(27, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:35:47'),
(28, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:35:47'),
(29, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:35:47'),
(30, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:35:47'),
(31, 8, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:35:47'),
(32, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 1, '2026-02-28 04:39:21'),
(33, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:39:21'),
(34, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:39:21'),
(35, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:39:21'),
(36, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:39:21'),
(37, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:39:21'),
(38, 8, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:39:21'),
(39, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 1, '2026-02-28 04:39:27'),
(40, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:39:27'),
(41, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:39:27'),
(42, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:39:27'),
(43, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:39:27'),
(44, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:39:27'),
(45, 8, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:39:27'),
(46, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 1, '2026-02-28 04:40:03'),
(47, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:40:03'),
(48, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:40:03'),
(49, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:40:03'),
(50, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:40:03'),
(51, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:40:03'),
(52, 8, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:40:03'),
(53, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 1, '2026-02-28 04:41:14'),
(54, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:41:14'),
(55, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:41:14'),
(56, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:41:14'),
(57, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:41:14'),
(58, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:41:14'),
(59, 8, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:41:14'),
(60, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 1, '2026-02-28 04:49:49'),
(61, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:49:49'),
(62, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:49:49'),
(63, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:49:49'),
(64, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:49:49'),
(65, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:49:49'),
(66, 8, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:49:49'),
(67, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 1, '2026-02-28 04:52:49'),
(68, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:52:49'),
(69, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:52:49'),
(70, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:52:49'),
(71, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:52:49'),
(72, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:52:49'),
(73, 8, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:52:49'),
(74, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 1, '2026-02-28 04:57:48'),
(75, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:57:48'),
(76, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:57:48'),
(77, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:57:48'),
(78, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:57:48'),
(79, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:57:48'),
(80, 8, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 04:57:48'),
(81, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 1, '2026-02-28 16:56:10'),
(82, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 16:56:10'),
(83, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 16:56:10'),
(84, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 16:56:10'),
(85, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 16:56:10'),
(86, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 16:56:10'),
(87, 8, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 16:56:10'),
(88, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 1, '2026-02-28 17:04:41'),
(89, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 17:04:41'),
(90, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 17:04:41'),
(91, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 17:04:41'),
(92, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 17:04:41'),
(93, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 17:04:41'),
(94, 8, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-02-28 17:04:41'),
(95, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 1, '2026-03-02 02:03:10'),
(96, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-03-02 02:03:10'),
(97, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-03-02 02:03:10'),
(98, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-03-02 02:03:10'),
(99, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-03-02 02:03:10'),
(100, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-03-02 02:03:10'),
(101, 8, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/pasantes', 0, '2026-03-02 02:03:10'),
(102, 9, 'cambio_estado', 'Actualización de Estado', 'El estado de tu pasantía ha cambiado a: Pendiente.', 'http://localhost/proyecto_sgp/sgp/public/perfil', 0, '2026-03-02 03:14:03'),
(103, 9, 'cambio_estado', 'Actualización de Estado', 'El estado de tu pasantía ha cambiado a: Activo.', 'http://localhost/proyecto_sgp/sgp/public/perfil', 0, '2026-03-02 03:14:09'),
(104, 15, 'cambio_estado', 'Actualización de Estado', 'El estado de tu pasantía ha cambiado a: Activo.', 'http://localhost/proyecto_sgp/sgp/public/perfil', 0, '2026-03-02 03:17:10'),
(105, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 1, '2026-03-02 12:45:08'),
(106, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 12:45:08'),
(107, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 12:45:08'),
(108, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 12:45:08'),
(109, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 12:45:08'),
(110, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 12:45:08'),
(111, 8, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 12:45:08'),
(112, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 1, '2026-03-02 13:15:24'),
(113, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 13:15:24'),
(114, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 13:15:24'),
(115, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 13:15:24'),
(116, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 13:15:24'),
(117, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 13:15:24'),
(118, 8, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante jose luis gomezlo (V-30342975) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 13:15:24'),
(119, 12, 'asignacion_nueva', 'Nueva Asignación de Pasantía', 'Has sido asignado a Atención al Usuario. Fecha de inicio: 2026-03-02.', 'http://localhost/proyecto_sgp/sgp/public/perfil', 0, '2026-03-02 13:36:47'),
(120, 29, 'cambio_estado', 'Actualización de Estado', 'El estado de tu pasantía ha cambiado a: Activo.', 'http://localhost/proyecto_sgp/sgp/public/perfil', 0, '2026-03-02 13:48:40'),
(121, 1, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante wilfredo rivas (V-30587335) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 1, '2026-03-02 13:50:23'),
(122, 13, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante wilfredo rivas (V-30587335) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 13:50:23'),
(123, 16, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante wilfredo rivas (V-30587335) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 13:50:23'),
(124, 19, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante wilfredo rivas (V-30587335) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 13:50:23'),
(125, 21, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante wilfredo rivas (V-30587335) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 13:50:23'),
(126, 28, 'solicitud_pin', 'Solicitud de reseteo de PIN', 'El pasante wilfredo rivas (V-30587335) ha olvidado su PIN y solicita un reseteo.', 'http://localhost/proyecto_sgp/sgp/public/configuracion#restablecer-pin', 0, '2026-03-02 13:50:23'),
(127, 12, 'asignacion_nueva', 'Nueva Asignación de Pasantía', 'Has sido asignado a Atención al Usuario. Fecha de inicio: 2026-03-03.', 'http://localhost/proyecto_sgp/sgp/public/perfil', 0, '2026-03-03 01:12:03'),
(128, 12, 'cambio_estado', 'Actualización de Estado', 'El estado de tu pasantía ha cambiado a: Activo.', 'http://localhost/proyecto_sgp/sgp/public/perfil', 0, '2026-03-03 01:13:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_seguridad`
--

CREATE TABLE `preguntas_seguridad` (
  `id` int(11) NOT NULL,
  `pregunta` text NOT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `preguntas_seguridad`
--

INSERT INTO `preguntas_seguridad` (`id`, `pregunta`, `activa`, `created_at`) VALUES
(1, 'Cual es tu postre favorito?', 1, '2026-01-10 00:02:19'),
(2, 'Cual es tu color favorito?', 1, '2026-01-10 00:02:19'),
(3, 'Cual es el nombre de tu mascota?', 1, '2026-01-10 00:02:19'),
(4, '¿Cuál es el nombre de tu padre?', 1, '2026-01-30 05:02:15'),
(5, '¿En qué ciudad se conocieron tus padres?', 1, '2026-01-30 05:02:15'),
(6, '¿Cuál fue el nombre de tu primera escuela?', 1, '2026-01-30 05:02:15'),
(7, '¿Cuál es el nombre de tu madre?', 1, '2026-01-30 05:02:15'),
(8, '¿Cuál es tu personaje histórico favorito?', 1, '2026-01-30 05:02:15'),
(9, '¿Cuál es la marca de tu primer vehículo?', 1, '2026-01-30 05:02:15'),
(10, '¿Cuál es tu comida favorita?', 1, '2026-01-30 05:02:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `created_at`) VALUES
(1, 'Administrador', 'Acceso total al sistema', '2026-01-10 00:02:19'),
(2, 'Tutor', 'Supervisor de pasantes', '2026-01-10 00:02:19'),
(3, 'Pasante', 'Usuario b??sico del sistema', '2026-01-10 00:02:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `cedula`, `correo`, `password`, `pin_asistencia`, `rol_id`, `departamento_id`, `avatar`, `requiere_cambio_clave`, `estado`, `created_at`, `updated_at`) VALUES
(1, '12345678', 'admin@sgp.local', '$2y$10$pHGl1zzjkxGnBF1urDNWveLNCYPWWoTudM5TSHN.YMSQ9EZwaRBAK', NULL, 1, 1, 'default.png', 0, 'activo', '2026-01-10 00:02:19', '2026-03-23 23:24:24'),
(2, '30342971', 'tutor@sgp.local', '$2y$10$TxrLZz9BSpdfUFZSH127qu6h0c/GW83ujwnnA4DqEkEvMjyrHCw2S', NULL, 2, NULL, 'default.png', 0, 'activo', '2026-01-10 00:02:19', '2026-02-21 19:22:00'),
(4, '30342975', 'luisgabrielyanez9@gmail.com', '$2y$10$eC6xnVcpemReNhypJiV8Hu1bIvwhxuBjChsVAm5PCJkqTNyvpVEVK', '$2y$10$UsJj1iAG/xe7WYfLEruJn.F4H3gPkqH5B4nVbHwVDEh/Hd/LWormW', 3, 1, 'default.png', 0, 'activo', '2026-01-10 05:18:37', '2026-03-23 23:23:48'),
(5, '30342972', 'yurimarprieto.79@gmail.com', '$2y$10$Ga.8N.cmbEnxUIO08Jc1OupJT/HVvcJXI63.lEGk3G3JQcIk4PP76', NULL, 3, NULL, 'default.png', 0, 'inactivo', '2026-01-11 22:25:54', '2026-02-21 19:22:00'),
(8, '30342973', 'petra.79@GMAIL.COM', '$2y$10$x3mj9u/wybL.KxEGbcy9reLgLq0eDbAktp2Icso7DT4lDnJ5dUspO', NULL, 2, NULL, 'default.png', 0, 'activo', '2026-01-14 04:21:10', '2026-02-21 19:22:00'),
(9, '30342974', 'albert@gmail.com', '$2y$10$xQg8s9puOmRpvfAqCkFWPOQKaYRTqKOdI/NYRGYCYEnKhWP5Dq4qu', NULL, 3, 1, 'default.png', 0, 'activo', '2026-01-17 12:25:11', '2026-03-06 02:25:09'),
(11, '30342976', 'yurima@sgp.local', '$2y$10$QrTasd0TeXvkXbGpgKeROOvyn3WeHc6ejV6gSyLWig1NtsUW2AMwe', NULL, 3, 2, 'default.png', 0, 'activo', '2026-01-30 23:31:18', '2026-03-06 02:25:09'),
(12, '30342977', 'mariayepez@sgp.local', '$2y$10$EgRnlUwlJIIcQXCDAQeYQOi/yj/OdntzLpoduHAO0/SjgUtliaIb.', '$2y$10$82/hv7k3aSCwtcdAqPMyd.61orOKLsr4cmb7wqY1s0QLS0ZptNRpG', 3, 4, 'default.png', 0, 'activo', '2026-02-01 05:12:46', '2026-03-06 02:25:09'),
(13, '303429758', 'delmaryguzman@gmail.com', NULL, NULL, 1, 3, 'default.png', 0, 'activo', '2026-02-01 20:07:11', '2026-02-28 15:20:06'),
(15, '30342979', 'albertr@gmail.commm', '$2y$10$UEL4uEPCMpzZNdZVlmHJzeOiGKW7EsDJNcAAXnWWlULTdLBON27l6', NULL, 3, 2, 'default.png', 0, 'activo', '2026-02-05 17:28:20', '2026-03-06 02:25:09'),
(16, '31341971', 'catiremanuel170@gmail.com', '$2y$10$DbKDoFu5uCYFV6IS6hb7K.0V/Sc7VK44STCxb0662ekKGTD1yNf.2', NULL, 1, NULL, 'default.png', 0, 'activo', '2026-02-16 16:41:12', '2026-02-21 19:22:00'),
(17, '31342972', 'gabriel@sgp.local', '$2y$10$WBiy3Ht2kZVph6HN6G44z.yrhifxZp6dJzhTyWiOPVPK6ApUVqf0W', '2020', 3, 3, 'default.png', 0, 'activo', '2026-02-21 01:15:04', '2026-03-06 02:25:09'),
(19, '12345677', 'gabrielucho@gmail.com', '$2y$10$7oyfFcb5L8x0Zbi.d7xaLODkzJTpSnsvBdDY0/uJryIiQ/9gqDMzi', NULL, 1, NULL, 'default.png', 0, 'activo', '2026-02-21 17:58:48', '2026-02-21 19:25:57'),
(21, '12345676', 'luciayanez@gmail.com', '$2y$10$yCayWuRfNhxVN32aIDqm2eFpKjYw.H3W8yQI/LovotMTX49UVCQie', NULL, 1, 1, 'default.png', 0, 'activo', '2026-02-21 19:40:28', '2026-02-21 19:44:44'),
(23, '15020928', 'yarimar.79@gmail.com', '$2y$10$UliJoserAEXjticcnhqwmO1jZ22tZ4Wp6CHHFYooGacap26r352ja', '4301', 3, 3, 'default.png', 0, 'activo', '2026-02-21 23:44:21', '2026-03-06 02:25:09'),
(26, '28694068', 'isabelgutierrez@gmail.com', '$2y$10$qA0DWToBZFFdKui75QQPvu/lkQqJbrnsFn5vfp8Sjr5QM6tRZ5oaO', '1508', 3, 4, 'default.png', 0, 'activo', '2026-02-23 00:01:43', '2026-03-06 02:25:09'),
(28, '28995588', 'carlos@gmail.com', '$2y$10$OzkjBNlLzOkSr7xerUwta.Yogz4OcSvCfOqRs1..o6/iA.ZY/ONLW', NULL, 1, NULL, 'default.png', 0, 'activo', '2026-02-24 18:05:30', '2026-02-27 22:37:58'),
(29, '30587335', 'wilfredo2004@gmail.com', '$2y$10$Xrvyh873CnsNme0w.f935e81rVt65rSsLySGQe6skOqTng6sG76Oe', '$2y$10$XOUDGnmfgWpM.2Abpp7EH.BolS7jA6.qtSwKKe9hqnTLTHaHuMf5.', 3, 4, 'default.png', 0, 'activo', '2026-03-02 13:43:03', '2026-03-06 11:40:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_respuestas`
--

CREATE TABLE `usuarios_respuestas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `respuesta_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios_respuestas`
--

INSERT INTO `usuarios_respuestas` (`id`, `usuario_id`, `pregunta_id`, `respuesta_hash`, `created_at`) VALUES
(1, 4, 3, '$2y$10$xFGprh.0upQAJelJoXCJ5uon7.EDrbGYq0Uo/Vr6j.ceQWGAN685C', '2026-01-10 05:18:37'),
(2, 4, 1, '$2y$10$S4LfO25gdPAkJ1cNmaWZDeR6WAh2CiQasUn0YeXB2JDjxAdbkDAHS', '2026-01-10 05:18:37'),
(3, 4, 2, '$2y$10$VwMPdeqg8JlvS9RijviRc.NQtAzUeBdc0VMMO9lJ6xuhLgG2AxCVK', '2026-01-10 05:18:38'),
(4, 5, 3, '$2y$10$7ry4F6QZdx1/LiAvzFbVNO4V580B9CH/1jcCdljMHOB4ccE8wviXm', '2026-01-11 22:25:54'),
(5, 5, 2, '$2y$10$Vbfvz0iOJAL9kC/XGpPFfOSrPdR9cy0VBx70.GPG73wToU9fBrdWa', '2026-01-11 22:25:54'),
(6, 5, 1, '$2y$10$eMlCZ9OXCBRpDRD43Jww1uawqEVym00oF2P0Sgtm1xKvAk5mh3vw6', '2026-01-11 22:25:54'),
(13, 9, 2, '$2y$10$WMMtN/3kHpy0nyTQRp6rLO1ieDbVS1536XmicE.k/9S3vgYcKceOK', '2026-01-17 12:25:12'),
(14, 9, 1, '$2y$10$WZ.fpXuthYBKK8GXk2JCoOlpnb5BCTJFdOtIGADvrFDIKOjUFyOuK', '2026-01-17 12:25:12'),
(15, 9, 3, '$2y$10$KxQ2abWJ/ZvUelhXAvg5wuklX7tOB/iXrbjjYEHd/U4vvmAv3FNxi', '2026-01-17 12:25:12'),
(16, 12, 1, '$2y$10$30V8dqKNPN5F/PmWbT0JYe0vnJRW77ZV0mpwQQefHlpISL9b.tO5.', '2026-02-01 05:12:46'),
(17, 12, 10, '$2y$10$sx1/K4HvOgW9GK79DEqkTe/9fXzPpqyNoURhjFZIGXhHNYHLaxN.y', '2026-02-01 05:12:46'),
(18, 12, 9, '$2y$10$gpv0Fx12lCXNHT/keQSXROqbSYRKkd0dd8TpMwJ/3OHhpPiXZeRma', '2026-02-01 05:12:46'),
(36, 8, 10, '$2y$10$Xf0LB66wIZR8qqD3YwvRxODyNWGM.B32tnTgmtzIWQDIqvwGG2LBy', '2026-02-03 06:31:36'),
(37, 8, 2, '$2y$10$GkJc34Vtn0dK1i2PkxLVOeUJlnLkQBEfIXExWqpgzZQ0HgQ4HNOoi', '2026-02-03 06:31:36'),
(38, 8, 9, '$2y$10$l/W/tJqKNb.rzu.bIJWpauf262JeqfTbotrGS3Kl7FvlESr7s4kQK', '2026-02-03 06:31:36'),
(42, 15, 1, '$2y$10$VxaL8xANLbbDt4Ei4j9v5eVy36QJgHCFCwAx8PbISOFAaNqXrFNPi', '2026-02-05 17:34:13'),
(43, 15, 2, '$2y$10$.uVXv9sVZsh3MpKGE1.hPerfC5nGQU7FqeHrL28Jn9/01rQQwRd1q', '2026-02-05 17:34:13'),
(44, 15, 3, '$2y$10$yRbqbo8hJ/HuzdUpV4iLceDbRIItC686WaOuMbhJpCj8b4lq5rXie', '2026-02-05 17:34:13'),
(45, 16, 1, '$2y$10$RiKlsorGYbfiGUbEc.69pe2r6AiTq1i19zJpOPFjtAHtPXLGRp9eG', '2026-02-17 02:13:13'),
(46, 16, 2, '$2y$10$7zRG8OkR/YrTXaiBB0EMyuI9nc0G9K4MGXi0B9CX1OmmzH5yqxjB.', '2026-02-17 02:13:14'),
(47, 16, 10, '$2y$10$PJLsBuTJ/32.r9IsrL6KGuxfU1rIacXr8b4NvFuChTat2otoqD7Hq', '2026-02-17 02:13:14'),
(54, 19, 1, '$2y$10$coQd5a7QKHNb6jdtgaJsb.iHVr6XMvf01BlZfzYCtZJKunFn01AP2', '2026-02-21 17:58:48'),
(55, 19, 10, '$2y$10$yrm0opwl99uiIyc/nC9HC.hFe3fSvduzpRXOIyenvN79DZAsD.xmy', '2026-02-21 17:58:48'),
(56, 19, 3, '$2y$10$hapRWiabsWiutTz5hQAO5eikFSPsdGZ2I.H/J6gFL72Zo4OMAB2Py', '2026-02-21 17:58:49'),
(57, 21, 10, '$2y$10$bVXQZGjqq9BP/3llIOjOnOwKJKcVWuvg3uQTVQnD/vnU8HRb52IAa', '2026-02-21 19:44:44'),
(58, 21, 2, '$2y$10$3szXub/KLpMQ2pMJxRC4a.RVBwj2lx3MTfgsW9Q9r1/2J6vRfcuAu', '2026-02-21 19:44:44'),
(59, 21, 9, '$2y$10$4GFRpIkWe.hWfIe.ZPrSKOxPXP68ZwMLrXj37Kkh5msIbxenmGWIG', '2026-02-21 19:44:44'),
(63, 23, 10, '$2y$10$av/1y8o77VP9zhsRg9l86.kZ8.PYrfowGM/E9oNLNZfROi2UjXP3e', '2026-02-21 23:47:49'),
(64, 23, 1, '$2y$10$C6LzOnBiqV3GoCycoDL/0.YlNUFdfXbZ6iX0GedxeV.lowxj4v6yu', '2026-02-21 23:47:49'),
(65, 23, 9, '$2y$10$YiEp1SsUs1KgCjdwxMF5Pe0Sr1XCdyDA2.zXYHBlR70R0j0V01oPS', '2026-02-21 23:47:49'),
(75, 26, 1, '$2y$10$PuiRjvAL2Vk4BXiLpD6cS.PV6jVVRVkvf.VZ/hGHwsh/TRCgkAxOW', '2026-02-23 00:07:19'),
(76, 26, 2, '$2y$10$nAsTkmFUzjH/9rtA.xxp8uJ6cYn5vpMnH6N8Sa1Xq7953AY7ljroO', '2026-02-23 00:07:19'),
(77, 26, 10, '$2y$10$AKtyqmtb6W3niGt6RE.So.YvecgSuSKsUJem4NZgZjqQzPjWmrRs.', '2026-02-23 00:07:19'),
(84, 28, 1, '$2y$10$.HKHQIhgcTDjnI1dh5hpyuaJjHMoWU6.Y5oNm1DIFRVdbFlwk8PXa', '2026-02-24 18:08:28'),
(85, 28, 3, '$2y$10$3NFwPp4NKgBWHKW8UUdJ2u5YAcZMzUgwuMVBjGUxlM/x6l1R8MCie', '2026-02-24 18:08:28'),
(86, 28, 10, '$2y$10$EeZqKZb4AfNzP6QsaD3IxuICt6cHGKayRw5fJ8obu3g60ZqP4Mbf2', '2026-02-24 18:08:28'),
(90, 1, 1, '$2y$10$sAQIahFxNeG5rCOocP6oaefaVNv9zRIRa9zwMlJ.rlb84MfAceGkq', '2026-02-28 15:23:16'),
(91, 1, 2, '$2y$10$Xjc/.X4wT2Cunp6UIKsN6uC8vH8bW9qhvDFp1tMQPMZqLzuGkMoL.', '2026-02-28 15:23:16'),
(92, 1, 3, '$2y$10$c9CoYh1poQ8oRR.jSE5M5ueXDSDQPISLjfb0NSfHDsibyT.HHHFJm', '2026-02-28 15:23:16');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_asignacion_departamento` (`departamento_id`),
  ADD KEY `fk_asignacion_pasante_v2` (`pasante_id`),
  ADD KEY `fk_asignacion_tutor_v2` (`tutor_id`);

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_asistencia_asignacion` (`asignacion_id`),
  ADD KEY `idx_pasante_fecha` (`pasante_id`, `fecha`);

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_accion` (`accion`),
  ADD KEY `idx_fecha` (`created_at`),
  ADD KEY `idx_tabla` (`tabla_afectada`);

--
-- Indices de la tabla `datos_pasante`
--
ALTER TABLE `datos_pasante`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`),
  ADD KEY `departamento_asignado_id` (`departamento_asignado_id`),
  ADD KEY `fk_dp_tutor` (`tutor_id`);

--
-- Indices de la tabla `datos_personales`
--
ALTER TABLE `datos_personales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `evaluaciones`
--
ALTER TABLE `evaluaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pasante_id` (`pasante_id`);

--
-- Indices de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `intentos_acceso`
--
ALTER TABLE `intentos_acceso`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ip_correo` (`direccion_ip`,`correo`),
  ADD KEY `idx_bloqueado_hasta` (`bloqueado_hasta`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_leida` (`usuario_id`,`leida`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indices de la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD KEY `departamento_id` (`departamento_id`),
  ADD KEY `idx_correo` (`correo`),
  ADD KEY `idx_rol` (`rol_id`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `usuarios_respuestas`
--
ALTER TABLE `usuarios_respuestas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_question` (`usuario_id`,`pregunta_id`),
  ADD KEY `pregunta_id` (`pregunta_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=398;

--
-- AUTO_INCREMENT de la tabla `datos_pasante`
--
ALTER TABLE `datos_pasante`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `datos_personales`
--
ALTER TABLE `datos_personales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `evaluaciones`
--
ALTER TABLE `evaluaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `intentos_acceso`
--
ALTER TABLE `intentos_acceso`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT de la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `usuarios_respuestas`
--
ALTER TABLE `usuarios_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  ADD CONSTRAINT `fk_asignacion_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_asignacion_pasante_v2` FOREIGN KEY (`pasante_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_asignacion_tutor_v2` FOREIGN KEY (`tutor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `fk_asistencia_asignacion` FOREIGN KEY (`asignacion_id`) REFERENCES `asignaciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `datos_pasante`
--
ALTER TABLE `datos_pasante`
  ADD CONSTRAINT `fk_dp_tutor` FOREIGN KEY (`tutor_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pasante_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `datos_personales`
--
ALTER TABLE `datos_personales`
  ADD CONSTRAINT `fk_personales_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `evaluaciones`
--
ALTER TABLE `evaluaciones`
  ADD CONSTRAINT `evaluaciones_ibfk_1` FOREIGN KEY (`pasante_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `usuarios_respuestas`
--
ALTER TABLE `usuarios_respuestas`
  ADD CONSTRAINT `usuarios_respuestas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuarios_respuestas_ibfk_2` FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas_seguridad` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
