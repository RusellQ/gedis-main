# 002 · Visor de unidades documentales (PDF) — Tareas

**Ticket:** —

_Checklist accionable derivada del `plan.md`. Tareas pequeñas y concretas; marca `[x]` al completarlas._

## Investigación

- [x] Revisar uso de Fancybox en `resources/js/admin/Buscar.vue` (`openFancybox` / imports CSS) y no copiar el `baseURL` con puerto fijo.
- [x] Confirmar en `Tramite.vue` / `Concentracion.vue` / `Historico.vue` / `Expedientes.vue` la duplicación del modal de unidades y del alta.
- [x] Revisar `UnidadDocumentalController::store` y el `estado` del modelo `Expediente`.

## Implementación

- [x] Crear `resources/js/utils/visorUnidadesDocumentales.js` (abrir Fancybox con PDFs del expediente + `startIndex` + captions).
- [x] Integrar el visor en el modal **Ver Unidades Documentales** de `Tramite.vue`.
- [x] Integrar el visor en `Concentracion.vue`, `Historico.vue` y `Expedientes.vue`.
- [x] En `Tramite.vue`: limitar input a PDF (`accept`) y mensaje de error si el tipo no es válido.
- [x] Quitar UI y lógica de **Añadir Unidad Documental** en Concentración, Histórico y Expedientes.
- [x] Endurecer `UnidadDocumentalController::store`: validación PDF + expediente `en_tramite`; mensajes en español.
- [x] Para unidades con archivo no-PDF legacy: enlace de descarga/abrir pestaña, sin incluirlas en el carrusel del visor.

## Validación

- [x] `npm run build` (o `npm run dev`) sin errores en lo tocado.
- [x] Validación manual: expediente en trámite con 0, 1 y N PDFs (navegación anterior/siguiente).
- [x] Validación manual: en Concentración/Histórico/Expedientes se ve el visor y **no** hay alta.
- [x] Validación manual: POST de alta con expediente no-trámite o archivo no-PDF es rechazado.
- [x] Probar con al menos un rol distinto de `root` (p. ej. `lector` o `editor`) que ya pueda ver el modal.
- [x] Validar contra los criterios de aceptación de `spec.md`.
- [x] Mover la feature a "Hecho" en `../../constitution/roadmap.md` (y quitarla de "Siguiente").
