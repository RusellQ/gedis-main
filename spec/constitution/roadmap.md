# Roadmap

> Orden y estado de las features. Vista de "qué hay hecho, qué toca ahora y qué viene". Cada feature nueva apunta a su carpeta en `features/`.

## Hecho ✅

> Capacidades ya existentes en el proyecto (documentadas en retrospectiva; no tienen carpeta `features/` propia salvo que se retomen).

1. **Autenticación y administración de usuarios** — login por sesión Laravel; roles `root` / `administrador` / `editor` / `lector`; alta/listado de usuarios; alcance por dependencia y departamento.
2. **Dependencias y departamentos** — CRUD/gestión organizativa (dependencias solo `root`); filtros de negocio por pertenencia del usuario.
3. **Catálogo de clasificación archivística** — jerarquía Fondo → (Subfondo) → Sección → Serie → (Subserie) → Expediente; pantallas de creación/consulta asociadas.
4. **Instrumento de control** — modelo/servicio que enlaza niveles del catálogo (`codigo_clasificacion`, `tipo_nivel`, padre) y sostiene clasificación.
5. **Reglas documentales por serie** — vigencias, plazos AT/AC, clasificación (pública/reservada/confidencial), técnicas de selección; panel Vue y endpoints `reglas-documentales`.
6. **Carátulas de expediente** — captura y consulta de fichas descriptivas (`DocumentoController` / `Caratula`); edición y Baja según permisos.
7. **Carátula v2** — selección en cascada desde el catálogo, carga de datos del expediente, validación, PDF y opción de carátula en blanco (`CaratulaV2.vue`, `/caratula-v2`).
8. **Ciclo vital del archivo** — vistas y transición de estado: trámite, concentración, histórico; bajas con historial (`ExpedienteBaja`, rol `root`).
9. **Unidades documentales** — alta/asociación de unidades a expedientes (flujo en pantallas de archivo/carga).
10. **Cuadro general de clasificación** — vista previa + PDF (`CuadroGeneralController`).
11. **Catálogo de disposición documental** — vista previa + PDF.
12. **Catálogo de secciones** — datos, vista previa + PDF.
13. **Reportes** — filtrado por catálogo y exportación PDF.
14. **Estadísticas / recursos (MongoDB)** — consultas a `recursos_collection` / `desechados_collection` (módulo existente; no es el núcleo del catálogo).
15. **Inventario / export Excel** — pantalla e export (`InventarioExport` / Maatwebsite).
16. **Shell de navegación por módulos** — sidebar SB Admin agrupado (Expedientes, Archivo, Catálogo y estructura, Reportes, Administración) según rol.
17. **Mejoras UX de creación de expedientes** — refactor de `Fondos.vue` (selectores/secciones) e integración visual de reglas/serie en formularios. *(Última entrega integrada antes de adoptar SDD.)*
18. **[001 · Administración del catálogo de clasificación (CRUD)](../features/001-admin-catalogo-estructura/)** — módulo solo `root` en Administración (`/catalogo-clasificacion`) para alta/edición/baja de Fondo → Subserie.
19. **[002 · Expedientes vencidos](../features/002-expedientes-vencidos/)** — módulo para visualizar expedientes cuya vigencia documental ha vencido, accesible a `root` y `administrador`.
20. **[002 · Visor de unidades documentales (PDF)](../features/002-visor-unidades-documentales/)** — preview Fancybox con navegación entre PDFs; alta de unidades solo en Trámite.
21. **[003 · Estadísticas en inicio (gráficas + PDF)](../features/003-dashboard-estadisticas-pastel/)** — dashboard con gráfica; multi-selección Fondo → (Subfondo) → Sección → Serie → (Subserie) (opcionales respetados); uno–tres tipos de expediente; agrupación por nivel elegido; PDF con encabezado oficial + gráfica.
22. **[004 · Módulo de Correspondencia](../features/004-modulo-correspondencia/)** — Recepción y control de documentos con editor WYSIWYG, firma electrónica SAT (con modo sandbox) y adjunción de PDFs.
23. **[006 · Contraseña temporal en el primer inicio](../features/006-contrasena-temporal-primer-inicio/)** — La contraseña del alta, y la que el administrador asigne después desde el listado, es temporal: en el siguiente inicio hay que cambiarla antes de usar el sistema.

## Siguiente 🔜

> Lo próximo a abordar. Idealmente una sola feature "en curso" a la vez.

- **[005 · Correspondencia: oficios, remisión y archivo](../features/005-correspondencia-oficios-remision/)** — Menú en dos vistas (bandeja/remisión y recepción/archivo); oficio con plantilla oficial (fijo / automático / editable); archivar como unidad documental reutilizando jerarquía existente. *(En curso — reemplaza el enfoque monolítico del 004.)*

## Backlog / ideas 💡

> Sin comprometer ni ordenar del todo. Ideas que respetan la constitución.

- **Pruebas automatizadas y depuración** — cobertura de tests de integración para módulos recientes.
- **Documentar entorno** — crear/mantener `.env.example` alineado a MySQL + Mongo opcional (stats).
- **Depurar pantallas legacy fuera del menú activo** — `/buscar`, `/capturar` (legado), `/inventario`, `/estadisticas`, `/series`, `/indexfondos`: consolidar, archivar o reintegrar con criterio de producto.
- **Pruebas automatizadas de dominio** — Feature/Unit tests para reglas documentales, cambio de estado de expediente y permisos por rol/dependencia.
- **Endurecer carátula v2** — casos borde de catálogo incompleto, permisos y consistencia PDF ↔ datos guardados.
- **Trazabilidad de bajas y cambios de estado** — auditoría explícita de quién/cuándo movió un expediente en el ciclo vital.
- **Firma electrónica real (SAT/FIEL) para oficios** — el 005 conserva solo sandbox de prueba; credenciales reales solo si negocio lo exige.

> Cada feature nueva se crea como `features/NNN-nombre-feature/` con `spec.md`, `plan.md` y `tasks.md` antes de tocar código.
