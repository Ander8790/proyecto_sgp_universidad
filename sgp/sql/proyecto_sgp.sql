-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-02-2026 a las 03:07:32
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
  `asignacion_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time DEFAULT NULL,
  `horas_calculadas` decimal(10,2) DEFAULT 0.00,
  `observacion` text DEFAULT NULL,
  `estado` enum('abierto','cerrado') DEFAULT 'abierto',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(98, 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, '2026-02-21 01:19:38');

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
  `observaciones` text DEFAULT NULL COMMENT 'Notas adicionales'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `datos_pasante`
--

INSERT INTO `datos_pasante` (`id`, `usuario_id`, `institucion_procedencia`, `created_at`, `updated_at`, `estado_pasantia`, `fecha_inicio_pasantia`, `fecha_fin_estimada`, `horas_acumuladas`, `horas_meta`, `departamento_asignado_id`, `observaciones`) VALUES
(1, 9, 'moral y luces', '2026-02-02 03:30:18', '2026-02-02 03:30:18', 'Pendiente', NULL, NULL, 0, 240, NULL, NULL),
(2, 15, 'jdhdhd', '2026-02-05 17:34:59', '2026-02-05 17:34:59', 'Pendiente', NULL, NULL, 0, 240, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos_personales`
--

CREATE TABLE `datos_personales` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `datos_personales`
--

