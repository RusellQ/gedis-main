# 003 · Estadísticas en inicio (gráficas + PDF) — Plan

**Ticket:** —

_Cómo se implementa lo descrito en `spec.md`. Debe respetar la `constitution/`._

## Enfoque

Extender el **dashboard de inicio** (`Dashboard.vue`) con:

- Modo **Expedientes** (un estado a la vez: trámite | concentración | histórico) o **Series creadas**.
- Cascada de catálogo con **regla de nivel**: Fondo → (Subfondo) → Sección → Serie (específica o todas).
- Agrupación automática al **siguiente nivel** no fijado (p. ej. con sección y “todas las series” → pastel por serie).
- Tipo de gráfica Chart.js: *pie*, *bar*, *polarArea*.
- PDF DomPDF: encabezado oficial + imagen de la gráfica.

Agregaciones en **MySQL/Eloquent** con alcance por dependencia/departamento (`root` = global). Sin MongoDB, sin migraciones, sin librerías nuevas.

## Hallazgos de investigación

- Inicio: `GET /dashboard` → `UsersController::dashboard` → `Dashboard.vue` (solo saludo).
- Chart.js / `vue-chartjs` ya en el stack; hoy `StatisticsChart.vue` solo usa Bar. Hay que registrar elementos para Pie / PolarArea / Bar según el tipo elegido.
- PDF de referencia: `resources/views/pdf/expedientes.blade.php` — logos `escudo.png` / `gobierno.png`, título institucional, fecha.
- Alcance: patrón `ExpedienteController` (root vs `id_dependencia` + `id_departamento`).
- Legacy Mongo `/estadisticas`: no tocar.

## Archivos afectados

| Archivo | Tipo de cambio |
|---|---|
| `routes/web.php` | modificar — JSON stats + PDF |
| `app/Http/Controllers/DashboardStatsController.php` | nuevo — agregaciones + PDF |
| `resources/views/pdf/dashboard-estadisticas.blade.php` | nuevo — header tipo inventario + `<img>` de la gráfica |
| `resources/js/admin/Dashboard.vue` | modificar — filtros, tipo de chart, descarga |
| `resources/js/components/DashboardStatsChart.vue` (orientativo) | nuevo — renderiza pie / bar / polarArea según prop |
| `resources/js/app.js` | solo si hace falta registro global |
| `EstadisticasController` / Mongo | **no tocar** |

## Implementación

1. **Backend — alcance** sobre consultas de `Expediente` / bajas: no-root filtra dependencia + departamento; `root` sin filtro.
2. **`GET` JSON** `/dashboard/estadisticas?dimension=...` → labels, data, total, alcance.
3. **`POST` PDF** `/dashboard/estadisticas/pdf` con: `dimension`, `tipo_grafica`, `titulo` (o construido en servidor), `chart_image` (base64 **obligatorio**). DomPDF: header + imagen a ancho útil de página.
4. **Frontend:** selectores de dimensión y tipo; componente de chart que cambie entre Pie / Bar / PolarArea; al descargar, exportar canvas → POST → blob PDF.
5. Validar UI vacía/error y PDF con logos + gráfica correcta para cada tipo.

## Decisiones

- **MySQL, no Mongo** — pedido explícito + constitución.
- **Contenido del PDF = gráfica (imagen)** — no basta una tabla como sustituto; el usuario elige el tipo y eso debe verse en el PDF. Una tabla auxiliar bajo la imagen es opcional (no requerida por producto).
- **POST con base64** — DomPDF no ejecuta Chart.js; el front captura el canvas. Mitigar tamaño (JPEG/PNG razonable, ancho acotado).
- **Tres tipos Chart.js** — pie, bar, polarArea; sin otras variantes en v1.
- **Filtro = dimensión completa de v1** — ciclo vital, bajas, fondo, sección, serie, subserie (todo lo acordado para expedientes/catálogo en esta feature).
- **`root` = global**; no selector de otra dependencia en v1.
- **Título del PDF** — debe dejar claro que es estadísticas del sistema/AG y la dimensión (y tipo) aplicada, no copiar literal “INVENTARIO GENERAL DE ARCHIVO” salvo que producto lo pida; la **estructura visual** del header sí se copia.

## Riesgos

- **Base64 grande / canvas vacío** — validar en servidor que venga imagen; si falla, 422 con mensaje en español; no generar PDF solo con header vacío.
- **Muchos segmentos** — pastel/polar ilegible; ordenar por conteo; valorar top N + “Otros” en implementación.
- **Expedientes sin clasificar** — bucket “Sin clasificar”.
- **Usuario sin dependencia/departamento** (no-root) — 403 o vacío explícito, nunca datos globales.
- **PolarArea / Pie con un solo valor** — Chart.js lo soporta; probar visualmente.

## Impacto en datos / permisos

- Sin migraciones ni Mongo.
- Sin roles nuevos.
- Solo lectura agregada acotada por pertenencia.
