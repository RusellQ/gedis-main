-- =============================================================================
-- 005 · Correspondencia: oficios, remisión y archivo
-- =============================================================================
-- Extiende `correspondencias` para el flujo bandeja → remisión → archivo.
-- REQUISITO: haber aplicado database/sql/004-correspondencias.sql (tipos int correctos).
-- Ejecutar UNA sola vez. Si alguna columna ya existe, comentar esa línea ADD.
--
-- USO:
--   mysql -u USUARIO -p gda < database/sql/005-correspondencia-oficios-remision.sql
-- =============================================================================

USE `gda`;

ALTER TABLE `correspondencias`
  MODIFY COLUMN `id_expediente` int NULL DEFAULT NULL
    COMMENT 'FK expediente al archivar; NULL mientras está en bandeja/remisión';

ALTER TABLE `correspondencias`
  ADD COLUMN `numero_oficio` varchar(120) NULL
    COMMENT 'Ej. SEGOB/AGE/DG/DTlyC/0049/2026' AFTER `numero_consecutivo`,
  ADD COLUMN `leyenda_anio` varchar(500) NULL
    COMMENT 'Leyenda editable del año' AFTER `asunto`,
  ADD COLUMN `fecha_oficio` date NULL
    COMMENT 'Fecha automática del oficio' AFTER `leyenda_anio`,
  ADD COLUMN `id_usuario_destinatario` int unsigned NULL
    COMMENT 'Usuario interno al que se remite' AFTER `id_usuario_autor`,
  ADD COLUMN `id_unidad_documental` int NULL
    COMMENT 'Unidad documental creada al archivar' AFTER `id_expediente`,
  ADD COLUMN `remitido_at` timestamp NULL DEFAULT NULL AFTER `fecha_modificacion`,
  ADD COLUMN `archivado_at` timestamp NULL DEFAULT NULL AFTER `remitido_at`;

ALTER TABLE `correspondencias`
  MODIFY COLUMN `estado` enum(
    'borrador',
    'emitido',
    'remitido',
    'archivado',
    'firmado',
    'cancelado'
  ) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador';

ALTER TABLE `correspondencias`
  ADD KEY `idx_numero_oficio` (`numero_oficio`),
  ADD KEY `idx_usuario_destinatario` (`id_usuario_destinatario`);

CREATE TABLE IF NOT EXISTS `oficio_consecutivos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `prefijo` varchar(120) NOT NULL COMMENT 'Ej. SEGOB/AGE/DG/DTlyC',
  `anio` smallint unsigned NOT NULL,
  `ultimo_numero` int unsigned NOT NULL DEFAULT 0,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_prefijo_anio` (`prefijo`, `anio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Consecutivos de número de oficio por prefijo y año';
