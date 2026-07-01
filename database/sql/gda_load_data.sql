-- =============================================================================
-- GDA - Datos iniciales (autocontenido, NO depende de gedis)
-- =============================================================================
--
-- Este script inserta directamente en la base `gda` todos los datos del dump
-- dump-gedis-202606291112.sql, ya transformados al nuevo diseño.
--
-- REQUISITO: haber ejecutado antes gedis_schema_instrumentos_control.sql
--
-- USO:
--   mysql -u USUARIO -p < database/sql/gda_load_data.sql
--
-- =============================================================================

/*!40101 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET NAMES utf8mb4 */;

USE `gda`;

-- Limpiar tablas (esquema vacío o re-ejecución)
TRUNCATE TABLE `caratulas`;
TRUNCATE TABLE `unidades_documentales`;
TRUNCATE TABLE `expedientes`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `instrumentos_control`;
TRUNCATE TABLE `subseries`;
TRUNCATE TABLE `series`;
TRUNCATE TABLE `secciones`;
TRUNCATE TABLE `subfondos`;
TRUNCATE TABLE `fondos`;
TRUNCATE TABLE `categorias`;
TRUNCATE TABLE `departamentos`;
TRUNCATE TABLE `dependencias`;

INSERT INTO `dependencias` VALUES (1,'Sistemas','SIS-ROOT','Dependencia del sistema utilizada para el usuario root','2025-06-11 16:52:43','2025-06-11 16:52:57'),(2,'Archivo General del Estado de Quintana Roo','AGE','Archivo General del Estado de Quintana Roo','2025-06-11 17:59:48','2025-06-19 17:30:40'),(3,'Secretaria de Gobierno','SEGOB','Secretaria de Gobierno','2025-06-12 15:43:29','2025-06-19 17:30:44');
INSERT INTO `departamentos` VALUES (1,2,'Departamento de Tecnologias de la Informacion y Comunicaciones','DTIyC','Archivo General del Estado de Quintana Roo','2025-06-11 19:29:25','2025-06-11 19:29:25'),(2,2,'Dirección General del Archivo General del Estado de Quintana Roo','DG','Oficina del Director General del Archivo General del Estado de Quintana Roo','2025-12-03 16:51:11','2025-12-03 16:51:11'),(3,2,'Departamento Administrativo','DA','Departamento Administrativo','2025-12-03 16:53:29','2025-12-03 20:47:08'),(4,2,'Departamento de Normatividad Jurídica y Titular de la Unidad de Transparencia, Acceso Pública y Protección de Datos Personales','DNJyUTAIPyPDP','Departamento de Normatividad Jurídica y Titular de la Unidad de Transparencia, Acceso Pública y Protección de Datos Personales','2025-12-03 17:00:18','2025-12-03 17:00:18'),(5,2,'Dirección de Archivos del Ejecutivo y Mejora Regulatoria','DAEyMR','Dirección de Archivos del Ejecutivo y Mejora Regulatoria','2025-12-03 19:41:04','2025-12-03 19:41:04'),(6,2,'Departamento de Biblioteca y Hemeroteca','DBH','Departamento de biblioteca y Hemeroteca','2025-12-03 19:42:17','2025-12-03 21:13:10'),(7,2,'Departamento de Archivo Histórico y Mejora Regulatoria','DAHyMR','Departamento de Archivo Histórico y Mejora Regulatoria','2025-12-03 19:43:49','2025-12-03 19:43:49');
-- categorias: sin datos

INSERT INTO `fondos` (`id`, `id_dependencia`, `id_departamento`, `id_instrumento_control`, `nombre`, `codigo`, `descripcion`, `created_at`, `updated_at`)
VALUES (20,2,1,NULL,'Secretaría de Gobierno','SEGOB','Secretaría de Gobierno','2025-12-05 13:57:49','2025-12-05 13:57:49');

