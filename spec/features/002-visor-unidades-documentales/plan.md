# 002 · Visor de unidades documentales (PDF) — Plan

**Ticket:** —

_Cómo se implementa lo descrito en `spec.md`. Debe respetar la `constitution/`._

## Enfoque

Reutilizar **Fancybox** (`@fancyapps/ui`, ya en el stack y usado en `Buscar.vue` / `Consultar.vue`) para previsualizar PDFs de unidades documentales en overlay con navegación entre ítems. Extraer la lógica de apertura a un helper o componente reutilizable y conectarlo al modal **Ver Unidades Documentales** en las pantallas de Archivo/Expedientes.

En paralelo, restringir el **alta** de unidades al flujo de **Trámite** (UI + validación en `UnidadDocumentalController::store` según `estado` del expediente = `en_tramite`) y validar MIME/extensión PDF.

Sin migraciones ni dependencias nuevas.

## Hallazgos de investigación

- Alta actual: `POST /unidades-documentales` → `UnidadDocumentalController::store` guarda en disco `public` bajo `documentos/` y persiste ruta en `unidades_documentales.archivo`.
- UI duplicada en `Tramite.vue`, `Concentracion.vue`, `Historico.vue`, `Expedientes.vue`: botón **Añadir**, modal de alta, botón **Ver Unidades Documentales** y modal listado con enlace `target="_blank"` a `/storage/${unidad.archivo}`.
- Fancybox ya importado en legacy (`resources/js/admin/Buscar.vue`: `Fancybox.show(items)` con `src` bajo `/storage/...`).
- Modelo `UnidadDocumental` / relación `Expediente::unidadesDocumentales()` sin cambios de esquema necesarios.
- Tech stack: no agregar librería de PDF/carrusel nueva; Fancybox cubre PDF + navegación.

## Archivos afectados

| Archivo | Tipo de cambio |
|---|---|
| `app/Http/Controllers/UnidadDocumentalController.php` | modificar — validar PDF; rechazar alta si expediente no está `en_tramite` |
| `resources/js/admin/Tramite.vue` | modificar — alta PDF; lista clicable + cierre al abrir visor |
| `resources/js/admin/Concentracion.vue` | modificar — quitar alta; lista + visor |
| `resources/js/admin/Historico.vue` | modificar — quitar alta; lista + visor |
| `resources/js/admin/Expedientes.vue` | modificar — quitar alta; lista + visor |
| `resources/js/utils/visorUnidadesDocumentales.js` | nuevo — helper Fancybox PDF |
| `resources/js/components/ListaUnidadesDocumentales.vue` | nuevo — lista clicable; emite `seleccionada` para cerrar modal |
| `routes/web.php` | sin cambio de rutas (el POST existente se endurece en controlador) |

## Implementación

1. Crear helper JS reutilizable que filtre unidades con `archivo` PDF, arme ítems Fancybox (`src: /storage/...`, caption con tipo/nombre/descripción) y llame `Fancybox.show(items, { startIndex })`.
2. En el modal **Ver Unidades Documentales** de las cuatro pantallas: sustituir el enlace “Ver archivo” por acción que abre el visor en el índice correspondiente; mantener listado de metadatos.
3. En `Tramite.vue`: `accept` PDF en el input; feedback si el usuario elige otro tipo.
4. En `Concentracion.vue`, `Historico.vue`, `Expedientes.vue`: eliminar botón, modal y métodos de **Añadir Unidad Documental** (dejar solo consulta + visor).
5. En `UnidadDocumentalController::store`: validar `archivo` como `mimes:pdf` / `mimetypes:application/pdf`; cargar expediente y exigir `estado === 'en_tramite'`; responder 422/403 con mensaje en español.
6. Validar manualmente en UI con expediente en trámite (varios PDFs) y en concentración/histórico (solo ver, sin alta). **Hecho (validado por producto).**

## Decisiones

- **Fancybox overlay con navegación** — ya está en dependencias; evita carrusel Bootstrap (malo para PDF) y librerías nuevas. Descartado: solo `target="_blank"` o iframe fijo en el modal.
- **Solo PDF en v1** — acotado por producto; imágenes/otros formatos fuera de alcance.
- **Alta solo en Trámite** — captura digital ligada a `en_tramite`; UI + backend para no depender solo de ocultar botones.
- **Sin endpoint nuevo de “stream”** — se sigue sirviendo por `/storage/...` (disco público); no ampliar `routes/api.php`.
- **Helper + componente de lista** — `visorUnidadesDocumentales.js` + `ListaUnidadesDocumentales.vue` para UI de lista clicable compartida; al seleccionar se cierra el modal Bootstrap y se abre Fancybox.

## Riesgos

- **Archivos históricos no-PDF** ya subidos — el listado los muestra; al abrir, o bien se excluyen del carrusel Fancybox y se ofrece “Descargar / abrir en pestaña”, o se intenta abrir y Fancybox falla. Mitigación: filtrar por extensión `.pdf` en el visor; para no-PDF mostrar enlace de descarga sin overlay.
- **Storage link** — si `php artisan storage:link` no existe en el entorno, el preview falla igual que hoy. Mitigación: documentar en validación manual; no es cambio de feature.
- **URL hardcodeada con puerto en Buscar.vue** — no copiar ese patrón (`:8000`); usar rutas relativas `/storage/...`.
- **Bypass del UI** — alguien podría POST desde Concentración vía API. Mitigación: validación de estado en el controlador.

## Impacto en datos / permisos

- Sin migraciones MySQL ni Mongo.
- Sin cambios de roles: quien ya ve el modal puede previsualizar.
- Regla de negocio nueva: alta de unidad documental solo si el expediente está `en_tramite`.
