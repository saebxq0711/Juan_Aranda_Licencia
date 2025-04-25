-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-04-2025 a las 16:00:04
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
-- Base de datos: `codigo_barras`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaciones`
--

CREATE TABLE `asignaciones` (
  `id` int(11) NOT NULL,
  `dispositivo_id` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `fecha_asignacion` datetime DEFAULT NULL,
  `fecha_devolucion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asignaciones`
--

INSERT INTO `asignaciones` (`id`, `dispositivo_id`, `id_empleado`, `fecha_asignacion`, `fecha_devolucion`) VALUES
(2, 19, 1107978281, '2025-04-24 19:21:01', '2025-04-25 04:00:39'),
(3, 20, 1107978281, '2025-04-24 20:06:30', '2025-04-25 04:00:45'),
(4, 20, 1107978281, '2025-04-25 04:02:09', '2025-04-25 04:02:30'),
(5, 20, 1107978281, '2025-04-25 04:02:33', '2025-04-25 04:02:35'),
(6, 20, 1107978281, '2025-04-25 04:02:39', '2025-04-25 04:02:45'),
(7, 19, 1107978281, '2025-04-25 04:02:42', '2025-04-25 04:02:44'),
(8, 19, 1107978281, '2025-04-25 04:04:48', '2025-04-25 04:04:49'),
(9, 19, 1107978281, '2025-04-25 04:23:10', '2025-04-25 04:23:13'),
(10, 19, 1107978281, '2025-04-25 04:23:18', '2025-04-25 04:24:00'),
(11, 19, 1107978281, '2025-04-25 04:24:02', '2025-04-25 04:33:53'),
(12, 20, 1107978281, '2025-04-25 04:24:04', '2025-04-25 04:34:01'),
(13, 20, 1107978281, '2025-04-25 04:34:18', NULL),
(14, 21, 41664864, '2025-04-25 13:10:47', NULL),
(15, 19, 41664864, '2025-04-25 13:10:49', NULL),
(16, 22, 1107978281, '2025-04-25 07:31:27', NULL),
(17, 23, 41664864, '2025-04-25 08:47:40', NULL),
(18, 25, 1107978281, '2025-04-25 08:57:22', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivos`
--

CREATE TABLE `dispositivos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `serial` varchar(100) DEFAULT NULL,
  `id_estado` int(3) DEFAULT NULL,
  `fecha_registro` date DEFAULT curdate(),
  `codigo_barra` varchar(100) NOT NULL,
  `nit` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dispositivos`
--

INSERT INTO `dispositivos` (`id`, `nombre`, `marca`, `modelo`, `serial`, `id_estado`, `fecha_registro`, `codigo_barra`, `nit`) VALUES
(19, 'Portatil', 'Acer', 'AN515', '01942190', 4, '2025-04-25', 'DISP-000019', 514124),
(20, 'Portatil', 'Asus', 'TUF12340', '51293102950', 4, '2025-04-25', 'DISP-000020', 514124),
(21, 'Impresora', 'Samsung', 'FALSF-VA12', '877856745', 4, '2025-04-25', 'DISP-000021', 514124),
(22, 'Mouse', 'Onikuma', 'ON3124-123', '41949219', 3, '2025-04-25', 'DISP-800522', 514124),
(23, 'Celular', 'Iphone', 'IOS1840912', '49182938', 3, '2025-04-25', '7703336004959', 514124),
(24, 'Escaner', 'Lg', 'OKAOJR9P14I', '98421989', 3, '2025-04-25', 'DISP-186724', 514124),
(25, 'Celular', 'Xiaomi', 'JFAJSOIRUI', '49012412', 3, '2025-04-25', '864469068846700', 514124);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `nit` int(50) NOT NULL,
  `nombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`nit`, `nombre`) VALUES
(514124, 'Sistemas S.A.S');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

CREATE TABLE `estado` (
  `id_estado` int(11) NOT NULL,
  `estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado`
--

INSERT INTO `estado` (`id_estado`, `estado`) VALUES
(1, 'Activo'),
(2, 'Inactivo'),
(3, 'Disponible'),
(4, 'Asignado'),
(5, 'Dañado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `licencias`
--

CREATE TABLE `licencias` (
  `id_licencia` varchar(10) NOT NULL,
  `id_tipo_licencia` int(3) NOT NULL,
  `fecha_ini` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `id_estado` int(3) NOT NULL,
  `nit` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `licencias`
--

INSERT INTO `licencias` (`id_licencia`, `id_tipo_licencia`, `fecha_ini`, `fecha_fin`, `id_estado`, `nit`) VALUES
('XLCAS45HV0', 3, '2025-04-24 21:10:00', '2027-04-24 21:10:00', 1, 514124);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `rol`) VALUES
(1, 'Superadmin'),
(2, 'Admin'),
(3, 'Empleado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_licencia`
--

