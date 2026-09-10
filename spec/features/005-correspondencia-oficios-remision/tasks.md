# 005 · Correspondencia: oficios, remisión y archivo — Tareas

**Ticket:** —

_Checklist accionable derivada del `plan.md`. Marca `[x]` al completarlas._

## Investigación

- [x] Revisar flujo actual `Correspondencia.vue` / `CorrespondenciaEditor.vue` / `CorrespondenciaController` y listar qué se reutiliza vs se reemplaza.
- [x] Revisar alta de expediente (`/guardarexpediente`, `Fondos.vue`) y alta de unidades documentales (Trámite / endpoints existentes).
- [x] Confirmar con producto la **regla exacta del número de oficio** (origen de cada segmento del prefijo) usando el ejemplo `SEGOB/AGE/DG/DTlyC/0049/2026`.
  - _Decisión:_ mismo patrón que la clave de expediente — `dependencia.clave`[/`departamento.clave`]/consecutivo/año (sin prefijo en `.env`).
- [x] Inventariar assets del membrete (escudo, logos, pie) para la plantilla PDF/UI.

## Implementación — datos y dominio

- [x] Definir estados del oficio y campos de remisión/archivo en modelo (`database/sql/005-correspondencia-oficios-remision.sql`).
- [x] Servicio de **número de oficio** (`OficioNumeroService`).
- [x] Endpoints: crear/editar borrador, emitir, listar bandeja, remitir, listar recepción, archivar en expediente, usuarios-remisión.
- [x] Plantilla DomPDF del oficio (`resources/views/pdf/oficio.blade.php`).
- [x] Conservar firma SAT **sandbox** (acción opcional “Firmar (prueba)”).

## Implementación — frontend y menú

- [x] Sidebar: Correspondencia con **dos** ítems (bandeja/remisión y recepción/archivo) en `master.blade.php`.
- [x] Vista **Bandeja**: listado + crear oficio (plantilla) + remitir.
- [x] Editor de oficio: campos verdes + TinyMCE en **cuerpo**; fecha y número en azul no editables.
- [x] Vista **Recepción**: listado de remitidos + archivar / crear expediente vía `SelectorExpedienteCascada`.
- [x] Registrar componentes en `app.js`; eliminar registro de Explorer / editor huérfano.
- [x] CSS de contenido TinyMCE **no** aplica `margin` al `body` (solo `skin.css` + `content_style`).

## Limpieza 004 (mínima obligatoria)

- [x] Quitar del menú/rutas activas el flujo monolítico de cascada duplicada (`/correspondencia` → redirect bandeja; sin `/correspondencia/crear`).
- [x] Documentar en README qué scripts SQL aplicar (004, 005; 004b solo entornos rotos).

## Validación

- [x] `npm run build` sin errores en lo tocado (Node 18+).
- [x] `php artisan test` filtros OficioNumero + Correspondencia.
- [ ] Validación manual: crear oficio con tabla e imagen → PDF; remitir → archivar en expediente existente; crear expediente + unidad.
- [ ] Validar sidebar sin margen blanco (regresión TinyMCE).
- [ ] Validar contra criterios de aceptación de `spec.md`.
- [ ] Mover la feature a "Hecho" en `../../constitution/roadmap.md` (y quitarla de "Siguiente").

## Mantenimiento

- [ ] Al tocar la plantilla de oficio, contrastar PDF generado vs ejemplo oficial adjunto en el ticket/chat.
- [ ] Si cambian códigos de área/dependencia, revisar el servicio de numeración.
