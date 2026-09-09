-- =============================================================================
-- Fix: la tabla `correspondencias` fue creada sin la columna `id_expediente`
-- (probablemente con una versión anterior de 004-correspondencias.sql, y
-- `CREATE TABLE IF NOT EXISTS` no actualiza tablas ya existentes).
--
-- Error que corrige:
--   SQLSTATE[42S22]: Column not found: 1054 Unknown column 'id_expediente'
--
-- Tipo alineado al esquema GEDIS: expedientes.id es `int` (signed), NO bigint.
--
-- USO:
--   mysql -u root gda < database/sql/004b-fix-correspondencias-id_expediente.sql
-- =============================================================================

USE `gda`;

ALTER TABLE `correspondencias`
  ADD COLUMN `id_expediente` int NOT NULL AFTER `id`,
  ADD KEY `idx_expediente` (`id_expediente`),
  ADD CONSTRAINT `fk_correspondencias_expediente`
    FOREIGN KEY (`id_expediente`) REFERENCES `expedientes` (`id`) ON DELETE CASCADE;
