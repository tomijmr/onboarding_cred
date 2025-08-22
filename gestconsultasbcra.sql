-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-08-2025 a las 11:00:17
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
-- Base de datos: `gestconsultasbcra`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `tasa_interes` decimal(5,2) NOT NULL DEFAULT 5.00,
  `monto_maximo` decimal(12,2) NOT NULL DEFAULT 100000.00,
  `cuotas_permitidas` varchar(50) NOT NULL DEFAULT '3,6,9,12'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `tasa_interes`, `monto_maximo`, `cuotas_permitidas`) VALUES
(1, 5.00, 6000000.00, '3,6,9,12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consultas`
--

CREATE TABLE `consultas` (
  `id` int(11) NOT NULL,
  `cuit` varchar(11) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `denominacion` varchar(255) DEFAULT NULL,
  `fecha_consulta` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado_credito` varchar(20) DEFAULT NULL,
  `monto_credito` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `consultas`
--

INSERT INTO `consultas` (`id`, `cuit`, `telefono`, `denominacion`, `fecha_consulta`, `estado_credito`, `monto_credito`) VALUES
(1, '23434291429', '3873076160', 'CANAVIDEZ MARCO TOMAS AGUSTIN', '2025-08-22 08:39:27', NULL, NULL),
(2, '23434291429', '3873076160', 'CANAVIDEZ MARCO TOMAS AGUSTIN', '2025-08-22 08:42:09', 'Rechazado', 0.00),
(3, '20413702969', '3868101010101', 'YAPURA JOEL GABRIEL ALEJANDRO', '2025-08-22 08:42:39', 'Aprobado', 1199000.00),
(4, '23434291429', '38730761601', 'CANAVIDEZ MARCO TOMAS AGUSTIN', '2025-08-22 08:45:33', 'Rechazado', 0.00),
(5, '23434291429', '38730761601', 'CANAVIDEZ MARCO TOMAS AGUSTIN', '2025-08-22 08:46:05', 'Rechazado', 0.00),
(6, '23434291429', '38730761601', 'CANAVIDEZ MARCO TOMAS AGUSTIN', '2025-08-22 08:53:33', 'Rechazado', 0.00),
(7, '20223544984', '1022', 'CORREJIDOR SERGIO ALBERTO', '2025-08-22 08:53:53', 'Rechazado', 0.00),
(8, '20413702969', '3868191919191', 'YAPURA JOEL GABRIEL ALEJANDRO', '2025-08-22 08:54:19', 'Aprobado', 1199000.00),
(9, '20413702969', '3868191919191', 'YAPURA JOEL GABRIEL ALEJANDRO', '2025-08-22 08:54:36', 'Aprobado', 1199000.00),
(10, '20413702969', '3868191919191', 'YAPURA JOEL GABRIEL ALEJANDRO', '2025-08-22 08:55:33', 'Aprobado', 1199000.00);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `consultas`
--
ALTER TABLE `consultas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
