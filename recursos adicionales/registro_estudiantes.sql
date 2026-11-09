-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-02-2024 a las 01:11:42
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `colegio_superacion`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_estudiantes`
--

CREATE TABLE `registro_estudiantes` (
  `id` int(6) UNSIGNED NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `carnet` varchar(20) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `sexo` varchar(10) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `jornada` varchar(20) NOT NULL,
  `direccion_vivienda` varchar(100) DEFAULT NULL,
  `nombre_padre` varchar(50) DEFAULT NULL,
  `nombre_madre` varchar(50) DEFAULT NULL,
  `fecha_inscripcion` date DEFAULT NULL,
  `info_adicional` text DEFAULT NULL,
  `tel_alumno` varchar(15) DEFAULT NULL,
  `tel_encargado` varchar(15) DEFAULT NULL,
  `email_encargado` varchar(50) DEFAULT NULL,
  `grado_asignado` varchar(30) NOT NULL,
  `nombre_tutor` varchar(50) DEFAULT NULL,
  `tipo_sangre` varchar(30) DEFAULT NULL,
  `tipo_inscripcion` varchar(30) DEFAULT NULL,
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registro_estudiantes`
--

INSERT INTO `registro_estudiantes` (`id`, `codigo`, `carnet`, `nombre`, `apellido`, `sexo`, `fecha_nacimiento`, `jornada`, `direccion_vivienda`, `nombre_padre`, `nombre_madre`, `fecha_inscripcion`, `info_adicional`, `tel_alumno`, `tel_encargado`, `email_encargado`, `grado_asignado`, `nombre_tutor`, `tipo_sangre`, `tipo_inscripcion`, `reg_date`) VALUES
(1, '103056610', '30901920992', 'Luis Fernando', 'Hernández Velis', 'masculino ', '2000-08-23', 'nocturna', 'Zona 0 Colonia Los Pajares Retalhuleu ', 'Juan Carlos Mejia Escobar', 'Michelle Rivera Velásquez', '2024-02-06', '', '12345678', '87654321', 'prueba123@prueba.com', 'grado2', '', 'tipoA+', 'ordinaria', '2024-02-06 23:55:52'),
(3, '3109113', '30901920973', 'Fernando José ', 'Cordero Ruiz', 'masculino ', '2005-03-12', 'nocturna', 'Casa campo la paz', 'Roberto De la Fuente ORtiz', 'Idelaria Consueloi Porras', '2024-02-06', 'Indica tomar medicamentos', '87654321', '12345678', 'prueba123@prueba.com', 'grado3', '', 'desconocido', 'extraordinaria', '2024-02-07 00:06:57');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `registro_estudiantes`
--
ALTER TABLE `registro_estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_UNIQUE` (`codigo`),
  ADD UNIQUE KEY `carnet_UNIQUE` (`carnet`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `registro_estudiantes`
--
ALTER TABLE `registro_estudiantes`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