INSERT INTO `subfondos` (`id`, `id_fondo`, `id_dependencia`, `id_departamento`, `id_instrumento_control`, `nombre`, `codigo`, `descripcion`, `created_at`, `updated_at`)
VALUES (9,20,2,1,NULL,'Archivo General del Estado de Quintana Roo','AGE','Archivo General del Estado de Quintana Roo','2025-12-05 13:58:35','2025-12-05 13:58:35');
INSERT INTO `secciones` (`id`, `id_fondo`, `id_dependencia`, `id_subfondo`, `id_departamento`, `id_instrumento_control`, `nombre`, `codigo`, `descripcion`, `created_at`, `updated_at`) VALUES
(28,20,2,9,1,NULL,'Programación, Organización y Presupuestación','3C','Programación, Organización y Presupuestación','2025-12-05 14:00:26','2025-12-05 14:00:26'),
(29,20,2,NULL,1,NULL,'ASUNTOS JURÍDICOS','2C','ASUNTOS JURÍDICOS','2026-02-20 14:34:24','2026-02-20 14:34:24'),
(30,20,2,NULL,1,NULL,'PROGRAMACIÓN, ORGANIZACIÓN Y PRESUPUESTACIÓN','3C','PROGRAMACIÓN, ORGANIZACIÓN Y PRESUPUESTACIÓN','2026-02-20 14:41:22','2026-02-20 14:41:22'),
(31,20,2,NULL,1,NULL,'RECURSOS HUMANOS','4C','RECURSOS HUMANOS','2026-02-20 14:42:22','2026-02-20 14:42:22'),
(32,20,2,NULL,1,NULL,'RECURSOS FINANCIEROS','5C','RECURSOS FINANCIEROS','2026-02-20 14:42:52','2026-02-20 14:42:52'),
(33,20,2,NULL,1,NULL,'RECURSOS MATERIALES Y OBRA PÚBLICA','6C','RECURSOS MATERIALES Y OBRA PÚBLICA','2026-02-20 14:47:45','2026-02-20 14:47:45'),
(34,20,2,NULL,1,NULL,'SERVICIOS GENERALES','7C','SERVICIOS GENERALES','2026-02-20 14:48:27','2026-02-20 14:48:27'),
(35,20,2,NULL,1,NULL,'TECNOLOGÍAS Y SERVICIOS DE LA INFORMACIÓN','8C','TECNOLOGÍAS Y SERVICIOS DE LA INFORMACIÓN','2026-02-20 14:49:23','2026-02-20 14:49:23'),
(36,20,2,NULL,1,NULL,'COMUNICACIÓN SOCIAL','9C','COMUNICACIÓN SOCIAL','2026-02-20 14:49:55','2026-02-20 14:49:55'),
(37,20,2,NULL,1,NULL,'CONTROL Y AUDITORÍA DE ACTIVIDADES PÚBLICAS','10C','CONTROL Y AUDITORIA DE ACTIVIDADES PÚBLICAS','2026-02-20 14:50:54','2026-02-20 14:50:54'),
(38,20,2,NULL,1,NULL,'PLANEACIÓN, INFORMACIÓN, EVALUACIÓN Y POLÍTICAS','11C','PLANEACIÓN, INFORMACIÓN, EVALUACIÓN Y POLÍTICAS','2026-02-20 14:51:38','2026-02-20 14:51:38'),
(39,20,2,NULL,1,NULL,'TRANSPARENCIA Y ACCESO A LA INFORMACIÓN PÚBLICA','12C','TRANSPARENCIA Y ACCESO A LA INFORMACIÓN PÚBLICA','2026-02-20 14:52:31','2026-02-20 14:52:31'),
(40,20,2,NULL,1,NULL,'PROMOCIÓN DE LA ADMINISTRACIÓN DE LOS ARCHIVOS Y GESTIÓN DOCUMENTAL','1S','PROMOCIÓN DE LA ADMINISTRACIÓN DE LOS ARCHIVOS Y GESTIÓN DOCUMENTAL','2026-02-20 14:54:21','2026-02-20 14:54:21'),
(41,20,2,NULL,1,NULL,'PRESERVACIÓN DEL PATRIMONIO DOCUMENTAL','2S','PRESERVACIÓN DEL PATRIMONIO DOCUMENTAL','2026-02-20 14:55:23','2026-02-20 14:55:23'),
(42,20,2,NULL,1,NULL,'DIFUSIÓN DEL PATRIMONIO DOCUMENTAL','3S','DIFUSIÓN DEL PATRIMONIO DOCUMENTAL','2026-02-20 14:56:13','2026-02-20 14:56:13'),
(43,20,2,9,1,NULL,'PROMOCIÓN DE LA ADMINISTRACIÓN DE LOS ARCHIVOS Y GESTIÓN DOCUMENTAL','1S','PROMOCIÓN DE LA ADMINISTRACIÓN DE LOS ARCHIVOS Y GESTIÓN DOCUMENTAL','2026-05-11 17:21:13','2026-05-11 17:21:13'),
(44,20,2,9,1,NULL,'Sin Sección','0','Caso particular para el Archivo General del Estado de Quintana Roo','2026-06-18 19:11:59','2026-06-18 19:11:59');

