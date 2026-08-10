# 003 · Estadísticas en inicio (gráficas + PDF) — Tareas

**Ticket:** —

## Investigación

- [x] Revisar `Dashboard.vue`, `dashboard.blade.php` y ruta `/dashboard`.
- [x] Revisar alcance en `ExpedienteController` (root vs dependencia/departamento).
- [x] Revisar header PDF en `resources/views/pdf/expedientes.blade.php` (logos y layout).
- [x] Revisar `ExpedienteBaja`.
- [x] Revisar Chart.js / `vue-chartjs` para Pie, Bar y PolarArea sin dependencias nuevas.
- [x] Confirmar cómo exportar el canvas (`toBase64Image` / `toDataURL`) para el PDF.
- [x] Ajustar filtros a cascada jerárquica (Fondo → Sección → Serie) y estados separados.
- [x] Confirmar modelo Sección (`id_fondo` + `id_subfondo` nullable) y endpoints `/fondos/{id}/subfondos`, `/subfondos/{id}/secciones`, `/series/{id}/subseries`.
- [x] Confirmar patrón UI opcional en `CaratulaV2.vue`.

## Implementación (base ya hecha)

- [x] Crear `DashboardStatsController` con alcance, estados y agrupación automática (v1 sin subfondo/subserie en UI).
- [x] Rutas autenticadas: JSON + `POST` PDF.
- [x] Plantilla DomPDF con encabezado tipo inventario + imagen de gráfica.
- [x] Componente Vue pie / barras / polar area.
- [x] `Dashboard.vue` con cascada Fondo/Sección/Serie + estado + tipo de gráfica + PDF.
- [x] UI en español.
- [x] No tocar Mongo / migraciones / env.

## Implementación (ampliación: subfondo / subserie)

- [x] Backend: aceptar y validar `id_subfondos[]` e `id_subseries[]` (cascada y pertenencia).
- [x] Backend: filtrar expedientes por subfondo/subserie sin excluir los que no tienen el nivel cuando el filtro viene vacío.
- [x] Backend: agrupación automática — series (1+) → `serie` hasta elegir subserie; subfondos → `subfondo`; subserie solo si se seleccionó; varios estados → `estado`.
- [x] Backend: incluir subfondos/subseries en `filtro_resumen` / título.
- [x] Frontend: modal multi-select **Subfondos** (tras fondos) y cascada de limpieza.
- [x] Frontend: modal multi-select **Subseries** (tras series) y cascada de limpieza.
- [x] Frontend: carga de secciones según haya o no subfondos elegidos (directas + vía subfondos vs solo subfondos).
- [x] Frontend: enviar `id_subfondos` / `id_subseries` en GET stats y POST PDF; actualizar copy de jerarquía.
- [x] No tocar Mongo / migraciones / env / catálogo CRUD.

## Validación

- [x] `npm run build` (o `npm run dev`) sin errores en lo tocado.
- [x] Cascada: fondos → subfondos opc. → secciones → series → subseries opc.; botones deshabilitados en el orden correcto.
- [x] Con fondos y subfondos vacíos: secciones = directas + de todos los subfondos de esos fondos.
- [x] Con subfondos elegidos: solo secciones de esos subfondos.
- [x] Con series y subseries vacías: cuentan expedientes con y sin subserie; gráfica por serie (no salta a subserie).
- [x] Con subseries elegidas: solo esos nodos; gráfica por subserie.
- [x] Varios tipos → segmentos por estado; un tipo → según nivel elegido en el spec.
- [x] Probar como dependencia vs `root`.
- [x] PDF con logos, título y gráfica del tipo elegido (con filtros que incluyan opcionales).
- [x] Validar contra `spec.md` y mover a Hecho en roadmap.
