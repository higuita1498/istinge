-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 25-09-2018 a las 15:09:56
-- Versión del servidor: 5.7.19
-- Versión de PHP: 7.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- 
-- Base de datos: `SCA`
--

--
-- Estructura de tabla para la tabla `users`
--
DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(1) NOT NULL  PRIMARY KEY,
  `rol` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `roles` (`id`, `rol`) VALUES
('1', 'Master'),
('2', 'Empresa');




DROP TABLE IF EXISTS `tipos_identificacion`;
CREATE TABLE IF NOT EXISTS `tipos_identificacion` (
  `id` int(1) NOT NULL  PRIMARY KEY,
  `identificacion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `tipos_identificacion` (`id`, `identificacion`) VALUES
('1', 'Registro civil (RC)'),
('2', 'Tarjeta de identidad (TI)'),
('3', 'Cédula de ciudadanía (CC)'),
('4', 'Tarjeta de extranjería (TE)'),
('5', 'Cédula de extranjería (CE)'),
('6', 'Número de identificación tributaria (NIT)'),
('7', 'Pasaporte (PP)'),
('8', 'Documento de identificación extrajero (DIE)');

DROP TABLE IF EXISTS `empresas`;
CREATE TABLE IF NOT EXISTS `empresas` (
  `id` int(4) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `web` varchar(255)  NULL,
  `logo` varchar(255) NULL,
  `nit` varchar(255) NOT NULL,
  `tip_iden` int(1) NOT NULL,
  `tipo_persona` varchar(1) NULL,
  `direccion` varchar(255) NOT NULL,
  `telefono` varchar(255) NOT NULL,  
  `email` varchar(255) NOT NULL,  
  `status` int(1) NOT NULL DEFAULT '1',
  `terminos_cond` varchar(255) NULL, 
  `notas_fact` varchar(255) NULL,
  `edo_cuenta_fact`  int(1) NOT NULL DEFAULT '0',
  `moneda` varchar(255) NOT NULL DEFAULT '$' COMMENT 'Simbolo de la moneda', 
  `precision` INT(1) NOT NULL DEFAULT '0' COMMENT 'Precion decimal', 
  `carrito` INT(1) NOT NULL DEFAULT '0' COMMENT 'Tendra web',
  `img_default` varchar(255) NULL DEFAULT NULL COMMENT 'Imagen para cuando un producto no tenga',
  `sep_dec` varchar(1) NOT NULL DEFAULT '.' COMMENT 'Separador decimal', 
  `codigo` varchar(3) NULL COMMENT 'Codigo de Paises', 
  `categoria_default` int(12) null,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_empresas_tip_iden` FOREIGN KEY (`tip_iden`) REFERENCES `tipos_identificacion` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `terminos_pago`;
CREATE TABLE IF NOT EXISTS `terminos_pago` (
  `id` int(8) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `dias` varchar(3) NOT NULL,
  `empresa` int(4)  NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  CONSTRAINT `FK_terminos_pago_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO terminos_pago (id, nombre, dias) VALUES 
(1, 'De Contado', 0),
(2, '8 Días', 8),
(3, '15 Días', 15),
(4, '30 Días', 30);

DROP TABLE IF EXISTS `numeraciones`;
CREATE TABLE IF NOT EXISTS `numeraciones` (
  `id` int(8) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `empresa` int(4) NOT NULL,
  `caja` int(5) NOT NULL DEFAULT 1,
  `cajar` int(5) NOT NULL DEFAULT 1,
  `pago` int(5) NOT NULL DEFAULT 1,
  `credito` int(5) NOT NULL DEFAULT 1,
  `remision` int(5) NOT NULL DEFAULT 1,
  `cotizacion` int(5) NOT NULL DEFAULT 1,
  `orden` int(5) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  CONSTRAINT `FK_numeraciones_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(12) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `asociado` int(12) NULL,
  `empresa` int(4) NULL,
  `nro` int(12) not NULL,
  `nombre` varchar(255) NOT NULL,
  `codigo` varchar(255)  NULL,
  `estatus` int(1) not NULL DEFAULT 1,
  `descripcion` varchar(255)  NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  CONSTRAINT `FK_categorias_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO categorias (id, nro, nombre, descripcion) VALUES 
(1, 1, 'Activos', 'Bajo esta categoría se encuentran los activos que tiene la empresa'),
(2, 2, 'Egresos', 'Bajo esta categoría se encuentran todos los tipos de egresos'),
(3, 3, 'Ingresos', 'Bajo esta categoría se encuentran todos los tipos de ingresos'),
(4, 4, 'Pasivos', 'Bajo esta categoría se encuentran los pasivos de la empresa'),
(5, 5, 'Patrimonio', 'Bajo esta categoría se encuentra el patrimonio de la empresa'),
(6, 6, 'Transferencias bancarias', 'Bajo esta categoría se ubican todas las transferencias que se hagan entre bancos de la empresa');


DROP TABLE IF EXISTS `numeraciones_facturas`;
CREATE TABLE IF NOT EXISTS `numeraciones_facturas` (
  `id` int(8) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `empresa` int(4) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `prefijo` varchar(5) NULL,
  `inicio` int(5) NULL,
  `final` int(5) NULL,
  `desde` date NULL,
  `hasta` date NULL,
  `estado` int(1) NOT NULL DEFAULT 1,
  `preferida` int(1) NOT NULL,
  `nroresolucion` int(5) NULL,
  `resolucion` varchar(255) NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  CONSTRAINT `FK_numeraciones_facturas_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `vendedores`;
CREATE TABLE IF NOT EXISTS `vendedores` (
  `id` int(8) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `identificacion` varchar(255)  NULL,
  `observaciones` varchar(255) NULL,
  `empresa` int(4)  NULL,
  `estado` int(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  CONSTRAINT `FK_vendedores_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `impuestos`;
CREATE TABLE IF NOT EXISTS `impuestos` (
  `id` int(8) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `empresa` int(4)  NULL,
  `nombre` varchar(255) NOT NULL,
  `porcentaje` float(5, 2) NOT NULL,
  `tipo` int(1)  NULL COMMENT '1 IVA, 2 ICO, 3 Otro',
  `descripcion` varchar(255)  NULL,
  `estado` int(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  CONSTRAINT `FK_impuestos_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


INSERT INTO impuestos (id, nombre, porcentaje) VALUES 
(0, 'Ninguno', 0);

DROP TABLE IF EXISTS `retenciones`;
CREATE TABLE IF NOT EXISTS `retenciones` (
  `id` int(8) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `empresa` int(4)  NULL,
  `nombre` varchar(255) NOT NULL,
  `porcentaje` float(5,2) NOT NULL,
  `tipo` int(1)  NULL COMMENT '1 IVA, 2 ICO, 3 Otro',
  `descripcion` varchar(255)  NULL,
  `estado` int(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  CONSTRAINT `FK_retenciones_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(10) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nro` int(10)  NULL,
  `cedula` int(10) NULL,
  `nombres` varchar(100) NOT NULL,
  `image` varchar(100) NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(255) NULL,  
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,  
  `user_status` int(1) NOT NULL DEFAULT '1',
  `rol` int(1) NOT NULL DEFAULT '1' COMMENT '',
  `empresa` int(4) NULL,
  `created_at` timestamp  NOT NULL,
  `updated_at` timestamp  NOT NULL,
  CONSTRAINT `FK_usuarios_rol` FOREIGN KEY (`rol`) REFERENCES `roles` (`id`), 
  CONSTRAINT `FK_usuarios_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `usuarios` (`id`, `cedula`, `nombres`, `email`, `username`, `telefono`, `password`, `remember_token`, `user_status`, `rol`, `empresa`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Asministrador ', 'master@gmail.com', 'master', NULL, '$2y$10$pkikSHyuqnhQZjWqKmffk.8PW9bKKINKfm133gOaHtN1RzjlUjDBq', 'rfbbxPJMEFDVHWbmd4SQEPNHYtv0nM1psFOz2diZ7qElN4JOeDR8ZlI2Ulpr', 1, 1, NULL, '2018-09-29 04:00:00', '2018-09-29 04:00:00');

DROP TABLE IF EXISTS `bancos`;
CREATE TABLE IF NOT EXISTS `bancos` (
  `id` int(8) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nro` int(8) NOT NULL,
  `empresa` int(4) NOT NULL,
  `tipo_cta` int(1) NOT NULL COMMENT '1 Banco, 2 Tarjeta de crédito, 3 Efectivo',
  `nombre` varchar(255) NOT NULL,
  `nro_cta` varchar(255) NULL,
  `saldo` float(24, 4) NOT  NULL DEFAULT 0,
  `fecha` date NOT NULL,
  `descripcion` varchar(255) NULL,
  `estatus` int(1) NOT NULL DEFAULT 1,
  `created_at` timestamp  NOT NULL,
  `updated_at` timestamp  NOT NULL,
  CONSTRAINT `FK_bancos_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `modulo`;
CREATE TABLE IF NOT EXISTS `modulo` (
  `id` int(3) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL, 
  `modulo` varchar(255) NOT NULL, 
  `orden` int(4) NULL, 
  `estatus` int(1) NOT NULL 
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `modulo` (`id`, `nombre`, `modulo`, `orden`, `estatus`) VALUES
(1, 'Pagos Recibidos', 'ingresos', null, 1),
(2, ' Pagos Recibidos de Remisiones', 'ingresosr', null, 1),
(3, 'Pagos', 'pagos', null, 1),
(4, 'Transferencias', 'transferencia', null, 1);


CREATE TABLE IF NOT EXISTS `movimientos` (
  `id` bigint(100) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `empresa` int(4) NOT NULL,
  `banco` int(8) NOT NULL,
  `contacto` int(8) NULL,
  `tipo` int(1) NOT NULL COMMENT '1 Entrada, 2 Salida',
  `saldo` float(24, 4) NOT  NULL DEFAULT 0,
  `fecha` date NOT NULL,
  `estatus` int(1) NOT NULL DEFAULT 1,
  `conciliado` int(1) NOT NULL DEFAULT 0,
  `modulo` int(5) NOT NULL,
  `id_modulo` int(5) NOT NULL, 
  `transferencia` int(5) NULL,
  `descripcion` varchar(255) NULL,
  `created_at` timestamp  NOT NULL,
  `updated_at` timestamp  NOT NULL,
  CONSTRAINT `FK_movimientos_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_movimientos_banco` FOREIGN KEY (`banco`) REFERENCES `bancos` (`id`),
  CONSTRAINT `FK_movimientos_contacto` FOREIGN KEY (`contacto`) REFERENCES `contactos` (`id`) 
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `tipos_empresa`;
CREATE TABLE IF NOT EXISTS `tipos_empresa` (
  `id` int(8) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` varchar(255) NULL,
  `empresa` int(4)  NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  CONSTRAINT `FK_tipos_empresa_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `contactos`;
CREATE TABLE IF NOT EXISTS `contactos` (
  `id` int(8) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `empresa` int(4) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `nit` varchar(255) NOT NULL,
  `tip_iden` int(1) NOT NULL,
  `tipo_contacto` int(1) not NULL COMMENT '0 Cliente, 1 Proovedor, 2 Ambos',
  `tipo_empresa` int(8) NULL,
  `direccion` varchar(255)  NULL,
  `ciudad` varchar(255)  NULL,
  `telefono1` varchar(255) NOT NULL,  
  `telefono2` varchar(255)  NULL,
  `celular` varchar(255)  NULL,
  `fax` varchar(255)  NULL,  
  `observaciones` text  NULL,
  `email` varchar(255) NULL,  
  `status` int(1) NOT NULL DEFAULT 1,
  `vendedor` int(8) null,
  `lista_precio` int(12) null,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_contactos_tip_iden` FOREIGN KEY (`tip_iden`) REFERENCES `tipos_identificacion` (`id`), 
  CONSTRAINT `FK_contactos_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_contactos_tipo_empresa` FOREIGN KEY (`tipo_empresa`) REFERENCES `tipos_empresa` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `asociados_contactos` (
  `contacto` int(8) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `telefono` varchar(255) NOT NULL, 
  `celular` varchar(255)  NULL,
  `email` varchar(255) NULL, 
  `notificacion` int(1) not NULL, 
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_asociados_contactos` FOREIGN KEY (`contacto`) REFERENCES `contactos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `medidas`;
CREATE TABLE IF NOT EXISTS `medidas` (
  `id` int(1) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `medida` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `medidas` (`id`, `medida`) VALUES
(1, 'Unidad'), 
(2, 'Longitud'),
(3, 'Área'), 
(4, 'Volumen'),
(5, 'Peso');

DROP TABLE IF EXISTS `unidades_medida`;
CREATE TABLE IF NOT EXISTS `unidades_medida` (
  `id` int(2) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `unidad` varchar(255) NOT NULL, 
  `tipo` int(1) NULL,
  CONSTRAINT `FK_unidades_medida_tipo` FOREIGN KEY (`tipo`) REFERENCES `medidas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `unidades_medida` (`unidad`, `tipo`) VALUES
('Unidad', 1),
('Servicio', 1),
('Pieza', 1),
('Millar', 1),
('Centímetro (cm)', 2),
('Metro (m)', 2),
('Pulgada', 2),
('Área', 3),
('Centímetro cuadrado (cm3)', 3),
('Pulgada cuadrada', 3),
('Mililitro (mL)', 4),
('Litro (L)', 4),
('Galón', 4),
('Gramo (g)', 5),
('Kilogramo (Kg)', 5),
('Tonelada', 5),
('Libra', 5),
('Centímetro (cm)', 5);

DROP TABLE IF EXISTS `bodegas`;
CREATE TABLE IF NOT EXISTS `bodegas` (
  `id` int(12) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nro` int(12) NOT NULL,
  `empresa` int(4) NOT NULL,
  `bodega` varchar(255) NOT NULL,
  `direccion` text NULL,
  `observaciones` text NULL,
  `principal` int(1) NOT NULL DEFAULT 0,
  `status` int(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_bodegas_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `lista_precios`;
CREATE TABLE IF NOT EXISTS `lista_precios` (
  `id` int(12) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nro` int(12) NOT NULL,
  `empresa` int(4) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tipo` int(1) NOT NULL COMMENT '1 Porcentaje, 0 Valor',
  `status` int(1) NOT NULL DEFAULT 1,
  `porcentaje` float(5, 2) NULL COMMENT '0% 99%',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_lista_precios_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`)  
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `inventario`;
CREATE TABLE IF NOT EXISTS `inventario` (
  `id` int(12) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `empresa` int(4) NOT NULL,
  `producto` varchar(255) NOT NULL,
  `tipo_producto` int(1) NOT NULL DEFAULT 1 COMMENT '1 Inventariable, 2 Intangible',
  `ref` varchar(255) NOT NULL,
  `precio` float(24, 4) NOT  NULL DEFAULT 0,
  `descripcion` varchar(255) NULL,
  `id_impuesto` int(8)  NULL COMMENT 'asociado a la tabla impuestos',
  `impuesto` float(5, 2) NOT NULL COMMENT '0% 99%',
  `imagen` varchar(255) NULL,
  `unidad` int(2) NULL,
  `costo_unidad` float(24, 4) NULL DEFAULT NULL,  
  `nro` float(24, 4) NULL DEFAULT 0,
  `inicial` float(24, 4) NULL DEFAULT 0,
  `categoria` int(12) NULL,
  `status` int(1) NOT NULL DEFAULT 1,
  `publico` int(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_inventario_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_inventario_unidad` FOREIGN KEY (`unidad`) REFERENCES `unidades_medida` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `productos_bodegas`;
CREATE TABLE IF NOT EXISTS `productos_bodegas` (
  `id` bigint(20) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `empresa` int(4) NOT NULL,
  `bodega` int(12) NOT NULL,
  `producto` int(12) NOT NULL,
  `nro` float(24, 4) NULL DEFAULT 0,
  `inicial` float(24, 4) NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_productos_bodegas_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_productos_bodegas_producto` FOREIGN KEY (`producto`) REFERENCES `inventario` (`id`),
  CONSTRAINT `FK_productos_bodegas_bodega` FOREIGN KEY (`bodega`) REFERENCES `bodegas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `transferencias_bodegas` (
  `id` bigint(20) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nro` int(12) NOT NULL,
  `empresa` int(4) NOT NULL,
  `bodega_origen` int(12) NOT NULL,
  `bodega_destino` int(12) NOT NULL,
  `fecha` date NOT NULL,
  `observaciones` text NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_transferencias_bodegas_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`),
  CONSTRAINT `FK_transferencias_bodegas_bodega_origen` FOREIGN KEY (`bodega_origen`) REFERENCES `bodegas` (`id`),
  CONSTRAINT `FK_transferencias_bodegas_bodega_destino` FOREIGN KEY (`bodega_destino`) REFERENCES `bodegas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `productos_transferencia` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `transferencia` bigint(20) NOT NULL,
  `bodega_origen` int(12) NOT NULL,
  `bodega_destino` int(12) NOT NULL,  
  `producto` int(12) NOT NULL,
  `nro` float(24, 4) NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_productos_transferencia_transferencia` FOREIGN KEY (`transferencia`) REFERENCES `transferencias_bodegas` (`id`), 
  CONSTRAINT `FK_productos_transferencia_producto` FOREIGN KEY (`producto`) REFERENCES `inventario` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `productos_precios`;
CREATE TABLE IF NOT EXISTS `productos_precios` (
  `id` bigint(20) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `empresa` int(4) NOT NULL,
  `lista` int(12) NOT NULL,
  `producto` int(12) NOT NULL,
  `precio` float(24, 4) NOT  NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_productos_precios_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_productos_precios_lista_precios` FOREIGN KEY (`lista`) REFERENCES `lista_precios` (`id`), 
  CONSTRAINT `FK_productos_precios_producto` FOREIGN KEY (`producto`) REFERENCES `inventario` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `ajuste_inventario` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `empresa` int(4) NOT NULL,
  `nro` int(12) NOT NULL,
  `bodega` int(12) NOT NULL,
  `ajuste` int(1) NOT NULL COMMENT '0 disminucion, 1 incremento',  
  `producto` int(12) NOT NULL,
  `cant` float(24, 4) NULL DEFAULT 0,
  `fecha` date NOT NULL,
  `observaciones` text NULL,
  `costo_unitario` float(24, 4) NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_ajuste_inventario_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_ajuste_inventario_bodega` FOREIGN KEY (`bodega`) REFERENCES `bodegas` (`id`), 
  CONSTRAINT `FK_ajuste_inventario_producto` FOREIGN KEY (`producto`) REFERENCES `inventario` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
--
-- Table structure for table `imagenesxinventario`
--

CREATE TABLE if not EXISTS `imagenesxinventario` (
`id` bigint(20) not NULL AUTO_INCREMENT PRIMARY KEY,
`producto` int(12) NOT NULL,
`imagen` text NOT NULL,
`created_at` timestamp NULL DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT NULL, 
CONSTRAINT FK_imagenesxinventario_producto FOREIGN KEY (producto) REFERENCES inventario(id)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `inventario_meta` (
  `id` bigint(30) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `empresa` int(4) NOT NULL,
  `id_producto`  int(12) NOT NULL,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` text NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_inventario_meta_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `inventario_volatil` (
  `id` int(12) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `empresa` int(4) NOT NULL,
  `producto` varchar(255) NOT NULL,
  `ref` varchar(255) NULL,
  `precio` float(24, 4) NULL DEFAULT 0,
  `descripcion` varchar(255) NULL,
  `id_impuesto` int(8)  NULL COMMENT 'asociado a la tabla impuestos',
  `impuesto` float(5, 2) NULL COMMENT '0% 99%',
  `imagen` varchar(255) NULL,
  `unidad` int(2) NULL,
  `status` int(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_inventario_volatil_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_inventario_volatil_unidad` FOREIGN KEY (`unidad`) REFERENCES `unidades_medida` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `inventario_volatil_meta` (
  `id` bigint(30) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `empresa` int(4) NOT NULL,
  `id_producto`  int(12) NOT NULL,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` text NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_inventario_volatil_meta_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `campos_extra_inventario`;
CREATE TABLE IF NOT EXISTS `campos_extra_inventario` (
  `id` int(12) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `empresa` int(4) NOT NULL,
  `campo` varchar(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` varchar(255) NULL,
  `varchar` int(8) NULL,
  `tipo` int(1) NOT NULL COMMENT 'Si es requerido' DEFAULT 0,
  `default` varchar(255) NULL,
  `status` int(1) NOT NULL DEFAULT 1,
  `autocompletar` int(1) NOT NULL DEFAULT 1,
  `tabla` int(1) NOT NULL COMMENT 'Si se mostrara en la tabla' DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_campos_extra_inventario_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `factura`;
CREATE TABLE IF NOT EXISTS `factura` (
  `id` bigint(20) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nro` int(10) NULL,
  `cot_nro` int(10) NULL,
  `codigo` varchar(10) NULL,
  `empresa` int(4) NOT NULL,
  `numeracion` int(8) NULL,
  `vendedor` int(8) NULL,
  `plazo` varchar(8) NULL,
  `tipo` int(1) NOT NULL DEFAULT 1 COMMENT '1 Factura, 2 Cotizacion',
  `cliente` int(8) NULL,
  `fecha` date NOT NULL,
  `vencimiento` date  NULL,
  `observaciones` text  NULL,
  `estatus` int(8) NOT NULL DEFAULT 1 COMMENT '1 Abierta, 0 Cerrada, 2 Por Cotizar',
  `notas` text  NULL,
  `term_cond` text  NULL,
  `facnotas` text  NULL,
  `lista_precios` int(12) NULL,
  `bodega` int(12) NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_factura_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_factura_cliente` FOREIGN KEY (`cliente`) REFERENCES `contactos` (`id`), 
  CONSTRAINT `FK_factura_vendedor` FOREIGN KEY (`vendedor`) REFERENCES `vendedores` (`id`), 
  CONSTRAINT `FK_factura_numeracion` FOREIGN KEY (`numeracion`) REFERENCES `numeraciones_facturas` (`id`),
  CONSTRAINT `FK_factura_precios_lista_precios` FOREIGN KEY (`lista_precios`) REFERENCES `lista_precios` (`id`),
  CONSTRAINT `FK_factura__bodega` FOREIGN KEY (`bodega`) REFERENCES `bodegas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `factura_contacto` (
  `factura` bigint(20) NOT NULL  PRIMARY KEY,
  `nombre` varchar(255) NOT NULL,
  `telefono` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_factura_factura` FOREIGN KEY (`factura`) REFERENCES `factura` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `items_factura` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `factura` bigint(20) NOT NULL,
  `tipo_inventario` int(1) NOT NULL COMMENT '1 inventario 2 inventario_volatilo' DEFAULT 1,
  `producto` int(12) NULL,
  `ref` varchar(255)  NULL,
  `precio` float(24, 4) NOT  NULL DEFAULT 0,
  `descripcion` varchar(255) NULL,
  `impuesto` float(5, 2) NOT NULL COMMENT '0% 99%',
  `id_impuesto` int(8)  NULL COMMENT 'asociado a la tabla impuestos',
  `cant` float(24, 4) NOT NULL,
  `desc` float(5, 2) NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_items_factura_factura` FOREIGN KEY (`factura`) REFERENCES `factura` (`id`), 
  CONSTRAINT `FK_items_factura_producto` FOREIGN KEY (`producto`) REFERENCES `inventario` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `facturas_recurrentes` (
  `id` bigint(20) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nro` varchar(10) NULL,
  `numeracion` int(8) NOT NULL,
  `empresa` int(4) NOT NULL,  
  `plazo` varchar(8) NULL,
  `cliente` int(8) NULL,
  `frecuencia` int(8) NULL,
  `fecha` date NOT NULL,
  `vencimiento` date  NULL,
  `proxima` date NOT NULL,
  `observaciones` text  NULL,
  `notas` text  NULL,
  `term_cond` text  NULL,
  `lista_precios` int(12) NULL,
  `bodega` int(12) NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_facturas_recurrentes_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_facturas_recurrentes_cliente` FOREIGN KEY (`cliente`) REFERENCES `contactos` (`id`), 
  CONSTRAINT `FK_facturas_recurrentes_numeracion` FOREIGN KEY (`numeracion`) REFERENCES `numeraciones_facturas` (`id`),
  CONSTRAINT `FK_facturas_recurrentes_precios_lista_precios` FOREIGN KEY (`lista_precios`) REFERENCES `lista_precios` (`id`),
  CONSTRAINT `FK_facturas_recurrentes__bodega` FOREIGN KEY (`bodega`) REFERENCES `bodegas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `items_factura_recurrente` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `factura_recurrente` bigint(20) NOT NULL,
  `producto` int(12) NULL,
  `ref` varchar(255)  NULL,
  `precio` float(24, 4) NOT  NULL DEFAULT 0,
  `descripcion` varchar(255) NULL,
  `impuesto` float(5, 2) NOT NULL COMMENT '0% 99%',
  `id_impuesto` int(8)  NULL COMMENT 'asociado a la tabla impuestos',
  `cant` float(24, 4) NOT NULL,
  `desc` float(5, 2) NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_items_factura_recurrente_factura` FOREIGN KEY (`factura_recurrente`) REFERENCES `facturas_recurrentes` (`id`), 
  CONSTRAINT `FK_items_factura_recurrente_producto` FOREIGN KEY (`producto`) REFERENCES `inventario` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;





DROP TABLE IF EXISTS `soporte`;
CREATE TABLE IF NOT EXISTS `soporte` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `asociada` bigint(40) NULL,
  `empresa` int(4) NOT NULL,
  `usuario` int(10) NOT NULL,
  `modulo` int(3) NOT NULL,
  `imagen` varchar(255)  NULL,
  `titulo` text NOT NULL,
  `error` text NOT NULL,
  `estatus` int(8) NOT NULL DEFAULT 1 COMMENT '1 Pendiente, 2 Resuelto, 0 Cerrada',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_soporte_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_soporte_modulo` FOREIGN KEY (`modulo`) REFERENCES `modulo` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `metodos_pago`;
CREATE TABLE IF NOT EXISTS `metodos_pago` (
  `id` int(2) NOT NULL  PRIMARY KEY,
  `metodo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `metodos_pago` (`id`, `metodo`) VALUES
(1, 'Efectivo'),
(2, 'Consignación'),
(3, 'Transferencia'),
(4, 'Cheque'),
(5, 'Tarjeta crédito'),
(6, 'Tarjeta débito');


CREATE TABLE IF NOT EXISTS `ingresos` (
  `id` bigint(20) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nro` int(5) NOT NULL,
  `empresa` int(4) NOT NULL,
  `cliente` int(8) NULL,
  `cuenta` int(8) NOT NULL,
  `metodo_pago` int(2) NULL,
  `fecha` date NOT  NULL,
  `observaciones` text  NULL,
  `notas` text  NULL,
  `tipo` int(1) NOT NULL DEFAULT 1 COMMENT '1 Asociada a Facturas, 2 Asociada a categorias, 3 Asociada a Notas de Credito, 4 Asociada a transferencias',
  `estatus` int(1) NOT NULL DEFAULT 1 COMMENT '1 Consolidado, 0 No Consolidado',
  `nota_debito` bigint(20) null,
  `total_debito` float(24,4) NULL,
  `nro_devolucion` bigint(20) NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_ingresos_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_ingresos_cliente` FOREIGN KEY (`cliente`) REFERENCES `contactos` (`id`), 
  CONSTRAINT `FK_ingresos_cuenta` FOREIGN KEY (`cuenta`) REFERENCES `bancos` (`id`) , 
  CONSTRAINT `FK_ingresos_metodo_pago` FOREIGN KEY (`metodo_pago`) REFERENCES `metodos_pago` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ingresos_factura` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `ingreso` bigint(20) NOT NULL,
  `factura` bigint(20) NOT NULL,
  `pagado` float(24,4) NOT  NULL,
  `pago` float(24, 4) NOT  NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_iingresos_factura_factura` FOREIGN KEY (`factura`) REFERENCES `factura` (`id`), 
  CONSTRAINT `FK_iingresos_factura_ingreso` FOREIGN KEY (`ingreso`) REFERENCES `ingresos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ingresos_categoria` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `ingreso` bigint(20) NOT NULL,
  `categoria` int(12) NOT NULL,
  `valor` float(24,4) NOT  NULL,
  `impuesto` float(5, 2) NOT NULL COMMENT '0% 99%',
  `id_impuesto` int(8)  NULL COMMENT 'asociado a la tabla impuestos',
  `descripcion` varchar(255) NULL,
  `cant` float(24,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_ingresos_categoria_ingreso` FOREIGN KEY (`ingreso`) REFERENCES `ingresos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ingresos_retenciones` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `ingreso` bigint(20) NOT NULL,
  `factura` bigint(40) NULL,
  `valor` float(24,4) NOT  NULL,
  `retencion` float(5, 2) NOT NULL COMMENT '0% 99%',
  `id_retencion` int(8)  NULL COMMENT 'asociado a la tabla impuestos',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_iingresos_retenciones_factura` FOREIGN KEY (`factura`) REFERENCES `factura` (`id`), 
  CONSTRAINT `FK_ingresos_retenciones_id_retencion` FOREIGN KEY (`id_retencion`) REFERENCES `retenciones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE if not EXISTS `prefijos_telefonicos`(
  `id` int(4) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(200) not NULL,
  `name` varchar(200) not NULL,
  `nom` varchar(200) not NULL,
  `iso2` varchar(200) not NULL,
  `iso3` varchar(200) not NULL,
  `phone_code` varchar(200) not NULL
)ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `remisiones`;
CREATE TABLE IF NOT EXISTS `remisiones` (
  `id` bigint(20) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nro` int(10) NULL,
  `empresa` int(4) NOT NULL,
  `vendedor` int(8) NULL,
  `documento` int(1) NOT NULL DEFAULT 1 COMMENT '1 Remision, 2 Orden Servicio',
  `cliente` int(8) NOT NULL,
  `fecha` date NOT NULL,
  `vencimiento` date  NULL,
  `observaciones` text  NULL,
  `estatus` int(8) NOT NULL DEFAULT 1 ,
  `notas` text  NULL,
  `lista_precios` int(12) NULL,
  `bodega` int(12) NULL,  
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_remisiones_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_remisiones_cliente` FOREIGN KEY (`cliente`) REFERENCES `contactos` (`id`), 
  CONSTRAINT `FK_remisiones_vendedor` FOREIGN KEY (`vendedor`) REFERENCES `vendedores` (`id`),
  CONSTRAINT `FK_remisiones_lista_precios` FOREIGN KEY (`lista_precios`) REFERENCES `lista_precios` (`id`),
  CONSTRAINT `FK_remisiones__bodega` FOREIGN KEY (`bodega`) REFERENCES `bodegas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;




CREATE TABLE IF NOT EXISTS `items_remision` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `remision` bigint(20) NOT NULL,
  `producto` int(12) NULL,
  `ref` varchar(255) NOT NULL,
  `precio` float(24,2) NOT  NULL,
  `descripcion` varchar(255) NULL,
  `impuesto` float(5, 2) NOT NULL COMMENT '0% 99%',
  `id_impuesto` int(8)  NULL COMMENT 'asociado a la tabla impuestos',
  `cant` float(24,2) NOT NULL,
  `desc` float(5, 2) NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_items_remision_remision` FOREIGN KEY (`remision`) REFERENCES `remisiones` (`id`), 
  CONSTRAINT `FK_items_remision_producto` FOREIGN KEY (`producto`) REFERENCES `inventario` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `ingresosr` (
  `id` bigint(20) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nro` int(5) NOT NULL,
  `empresa` int(4) NOT NULL,
  `cliente` int(8) NULL,
  `cuenta` int(8) NOT NULL,
  `metodo_pago` int(2) NULL,
  `fecha` date NOT  NULL,
  `observaciones` text  NULL,
  `notas` text  NULL,
  `estatus` int(1) NOT NULL DEFAULT 1 COMMENT '1 Consolidado, 0 No Consolidado',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_ingresosr_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_ingresosr_cliente` FOREIGN KEY (`cliente`) REFERENCES `contactos` (`id`), 
  CONSTRAINT `FK_ingresosr_cuenta` FOREIGN KEY (`cuenta`) REFERENCES `bancos` (`id`) , 
  CONSTRAINT `FK_ingresosr_metodo_pago` FOREIGN KEY (`metodo_pago`) REFERENCES `metodos_pago` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `ingresosr_remisiones` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `ingreso` bigint(20) NOT NULL,
  `remision` bigint(20) NOT NULL,
  `pagado` float(24,4) NOT  NULL,
  `pago` float(24, 4) NOT  NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_ingresosr_remisiones_remision` FOREIGN KEY (`remision`) REFERENCES `remisiones` (`id`), 
  CONSTRAINT `FK_ingresosr_remisiones_ingreso` FOREIGN KEY (`ingreso`) REFERENCES `ingresosr` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ingresosr_retenciones` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `ingreso` bigint(20) NOT NULL,
  `remision` bigint(20) NULL,
  `valor` float(24,4) NOT  NULL,
  `retencion` float(5, 2) NOT NULL COMMENT '0% 99%',
  `id_retencion` int(8)  NULL COMMENT 'asociado a la tabla impuestos',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_ingresosr_retenciones_remision` FOREIGN KEY (`remision`) REFERENCES `remisiones` (`id`), 
  CONSTRAINT `FK_ingresosr_retenciones_id_retencion` FOREIGN KEY (`id_retencion`) REFERENCES `retenciones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `tipos_nota_credito` (
  `id` int(1) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `tipo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tipos_nota_credito` (`id`, `tipo`) VALUES
(1, 'Devolución de parte de los bienes; no aceptación de partes del servicio'), 
(2, 'Anulación de factura electrónica'),
(3, 'Rebaja total aplicada'), 
(4, 'Descuento total aplicado'),
(5, 'Rescisión: nulidad por falta de requisitos'),
(6, 'Otros');

CREATE TABLE IF NOT EXISTS `notas_credito` (
  `id` bigint(20) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nro` int(10) NULL,
  `empresa` int(4) NOT NULL,
  `tipo` int(1) NULL,
  `cliente` int(8) NULL,
  `fecha` date NOT NULL,
  `observaciones` text  NULL,
  `estatus` int(8) NOT NULL DEFAULT 1 COMMENT '1 Abierta, 0 Cerrada, 2 Por Cotizar',
  `notas` text  NULL,
  `lista_precios` int(12) NULL,
  `bodega` int(12) NULL,  
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_notas_credito_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_notas_credito_cliente` FOREIGN KEY (`cliente`) REFERENCES `contactos` (`id`), 
  CONSTRAINT `FK_notas_credito_tipo` FOREIGN KEY (`tipo`) REFERENCES `tipos_nota_credito` (`id`),
  CONSTRAINT `FK_notas_credito_lista_precios` FOREIGN KEY (`lista_precios`) REFERENCES `lista_precios` (`id`),
  CONSTRAINT `FK_notas_credito__bodega` FOREIGN KEY (`bodega`) REFERENCES `bodegas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `items_notas` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nota` bigint(20) NOT NULL,
  `producto` int(12) NULL,
  `ref` varchar(255)  NULL,
  `precio` float(24, 4) NOT  NULL DEFAULT 0,
  `descripcion` varchar(255) NULL,
  `impuesto` float(5, 2) NOT NULL COMMENT '0% 99%',
  `id_impuesto` int(8)  NULL COMMENT 'asociado a la tabla impuestos',
  `cant` float(24, 4) NOT NULL,
  `desc` float(5, 2) NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_items_notas_nota` FOREIGN KEY (`nota`) REFERENCES `notas_credito` (`id`), 
  CONSTRAINT `FK_items_notas_producto` FOREIGN KEY (`producto`) REFERENCES `inventario` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `notas_factura` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nota` bigint(20) NOT NULL,
  `factura` bigint(20) NOT NULL,
  `pago` float(24, 4) NOT  NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_notas_factura_factura` FOREIGN KEY (`factura`) REFERENCES `factura` (`id`), 
  CONSTRAINT `FK_notas_factura_nota` FOREIGN KEY (`nota`) REFERENCES `notas_credito` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `notas_devolucion_dinero` (
  `id` bigint(20) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nota` bigint(20) NOT NULL,
  `empresa` int(4) NOT NULL,
  `fecha` date NOT NULL,
  `monto` float(24, 4) NOT  NULL,
  `cuenta` int(8) NOT NULL,
  `observaciones` text  NULL,
  `estatus` int(1) NOT NULL DEFAULT 1 COMMENT '1 Consolidado, 0 No Consolidado',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_notas_devolucion_dinero_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_notas_devolucion_dinero_nota` FOREIGN KEY (`nota`) REFERENCES `notas_credito` (`id`), 
  CONSTRAINT `FK_notas_devolucion_dinero_cuenta` FOREIGN KEY (`cuenta`) REFERENCES `bancos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `factura_proveedores` (
  `id` bigint(20) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nro` varchar(10) NULL,
  `orden_nro` varchar(10) NULL,
  `codigo` varchar(10) NULL,
  `empresa` int(4) NOT NULL,
  `tipo` int(1) NOT NULL DEFAULT 1 COMMENT '1 Factura a Proveedores, 2 Orden de Compra',
  `proveedor` int(8) NULL,
  `fecha` date NULL,
  `vencimiento` date  NULL,
  `fecha_factura` date  NULL,
  `vencimiento_factura` text  NULL,
  `estatus` int(8) NOT NULL DEFAULT 1 COMMENT '1 Abierta, 0 Cerrada, 2 Por Facturar',
  `notas` text  NULL,  
  `term_cond` text  NULL,   
  `observaciones_factura` text  NULL,  
  `observaciones` text  NULL,   
  `bodega` int(12) NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_factura_proveedores_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_factura_proveedores_cliente` FOREIGN KEY (`proveedor`) REFERENCES `contactos` (`id`),
  CONSTRAINT `FK_factura_proveedores__bodega` FOREIGN KEY (`bodega`) REFERENCES `bodegas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `items_factura_proveedor` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `factura` bigint(20) NOT NULL,
  `tipo_item` int(1) NOT NULL COMMENT '1 inventario 2 categorias' DEFAULT 1,
  `producto` int(12) NULL,
  `ref` varchar(255)  NULL,
  `precio` float(24, 4) NOT  NULL DEFAULT 0,
  `descripcion` varchar(255) NULL,
  `impuesto` float(5, 2) NOT NULL COMMENT '0% 99%',
  `id_impuesto` int(8)  NULL COMMENT 'asociado a la tabla impuestos',
  `cant` float(24, 4) NOT NULL,
  `desc` float(5, 2) NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_items_factura_proveedor_factura` FOREIGN KEY (`factura`) REFERENCES `factura_proveedores` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `factura_proveedores_retenciones` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `factura` bigint(40) NULL,
  `valor` float(24,4) NOT  NULL,
  `retencion` float(5, 2) NOT NULL COMMENT '0% 99%',
  `id_retencion` int(8)  NULL COMMENT 'asociado a la tabla impuestos',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_factura_proveedores_retenciones_factura` FOREIGN KEY (`factura`) REFERENCES `factura_proveedores` (`id`), 
  CONSTRAINT `FK_factura_proveedores_retenciones_id_retencion` FOREIGN KEY (`id_retencion`) REFERENCES `retenciones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `gastos` (
  `id` bigint(20) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nro` int(5) NOT NULL,
  `empresa` int(4) NOT NULL,
  `beneficiario` int(8) NULL,
  `cuenta` int(8) NOT NULL,
  `metodo_pago` int(2) NULL,
  `fecha` date NOT  NULL,
  `observaciones` text  NULL,
  `notas` text  NULL,
  `nota_credito` bigint(20) null,
  `total_credito` float(24,4) NULL,
  `nro_devolucion` bigint(20) NULL,
  `tipo` int(1) NOT NULL DEFAULT 1 COMMENT '1 Asociada a Facturas, 2 Asociada a categorias, 3 asociada a nota de credito',
  `estatus` int(1) NOT NULL DEFAULT 1 COMMENT '1 Consolidado, 0 No Consolidado',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_gastos_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_gastos_beneficiario` FOREIGN KEY (`beneficiario`) REFERENCES `contactos` (`id`), 
  CONSTRAINT `FK_gastos_cuenta` FOREIGN KEY (`cuenta`) REFERENCES `bancos` (`id`) , 
  CONSTRAINT `FK_gastos_metodo_pago` FOREIGN KEY (`metodo_pago`) REFERENCES `metodos_pago` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `gastos_factura` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `gasto` bigint(20) NOT NULL,
  `factura` bigint(20) NOT NULL,
  `pagado` float(24,4) NOT  NULL,
  `pago` float(24, 4) NOT  NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_gastos_factura_factura` FOREIGN KEY (`factura`) REFERENCES `factura_proveedores` (`id`), 
  CONSTRAINT `FK_gastos_factura_gasto` FOREIGN KEY (`gasto`) REFERENCES `gastos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `gastos_categoria` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `gasto` bigint(20) NOT NULL,
  `categoria` int(12) NOT NULL,
  `valor` float(24,4) NOT  NULL,
  `impuesto` float(5, 2) NOT NULL COMMENT '0% 99%',
  `id_impuesto` int(8)  NULL COMMENT 'asociado a la tabla impuestos',
  `descripcion` varchar(255) NULL,
  `cant` float(24,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_gastos_categoria_gasto` FOREIGN KEY (`gasto`) REFERENCES `gastos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `gastos_retenciones` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `gasto` bigint(20) NOT NULL,
  `factura` bigint(40) NULL,
  `valor` float(24,4) NOT  NULL,
  `retencion` float(5, 2) NOT NULL COMMENT '0% 99%',
  `id_retencion` int(8)  NULL COMMENT 'asociado a la tabla impuestos',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_igastos_retenciones_factura` FOREIGN KEY (`factura`) REFERENCES `factura_proveedores` (`id`), 
  CONSTRAINT `FK_gastos_retenciones_id_retencion` FOREIGN KEY (`id_retencion`) REFERENCES `retenciones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `gastos_recurrentes` (
  `id` bigint(20) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nro` int(5) NOT NULL,
  `empresa` int(4) NOT NULL,
  `beneficiario` int(8) NULL,
  `cuenta` int(8) NOT NULL,
  `metodo_pago` int(2) NULL,
  `fecha` date NOT  NULL,
  `vencimiento` date NULL,

  `frecuencia` int(8) NULL,
  `proxima` date NOT NULL,
  `observaciones` text  NULL,
  `notas` text  NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_gastos_recurrentes_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_gastos_recurrentes_beneficiario` FOREIGN KEY (`beneficiario`) REFERENCES `contactos` (`id`), 
  CONSTRAINT `FK_gastos_recurrentes_cuenta` FOREIGN KEY (`cuenta`) REFERENCES `bancos` (`id`) , 
  CONSTRAINT `FK_gastos_recurrentes_metodo_pago` FOREIGN KEY (`metodo_pago`) REFERENCES `metodos_pago` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `gastos_recurrentes_categoria` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `gasto_recurrente` bigint(20) NOT NULL,
  `categoria` int(12) NOT NULL,
  `valor` float(24,4) NOT  NULL,
  `impuesto` float(5, 2) NOT NULL COMMENT '0% 99%',
  `id_impuesto` int(8)  NULL COMMENT 'asociado a la tabla impuestos',
  `descripcion` varchar(255) NULL,
  `cant` float(24,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_gastos_recurrentes_categoria_gasto` FOREIGN KEY (`gasto_recurrente`) REFERENCES `gastos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `notas_debito` (
  `id` bigint(20) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nro` varchar(10) NULL,
  `codigo` varchar(10) NULL,
  `empresa` int(4) NOT NULL,
  `proveedor` int(8) NULL,
  `fecha` date NOT NULL,
  `estatus` int(8) NOT NULL DEFAULT 1,
  `observaciones` text  NULL,
  `bodega` int(12) NULL,  
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_notas_debito_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_notas_debito_proveedor` FOREIGN KEY (`proveedor`) REFERENCES `contactos` (`id`), 
  CONSTRAINT `FK_notas_debito__bodega` FOREIGN KEY (`bodega`) REFERENCES `bodegas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `items_notas_debito` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nota` bigint(20) NOT NULL,
  `tipo_item` int(1) NOT NULL COMMENT '1 inventario 2 categorias' DEFAULT 1,
  `producto` int(12) NULL,
  `ref` varchar(255)  NULL,
  `precio` float(24, 4) NOT  NULL DEFAULT 0,
  `descripcion` varchar(255) NULL,
  `impuesto` float(5, 2) NOT NULL COMMENT '0% 99%',
  `id_impuesto` int(8)  NULL COMMENT 'asociado a la tabla impuestos',
  `cant` float(24, 4) NOT NULL,
  `desc` float(5, 2) NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_items_notas_debito_nota` FOREIGN KEY (`nota`) REFERENCES `notas_debito` (`id`) 
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `notas_factura_debito` (
  `id` bigint(40) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nota` bigint(20) NOT NULL,
  `factura` bigint(20) NOT NULL,
  `pago` float(24, 4) NOT  NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_notas_factura_debito_factura` FOREIGN KEY (`factura`) REFERENCES `factura_proveedores` (`id`), 
  CONSTRAINT `FK_notas_factura_debito_nota` FOREIGN KEY (`nota`) REFERENCES `notas_debito` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `notas_debito_devolucion_dinero` (
  `id` bigint(20) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `nota` bigint(20) NOT NULL,
  `empresa` int(4) NOT NULL,
  `fecha` date NOT NULL,
  `monto` float(24, 4) NOT  NULL,
  `cuenta` int(8) NOT NULL,
  `observaciones` text  NULL,
  `estatus` int(1) NOT NULL DEFAULT 1 COMMENT '1 Consolidado, 0 No Consolidado',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL, 
  CONSTRAINT `FK_notas_debito_devolucion_dinero_empresa` FOREIGN KEY (`empresa`) REFERENCES `empresas` (`id`), 
  CONSTRAINT `FK_notas_debito_devolucion_dinero_nota` FOREIGN KEY (`nota`) REFERENCES `notas_debito` (`id`), 
  CONSTRAINT `FK_notas_debito_devolucion_dinero_cuenta` FOREIGN KEY (`cuenta`) REFERENCES `bancos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*
CREATE TABLE IF NOT EXISTS `logistica`(
  `id` bigint(20) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `cliente` int(8) NOT NULL,
  `guia` varchar(200) NOT NULL,
  `foto_producto` varchar(200) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  `nombre_receptor` varchar(200) NOT NULL,
  `nit_receptor` varchar(200) NOT NULL,
  `empresa_envio` varchar(200) NULL,
  `guia_envio` varchar(200) NULL,
  `documento_envio` varchar(200) NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
*/


INSERT INTO `prefijos_telefonicos` (`nombre`, `name`, `nom`, `iso2`, `iso3`, `phone_code`)  VALUES
("Afganistán","Afghanistan","Afghanistan","AF","AFG","93"),
("Albania","Albania","Albanie","AL","ALB","355"),
("Alemania","Germany","Allemagne","DE","DEU","49"),
("Algeria","Algeria","Algérie","DZ","DZA","213"),
("Andorra","Andorra","Andorra","AD","AND","376"),
("Angola","Angola","Angola","AO","AGO","244"),
("Anguila","Anguilla","Anguilla","AI","AIA","1-264"),
("Antártida","Antarctica","L'Antarctique","AQ","ATA","672"),
("Antigua y Barbuda","Antigua and Barbuda","Antigua et Barbuda","AG","ATG","1-268"),
("Antillas Neerlandesas","Netherlands Antilles","Antilles Néerlandaises","AN","ANT","599"),
("Arabia Saudita","Saudi Arabia","Arabie Saoudite","SA","SAU","966"),
("Argentina","Argentina","Argentine","AR","ARG","54"),
("Armenia","Armenia","L'Arménie","AM","ARM","374"),
("Aruba","Aruba","Aruba","AW","ABW","297"),
("Australia","Australia","Australie","AU","AUS","61"),
("Austria","Austria","Autriche","AT","AUT","43"),
("Azerbayán","Azerbaijan","L'Azerbaïdjan","AZ","AZE","994"),
("Bélgica","Belgium","Belgique","BE","BEL","32"),
("Bahamas","Bahamas","Bahamas","BS","BHS","1-242"),
("Bahrein","Bahrain","Bahreïn","BH","BHR","973"),
("Bangladesh","Bangladesh","Bangladesh","BD","BGD","880"),
("Barbados","Barbados","Barbade","BB","BRB","1-246"),
("Belice","Belize","Belize","BZ","BLZ","501"),
("Benín","Benin","Bénin","BJ","BEN","229"),
("Bhután","Bhutan","Le Bhoutan","BT","BTN","975"),
("Bielorrusia","Belarus","Biélorussie","BY","BLR","375"),
("Birmania","Myanmar","Myanmar","MM","MMR","95"),
("Bolivia","Bolivia","Bolivie","BO","BOL","591"),
("Bosnia y Herzegovina","Bosnia and Herzegovina","Bosnie-Herzégovine","BA","BIH","387"),
("Botsuana","Botswana","Botswana","BW","BWA","267"),
("Brasil","Brazil","Brésil","BR","BRA","55"),
("Brunéi","Brunei","Brunei","BN","BRN","673"),
("Bulgaria","Bulgaria","Bulgarie","BG","BGR","359"),
("Burkina Faso","Burkina Faso","Burkina Faso","BF","BFA","226"),
("Burundi","Burundi","Burundi","BI","BDI","257"),
("Cabo Verde","Cape Verde","Cap-Vert","CV","CPV","238"),
("Camboya","Cambodia","Cambodge","KH","KHM","855"),
("Camerún","Cameroon","Cameroun","CM","CMR","237"),
("Canadá","Canada","Canada","CA","CAN","1"),
("Chad","Chad","Tchad","TD","TCD","235"),
("Chile","Chile","Chili","CL","CHL","56"),
("China","China","Chine","CN","CHN","86"),
("Chipre","Cyprus","Chypre","CY","CYP","357"),
("Ciudad del Vaticano","Vatican City State","Cité du Vatican","VA","VAT","39"),
("Colombia","Colombia","Colombie","CO","COL","57"),
("Comoras","Comoros","Comores","KM","COM","269"),
("Congo","Congo","Congo","CG","COG","242"),
("Congo","Congo","Congo","CD","COD","243"),
("Corea del Norte","North Korea","Corée du Nord","KP","PRK","850"),
("Corea del Sur","South Korea","Corée du Sud","KR","KOR","82"),
("Costa de Marfil","Ivory Coast","Côte-d'Ivoire","CI","CIV","225"),
("Costa Rica","Costa Rica","Costa Rica","CR","CRI","506"),
("Croacia","Croatia","Croatie","HR","HRV","385"),
("Cuba","Cuba","Cuba","CU","CUB","53"),
("Dinamarca","Denmark","Danemark","DK","DNK","45"),
("Dominica","Dominica","Dominique","DM","DMA","1-767"),
("Ecuador","Ecuador","Equateur","EC","ECU","593"),
("Egipto","Egypt","Egypte","EG","EGY","20"),
("El Salvador","El Salvador","El Salvador","SV","SLV","503"),
("Emiratos Árabes Unidos","United Arab Emirates","Emirats Arabes Unis","AE","ARE","971"),
("Eritrea","Eritrea","Erythrée","ER","ERI","291"),
("Eslovaquia","Slovakia","Slovaquie","SK","SVK","421"),
("Eslovenia","Slovenia","Slovénie","SI","SVN","386"),
("España","Spain","Espagne","ES","ESP","34"),
("Estados Unidos de América","United States of America","États-Unis d'Amérique","US","USA","1"),
("Estonia","Estonia","L'Estonie","EE","EST","372"),
("Etiopía","Ethiopia","Ethiopie","ET","ETH","251"),
("Filipinas","Philippines","Philippines","PH","PHL","63"),
("Finlandia","Finland","Finlande","FI","FIN","358"),
("Fiyi","Fiji","Fidji","FJ","FJI","679"),
("Francia","France","France","FR","FRA","33"),
("Gabón","Gabon","Gabon","GA","GAB","241"),
("Gambia","Gambia","Gambie","GM","GMB","220"),
("Georgia","Georgia","Géorgie","GE","GEO","995"),
("Ghana","Ghana","Ghana","GH","GHA","233"),
("Gibraltar","Gibraltar","Gibraltar","GI","GIB","350"),
("Granada","Grenada","Grenade","GD","GRD","1-473"),
("Grecia","Greece","Grèce","GR","GRC","30"),
("Groenlandia","Greenland","Groenland","GL","GRL","299"),
("Guam","Guam","Guam","GU","GUM","1-671"),
("Guatemala","Guatemala","Guatemala","GT","GTM","502"),
("Guinea","Guinea","Guinée","GN","GIN","224"),
("Guinea Ecuatorial","Equatorial Guinea","Guinée Equatoriale","GQ","GNQ","240"),
("Guinea-Bissau","Guinea-Bissau","Guinée-Bissau","GW","GNB","245"),
("Guyana","Guyana","Guyane","GY","GUY","592"),
("Haití","Haiti","Haïti","HT","HTI","509"),
("Honduras","Honduras","Honduras","HN","HND","504"),
("Hong kong","Hong Kong","Hong Kong","HK","HKG","852"),
("Hungría","Hungary","Hongrie","HU","HUN","36"),
("India","India","Inde","IN","IND","91"),
("Indonesia","Indonesia","Indonésie","ID","IDN","62"),
("Irán","Iran","Iran","IR","IRN","98"),
("Irak","Iraq","Irak","IQ","IRQ","964"),
("Irlanda","Ireland","Irlande","IE","IRL","353"),
("Isla de Man","Isle of Man","Ile de Man","IM","IMN","44"),
("Isla de Navidad","Christmas Island","Christmas Island","CX","CXR","61"),
("Islandia","Iceland","Islande","IS","ISL","354"),
("Islas Bermudas","Bermuda Islands","Bermudes","BM","BMU","1-441"),
("Islas Caimán","Cayman Islands","Iles Caïmans","KY","CYM","1-345"),
("Islas Cocos (Keeling)","Cocos (Keeling) Islands","Cocos (Keeling","CC","CCK","61"),
("Islas Cook","Cook Islands","Iles Cook","CK","COK","682"),
("Islas Feroe","Faroe Islands","Iles Féro","FO","FRO","298"),
("Islas Maldivas","Maldives","Maldives","MV","MDV","960"),
("Islas Malvinas","Falkland Islands (Malvinas)","Iles Falkland (Malvinas","FK","FLK","500"),
("Islas Marianas del Norte","Northern Mariana Islands","Iles Mariannes du Nord","MP","MNP","1-670"),
("Islas Marshall","Marshall Islands","Iles Marshall","MH","MHL","692"),
("Islas Pitcairn","Pitcairn Islands","Iles Pitcairn","PN","PCN","870"),
("Islas Salomón","Solomon Islands","Iles Salomon","SB","SLB","677"),
("Islas Turcas y Caicos","Turks and Caicos Islands","Iles Turques et Caïques","TC","TCA","1-649"),
("Islas Vírgenes Británicas","Virgin Islands","Iles Vierges","VG","VG","1-284"),
("Islas Vírgenes de los Estados Unidos","United States Virgin Islands","Îles Vierges américaines","VI","VIR","1-340"),
("Israel","Israel","Israël","IL","ISR","972"),
("Italia","Italy","Italie","IT","ITA","39"),
("Jamaica","Jamaica","Jamaïque","JM","JAM","1-876"),
("Japón","Japan","Japon","JP","JPN","81"),
("Jordania","Jordan","Jordan","JO","JOR","962"),
("Kazajistán","Kazakhstan","Le Kazakhstan","KZ","KAZ","7"),
("Kenia","Kenya","Kenya","KE","KEN","254"),
("Kirgizstán","Kyrgyzstan","Kirghizstan","KG","KGZ","996"),
("Kiribati","Kiribati","Kiribati","KI","KIR","686"),
("Kuwait","Kuwait","Koweït","KW","KWT","965"),
("Líbano","Lebanon","Liban","LB","LBN","961"),
("Laos","Laos","Laos","LA","LAO","856"),
("Lesoto","Lesotho","Lesotho","LS","LSO","266"),
("Letonia","Latvia","La Lettonie","LV","LVA","371"),
("Liberia","Liberia","Liberia","LR","LBR","231"),
("Libia","Libya","Libye","LY","LBY","218"),
("Liechtenstein","Liechtenstein","Liechtenstein","LI","LIE","423"),
("Lituania","Lithuania","La Lituanie","LT","LTU","370"),
("Luxemburgo","Luxembourg","Luxembourg","LU","LUX","352"),
("México","Mexico","Mexique","MX","MEX","52"),
("Mónaco","Monaco","Monaco","MC","MCO","377"),
("Macao","Macao","Macao","MO","MAC","853"),
("Macedônia","Macedonia","Macédoine","MK","MKD","389"),
("Madagascar","Madagascar","Madagascar","MG","MDG","261"),
("Malasia","Malaysia","Malaisie","MY","MYS","60"),
("Malawi","Malawi","Malawi","MW","MWI","265"),
("Mali","Mali","Mali","ML","MLI","223"),
("Malta","Malta","Malte","MT","MLT","356"),
("Marruecos","Morocco","Maroc","MA","MAR","212"),
("Mauricio","Mauritius","Iles Maurice","MU","MUS","230"),
("Mauritania","Mauritania","Mauritanie","MR","MRT","222"),
("Mayotte","Mayotte","Mayotte","YT","MYT","262"),
("Micronesia","Estados Federados de","Federados Estados de","FM","FSM","691"),
("Moldavia","Moldova","Moldavie","MD","MDA","373"),
("Mongolia","Mongolia","Mongolie","MN","MNG","976"),
("Montenegro","Montenegro","Monténégro","ME","MNE","382"),
("Montserrat","Montserrat","Montserrat","MS","MSR","1-664"),
("Mozambique","Mozambique","Mozambique","MZ","MOZ","258"),
("Namibia","Namibia","Namibie","NA","NAM","264"),
("Nauru","Nauru","Nauru","NR","NRU","674"),
("Nepal","Nepal","Népal","NP","NPL","977"),
("Nicaragua","Nicaragua","Nicaragua","NI","NIC","505"),
("Niger","Niger","Niger","NE","NER","227"),
("Nigeria","Nigeria","Nigeria","NG","NGA","234"),
("Niue","Niue","Niou","NU","NIU","683"),
("Noruega","Norway","Norvège","NO","NOR","47"),
("Nueva Caledonia","New Caledonia","Nouvelle-Calédonie","NC","NCL","687"),
("Nueva Zelanda","New Zealand","Nouvelle-Zélande","NZ","NZL","64"),
("Omán","Oman","Oman","OM","OMN","968"),
("Países Bajos","Netherlands","Pays-Bas","NL","NLD","31"),
("Pakistán","Pakistan","Pakistan","PK","PAK","92"),
("Palau","Palau","Palau","PW","PLW","680"),
("Panamá","Panama","Panama","PA","PAN","507"),
("Papúa Nueva Guinea","Papua New Guinea","Papouasie-Nouvelle-Guinée","PG","PNG","675"),
("Paraguay","Paraguay","Paraguay","PY","PRY","595"),
("Perú","Peru","Pérou","PE","PER","51"),
("Polinesia Francesa","French Polynesia","Polynésie française","PF","PYF","689"),
("Polonia","Poland","Pologne","PL","POL","48"),
("Portugal","Portugal","Portugal","PT","PRT","351"),
("Puerto Rico","Puerto Rico","Porto Rico","PR","PRI","1"),
("Qatar","Qatar","Qatar","QA","QAT","974"),
("Reino Unido","United Kingdom","Royaume-Uni","GB","GBR","44"),
("República Centroafricana","Central African Republic","République Centrafricaine","CF","CAF","236"),
("República Checa","Czech Republic","République Tchèque","CZ","CZE","420"),
("República Dominicana","Dominican Republic","République Dominicaine","DO","DOM","1-809"),
("Ruanda","Rwanda","Rwanda","RW","RWA","250"),
("Rumanía","Romania","Roumanie","RO","ROU","40"),
("Rusia","Russia","La Russie","RU","RUS","7"),
("Samoa","Samoa","Samoa","WS","WSM","685"),
("Samoa Americana","American Samoa","Les Samoa américaines","AS","ASM","1-684"),
("San Bartolomé","Saint Barthélemy","Saint-Barthélemy","BL","BLM","590"),
("San Cristóbal y Nieves","Saint Kitts and Nevis","Saint Kitts et Nevis","KN","KNA","1-869"),
("San Marino","San Marino","San Marino","SM","SMR","378"),
("San Martín (Francia)","Saint Martin (French part)","Saint-Martin (partie française)","MF","MAF","1-599"),
("San Pedro y Miquelón","Saint Pierre and Miquelon","Saint-Pierre-et-Miquelon","PM","SPM","508"),
("San Vicente y las Granadinas","Saint Vincent and the Grenadines","Saint-Vincent et Grenadines","VC","VCT","1-784"),
("Santa Elena","Ascensión y Tristán de Acuña","Ascensión y Tristan de Acuña","SH","SHN","290"),
("Santa Lucía","Saint Lucia","Sainte-Lucie","LC","LCA","1-758"),
("Santo Tomé y Príncipe","Sao Tome and Principe","Sao Tomé et Principe","ST","STP","239"),
("Senegal","Senegal","Sénégal","SN","SEN","221"),
("Serbia","Serbia","Serbie","RS","SRB","381"),
("Seychelles","Seychelles","Les Seychelles","SC","SYC","248"),
("Sierra Leona","Sierra Leone","Sierra Leone","SL","SLE","232"),
("Singapur","Singapore","Singapour","SG","SGP","65"),
("Siria","Syria","Syrie","SY","SYR","963"),
("Somalia","Somalia","Somalie","SO","SOM","252"),
("Sri lanka","Sri Lanka","Sri Lanka","LK","LKA","94"),
("Sudáfrica","South Africa","Afrique du Sud","ZA","ZAF","27"),
("Sudán","Sudan","Soudan","SD","SDN","249"),
("Suecia","Sweden","Suède","SE","SWE","46"),
("Suiza","Switzerland","Suisse","CH","CHE","41"),
("Surinám","Suriname","Surinam","SR","SUR","597"),
("Swazilandia","Swaziland","Swaziland","SZ","SWZ","268"),
("Tadjikistán","Tajikistan","Le Tadjikistan","TJ","TJK","992"),
("Tailandia","Thailand","Thaïlande","TH","THA","66"),
("Taiwán","Taiwan","Taiwan","TW","TWN","886"),
("Tanzania","Tanzania","Tanzanie","TZ","TZA","255"),
("Timor Oriental","East Timor","Timor-Oriental","TL","TLS","670"),
("Togo","Togo","Togo","TG","TGO","228"),
("Tokelau","Tokelau","Tokélaou","TK","TKL","690"),
("Tonga","Tonga","Tonga","TO","TON","676"),
("Trinidad y Tobago","Trinidad and Tobago","Trinidad et Tobago","TT","TTO","1-868"),
("Tunez","Tunisia","Tunisie","TN","TUN","216"),
("Turkmenistán","Turkmenistan","Le Turkménistan","TM","TKM","993"),
("Turquía","Turkey","Turquie","TR","TUR","90"),
("Tuvalu","Tuvalu","Tuvalu","TV","TUV","688"),
("Ucrania","Ukraine","L'Ukraine","UA","UKR","380"),
("Uganda","Uganda","Ouganda","UG","UGA","256"),
("Uruguay","Uruguay","Uruguay","UY","URY","598"),
("Uzbekistán","Uzbekistan","L'Ouzbékistan","UZ","UZB","998"),
("Vanuatu","Vanuatu","Vanuatu","VU","VUT","678"),
("Venezuela","Venezuela","Venezuela","VE","VEN","58"),
("Vietnam","Vietnam","Vietnam","VN","VNM","84"),
("Wallis y Futuna","Wallis and Futuna","Wallis et Futuna","WF","WLF","681"),
("Yemen","Yemen","Yémen","YE","YEM","967"),
("Yibuti","Djibouti","Djibouti","DJ","DJI","253"),
("Zambia","Zambia","Zambie","ZM","ZMB","260"),
("Zimbabue","Zimbabwe","Zimbabwe","ZW","ZWE","263");