INSERT INTO `series` VALUES (24,28,2,1,0,0,0,NULL,NULL,NULL,0,0,0,0,0,NULL,'Programas Operativos Anuales','3C.7','Programas Operativos Anuales','2025-12-05 14:01:48','2025-12-05 14:01:48'),(25,40,2,1,1,1,0,3,2,5,0,0,1,0,0,'Serie de alta prioridad, conservar original.','DESIGNACIÓN DE COORDINADORES DE ARCHIVO','1S.1','DESIGNACIÓN DE COORDINADORES DE ARCHIVO','2026-02-20 15:02:02','2026-02-20 15:02:02'),(26,40,2,1,1,1,0,3,2,5,0,1,0,0,0,'Serie de alta prioridad, conservar original.','CAPACITACION ARCHIVÍSTICA','1S.2','CAPACITACION ARCHIVÍSTICA','2026-02-20 15:02:46','2026-02-20 15:02:46'),(27,40,2,1,1,1,0,3,5,8,0,1,0,0,0,'Serie de alta prioridad, conservar original.','REGISTRO Y VALIDACIÓN DE INSTRUMENTOS','1S.3','REGISTRO Y VALIDACIÓN DE INSTRUMENTOS','2026-02-20 15:03:28','2026-02-20 15:03:28'),(28,40,2,1,1,1,0,2,7,9,0,1,0,0,0,'Serie de alta prioridad, conservar original.','EMISIÓN DEL DICTAMEN DE BAJA DOCUMENTAL','1S.4','EMISIÓN DEL DICTAMEN DE BAJA DOCUMENTAL','2026-02-20 15:04:19','2026-02-20 15:04:19'),(29,40,2,1,1,1,0,2,7,9,0,1,0,0,0,'Serie de alta prioridad, conservar original.','DICTAMINACIÓN DE TRANSFERENCIAS SECUNDARIAS','1S.5','DICTAMINACIÓN DE TRANSFERENCIAS SECUNDARIAS','2026-02-20 15:04:59','2026-02-20 15:04:59'),(30,40,2,1,1,1,0,3,2,5,1,0,0,0,0,'Serie de alta prioridad, conservar original.','DOCUMENTACIÓN SINIESTRADA','1S.6','DOCUMENTACIÓN SINIESTRADA','2026-02-20 15:05:47','2026-02-20 15:05:47'),(31,41,2,1,1,1,0,2,7,9,0,1,0,0,0,'Serie de alta prioridad, conservar original.','INCORPORACIÓN Y RESGUIARDO DEL PATRIMONIO DOCUMENTAL','2S.1','INCORPORACIÓN Y RESGUIARDO DEL PATRIMONIO DOCUMENTAL','2026-02-20 15:14:48','2026-02-20 15:14:48'),(32,41,2,1,1,1,0,2,7,9,0,1,0,0,0,'Serie de alta prioridad, conservar original.','TRANSFERENCIAS SECUNDARIAS','2S.2','TRANSFERENCIAS SECUNDARIAS','2026-02-20 15:15:32','2026-02-20 15:15:32'),(33,41,2,1,1,1,0,3,2,5,0,1,0,0,0,'Serie de alta prioridad, conservar original.','INCORPORACIÓN DE ACERVOS DOCUMENTALES','2S.3','NCORPORACIÓN DE ACERVOS DOCUMENTALES','2026-02-20 15:16:18','2026-02-20 15:16:18'),(34,41,2,1,1,1,0,3,2,5,0,1,0,0,0,'Serie de alta prioridad, conservar original.','DISPOSICIONES EN MATERIA DE PRESERVACIÓN','2S.4','DISPOSICIONES EN MATERIA DE PRESERVACIÓN','2026-02-20 15:17:12','2026-02-20 15:17:12'),(35,42,2,1,1,0,0,3,3,6,0,1,0,0,0,'Serie de alta prioridad, conservar original.','ORGANIZACIÓN DE EXPOSICIONES','3S.1','ORGANIZACIÓN DE EXPOSICIONES','2026-02-20 15:18:19','2026-02-20 15:18:19'),(36,42,2,1,1,0,0,3,3,6,0,1,0,0,0,'Serie de alta prioridad, conservar original.','VISITAS GUIADAS','3S.2','VISITAS GUIADAS','2026-02-20 15:18:53','2026-02-20 15:18:53'),(37,28,2,1,0,0,0,NULL,NULL,NULL,0,0,0,0,0,NULL,'PRUEBA 1','A01','PRUEBA 1','2026-04-01 17:07:51','2026-04-01 17:07:51'),(38,28,2,1,1,1,0,7,5,12,1,0,0,3,5,'NADA','TEST03','123','PROBANDO LOS VALORES BOOLEANOS Y LOS NUEVOS CAMPOS DE LA SERIE','2026-04-01 19:31:24','2026-04-01 19:31:24'),(39,43,2,1,1,1,0,3,2,5,0,0,1,3,0,NULL,'DESIGNACIÓN DE CORDINADORES DE ARCHIVO','1S.1','DESIGNACIÓN DE CORDINADORES DE ARCHIVO','2026-05-11 17:23:48','2026-05-11 17:23:48'),(40,28,2,1,1,0,0,3,2,5,1,0,0,3,5,'ASNDNASJDJASD','TEST04AB1','TEST04AB1','PRUEBA EN EL MODULO DE SERIES NUEVA VISTA','2026-05-26 14:45:20','2026-05-26 14:45:20'),(41,44,2,1,0,0,0,NULL,NULL,NULL,0,0,0,0,0,'','Programas Operativos Anuales','3C.7','Programas Operativos Anuales','2025-12-05 14:01:48','2025-12-05 14:01:48'),(42,44,2,1,1,1,0,3,2,5,0,0,1,0,0,'Serie de alta prioridad, conservar original.','DESIGNACIÓN DE COORDINADORES DE ARCHIVO','1S.1','DESIGNACIÓN DE COORDINADORES DE ARCHIVO','2026-02-20 15:02:02','2026-02-20 15:02:02'),(43,44,2,1,1,1,0,3,2,5,0,1,0,0,0,'Serie de alta prioridad, conservar original.','CAPACITACION ARCHIVÍSTICA','1S.2','CAPACITACION ARCHIVÍSTICA','2026-02-20 15:02:46','2026-02-20 15:02:46'),(44,44,2,1,1,1,0,3,5,8,0,1,0,0,0,'Serie de alta prioridad, conservar original.','REGISTRO Y VALIDACIÓN DE INSTRUMENTOS','1S.3','REGISTRO Y VALIDACIÓN DE INSTRUMENTOS','2026-02-20 15:03:28','2026-02-20 15:03:28'),(45,44,2,1,1,1,0,2,7,9,0,1,0,0,0,'Serie de alta prioridad, conservar original.','EMISIÓN DEL DICTAMEN DE BAJA DOCUMENTAL','1S.4','EMISIÓN DEL DICTAMEN DE BAJA DOCUMENTAL','2026-02-20 15:04:19','2026-02-20 15:04:19'),(46,44,2,1,1,1,0,2,7,9,0,1,0,0,0,'Serie de alta prioridad, conservar original.','DICTAMINACIÓN DE TRANSFERENCIAS SECUNDARIAS','1S.5','DICTAMINACIÓN DE TRANSFERENCIAS SECUNDARIAS','2026-02-20 15:04:59','2026-02-20 15:04:59'),(47,44,2,1,1,1,0,3,2,5,1,0,0,0,0,'Serie de alta prioridad, conservar original.','DOCUMENTACIÓN SINIESTRADA','1S.6','DOCUMENTACIÓN SINIESTRADA','2026-02-20 15:05:47','2026-02-20 15:05:47'),(48,44,2,1,1,1,0,2,7,9,0,1,0,0,0,'Serie de alta prioridad, conservar original.','INCORPORACIÓN Y RESGUIARDO DEL PATRIMONIO DOCUMENTAL','2S.1','INCORPORACIÓN Y RESGUIARDO DEL PATRIMONIO DOCUMENTAL','2026-02-20 15:14:48','2026-02-20 15:14:48'),(49,44,2,1,1,1,0,2,7,9,0,1,0,0,0,'Serie de alta prioridad, conservar original.','TRANSFERENCIAS SECUNDARIAS','2S.2','TRANSFERENCIAS SECUNDARIAS','2026-02-20 15:15:32','2026-02-20 15:15:32'),(50,44,2,1,1,1,0,3,2,5,0,1,0,0,0,'Serie de alta prioridad, conservar original.','INCORPORACIÓN DE ACERVOS DOCUMENTALES','2S.3','NCORPORACIÓN DE ACERVOS DOCUMENTALES','2026-02-20 15:16:18','2026-02-20 15:16:18'),(51,44,2,1,1,1,0,3,2,5,0,1,0,0,0,'Serie de alta prioridad, conservar original.','DISPOSICIONES EN MATERIA DE PRESERVACIÓN','2S.4','DISPOSICIONES EN MATERIA DE PRESERVACIÓN','2026-02-20 15:17:12','2026-02-20 15:17:12'),(52,44,2,1,1,0,0,3,3,6,0,1,0,0,0,'Serie de alta prioridad, conservar original.','ORGANIZACIÓN DE EXPOSICIONES','3S.1','ORGANIZACIÓN DE EXPOSICIONES','2026-02-20 15:18:19','2026-02-20 15:18:19'),(53,44,2,1,1,0,0,3,3,6,0,1,0,0,0,'Serie de alta prioridad, conservar original.','VISITAS GUIADAS','3S.2','VISITAS GUIADAS','2026-02-20 15:18:53','2026-02-20 15:18:53');

