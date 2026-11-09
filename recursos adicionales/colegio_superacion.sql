-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-04-2024 a las 20:39:11
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
-- Estructura de tabla para la tabla `control_pagos`
--

CREATE TABLE `control_pagos` (
  `id_pago` int(100) NOT NULL,
  `serie_recibo` varchar(100) NOT NULL,
  `num_autorizacion` varchar(100) DEFAULT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `carnet` varchar(20) NOT NULL,
  `tipo_pago` varchar(30) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `mes_pago` text DEFAULT NULL,
  `observacion_adicional` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `control_pagos`
--

INSERT INTO `control_pagos` (`id_pago`, `serie_recibo`, `num_autorizacion`, `codigo`, `carnet`, `tipo_pago`, `monto`, `fecha_pago`, `mes_pago`, `observacion_adicional`) VALUES
(2, 'A', 'kJvqQiXs', 'C417RNN', '2024-80', 'mensualidad', 250.00, '2024-04-23', 'enero', 'Cancela mes de Enero 2024'),
(3, 'A', 'zGZpXkvD', 'K516CPP', '2024-81', 'inscripcion', 300.00, '2024-04-23', 'febrero', 'Pago de Inscripcion Ordinaria');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_estudiantes`
--

CREATE TABLE `registro_estudiantes` (
  `id` int(100) UNSIGNED NOT NULL,
  `carnet` varchar(20) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `sexo` varchar(10) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `jornada` varchar(50) NOT NULL,
  `nivel` varchar(30) NOT NULL,
  `grado` varchar(30) NOT NULL,
  `ciclo_escolar` varchar(100) DEFAULT NULL,
  `tipo_sangre` varchar(30) DEFAULT NULL,
  `direccion_vivienda` varchar(100) DEFAULT NULL,
  `nombre_padre` varchar(50) DEFAULT NULL,
  `nombre_madre` varchar(50) DEFAULT NULL,
  `fecha_inscripcion` date DEFAULT NULL,
  `info_adicional` text DEFAULT NULL,
  `tel_alumno` varchar(15) DEFAULT NULL,
  `tel_encargado` varchar(15) DEFAULT NULL,
  `email_encargado` varchar(50) DEFAULT NULL,
  `nombre_tutor` varchar(50) DEFAULT NULL,
  `tipo_inscripcion` varchar(30) DEFAULT NULL,
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registro_estudiantes`
--

INSERT INTO `registro_estudiantes` (`id`, `carnet`, `codigo`, `nombre`, `apellido`, `sexo`, `fecha_nacimiento`, `jornada`, `nivel`, `grado`, `ciclo_escolar`, `tipo_sangre`, `direccion_vivienda`, `nombre_padre`, `nombre_madre`, `fecha_inscripcion`, `info_adicional`, `tel_alumno`, `tel_encargado`, `email_encargado`, `nombre_tutor`, `tipo_inscripcion`, `reg_date`) VALUES
(1, '2024-80', 'C417RNN', 'Luis Fernando', 'Hernández Velis', 'masculino', '2000-08-23', 'diario-matutino', 'primaria', '1grado', '2024', 'tipoAB-', 'Casa Campo La Paz Zona 0 San Francisco Zapotitlán', 'Mario Rene Castañeda Ataulfo', 'Maritza Del Rosario Paz Ordoñez', '2024-04-23', 'Se inscribió de forma para probar', '12345678', '98764321', '123@123.com', '', 'ordinaria', '2024-04-23 20:14:00'),
(2, '2024-81', 'K516CPP', 'Fernando José ', 'Menjiva Tobat', 'masculino', '2005-01-01', 'diario-matutino', 'basico', '3grado', '2024', 'desconocido', 'Casa Campo La Paz Zona 0 San Francisco Zapotitlán', 'Mario Rene Castañeda Ataulfo', 'Maritza Del Rosario Paz Ordoñez', '2024-04-23', 'Se inscribió de forma para probar', '12345678', '98764321', 'pruebadecorreo@prueba.com', '', 'ordinaria', '2024-04-23 20:13:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridadcontrolacceso`
--

CREATE TABLE `seguridadcontrolacceso` (
  `idpermiso` int(11) NOT NULL,
  `nombrepermiso` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `seguridadcontrolacceso`
--

INSERT INTO `seguridadcontrolacceso` (`idpermiso`, `nombrepermiso`, `descripcion`) VALUES
(1, 'root', 'usuario que tendra control total sobre los demas usuarios y funciones dentro del software'),
(2, 'administrador', 'usuario debajo de root que tendra ciertos privilegios');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `idusuario` int(11) NOT NULL,
  `nombre_us` varchar(255) NOT NULL,
  `apellido_us` varchar(255) NOT NULL,
  `telefono_us` int(13) NOT NULL,
  `nombreusuario` varchar(255) DEFAULT NULL,
  `contrasena` varchar(255) DEFAULT NULL,
  `correoelectronico` varchar(255) DEFAULT NULL,
  `idpermiso` int(11) DEFAULT NULL,
  `adicional_us` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idusuario`, `nombre_us`, `apellido_us`, `telefono_us`, `nombreusuario`, `contrasena`, `correoelectronico`, `idpermiso`, `adicional_us`) VALUES
(1, 'admin', 'admin', 12345678, 'administrador', 'admin', 'admin@colesuperacion.com', 2, 'persona que maneja los primeros 2 modulos del software');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `control_pagos`
--
ALTER TABLE `control_pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `carnet` (`carnet`);

--
-- Indices de la tabla `registro_estudiantes`
--
ALTER TABLE `registro_estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `carnet_UNIQUE` (`carnet`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `control_pagos`
--
ALTER TABLE `control_pagos`
  MODIFY `id_pago` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `registro_estudiantes`
--
ALTER TABLE `registro_estudiantes`
  MODIFY `id` int(100) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `control_pagos`
--
ALTER TABLE `control_pagos`
  ADD CONSTRAINT `control_pagos_ibfk_1` FOREIGN KEY (`carnet`) REFERENCES `registro_estudiantes` (`carnet`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
