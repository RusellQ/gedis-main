# 005 · Correspondencia: oficios, remisión y archivo — Plan

**Ticket:** —

_Cómo se implementa lo descrito en `spec.md`. Debe respetar la `constitution/`._

## Enfoque

Rediseñar el módulo 004 sobre el stack existente (Laravel 10 + Eloquent/MySQL + Blade/Vue 3 + Vite + Bootstrap 5 + DomPDF), con **TinyMCE self-hosted (MIT)** solo para el cuerpo editable del oficio (sin reimportar `content.css` al `body` global).

Modelo mental:

- Un **oficio** es el artefacto de correspondencia (metadatos + plantilla + HTML del cuerpo + PDF).
- **Remisión** asigna el oficio a un destinatario interno (usuario).
- **Archivo** convierte el PDF del oficio en `unidad_documental` de un `expediente` (existente o recién creado).
- La jerarquía archivística **no se reimplementa** en Correspondencia: se reutiliza el patrón de Fondos/Trámite (endpoints y/o componente compartido).

## Hallazgos de investigación

- UI actual: `resources/js/admin/Correspondencia.vue` (~873 líneas) con cascada Fondo→Expediente duplicada, tabs de unidades y alta de expediente embebida.
- Editor: `CorrespondenciaEditor.vue` (modal TinyMCE) + PDF `resources/views/pdf/correspondencia.blade.php`.
- Código muerto: `CorrespondenciaExplorer.vue` (registrado, no montado); ruta `/correspondencia/crear` + Blade editor suelto.
- Schema SQL `database/sql/004-correspondencias.sql`: `correspondencias`, `correspondencia_firmas`, `correspondencia_anexos` (sin uso real), `correspondencia_auditorias`. FKs deben seguir tipos GEDIS (`expedientes.id` = `int` signed; `users.id` = `int unsigned`).
- Deuda: `004b-fix-…sql` y `gedis:fix-correspondencia-schema` nacieron por tipos `bigint` incorrectos.
- Menú: un solo ítem en `master.blade.php` bajo Expedientes → Correspondencia.
- Unidades documentales: endpoints existentes de alta PDF a expediente (reutilizar).
- Constitución: no es gestor Drive ni API pública; firma electrónica no es núcleo de producto → se **conserva** `SATSigningService` en **sandbox** (sin credenciales) como función de prueba; no se endurece a SAT real.

## Archivos afectados

| Archivo | Tipo de cambio |
|---|---|
| `resources/views/layouts/master.blade.php` | Modificar — submenú Correspondencia (2 ítems) |
| `routes/web.php` | Modificar — rutas bandeja / recepción / oficio / remisión / archivo |
| `resources/views/administrativas/correspondencia-*.blade.php` | Nuevo/modificar — shells de las 2 vistas |
| `resources/js/admin/CorrespondenciaBandeja.vue` (nombre final en tasks) | Nuevo — bandeja + crear/remitir |
| `resources/js/admin/CorrespondenciaRecepcion.vue` | Nuevo — recepción + archivar |
| `resources/js/admin/OficioEditor.vue` (o evolución de `CorrespondenciaEditor.vue`) | Nuevo/modificar — plantilla + TinyMCE cuerpo |
| `resources/js/components/…` (cascada expediente reutilizable, si se extrae) | Nuevo/modificar — evitar duplicar Fondos/Trámite |
| `resources/js/app.js` | Modificar — registrar componentes; quitar Explorer muerto |
| `app/Http/Controllers/CorrespondenciaController.php` (o split) | Modificar — CRUD oficio, remitir, archivar |
| `app/Services/OficioNumeroService.php` (nombre tentativo) | Nuevo — consecutivo + prefijo por emisor |
| `app/Services/SATSigningService.php` | Conservar sandbox de prueba; no exigir credenciales reales; exponer “Firmar” como acción opcional del oficio |
| `app/Models/Correspondencia*.php` | Modificar — campos de remisión/estados; anexos sin uso |
| `database/sql/` o migración Laravel nueva | Nuevo — columnas/tablas de remisión y número de oficio (**no** editar migraciones ya aplicadas) |
| `resources/views/pdf/oficio.blade.php` | Nuevo/modificar — plantilla DomPDF del oficio oficial |
| `tests/Feature/…` | Modificar/nuevo — remisión, número, archivo a unidad |

## Implementación