-- subseries: sin datos

-- instrumentos_control: Fondos
INSERT INTO `instrumentos_control` (
  `id_padre`, `id_dependencia`, `id_departamento`, `id_fondo`, `id_seccion`, `id_serie`, `id_subserie`,
  `codigo_clasificacion`, `tipo_nivel`, `nombre_elemento`, `descripcion`, `activo`, `created_at`, `updated_at`
)
SELECT NULL, f.id_dependencia, f.id_departamento, f.id, NULL, NULL, NULL,
       f.codigo, 'fondo', f.nombre, f.descripcion, 1, f.created_at, f.updated_at
FROM fondos f;

-- instrumentos_control: Secciones (incluye 'Sin Sección' id=44)
INSERT INTO `instrumentos_control` (
  `id_padre`, `id_dependencia`, `id_departamento`, `id_fondo`, `id_seccion`, `id_serie`, `id_subserie`,
  `codigo_clasificacion`, `tipo_nivel`, `nombre_elemento`, `descripcion`, `activo`, `created_at`, `updated_at`
)
SELECT ic_fondo.id, s.id_dependencia, s.id_departamento, s.id_fondo, s.id, NULL, NULL,
       s.codigo, 'seccion', s.nombre, s.descripcion, 1, s.created_at, s.updated_at