INSERT INTO `datos_personales` (`id`, `usuario_id`, `cedula`, `nombres`, `apellidos`, `cargo`, `telefono`, `direccion`, `genero`, `fecha_nacimiento`, `created_at`, `updated_at`) VALUES
(1, 1, '12345678', 'Administrador', 'del Sistema', 'jefe de soporte tecnico', '0414-6785679', 'los proceres', 'M', '2026-01-15', '2026-01-10 00:02:19', '2026-02-19 17:52:03'),
(2, 2, '11111111', 'Carlos', 'Tutor', NULL, '0414-1234567', NULL, 'M', NULL, '2026-01-10 00:02:19', '2026-01-10 00:02:19'),
(5, 4, '30342975', 'jose luis', 'gomezlo', NULL, '04148965723', 'los proceres', 'M', '2000-06-10', '2026-01-10 05:27:05', '2026-01-10 05:27:05'),
(6, 5, '0000000000', 'Yurimar', 'Del Carmen', NULL, '0000000000', 'Por definir', 'M', '2000-01-01', '2026-01-11 22:25:54', '2026-01-11 22:25:54'),
(8, 8, '15020928', 'petra', 'prieto', NULL, '04241234567', 'los proceres', 'F', '2014-01-06', '2026-01-14 05:18:55', '2026-01-14 18:17:25'),
(9, 9, '30342978', 'albert', 'rodriguez', NULL, '04148965723', 'la sabanita 2', 'M', '2000-01-01', '2026-01-17 12:25:12', '2026-02-02 02:01:44'),
(11, 11, '15020929', 'Yarima', 'Del Carmen', NULL, NULL, NULL, NULL, NULL, '2026-01-30 23:31:18', '2026-01-30 23:31:18'),
(12, 12, '20242114', 'maria', 'yepez', NULL, NULL, NULL, NULL, NULL, '2026-02-01 05:12:46', '2026-02-01 05:12:46'),
(13, 13, '28708388', 'delmary', 'Nuevo', NULL, NULL, NULL, NULL, NULL, '2026-02-01 20:07:11', '2026-02-01 20:07:11'),
(14, 15, '31087083', 'Albert', 'Rodriguez', NULL, '04148965723', '8001\r\nCasa número 111', 'M', '2026-02-05', '2026-02-05 17:28:20', '2026-02-05 17:34:59'),
(15, 17, '30342971', 'gabriel', 'prieto', NULL, NULL, NULL, NULL, NULL, '2026-02-21 01:15:05', '2026-02-21 01:15:05');

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
(3, 'Universidad Politécnica Territorial del Estado Bolívar (UPTBolívar)', NULL, '2026-02-19 14:54:35', 'Universidad', 'Calle Rosario, Casco Histórico, Cdad. Bolívar');

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
(3, 1, 'warning', 'Actualiza tu perfil', 'Completa tu información de perfil para aprovechar todas las funcionalidades del sistema.', '/perfil/ver', 1, '2026-02-08 16:44:55');

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
(2, 'Tutor', 'Supervisi??n de pasantes', '2026-01-10 00:02:19'),
(3, 'Pasante', 'Usuario b??sico del sistema', '2026-01-10 00:02:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
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
  `telefono` varchar(20) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('M','F','Otro') DEFAULT NULL,
  `institucion_id` int(11) DEFAULT NULL,
  `modalidad` enum('Pasantía','Servicio Comunitario') DEFAULT 'Pasantía',
  `horas_meta` int(11) DEFAULT 1440,
  `estado_pasantia` enum('Sin Asignar','Activo','Finalizado') DEFAULT 'Sin Asignar',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin_estimada` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `correo`, `password`, `pin_asistencia`, `rol_id`, `departamento_id`, `avatar`, `requiere_cambio_clave`, `estado`, `created_at`, `updated_at`, `telefono`, `fecha_nacimiento`, `genero`, `institucion_id`, `modalidad`, `horas_meta`, `estado_pasantia`, `fecha_inicio`, `fecha_fin_estimada`) VALUES
(1, 'admin@sgp.local', '$2y$10$X6FrcqzhY8agHsYPh9.wVO4Mga9FhsDXKgbX.4mXHwNhlh2rvM2yC', NULL, 1, NULL, 'default.png', 0, 'activo', '2026-01-10 00:02:19', '2026-01-11 23:06:29', NULL, NULL, NULL, NULL, 'Pasantía', 1440, 'Sin Asignar', NULL, NULL),
(2, 'tutor@sgp.local', '$2y$10$TxrLZz9BSpdfUFZSH127qu6h0c/GW83ujwnnA4DqEkEvMjyrHCw2S', NULL, 2, NULL, 'default.png', 0, 'activo', '2026-01-10 00:02:19', '2026-01-12 01:36:35', NULL, NULL, NULL, NULL, 'Pasantía', 1440, 'Sin Asignar', NULL, NULL),
(4, 'luisgabrielyanez9@gmail.com', '$2y$10$suzP73.i87XCpegPe8U4I.EKJMTziXRd5kWfFZVvP13HijTCJgnO2', NULL, 3, NULL, 'default.png', 0, 'activo', '2026-01-10 05:18:37', '2026-01-20 04:38:36', NULL, NULL, NULL, NULL, 'Pasantía', 1440, 'Sin Asignar', NULL, NULL),
(5, 'yurimarprieto.79@gmail.com', '$2y$10$Ga.8N.cmbEnxUIO08Jc1OupJT/HVvcJXI63.lEGk3G3JQcIk4PP76', NULL, 3, NULL, 'default.png', 0, 'inactivo', '2026-01-11 22:25:54', '2026-01-12 01:44:56', NULL, NULL, NULL, NULL, 'Pasantía', 1440, 'Sin Asignar', NULL, NULL),
(8, 'petra.79@GMAIL.COM', '$2y$10$x3mj9u/wybL.KxEGbcy9reLgLq0eDbAktp2Icso7DT4lDnJ5dUspO', NULL, 2, NULL, 'default.png', 0, 'activo', '2026-01-14 04:21:10', '2026-02-03 06:31:36', NULL, NULL, NULL, NULL, 'Pasantía', 1440, 'Sin Asignar', NULL, NULL),
(9, 'albert@gmail.com', '$2y$10$xQg8s9puOmRpvfAqCkFWPOQKaYRTqKOdI/NYRGYCYEnKhWP5Dq4qu', NULL, 3, NULL, 'default.png', 0, 'activo', '2026-01-17 12:25:11', '2026-01-17 12:25:11', NULL, NULL, NULL, NULL, 'Pasantía', 1440, 'Sin Asignar', NULL, NULL),
(11, 'yurima@sgp.local', '$2y$10$QrTasd0TeXvkXbGpgKeROOvyn3WeHc6ejV6gSyLWig1NtsUW2AMwe', NULL, 3, NULL, 'default.png', 0, 'activo', '2026-01-30 23:31:18', '2026-01-30 23:31:18', NULL, NULL, NULL, NULL, 'Pasantía', 1440, 'Sin Asignar', NULL, NULL),
(12, 'mariayepez@sgp.local', '$2y$10$EgRnlUwlJIIcQXCDAQeYQOi/yj/OdntzLpoduHAO0/SjgUtliaIb.', NULL, 3, NULL, 'default.png', 0, 'activo', '2026-02-01 05:12:46', '2026-02-01 05:12:46', NULL, NULL, NULL, NULL, 'Pasantía', 1440, 'Sin Asignar', NULL, NULL),
(13, 'delmaryguzman@gmail.com', '$2y$10$34JGLe.CxtwtAM8SnMXyV.Zu/MVhj4lhP7B7wNZfs66aWBS3QojHu', NULL, 1, NULL, 'default.png', 0, 'activo', '2026-02-01 20:07:11', '2026-02-01 20:13:03', NULL, NULL, NULL, NULL, 'Pasantía', 1440, 'Sin Asignar', NULL, NULL),
(14, 'ortiz9@gmail.com', '$2y$10$xrm3mgtQVm1Nf74R0JnbQeHuDkI7h9o8UUZGlApKqekIc.mq3Vd7W', NULL, 1, NULL, 'default.png', 1, 'activo', '2026-02-02 18:12:30', '2026-02-02 18:12:30', NULL, NULL, NULL, NULL, 'Pasantía', 1440, 'Sin Asignar', NULL, NULL),
(15, 'albertr@gmail.commm', '$2y$10$UEL4uEPCMpzZNdZVlmHJzeOiGKW7EsDJNcAAXnWWlULTdLBON27l6', NULL, 3, NULL, 'default.png', 0, 'activo', '2026-02-05 17:28:20', '2026-02-05 17:34:13', NULL, NULL, NULL, NULL, 'Pasantía', 1440, 'Sin Asignar', NULL, NULL),
(16, 'catiremanuel170@gmail.com', '$2y$10$DbKDoFu5uCYFV6IS6hb7K.0V/Sc7VK44STCxb0662ekKGTD1yNf.2', NULL, 1, NULL, 'default.png', 0, 'activo', '2026-02-16 16:41:12', '2026-02-19 02:41:38', NULL, NULL, NULL, NULL, 'Pasantía', 1440, 'Sin Asignar', NULL, NULL),
(17, 'gabriel@sgp.local', '$2y$10$WBiy3Ht2kZVph6HN6G44z.yrhifxZp6dJzhTyWiOPVPK6ApUVqf0W', NULL, 3, NULL, 'default.png', 0, 'activo', '2026-02-21 01:15:04', '2026-02-21 01:15:04', NULL, NULL, NULL, NULL, 'Pasantía', 1440, 'Sin Asignar', NULL, NULL);

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
(19, 13, 1, '$2y$10$9brrF4Se5VyH.9GY0lfP2OvWOJjlJRsOc8mWq07l3TXhMDl.LyTSS', '2026-02-01 20:07:11'),
(20, 13, 2, '$2y$10$FmkTggeXhaNYp264VcBoxuf4ptNEi8kbT5a2dThX6/prUhP07EHu.', '2026-02-01 20:07:11'),
(21, 13, 3, '$2y$10$X5gAZqGOuqz59qjXSNmS3umZUp3Jx0XZ8ISj7.Bx6c0b1lAS/7mxW', '2026-02-01 20:07:11'),
(22, 1, 1, '$2y$10$8ThOERlFSNC4FEMkEr4LhOfQ24DO7ZfZX4nJ6291jlvK4atNjhyZi', '2026-02-02 02:06:02'),
(23, 1, 2, '$2y$10$AsXfeRp/18K3JHC7M9dZF.K2MbFP21M1rmfPyEb6bekcIn9AZEr7O', '2026-02-02 02:06:02'),
(24, 1, 10, '$2y$10$zsVPdu0Gs2/zzxdujwQ7OuSE0kF77Mrnu4fA8.N30xUfJFi07hF4q', '2026-02-02 02:06:02'),
(36, 8, 10, '$2y$10$Xf0LB66wIZR8qqD3YwvRxODyNWGM.B32tnTgmtzIWQDIqvwGG2LBy', '2026-02-03 06:31:36'),
(37, 8, 2, '$2y$10$GkJc34Vtn0dK1i2PkxLVOeUJlnLkQBEfIXExWqpgzZQ0HgQ4HNOoi', '2026-02-03 06:31:36'),
(38, 8, 9, '$2y$10$l/W/tJqKNb.rzu.bIJWpauf262JeqfTbotrGS3Kl7FvlESr7s4kQK', '2026-02-03 06:31:36'),
(42, 15, 1, '$2y$10$VxaL8xANLbbDt4Ei4j9v5eVy36QJgHCFCwAx8PbISOFAaNqXrFNPi', '2026-02-05 17:34:13'),
(43, 15, 2, '$2y$10$.uVXv9sVZsh3MpKGE1.hPerfC5nGQU7FqeHrL28Jn9/01rQQwRd1q', '2026-02-05 17:34:13'),
(44, 15, 3, '$2y$10$yRbqbo8hJ/HuzdUpV4iLceDbRIItC686WaOuMbhJpCj8b4lq5rXie', '2026-02-05 17:34:13'),
(45, 16, 1, '$2y$10$RiKlsorGYbfiGUbEc.69pe2r6AiTq1i19zJpOPFjtAHtPXLGRp9eG', '2026-02-17 02:13:13'),
(46, 16, 2, '$2y$10$7zRG8OkR/YrTXaiBB0EMyuI9nc0G9K4MGXi0B9CX1OmmzH5yqxjB.', '2026-02-17 02:13:14'),
(47, 16, 10, '$2y$10$PJLsBuTJ/32.r9IsrL6KGuxfU1rIacXr8b4NvFuChTat2otoqD7Hq', '2026-02-17 02:13:14'),
(48, 17, 10, '$2y$10$30p6BqPa0fBRqcPTVCb89ugmbbSCOS.P6KULKPQNTgEQ4G/6o6Cp6', '2026-02-21 01:15:05'),
(49, 17, 1, '$2y$10$m.s6X2.x6QEg2Awb8EpIeOe2ARDC2GOzhZP271AjTVqqavr2dXoeu', '2026-02-21 01:15:05'),
(50, 17, 9, '$2y$10$6tSTXiWUtiC6sRORCrtsUOYIsh5wVH4dBXqhYpACs2u97i/dUlgPm', '2026-02-21 01:15:05');

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
  ADD KEY `fk_asistencia_asignacion` (`asignacion_id`);

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
  ADD KEY `departamento_asignado_id` (`departamento_asignado_id`);

--
-- Indices de la tabla `datos_personales`
--
ALTER TABLE `datos_personales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD KEY `idx_cedula` (`cedula`);

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
  ADD KEY `departamento_id` (`departamento_id`),
  ADD KEY `idx_correo` (`correo`),
  ADD KEY `idx_rol` (`rol_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `fk_usuario_institucion` (`institucion_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT de la tabla `datos_pasante`
--
ALTER TABLE `datos_pasante`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `datos_personales`
--
ALTER TABLE `datos_personales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `usuarios_respuestas`
--
ALTER TABLE `usuarios_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

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
  ADD CONSTRAINT `fk_usuario_institucion` FOREIGN KEY (`institucion_id`) REFERENCES `instituciones` (`id`) ON DELETE SET NULL,
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
