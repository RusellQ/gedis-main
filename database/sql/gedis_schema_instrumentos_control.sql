-- =============================================================================
-- GEDIS - Esquema de base de datos v2
-- Reestructuración: catalogo_archivistico -> instrumentos_control (tabla maestra)
-- Generado a partir del dump: dump-gedis-202606291112.sql
-- Fecha: 2026-07-01
-- =============================================================================
--
-- CAMBIOS PRINCIPALES:
-- 1. Se elimina `catalogo_archivistico` y se crea `instrumentos_control` como
--    tabla maestra del CGCA (Fondo > Sección > Serie > Subserie).
-- 2. Los campos CADIDO (valoración, plazos, técnicas, clasificación) permanecen
--    en `series`. `instrumentos_control` los obtiene vía `id_serie`.
-- 3. Campos de reglas movidos desde `caratulas` y `expedientes` hacia
--    `instrumentos_control` (periodo_reserva, fundamento_legal).
-- 4. Tablas operativas referencian `instrumentos_control` vía `id_instrumento_control`.
--
-- =============================================================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- -----------------------------------------------------------------------------
-- Crear base de datos (ajustar nombre si es necesario)
-- -----------------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `gda`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `gda`;

-- -----------------------------------------------------------------------------
-- dependencias
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `dependencias`;
CREATE TABLE `dependencias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `clave` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- instrumentos_control (TABLA MAESTRA - reemplaza catalogo_archivistico)
-- Catálogo jerárquico CGCA. Las reglas CADIDO viven en `series` y se acceden
-- mediante id_serie cuando tipo_nivel = 'serie'.
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `instrumentos_control`;
CREATE TABLE `instrumentos_control` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_padre` int DEFAULT NULL COMMENT 'Jerarquía CGCA: Fondo > Sección > Serie > Subserie',
  `id_dependencia` int DEFAULT NULL,
  `id_departamento` int DEFAULT NULL,
  -- Referencias al nivel archivístico correspondiente (sin duplicar campos CADIDO)
  `id_fondo` int DEFAULT NULL COMMENT 'Cuando tipo_nivel = fondo',
  `id_seccion` int DEFAULT NULL COMMENT 'Cuando tipo_nivel = seccion',
  `id_serie` int DEFAULT NULL COMMENT 'Cuando tipo_nivel = serie; reglas CADIDO en tabla series',
  `id_subserie` int DEFAULT NULL COMMENT 'Cuando tipo_nivel = subserie',
  `codigo_clasificacion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Antes repartido en fondos/secciones/series/subseries',
  `tipo_nivel` enum('fondo','seccion','serie','subserie') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nivel archivístico del elemento',
  `nombre_elemento` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre oficial del elemento',
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `periodo_reserva` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Antes: caratulas.periodo_reserva / expedientes.periodo_reserva',
  `fundamento_legal` text COLLATE utf8mb4_unicode_ci COMMENT 'Antes: caratulas.fundamento_legal / expedientes.fundamento_legal',
  `activo` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Reemplaza status; 1=activo, 0=inactivo',
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_instrumentos_padre` (`id_padre`),
  KEY `idx_instrumentos_dependencia` (`id_dependencia`),
  KEY `idx_instrumentos_departamento` (`id_departamento`),
  KEY `idx_instrumentos_fondo` (`id_fondo`),
  KEY `idx_instrumentos_seccion` (`id_seccion`),
  KEY `idx_instrumentos_serie` (`id_serie`),
  KEY `idx_instrumentos_subserie` (`id_subserie`),
  KEY `idx_instrumentos_codigo` (`codigo_clasificacion`),
  KEY `idx_instrumentos_tipo_nivel` (`tipo_nivel`),
  KEY `idx_instrumentos_activo` (`activo`),
  CONSTRAINT `fk_instrumentos_padre` FOREIGN KEY (`id_padre`) REFERENCES `instrumentos_control` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_instrumentos_dependencia` FOREIGN KEY (`id_dependencia`) REFERENCES `dependencias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- departamentos
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `departamentos`;
CREATE TABLE `departamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_dependencia` int NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `clave` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_dependencia` (`id_dependencia`),
  CONSTRAINT `departamentos_ibfk_1` FOREIGN KEY (`id_dependencia`) REFERENCES `dependencias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- FK de instrumentos_control -> departamentos (requiere que departamentos exista)