FROM secciones s
JOIN instrumentos_control ic_fondo ON ic_fondo.id_fondo = s.id_fondo AND ic_fondo.tipo_nivel = 'fondo';

-- instrumentos_control: Series (incluye series bajo Sin Sección id_seccion=44)
INSERT INTO `instrumentos_control` (
  `id_padre`, `id_dependencia`, `id_departamento`, `id_fondo`, `id_seccion`, `id_serie`, `id_subserie`,
  `codigo_clasificacion`, `tipo_nivel`, `nombre_elemento`, `descripcion`, `activo`, `created_at`, `updated_at`
)
SELECT ic_sec.id, ser.id_dependencia, ser.id_departamento, NULL, ser.id_seccion, ser.id, NULL,
       ser.codigo, 'serie', ser.nombre, ser.descripcion, 1, ser.created_at, ser.updated_at
FROM series ser
JOIN instrumentos_control ic_sec ON ic_sec.id_seccion = ser.id_seccion AND ic_sec.tipo_nivel = 'seccion';

-- Enlazar id_instrumento_control
UPDATE fondos f JOIN instrumentos_control ic ON ic.id_fondo = f.id AND ic.tipo_nivel = 'fondo' SET f.id_instrumento_control = ic.id;
UPDATE secciones s JOIN instrumentos_control ic ON ic.id_seccion = s.id AND ic.tipo_nivel = 'seccion' SET s.id_instrumento_control = ic.id;
UPDATE subfondos sf JOIN fondos f ON f.id = sf.id_fondo SET sf.id_instrumento_control = f.id_instrumento_control;



