# 001 · Administración del catálogo de clasificación — Tareas

**Ticket:** —

_Checklist accionable derivada del `plan.md`. Tareas pequeñas y concretas; marca `[x]` al completarlas._

## Investigación

- [x] Revisar formularios/modales de alta en `resources/js/admin/Series.vue` (campos serie/subserie) como referencia de UI.
- [x] Revisar `InstrumentoControlService` y modelo `InstrumentoControl` (cómo se enlaza `tipo_nivel`, `codigo_clasificacion`, `id_padre`).
- [x] Confirmar validaciones y payload actual de cada `store` (Fondo → Subserie) y filtros por dependencia.

## Implementación

- [x] Añadir ruta de vista (p. ej. `GET /admin/catalogo`) + Blade que monte el componente Vue.
- [x] Crear `AdminCatalogo.vue` (nombre final a convenir): navegación jerárquica + formularios alta/edición + confirmación de borrado.
- [x] Registrar el componente en `resources/js/app.js`.
- [x] Ítem de menú en `master.blade.php` → sección Administración, solo `hasRole(['root'])`.
- [x] Restringir endpoints del módulo a `root` (check en controller; 403 si no).
- [x] `FondoController`: `update` + `destroy`; alta usable por root con selección de dependencia/departamento.
- [x] `SubfondoController`: `update` + `destroy` (+ ajuste root si aplica).
- [x] `SeccionController`: `update` + `destroy` (+ ajuste root si aplica).
- [x] `SerieController`: `update` + `destroy` (incl. campos CADIDO/reglas) (+ ajuste root si aplica).
- [x] `SubserieController`: `update` + `destroy` (+ ajuste root si aplica).
- [x] Extender `InstrumentoControlService` para sincronizar/eliminar instrumento al editar/borrar.
- [x] Reglas de borrado: bloquear si hay hijos o expedientes; mensaje en español.
- [x] Rutas `PUT/PATCH` y `DELETE` (o POST equivalentes al estilo del proyecto) en `routes/web.php`.
- [x] Sin migración de esquema (N/A salvo descubrimiento contrario).
- [x] Variables de entorno nuevas: N/A.

## Validación

- [x] `npm run build` (o `npm run dev`) sin errores en lo tocado.
- [ ] Prueba manual: root crea Fondo → Subfondo → Sección → Serie → Subserie y edita cada uno.
- [ ] Prueba manual: borrado bloqueado con hijos/expedientes; borrado OK en hoja sin hijos.
- [ ] Prueba manual: usuario `administrador` (u otro no-root) no ve el menú y recibe 403 en endpoints.
- [ ] Tras alta, el nivel nuevo aparece en selects de `/fondos` (Crear Expedientes) en el alcance correcto.
- [ ] Validar contra los criterios de aceptación de `spec.md`.
- [ ] Mover la feature a "Hecho" en `../../constitution/roadmap.md` (y quitarla de "Siguiente").
