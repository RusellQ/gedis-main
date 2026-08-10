# 003 · Estadísticas en inicio (gráficas + PDF) — Plan

**Ticket:** —

_Cómo se implementa lo descrito en `spec.md`. Debe respetar la `constitution/`._

## Enfoque

Extender el **dashboard de inicio** (`Dashboard.vue`) con estadísticas de expedientes (MySQL/Eloquent), multi-filtro de catálogo y PDF DomPDF.

La cascada de filtros sigue la jerarquía real **sin modificar el catálogo**:

**Fondo → (Subfondo opcional) → Sección → Serie → (Subserie opcional).**

Agregaciones con alcance por dependencia/departamento (`root` = global). Sin MongoDB, sin migraciones, sin librerías nuevas.

## Hallazgos de investigación

- Inicio: `GET /dashboard` → `UsersController::dashboard` → `Dashboard.vue`.
- Chart.js / `vue-chartjs` ya en el stack (`DashboardStatsChart.vue`).
- PDF: `resources/views/pdf/dashboard-estadisticas.blade.php` + rutas JSON/PDF en `DashboardStatsController`.
- **Gap actual:** UI y validación solo exponen Fondo / Sección / Serie. El copy dice “Fondo → Sección → Serie”. Subfondo no entra en filtros ni en `agrupar_por`. Subserie existe en `AGRUPACIONES` del controller pero la resolución automática **no** la usa (con series → agrupa por `serie`).
- Secciones: `id_fondo` siempre poblado; `id_subfondo` nullable. `FondoController::secciones` = directas (`id_subfondo` null); `SubfondoController::secciones` = bajo subfondo. Patrón UI de referencia: `CaratulaV2.vue` (selectores opcionales).
- Join actual `secciones.id_fondo` sigue válido para filtrar por fondo (también en secciones bajo subfondo). Falta filtrar por `id_subfondos` / `id_subseries` y validar pertenencia.

## Archivos afectados

| Archivo | Tipo de cambio |
|---|---|
| `routes/web.php` | sin cambio de rutas (mismos endpoints; contrato de query/body ampliado) |
| `app/Http/Controllers/DashboardStatsController.php` | modificar — validar/filtrar/agrupar subfondo y subserie; resumen de filtro |
| `resources/js/admin/Dashboard.vue` | modificar — modales Subfondo y Subserie; cascada; copy de jerarquía; params API |
| `resources/js/components/DashboardStatsChart.vue` | sin cambio esperado |
| `resources/views/pdf/dashboard-estadisticas.blade.php` | sin cambio esperado (usa labels del backend) |
| `EstadisticasController` / Mongo / migraciones | **no tocar** |

## Implementación

1. **Backend — validación** — aceptar `id_subfondos[]` e `id_subseries[]`; reglas de cascada:
   - subfondos requieren fondos y deben pertenecer a esos fondos;
   - secciones requieren fondos; si hay subfondos elegidos, cada sección debe tener `id_subfondo` ∈ elegidos; si no hay subfondos, la sección debe pertenecer a los fondos (`id_fondo` ∈ fondos);
   - series requieren secciones (como hoy);
   - subseries requieren series y deben pertenecer a esas series.
2. **Backend — filtro de expedientes** — además de fondos/secciones/series:
   - si `id_subfondos` no vacío → `whereIn(secciones.id_subfondo, …)`;
   - si `id_subseries` no vacío → `whereIn(expedientes.id_subserie, …)`;
   - si vacíos, no excluir expedientes sin subfondo/subserie.
3. **Backend — agrupación automática** — subserie/subfondo solo si el usuario los eligió; con series (1+) → `serie`; con subfondos → `subfondo`; secciones → `serie`; fondos → `seccion`; vacío → `fondo`.
4. **Backend — resumen** — incluir nombres de subfondos/subseries en `filtro_resumen` / título cuando aplique.
5. **Frontend — cascada** — dos botones/modales nuevos (misma UX de checkboxes):
   - Subfondos: habilitado con ≥1 fondo; carga `GET /fondos/{id}/subfondos` por cada fondo;
   - Secciones: si hay subfondos → solo `GET /subfondos/{id}/secciones`; si no → unión de secciones directas del fondo + secciones de todos sus subfondos (comportamiento ya parcialmente presente);
   - Series: como hoy;
   - Subseries: habilitado con ≥1 serie; carga `GET /series/{id}/subseries`.
6. Al cambiar fondos, limpiar subfondos/secciones/series/subseries; al cambiar subfondos, limpiar secciones→…; al cambiar series, limpiar subseries.
7. Enviar `id_subfondos` / `id_subseries` en JSON y PDF; actualizar texto de ayuda a la jerarquía completa con opcionales.
8. Validar UI vacía/error, conteos con/sin nivel opcional, PDF intacto.

## Decisiones

- **MySQL, no Mongo** — pedido explícito + constitución.
- **No se modifica la jerarquía del catálogo** — solo se completa el filtro/agrupación para respetar niveles opcionales ya existentes.
- **Subfondo y Subserie opcionales en UI** — multi-select; vacío = “todas las rutas / incluir sin ese nivel”.
- **Agrupación: opcionales solo si se eligen** — con series (aunque sea una) → por serie; por subserie solo si marcó subseries. Con subfondos → por subfondo; no se salta solo a ese nivel.
- **Contenido del PDF = gráfica (imagen)** — POST base64; DomPDF no ejecuta Chart.js.
- **Tres tipos Chart.js** — pie, bar, polarArea.
- **`root` = global**; sin selector de otra dependencia.

## Riesgos

- **Validación sección ↔ fondo/subfondo** — no asumir solo `id_fondo`; si hay `id_subfondos`, exigir pertenencia al subfondo.
- **Base64 grande / canvas vacío** — 422 en español.
- **Muchos segmentos** — top N + «Otros» (ya existe).
- **Usuario sin dependencia/departamento** (no-root) — 403 o vacío, nunca datos globales.
- **Endpoints de subseries** — confirmar forma de respuesta (`data` vs array plano) como en Carátula v2.

## Impacto en datos / permisos

- Sin migraciones ni Mongo.
- Sin roles nuevos.
- Solo lectura agregada acotada por pertenencia.
- Contrato JSON/PDF ampliado con `id_subfondos` e `id_subseries` (retrocompatible si ausentes = vacío).
