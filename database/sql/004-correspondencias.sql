-- =============================================================================
-- Módulo de Correspondencia - Tablas de base de datos
-- =============================================================================
--
-- Este script crea las tablas necesarias para el módulo de Correspondencia
-- con soporte para documentos con firma electrónica SAT.
--
-- REQUISITO: Base de datos `gda` existe con tablas de expedientes, usuarios, etc.
--
-- TIPOS DE FK (deben coincidir EXACTAMENTE con el esquema GEDIS):
--   expedientes.id              → int            (signed)
--   users.id                    → int unsigned
--   unidades_documentales.id    → int            (signed)
--
-- USO:
--   mysql -u USUARIO -p gda < database/sql/004-correspondencias.sql
--
-- Si una ejecución anterior dejó tablas a medias, bórralas antes:
--   DROP TABLE IF EXISTS correspondencia_auditorias, correspondencia_anexos,
--                         correspondencia_firmas, correspondencias;
-- =============================================================================

/*!40101 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET NAMES utf8mb4 */;

USE `gda`;

-- =============================================================================
-- Tabla de documentos de Correspondencia
-- =============================================================================
-- Almacena los documentos de correspondencia creados dentro de expedientes.
-- Cada correspondencia puede tener múltiples firmas en el histórico.

CREATE TABLE IF NOT EXISTS `correspondencias` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `id_expediente` int NOT NULL COMMENT 'FK → expedientes.id (int signed)',
  `numero_consecutivo` int DEFAULT 1 COMMENT 'Número de orden dentro del expediente',
  `id_usuario_autor` int unsigned NOT NULL COMMENT 'FK → users.id (int unsigned)',
  `remitente` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Quién envía el documento',
  `destinatario` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Quién recibe el documento',
  `asunto` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Asunto del documento',
  `contenido` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Contenido HTML del documento (editor WYSIWYG)',
  `clasificacion` enum('publica','reservada','confidencial') COLLATE utf8mb4_unicode_ci DEFAULT 'publica' COMMENT 'Clasificación de seguridad',
  `estado` enum('borrador','firmado','cancelado') COLLATE utf8mb4_unicode_ci DEFAULT 'borrador' COMMENT 'Estado del documento',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Eliminación lógica',
  PRIMARY KEY (`id`),
  KEY `idx_expediente` (`id_expediente`),
  KEY `idx_usuario_autor` (`id_usuario_autor`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha_creacion` (`fecha_creacion`),
  CONSTRAINT `fk_correspondencias_expediente` FOREIGN KEY (`id_expediente`) REFERENCES `expedientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_correspondencias_usuario` FOREIGN KEY (`id_usuario_autor`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Documentos de correspondencia de expedientes';

-- =============================================================================
-- Tabla de firmas electrónicas SAT
-- =============================================================================
-- Almacena el histórico de intentos de firma para cada correspondencia.
-- La firma más reciente con estado 'exitosa' es la vigente.

CREATE TABLE IF NOT EXISTS `correspondencia_firmas` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `id_correspondencia` int unsigned NOT NULL,
  `id_usuario_firmante` int unsigned NOT NULL COMMENT 'FK → users.id (int unsigned)',
  `certificado_sat` text COLLATE utf8mb4_unicode_ci COMMENT 'Certificado digital SAT (Base64 o referencia)',
  `timestamp_firma` timestamp NULL DEFAULT NULL COMMENT 'Timestamp de firma según SAT',
  `estado_firma` enum('pendiente','exitosa','rechazada') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente' COMMENT 'Estado del intento de firma',
  `respuesta_sat` json DEFAULT NULL COMMENT 'Respuesta JSON completa de API SAT',
  `fecha_intento` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Cuándo se intentó la firma',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_correspondencia_ultima_firma` (`id_correspondencia`),
  KEY `idx_usuario_firmante` (`id_usuario_firmante`),
  KEY `idx_estado_firma` (`estado_firma`),
  CONSTRAINT `fk_correspondencia_firmas_correspondencia` FOREIGN KEY (`id_correspondencia`) REFERENCES `correspondencias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_correspondencia_firmas_usuario` FOREIGN KEY (`id_usuario_firmante`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Firmas electrónicas SAT de documentos de correspondencia';

-- =============================================================================
-- Tabla de anexos de Correspondencia (opcional, para relación explícita)
-- =============================================================================
-- Vincula unidades documentales (PDFs) a correspondencias específicas.
-- Por defecto, los PDFs están asociados al expediente; esto permite detallar qué PDF va con qué documento.

CREATE TABLE IF NOT EXISTS `correspondencia_anexos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `id_correspondencia` int unsigned NOT NULL,
  `id_unidad_documental` int NOT NULL COMMENT 'FK → unidades_documentales.id (int signed)',
  `orden` int DEFAULT 0 COMMENT 'Orden de visualización',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_correspondencia_anexo` (`id_correspondencia`, `id_unidad_documental`),
  KEY `idx_unidad` (`id_unidad_documental`),
  CONSTRAINT `fk_correspondencia_anexos_correspondencia` FOREIGN KEY (`id_correspondencia`) REFERENCES `correspondencias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_correspondencia_anexos_unidad` FOREIGN KEY (`id_unidad_documental`) REFERENCES `unidades_documentales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Vinculación de PDFs con documentos de correspondencia';

-- =============================================================================
-- Tabla de auditoría de Correspondencia (opcional, para trazabilidad completa)
-- =============================================================================
-- Registra accesos, descargas y cambios de estado para seguridad.

CREATE TABLE IF NOT EXISTS `correspondencia_auditorias` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `id_correspondencia` int unsigned NOT NULL,
  `id_usuario` int unsigned NOT NULL COMMENT 'FK → users.id (int unsigned)',
  `accion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: crear, editar, firmar, descargar, eliminar',
  `detalles` text COLLATE utf8mb4_unicode_ci COMMENT 'Información adicional de la acción',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_correspondencia` (`id_correspondencia`),
  KEY `idx_usuario` (`id_usuario`),
  KEY `idx_accion` (`accion`),
  CONSTRAINT `fk_correspondencia_auditorias_correspondencia` FOREIGN KEY (`id_correspondencia`) REFERENCES `correspondencias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_correspondencia_auditorias_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Auditoría de acciones sobre correspondencia';

/*!40101 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;

-- =============================================================================
-- Fin del script
-- =============================================================================