CREATE TABLE `tipo_licencia` (
  `id_tipo_licencia` int(11) NOT NULL,
  `licencia` varchar(50) NOT NULL,
  `duracion` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_licencia`
--

INSERT INTO `tipo_licencia` (`id_tipo_licencia`, `licencia`, `duracion`) VALUES
(1, 'Basic', 365),
(2, 'Demo', 10),
(3, 'Premium', 730);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `id_rol` int(3) NOT NULL,
  `nit` int(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombres`, `correo`, `contrasena`, `id_rol`, `nit`) VALUES
(41664864, 'Gabriela Marin', 'gaby@gmail.com', '$2y$12$7fo8vcl5jpyZpRCyTjKX.OXdy5u5HbtH66f1nBa9DTmnawKuulauO', 3, 514124),
(65634846, 'Magdalena Lozano', 'magdys_2007@gmail.com', '$2y$12$vpcFsgpRq/kBAtbELNVFpOnBBrT30Ho1dIEQvidBhsKYhXnM.rT9.', 2, 514124),
(1107978281, 'Laura Quiñones', 'lavalozqui@gmail.com', '$2y$12$fRbWeM/F3RvmcAHTG7846eFKsoSA.unS3v.5gNqbz.DMCGCvlhjPm', 3, 514124),
(1109492105, 'Juan Aranda', 'jsebaslozano2006@gmail.com', '$2y$12$x6NHnBn0kD9BHeVDyC/iEewGdhZg5WXgQ46uIsQ8U1tU5VNhSoLca', 1, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dispositivo_id` (`dispositivo_id`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- Indices de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_barra` (`codigo_barra`),
  ADD KEY `nit` (`nit`),
  ADD KEY `id_estado` (`id_estado`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`nit`);

--
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`id_estado`);

--
-- Indices de la tabla `licencias`
--
ALTER TABLE `licencias`
  ADD PRIMARY KEY (`id_licencia`),
  ADD KEY `id_estado` (`id_estado`),
  ADD KEY `id_tipo_licencia` (`id_tipo_licencia`),
  ADD KEY `id_empresa` (`nit`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `tipo_licencia`
--
ALTER TABLE `tipo_licencia`
  ADD PRIMARY KEY (`id_tipo_licencia`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `id_empresa` (`nit`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `id_estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tipo_licencia`
--
ALTER TABLE `tipo_licencia`
  MODIFY `id_tipo_licencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  ADD CONSTRAINT `asignaciones_ibfk_1` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`),
  ADD CONSTRAINT `asignaciones_ibfk_2` FOREIGN KEY (`id_empleado`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD CONSTRAINT `dispositivos_ibfk_1` FOREIGN KEY (`nit`) REFERENCES `empresa` (`nit`),
  ADD CONSTRAINT `dispositivos_ibfk_2` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id_estado`);

--
-- Filtros para la tabla `licencias`
--
ALTER TABLE `licencias`
  ADD CONSTRAINT `licencias_ibfk_1` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id_estado`),
  ADD CONSTRAINT `licencias_ibfk_2` FOREIGN KEY (`id_tipo_licencia`) REFERENCES `tipo_licencia` (`id_tipo_licencia`),
  ADD CONSTRAINT `licencias_ibfk_3` FOREIGN KEY (`nit`) REFERENCES `empresa` (`nit`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`),
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`nit`) REFERENCES `empresa` (`nit`);

DELIMITER $$
--
-- Eventos
--
CREATE DEFINER=`root`@`localhost` EVENT `actualizar_estados_licencias` ON SCHEDULE EVERY 1 HOUR STARTS '2025-04-24 08:18:28' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE licencias
  SET id_estado = 2
  WHERE fecha_fin <= NOW() AND id_estado != 2$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