-- Expedientes (3 registros)
INSERT INTO `expedientes` (
  `id`, `id_serie`, `id_dependencia`, `id_departamento`, `id_subserie`, `id_instrumento_control`,
  `no_legajos`, `no_hojas`, `ubicacion_topografica`, `no_caja`, `observacion`, `nombre`, `codigo`, `estado`,
  `informacion_reservada_y_o_confidencial`, `fecha_clasificacion`, `rubrica_titular_de_la_unidad`,
  `fecha_desclasificacion`, `fecha_creacion`, `fecha_apertura`, `fecha_cierre`, `preservacion`, `clave`,
  `created_at`, `updated_at`
)
SELECT
  src.id, src.id_serie, src.id_dependencia, src.id_departamento, src.id_subserie, ic.id,
  src.no_legajos, src.no_hojas, src.ubicacion_topografica, src.no_caja, src.observacion, src.nombre, src.codigo, src.estado,
  src.informacion_reservada_y_o_confidencial, src.fecha_clasificacion, src.rubrica_titular_de_la_unidad,
  src.fecha_desclasificacion, src.fecha_creacion, src.fecha_apertura, src.fecha_cierre, src.preservacion, src.clave,
  src.created_at, src.updated_at
FROM (
  SELECT * FROM (
    SELECT 44 id, 52 id_serie, 2 id_dependencia, 1 id_departamento, NULL id_subserie,
      1 no_legajos, 20 no_hojas, 'ARCHIVERO 1 GAVETA 1. DAHMR' ubicacion_topografica, 1 no_caja,
      'No se cuenta con fundamentó legal' observacion, 'ORGANIZACIÓN DE EXPOSICIONES' nombre, '3S.1' codigo,
      'en_tramite' estado, NULL informacion_reservada_y_o_confidencial, NULL fecha_clasificacion,
      NULL rubrica_titular_de_la_unidad, NULL fecha_desclasificacion,
      DATE('2026-06-18') fecha_creacion, DATE('2026-06-18') fecha_apertura, DATE('2026-12-31') fecha_cierre,
      'No' preservacion, 'SEGOB/AGE/DG/DAEMR/DAEMR/3S.1/2026/1' clave,
      TIMESTAMP('2026-06-18 19:39:19') created_at, TIMESTAMP('2026-06-18 19:42:49') updated_at
    UNION ALL SELECT 45,52,2,1,NULL,1,20,'ARCHIVERO 1 GAVETA 1',1,'NO SE CUENTA CON FUNDAMENTO LEGAL','ORGANIZACIÓN DE EXPOSICIONES','2','en_tramite',NULL,NULL,NULL,NULL,DATE('2026-06-18'),DATE('2026-06-18'),DATE('2026-12-31'),'No','SEGOB/AGE/DG/DAEYMR/DAHMR/3S.1/2026/2',TIMESTAMP('2026-06-18 19:56:08'),TIMESTAMP('2026-06-18 19:58:07')
    UNION ALL SELECT 46,52,2,1,NULL,1,200,'ESTANTE TEST 01',3,'NINGUNA DE MOMENTO','TEST01','03','en_tramite','NADA',NULL,'RUSELL',NULL,DATE('2026-06-25'),DATE('2026-06-25'),DATE('2026-12-30'),'Si','SEGOB/AGE/3S.1/03',TIMESTAMP('2026-06-25 16:12:41'),TIMESTAMP('2026-06-25 16:12:41')
  ) x
) src
LEFT JOIN instrumentos_control ic ON ic.id_serie = src.id_serie AND ic.tipo_nivel = 'serie';