1. **Modelo de estados del oficio** — p. ej. `borrador` → `emitido` → `remitido` → `archivado` (nombres finales en español en UI). Guardar `id_usuario_emisor`, `id_usuario_destinatario`, `numero_oficio`, `asunto`, `leyenda_anio`, `destinatario_texto`, `cuerpo_html`, `fecha_oficio`, `id_expediente` / `id_unidad_documental` cuando se archive.
2. **Schema** — script SQL nuevo (`005-…sql`) o migración Laravel que altere `correspondencias` (o renombre conceptual a oficios) con tipos compatibles GEDIS; documentar en README. Deprecar uso de `correspondencia_anexos` si sigue sin lógica.
3. **Número de oficio** — misma lógica que la clave de expediente (`array_filter` + `implode('/')`): claves de dependencia/departamento del emisor + consecutivo anual + año. Sin `OFICIO_PREFIJO_BASE` en `.env`.
4. **PDF plantilla** — Blade DomPDF con zonas fijas (assets AGQROO), inyección de azul/verde; cuerpo HTML sanitizado/acotado para DomPDF.
5. **Vista Bandeja** — listado (recibidos/creados), crear oficio (plantilla + TinyMCE), remitir a usuario de la dependencia.
6. **Vista Recepción** — listado de remitidos al usuario; acción “Archivar en expediente”:
   - buscar/elegir expediente **reutilizando** endpoints/cascada existente (extraer componente compartido si hace falta);
   - o “Crear expediente” reutilizando flujo de Fondos/guardarexpediente;
   - subir/asociar PDF como unidad documental.
7. **Menú** — reemplazar el único link por dos collapse-items.
8. **Limpieza 004** — quitar registro de `CorrespondenciaExplorer`, ruta `/correspondencia/crear`, cascada monolítica actual; no cargar CSS de contenido TinyMCE en el `body` global.
9. **Permisos** — `root` / `administrador` / `editor` operan según vista; `lector` solo consulta si aplica; filtrar por dependencia y por `id_usuario_destinatario` en recepción.

## Decisiones

- **TinyMCE self-hosted (MIT)** — se mantiene; no TipTap en esta feature (menos riesgo, ya está). Solo `skin.css` global; estilos de cuerpo vía `content_style` / iframe.
- **SAT sandbox se mantiene** — sin credenciales → simulación; el hito operativo del 005 es remitir/archivar, pero “Firmar (prueba)” sigue como función opcional. SAT real = feature futura.
- **Reutilizar unidades documentales** — el oficio archivado es un PDF en `unidades_documentales`, no una tabla intermedia nueva.
- **No aplanar jerarquía** — crear expediente siempre por niveles existentes.
- **SQL/migración nueva** — no editar `004-correspondencias.sql` ya corrido en entornos; cambios posteriores en `005-…` o migración Laravel.
- **004b** — innecesario en instalaciones nuevas con 004 corregido; no es paso de esta feature salvo entornos rotos.

## Riesgos

- **Prefijo del número de oficio** — si el usuario no tiene códigos (SEGOB/AGE/…) en BD, hay que definir configuración mínima antes de emitir. Mitigación: servicio + campos/config y criterio de aceptación bloqueante.
- **DomPDF vs HTML rico** — tablas/imágenes complejas pueden verse distinto al editor. Mitigación: CSS acotado, prueba con el PDF de ejemplo adjunto.
- **Datos 004 existentes** — borradores HTML viejos pueden no mapear a plantilla. Mitigación: migración de datos mínima o convivir en borrador hasta reemitir.
- **Duplicación de cascada** — riesgo de volver a copiar Fondos.vue. Mitigación: componente/endpoints compartidos obligatorios en tasks.
- **Permisos encargada vs designado** — hoy solo hay roles genéricos. Mitigación: MVP por asignación `id_usuario_destinatario` + roles existentes; roles específicos “correspondencia” solo si se acuerda en implementación.

## Impacto en datos / permisos

- MySQL: nuevas columnas/estados (y posiblemente tabla de consecutivos de oficio); posible deprecación de `correspondencia_anexos` / desuso de `correspondencia_firmas` en el flujo principal.
- Permisos: filtrado por dependencia + destinatario; sin ampliar `routes/api.php` como API pública (endpoints web autenticados, prefijo `/api/correspondencia` actual puede reorganizarse bajo rutas web claras).
- MongoDB: ninguno.
