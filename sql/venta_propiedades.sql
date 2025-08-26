-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-08-2025 a las 00:54:29
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
-- Base de datos: `venta_propiedades`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `tema` enum('azul-amarillo-gris','blanco-gris') NOT NULL DEFAULT 'azul-amarillo-gris',
  `icono_principal` varchar(255) DEFAULT NULL,
  `icono_blanco` varchar(255) DEFAULT NULL,
  `banner_imagen` varchar(255) DEFAULT NULL,
  `banner_mensaje` varchar(150) NOT NULL DEFAULT 'Permítenos ayudarte a cumplir tus sueños',
  `quienes_somos` text DEFAULT NULL,
  `quienes_img` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `tiktok` varchar(255) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `tema`, `icono_principal`, `icono_blanco`, `banner_imagen`, `banner_mensaje`, `quienes_somos`, `quienes_img`, `facebook`, `instagram`, `tiktok`, `direccion`, `telefono`, `email`, `creado_en`, `actualizado_en`) VALUES
(1, 'azul-amarillo-gris', 'icono_principal_20250826_232702_ae153b.jpg', 'icono_blanco_20250826_232702_ce3870.jpg', 'banner_imagen_20250826_232702_3f0c68.jpg', 'Permítenos ayudarte a cumplir tus sueños', 'Somo una asociación que garantiza la mejor seguridad en el ambito de ventas de casas y alquileres', 'quienes_img_20250826_232702_e1030c.gif', 'https://www.facebook.com/share/1CwGyW19JE/?mibextid=wwXIfr', 'https://www.instagram.com/daniel_delgado_05?igsh=MWR6MmQxZm85emQ0Nw%3D%3D&utm_source=qr', 'https://www.tiktok.com/@daniel_delgado_a?_t=ZM-8z8HmISVeAa&_r=1', 'Moll, Plaza Santa Rosa', '85694071', 'Danidelalva05@gmail.com', '2025-08-22 19:39:18', '2025-08-26 22:32:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `privilegio`
--

CREATE TABLE `privilegio` (
  `id` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `privilegio`
--

INSERT INTO `privilegio` (`id`, `nombre`) VALUES
(1, 'administrador'),
(2, 'agente de ventas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propiedades`
--

CREATE TABLE `propiedades` (
  `id` int(11) NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `destacada` tinyint(1) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `agente_id` int(11) NOT NULL,
  `imagen` varchar(300) NOT NULL,
  `descripcion` text NOT NULL,
  `ubicacion` varchar(255) NOT NULL,
  `fecha_pub` date NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `propiedades`
--

INSERT INTO `propiedades` (`id`, `id_tipo`, `destacada`, `titulo`, `agente_id`, `imagen`, `descripcion`, `ubicacion`, `fecha_pub`, `precio`, `lat`, `lng`) VALUES
(1, 1, 1, 'Canas', 3, 'uploads/propiedades/1755983471_ImagenPrueba.jpg', 'Casa en buen estado y 0 problemas y en buena ubicacion', 'canas guanacaste', '2025-08-14', 1000000.00, NULL, NULL),
(3, 2, 0, 'Canas', 3, 'uploads/propiedades/1755988915_ImagenPrueba.jpg', 'Buena', 'Colorado guanacaste', '2025-08-23', 1000000.00, NULL, NULL),
(4, 2, 1, 'Canas', 3, 'uploads/propiedades/1755989764_ImagenPrueba.jpg', 'fhgfgf', 'Colorado guanacaste', '2025-08-15', 1000000.00, NULL, NULL),
(5, 2, 1, 'Canas', 3, 'uploads/propiedades/1755990991_ImagenPrueba.jpg', 'DFDFD', 'FDFDF', '2025-08-24', 1000000.00, NULL, NULL),
(6, 1, 1, 'Cabanga', 3, 'uploads/propiedades/1755991069_ImagenPrueba.jpg', 'En buen estado, se vende por temas personales', 'Alajuela del canton de san rafael, del lado de cabanga', '2025-08-22', 1000000.00, NULL, NULL),
(7, 2, 1, 'Cabanga', 3, 'uploads/propiedades/1755991240_ImagenPrueba.jpg', 'dsdsd', 'dsdsds', '2025-08-06', 1000000.00, NULL, NULL),
(8, 1, 1, 'Canas', 3, 'uploads/placeholder.jpg', 'Buen estado', 'Alajuela del canton de san rafael, del lado de cabanga', '2025-08-06', 1000000.00, NULL, NULL),
(9, 1, 1, 'Cabanga', 3, 'uploads/propiedades/1755997580_ImagenPrueba.jpg', 'dadsad', 'Alajuela del canton de san rafael, del lado de cabanga', '2025-08-24', 200000.00, NULL, NULL),
(10, 1, 1, 'Colorado', 3, 'uploads/placeholder.jpg', 'fddfd', 'Colorado guanacaste', '2025-08-23', 3000000.00, NULL, NULL),
(11, 2, 1, 'Cabanga', 3, 'uploads/propiedades/1755999212_ImagenPrueba.jpg', 'dsds', 'Colorado guanacaste', '2025-08-20', 200000.00, NULL, NULL),
(19, 1, 1, 'Casa pro', 4, 'uploads/propiedades/1756161143_ImagenPrueba.jpg', 'Falta de uso', 'Colorado guanacaste', '2025-08-26', 1000000.00, NULL, NULL),
(20, 2, 0, 'Cabanga', 4, 'uploads/propiedades/1756161303_ImagenPrueba.jpg', 'dssds', 'Alajuela del canton de san rafael, del lado de cabanga', '2025-08-26', 11111.00, NULL, NULL),
(21, 2, 0, 'Cabanga', 4, 'uploads/propiedades/1756161323_ImagenPrueba.jpg', 'dssds', 'Alajuela del canton de san rafael, del lado de cabanga', '2025-08-26', 11111.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_alquiler`
--

CREATE TABLE `tipo_alquiler` (
  `id` int(11) NOT NULL,
  `nombre` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_alquiler`
--

INSERT INTO `tipo_alquiler` (`id`, `nombre`) VALUES
(1, 'alquiler'),
(2, 'venta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `contrasena` varchar(300) NOT NULL,
  `privilegio` int(11) NOT NULL,
  `primerIngreso` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `nombre`, `telefono`, `email`, `usuario`, `contrasena`, `privilegio`, `primerIngreso`) VALUES
(3, 'Daniel Delgado', '85694071', 'Danidelalva05@gmail.com', 'admin', '$2y$12$6Iw7DdWx6vYQdW5N9Y7/aeS0r5Oy0NvTEpAbEnM9ZitvyFNo9ePe6', 1, 1),
(4, 'Johel M', '85577110', 'joel.mejias@gmail.com', 'Agente', '$2y$12$6Iw7DdWx6vYQdW5N9Y7/aeS0r5Oy0NvTEpAbEnM9ZitvyFNo9ePe6', 2, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `privilegio`
--
ALTER TABLE `privilegio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Ixid` (`id`);

--
-- Indices de la tabla `propiedades`
--
ALTER TABLE `propiedades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Ixid_tipo` (`id_tipo`),
  ADD KEY `Ixagente` (`agente_id`);

--
-- Indices de la tabla `tipo_alquiler`
--
ALTER TABLE `tipo_alquiler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Ixid` (`id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UkEmail` (`email`),
  ADD KEY `Ixprivilegio` (`privilegio`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `privilegio`
--
ALTER TABLE `privilegio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `propiedades`
--
ALTER TABLE `propiedades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `tipo_alquiler`
--
ALTER TABLE `tipo_alquiler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `propiedades`
--
ALTER TABLE `propiedades`
  ADD CONSTRAINT `propiedades_ibfk_1` FOREIGN KEY (`id_tipo`) REFERENCES `tipo_alquiler` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `propiedades_ibfk_2` FOREIGN KEY (`agente_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`privilegio`) REFERENCES `privilegio` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