-- Usuarios (8 registros)
INSERT INTO `users` VALUES (13,NULL,1,'Jhoedy','Dominguez','Dominguez','jidd.2201@gmail.com','$2y$12$/ZMzgxan93yAMtA.kHrGT.cjIfnBCxsdQHBRN./cX5vuL4grorrq2','[\"root\"]','2025-06-11 22:00:27','2025-06-18 00:14:45'),(14,1,2,'JOSE ALBERTO','DIAZ','LOPEZ','jose@jose.com','$2y$12$Hm1Zz.4LW48F/Sa4A9gGL.7MW8BAHhlB.UXkfBy6niQa/YvB8S5/u','[\"root\"]','2025-06-12 03:04:35','2026-02-12 16:23:35'),(15,2,2,'GABRIEL','PEREZ','CAAMAL','ageqroodsea2017@gmail.com','$2y$12$xNkZaRgPSqqTBX2WevsSWOc2sJtRvDyNtbt7TSYRZhiqUzUfv0Glu','[\"lector\"]','2025-12-03 19:56:53','2025-12-03 21:23:44'),(16,3,2,'CECILIO FERNANDO','CHE','MOGUEL','fermoguel@hotmail.com','$2y$12$3rYsXl7fJPFIXCEYwx5zLe4Jk5RIYC.ZdHAouIBSVB2pLb4QKwb7O','[\"lector\"]','2025-12-03 20:00:06','2025-12-03 20:00:06'),(17,4,2,'JOCELYNE','RODRIGUEZ','OJEDA','juridico.agqroo.2023@gmail.com','$2y$12$ZSUYy/.QMLaZAsYiXTC8qu6GEtWl48aP06FKyZZJEUEYucaeSvsfu','[\"lector\"]','2025-12-03 20:03:32','2025-12-03 20:03:32'),(18,5,2,'ADRIANA','CUELLAR','PRADO','prado@gmail.com','$2y$12$KYvMFSvro6058pnXK1dd2O2xxNs0bZMeX1E52EMX8uZ7OBpwYQ71.','[\"lector\"]','2025-12-03 20:08:00','2025-12-03 21:23:29'),(19,6,2,'SAN JUANA','SOTO','FABELA','fabelasanjuan@gmail.com','$2y$12$QzLHTjjR4BRTE3fzjJM8tuSf0QVNHMP1WMDjqa81Whnk2YXZE3Jd6','[\"lector\"]','2025-12-03 20:14:20','2025-12-03 20:14:20'),(20,7,2,'HORACIO','RODRIGUEZ','GUARNEROS','guarneros7@yahoo.com.mx','$2y$12$acWEh7SYl6HA4ohCRPwxZ.KmlL0fB2jr/IVkEKWwlY1n2UnLtbcQu','[\"lector\"]','2025-12-03 20:21:18','2025-12-03 20:21:18');


-- Carátulas (3 registros) - campos de reglas movidos a instrumentos_control
INSERT INTO `caratulas` (
  `id`, `id_dependencia`, `id_departamento`, `id_instrumento_control`,
  `nombre_sujeto_obligado`, `area_administrativa`, `no_consecutivo`, `area_generadora`,
  `codigo_clasificacion_archivistica`, `anio_apertura`, `anio_cierre`, `soporte_documental`,
  `titulo_expediente`, `descripcion_asunto_expediente`, `ubicacion_topografica`, `no_legajo`, `no_fojas`,
  `fecha_clasificacion`, `aplicacion_periodo_reserva`, `rubrica_titular`, `fecha_desclasificacion`,
  `observaciones`, `capturador`, `created_at`, `updated_at`
)
SELECT c.id, c.id_dependencia, c.id_departamento, exp.id_instrumento_control,
  c.nombre_sujeto_obligado, c.area_administrativa, c.no_consecutivo, c.area_generadora,
  c.codigo_clasificacion_archivistica, c.anio_apertura, c.anio_cierre, c.soporte_documental,
  c.titulo_expediente, c.descripcion_asunto_expediente, c.ubicacion_topografica, c.no_legajo, c.no_fojas,
  c.fecha_clasificacion, c.aplicacion_periodo_reserva, c.rubrica_titular, c.fecha_desclasificacion,
  c.observaciones, c.capturador, c.created_at, c.updated_at