ALTER TABLE `instrumentos_control`
  ADD CONSTRAINT `fk_instrumentos_departamento` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL;

-- -----------------------------------------------------------------------------
-- categorias
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- fondos
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `fondos`;
CREATE TABLE `fondos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_dependencia` int NOT NULL,
  `id_departamento` int NOT NULL,
  `id_instrumento_control` int DEFAULT NULL COMMENT 'Referencia al instrumento de control de nivel Fondo',
  `nombre` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_dependencia` (`id_dependencia`),
  KEY `FK_fondos_departamentos` (`id_departamento`),
  KEY `FK_fondos_instrumento_control` (`id_instrumento_control`),
  CONSTRAINT `FK_fondos_departamentos` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id`),
  CONSTRAINT `FK_fondos_instrumento_control` FOREIGN KEY (`id_instrumento_control`) REFERENCES `instrumentos_control` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fondos_ibfk_1` FOREIGN KEY (`id_dependencia`) REFERENCES `dependencias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- subfondos
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `subfondos`;
CREATE TABLE `subfondos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_fondo` int NOT NULL,
  `id_dependencia` int DEFAULT NULL,
  `id_departamento` int NOT NULL,
  `id_instrumento_control` int DEFAULT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `codigo` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_subfondos_fondos` (`id_fondo`),
  KEY `FK_subfondos_dependencias` (`id_dependencia`),
  KEY `FK_subfondos_departamentos` (`id_departamento`),
  KEY `FK_subfondos_instrumento_control` (`id_instrumento_control`),
  CONSTRAINT `FK_subfondos_departamentos` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id`),
  CONSTRAINT `FK_subfondos_dependencias` FOREIGN KEY (`id_dependencia`) REFERENCES `dependencias` (`id`),
  CONSTRAINT `FK_subfondos_fondos` FOREIGN KEY (`id_fondo`) REFERENCES `fondos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_subfondos_instrumento_control` FOREIGN KEY (`id_instrumento_control`) REFERENCES `instrumentos_control` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- secciones
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `secciones`;
CREATE TABLE `secciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_fondo` int NOT NULL,
  `id_dependencia` int DEFAULT NULL,
  `id_subfondo` int DEFAULT NULL,
  `id_departamento` int NOT NULL,
  `id_instrumento_control` int DEFAULT NULL COMMENT 'Referencia al instrumento de control de nivel Sección',
  `nombre` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_fondo` (`id_fondo`),
  KEY `FK_secciones_dependencias` (`id_dependencia`),
  KEY `FK_secciones_departamentos` (`id_departamento`),
  KEY `FK_secciones_instrumento_control` (`id_instrumento_control`),
  CONSTRAINT `FK_secciones_departamentos` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id`),
  CONSTRAINT `FK_secciones_dependencias` FOREIGN KEY (`id_dependencia`) REFERENCES `dependencias` (`id`),
  CONSTRAINT `FK_secciones_instrumento_control` FOREIGN KEY (`id_instrumento_control`) REFERENCES `instrumentos_control` (`id`) ON DELETE SET NULL,
  CONSTRAINT `secciones_ibfk_1` FOREIGN KEY (`id_fondo`) REFERENCES `fondos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- series
-- Conserva todos los campos CADIDO originales (fuente de verdad para reglas de serie)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `series`;
CREATE TABLE `series` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_seccion` int DEFAULT NULL,
  `id_dependencia` int DEFAULT NULL,
  `id_departamento` int NOT NULL,
  `vigencia_ad` tinyint(1) NOT NULL DEFAULT '0',
  `vigencia_le` tinyint(1) NOT NULL DEFAULT '0',
  `vigencia_confi` tinyint(1) NOT NULL DEFAULT '0',
  `plazo_at` int DEFAULT NULL,
  `plazo_ac` int DEFAULT NULL,
  `plazo_ta` int DEFAULT NULL,
  `tecnica_eliminacion` tinyint(1) NOT NULL DEFAULT '0',
  `tecnica_conservacion` tinyint(1) NOT NULL DEFAULT '0',
  `tecnica_muestreo` tinyint(1) NOT NULL DEFAULT '0',
  `clasificacion_r` tinyint(1) DEFAULT '0',
  `clasificacion_c` tinyint(1) DEFAULT '0',
  `observaciones` text COLLATE utf8mb4_general_ci,
  `nombre` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_seccion` (`id_seccion`),
  KEY `FK_series_dependencias` (`id_dependencia`),
  KEY `FK_series_departamentos` (`id_departamento`),
  CONSTRAINT `FK_series_departamentos` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id`),
  CONSTRAINT `FK_series_dependencias` FOREIGN KEY (`id_dependencia`) REFERENCES `dependencias` (`id`),
  CONSTRAINT `series_ibfk_1` FOREIGN KEY (`id_seccion`) REFERENCES `secciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- subseries
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `subseries`;
CREATE TABLE `subseries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_serie` int NOT NULL,
  `id_dependencia` int DEFAULT NULL,
  `id_departamento` int NOT NULL,
  `id_instrumento_control` int DEFAULT NULL COMMENT 'Referencia al instrumento de control de nivel Subserie',
  `nombre` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `codigo` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_serie` (`id_serie`),
  KEY `FK_subseries_dependencias` (`id_dependencia`),
  KEY `FK_subseries_departamentos` (`id_departamento`),
  KEY `FK_subseries_instrumento_control` (`id_instrumento_control`),
  CONSTRAINT `FK_subseries_departamentos` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id`),
  CONSTRAINT `FK_subseries_dependencias` FOREIGN KEY (`id_dependencia`) REFERENCES `dependencias` (`id`),
  CONSTRAINT `FK_subseries_instrumento_control` FOREIGN KEY (`id_instrumento_control`) REFERENCES `instrumentos_control` (`id`) ON DELETE SET NULL,
  CONSTRAINT `subseries_ibfk_1` FOREIGN KEY (`id_serie`) REFERENCES `series` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- FKs de instrumentos_control hacia tablas de nivel archivístico
-- (se agregan aquí porque fondos/secciones/series/subseries ya existen)
-- -----------------------------------------------------------------------------
ALTER TABLE `instrumentos_control`
  ADD CONSTRAINT `fk_instrumentos_fondo` FOREIGN KEY (`id_fondo`) REFERENCES `fondos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_instrumentos_seccion` FOREIGN KEY (`id_seccion`) REFERENCES `secciones` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_instrumentos_serie` FOREIGN KEY (`id_serie`) REFERENCES `series` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_instrumentos_subserie` FOREIGN KEY (`id_subserie`) REFERENCES `subseries` (`id`) ON DELETE SET NULL;

-- -----------------------------------------------------------------------------
-- expedientes
-- Campos de reglas REMOVIDOS (ahora en instrumentos_control):
--   tiempo_conservacion, vigencia (plazos)
--   clasificacion, caracter (valoración/clasificación)
--   caracter_de_la_informacion, fundamento_legal
--   periodo_reserva, ampliacion_periodo_reserva
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `expedientes`;
CREATE TABLE `expedientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_serie` int NOT NULL,
  `id_dependencia` int DEFAULT NULL,
  `id_departamento` int NOT NULL,
  `id_subserie` int DEFAULT NULL,
  `id_instrumento_control` int DEFAULT NULL COMMENT 'Reglas de negocio heredadas del instrumento de control',
  `no_legajos` int DEFAULT NULL,
  `no_hojas` int DEFAULT NULL,
  `ubicacion_topografica` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `no_caja` int DEFAULT NULL,
  `observacion` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `codigo` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `estado` enum('en_tramite','en_concentracion','en_historico') COLLATE utf8mb4_general_ci DEFAULT 'en_tramite',
  `informacion_reservada_y_o_confidencial` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_clasificacion` date DEFAULT NULL COMMENT 'Fecha operativa de clasificación del expediente',
  `rubrica_titular_de_la_unidad` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_desclasificacion` date DEFAULT NULL,
  `fecha_creacion` date DEFAULT NULL,
  `fecha_apertura` date DEFAULT NULL,
  `fecha_cierre` date DEFAULT NULL,
  `preservacion` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `clave` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_serie` (`id_serie`),
  KEY `FK_expedientes_dependencias` (`id_dependencia`),
  KEY `FK_expedientes_departamentos` (`id_departamento`),
  KEY `FK_expedientes_subseries` (`id_subserie`),
  KEY `FK_expedientes_instrumento_control` (`id_instrumento_control`),
  CONSTRAINT `expedientes_ibfk_1` FOREIGN KEY (`id_serie`) REFERENCES `series` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_expedientes_departamentos` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id`),
  CONSTRAINT `FK_expedientes_dependencias` FOREIGN KEY (`id_dependencia`) REFERENCES `dependencias` (`id`),
  CONSTRAINT `FK_expedientes_instrumento_control` FOREIGN KEY (`id_instrumento_control`) REFERENCES `instrumentos_control` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_expedientes_subseries` FOREIGN KEY (`id_subserie`) REFERENCES `subseries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- caratulas
