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

## Implementación

- [x] Crear `DashboardStatsController` con alcance, modo expedientes/series, estado separado y agrupación por siguiente nivel.
- [x] Rutas autenticadas: JSON + `POST` PDF.
- [x] Plantilla DomPDF con encabezado tipo inventario + imagen de gráfica.
- [x] Componente Vue pie / barras / polar area.
- [x] `Dashboard.vue` con cascada de catálogo + estado + tipo de gráfica + PDF.
- [x] UI en español.
- [x] No tocar Mongo / migraciones / env.

## Validación

- [x] `npm run build` (o `npm run dev`) sin errores en lo tocado.
- [ ] Probar cascada: sin fondo → por fondos; fondo → por secciones; sección + todas series → por series; serie concreta → por subserie.
- [ ] Probar estados trámite / concentración / histórico por separado.
- [ ] Probar modo series (conteo por fondo/sección y detalle con pertenencia).
- [ ] Probar como dependencia vs `root`.
- [ ] PDF con logos, título y gráfica del tipo elegido.
- [ ] Validar contra `spec.md` y mover a Hecho en roadmap.