FROM (
  SELECT 8 id, 2 id_dependencia, 1 id_departamento, 'Archivo General del Estado de Quintana Roo' nombre_sujeto_obligado,
    'Departamento de Tecnologias de la Informacion y Comunicaciones' area_administrativa, 1 no_consecutivo,
    'Departamento de Tecnologias de la Informacion y Comunicaciones' area_generadora,
    'SEGOB/AGE/DG/DAEMR/DAEMR/3S.1/2026/1' codigo_clasificacion_archivistica, 2026 anio_apertura, 2026 anio_cierre,
    'Físico' soporte_documental, 'ORGANIZACIÓN DE EXPOSICIONES' titulo_expediente,
    'No se cuenta con fundamentó legal' descripcion_asunto_expediente, 'ARCHIVERO 1 GAVETA 1. DAHMR' ubicacion_topografica,
    1 no_legajo, 20 no_fojas, DATE('2026-06-18') fecha_clasificacion, '0' aplicacion_periodo_reserva,
    'ARCHIVO GENERAL DEL ESTADO DE QUINTANA ROO' rubrica_titular, NULL fecha_desclasificacion,
    'No se cuenta con fundamentó legal | Serie de alta prioridad, conservar original.' observaciones,
    'JOSE ALBERTO LOPEZ DIAZ' capturador, TIMESTAMP('2026-06-18 19:50:48') created_at, TIMESTAMP('2026-06-18 19:50:48') updated_at
  UNION ALL SELECT 9,2,1,'Archivo General del Estado de Quintana Roo','Departamento de Tecnologias de la Informacion y Comunicaciones',2,'Departamento de Tecnologias de la Informacion y Comunicaciones','SEGOB/AGE/DG/DAEYMR/DAHMR/3S.1/2026/2',2026,2026,'Físico','ORGANIZACIÓN DE EXPOSICIONES','NO SE CUENTA CON FUNDAMENTO LEGAL','ARCHIVERO 1 GAVETA 1',1,20,DATE('2026-06-18'),'0','ARCHIVO GENERAL DEL ESTADO DE QUINTANA ROO',NULL,'NO SE CUENTA CON FUNDAMENTO LEGAL | Serie de alta prioridad, conservar original.','JOSE ALBERTO LOPEZ DIAZ',TIMESTAMP('2026-06-18 19:59:44'),TIMESTAMP('2026-06-18 19:59:44')
  UNION ALL SELECT 10,2,1,'Archivo General del Estado de Quintana Roo','Departamento de Tecnologias de la Informacion y Comunicaciones',3,'Departamento de Tecnologias de la Informacion y Comunicaciones','SEGOB/AGE/3S.1/03',2026,2026,'Físico','TEST01','UN TEST BASICO','ESTANTE TEST 01',1,200,DATE('2026-06-01'),'0','RUSELL',NULL,'NINGUNA DE MOMENTO','JOSE ALBERTO LOPEZ DIAZ',TIMESTAMP('2026-06-25 16:22:40'),TIMESTAMP('2026-06-25 16:22:40')
) c
LEFT JOIN expedientes exp ON exp.clave = c.codigo_clasificacion_archivistica;

-- periodo_reserva y fundamento_legal en instrumentos_control (desde expedientes/caratulas)
UPDATE instrumentos_control ic
JOIN expedientes e ON e.id_serie = ic.id_serie
SET ic.fundamento_legal = COALESCE(ic.fundamento_legal, NULLIF(TRIM(e.observacion), '')),
    ic.periodo_reserva = COALESCE(ic.periodo_reserva, '0')
WHERE ic.tipo_nivel = 'serie' AND e.id = 46;

UPDATE instrumentos_control ic SET ic.fundamento_legal = 'DE MOMENTO NO APLICA', ic.periodo_reserva = '0'
WHERE ic.id_serie = 52 AND ic.tipo_nivel = 'serie';


ALTER TABLE `dependencias` AUTO_INCREMENT = 10;
ALTER TABLE `departamentos` AUTO_INCREMENT = 10;
ALTER TABLE `fondos` AUTO_INCREMENT = 25;
ALTER TABLE `subfondos` AUTO_INCREMENT = 15;
ALTER TABLE `secciones` AUTO_INCREMENT = 50;
ALTER TABLE `series` AUTO_INCREMENT = 60;
ALTER TABLE `expedientes` AUTO_INCREMENT = 50;
ALTER TABLE `caratulas` AUTO_INCREMENT = 15;
ALTER TABLE `users` AUTO_INCREMENT = 25;
ALTER TABLE `instrumentos_control` AUTO_INCREMENT = 200;

/*!40101 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;

SELECT 'dependencias' AS tabla, COUNT(*) AS n FROM dependencias
UNION ALL SELECT 'departamentos', COUNT(*) FROM departamentos
UNION ALL SELECT 'fondos', COUNT(*) FROM fondos
UNION ALL SELECT 'secciones', COUNT(*) FROM secciones
UNION ALL SELECT 'series', COUNT(*) FROM series
UNION ALL SELECT 'series_sin_seccion (id_seccion=44)', COUNT(*) FROM series WHERE id_seccion = 44
UNION ALL SELECT 'instrumentos_control', COUNT(*) FROM instrumentos_control
UNION ALL SELECT 'instrumentos_serie_sin_seccion', COUNT(*) FROM instrumentos_control ic JOIN series s ON s.id = ic.id_serie WHERE s.id_seccion = 44
UNION ALL SELECT 'expedientes', COUNT(*) FROM expedientes
UNION ALL SELECT 'caratulas', COUNT(*) FROM caratulas
UNION ALL SELECT 'users', COUNT(*) FROM users;