-- Campos de reglas REMOVIDOS (ahora en instrumentos_control):
--   valor_documental_administrativo, valor_documental_legal, valor_documental_contable
--   vigencia_documental, anios_archivo_tramite, anios_archivo_concentracion
--   caracter_informacion, fundamento_legal, periodo_reserva
-- Se conservan campos operativos del expediente (fechas, rubrica, observaciones, etc.)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `caratulas`;
CREATE TABLE `caratulas` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `id_dependencia` int DEFAULT NULL,
  `id_departamento` int DEFAULT NULL,
  `id_instrumento_control` int DEFAULT NULL COMMENT 'Reglas de negocio del instrumento de control aplicables',
  `nombre_sujeto_obligado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `area_administrativa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_consecutivo` int NOT NULL,
  `area_generadora` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_clasificacion_archivistica` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anio_apertura` int DEFAULT NULL,
  `anio_cierre` int DEFAULT NULL,
  `soporte_documental` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `titulo_expediente` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion_asunto_expediente` text COLLATE utf8mb4_unicode_ci,
  `ubicacion_topografica` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_legajo` int DEFAULT NULL,
  `no_fojas` int DEFAULT NULL,
  `fecha_clasificacion` date DEFAULT NULL,
  `aplicacion_periodo_reserva` text COLLATE utf8mb4_unicode_ci COMMENT 'Aplicación operativa del periodo de reserva',
  `rubrica_titular` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_desclasificacion` date DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `capturador` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK_caratulas_dependencias` (`id_dependencia`) USING BTREE,
  KEY `FK_caratulas_departamentos` (`id_departamento`) USING BTREE,
  KEY `FK_caratulas_instrumento_control` (`id_instrumento_control`) USING BTREE,
  CONSTRAINT `FK_caratulas_departamentos` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id`),
  CONSTRAINT `FK_caratulas_dependencias` FOREIGN KEY (`id_dependencia`) REFERENCES `dependencias` (`id`),
  CONSTRAINT `FK_caratulas_instrumento_control` FOREIGN KEY (`id_instrumento_control`) REFERENCES `instrumentos_control` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- unidades_documentales
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `unidades_documentales`;
CREATE TABLE `unidades_documentales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_expediente` int NOT NULL,
  `tipo` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Carátula, Acta, Oficio, etc.',
  `nombre` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `archivo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Ruta o URL del archivo digitalizado',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_expediente` (`id_expediente`),
  CONSTRAINT `unidades_documentales_ibfk_1` FOREIGN KEY (`id_expediente`) REFERENCES `expedientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- users
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `id_departamento` int DEFAULT NULL,
  `id_dependencia` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `apellido_materno` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `apellido_paterno` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `id_departamento` (`id_departamento`),
  KEY `FK_users_dependencias` (`id_dependencia`),
  CONSTRAINT `FK_users_dependencias` FOREIGN KEY (`id_dependencia`) REFERENCES `dependencias` (`id`),
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id`),
  CONSTRAINT `users_chk_1` CHECK (json_valid(`roles`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
