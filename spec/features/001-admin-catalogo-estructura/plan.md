# 001 · Administración del catálogo de clasificación — Plan

**Ticket:** —

_Cómo se implementa lo descrito en `spec.md`. Debe respetar la `constitution/`._

## Enfoque

Nueva pantalla Blade + Vue en el grupo **Administración**, restringida a `root`, que ofrece CRUD completo de la jerarquía Fondo → Subfondo → Sección → Serie → Subserie. Se reutilizan los `store` y lecturas en cascada ya existentes; se añaden `update`/`destroy` (y listados admin si faltan) en los controladores correspondientes, con sincronización de `InstrumentoControl` vía extensión de `InstrumentoControlService`. Stack: Laravel 10 / Eloquent+MySQL / Blade+Vue 3 / Vite / Bootstrap 5; sin nuevas dependencias.

## Hallazgos de investigación

- **UI activa de expedientes:** `resources/js/admin/Fondos.vue` + ruta `/fondos` — solo selects en cascada + `POST /guardarexpediente`. Sin alta de niveles.
- **Stores existentes (sin update/destroy):**
  - `POST /guardarfondos` → `FondoController@store` (+ `InstrumentoControlService::crearParaFondo`)
  - `POST /guardarsubfondo` → `SubfondoController@store`
  - `POST /secciones`, `POST /fondos/{fondo}/secciones` → `SeccionController@store` (+ instrumento)
  - `POST /guardarserie` / `POST /series` → `SerieController@store` (+ instrumento + campos CADIDO)
  - `POST /guardarSubserie` → `SubserieController@store` (+ instrumento)
- **Lecturas en cascada:** `/mostrarfondos`, `/fondos/{id}/subfondos`, `/fondos/{id}/secciones`, `/subfondos/{id}/secciones`, `/secciones/{id}/series`, `/series/{id}/subseries`.
- **Legacy UI:** `Series.vue` (`/series`) tiene modales Crear Serie/Subserie/Expediente — menú comentado; útil como referencia de campos. `Indexfondos.vue` es árbol de consulta.
- **Menú Administración** (`master.blade.php` ~174–190): Dependencias (`root`), Departamentos, Crear/Listar usuarios — aquí irá el nuevo ítem solo `root`.
- **Permisos hoy:** sin middleware de rol en rutas de catálogo; el control es el sidebar. Los `store` de hijos filtran padre por `id_dependencia`/`id_departamento` del usuario — problemático para `root` sin pertenencia: el módulo debe permitir a `root` elegir dependencia/departamento (o operar sin ese filtro).
- **InstrumentoControlService:** solo métodos `crearPara*`; no hay actualizar/eliminar instrumento al editar/borrar nivel.
- **Modelo:** relaciones `hasMany` padres→hijos; `Serie`/`Subserie` → `expedientes`. Borrado debe validar hijos y expedientes.

## Archivos afectados

| Archivo | Tipo de cambio |
|---|---|
| `routes/web.php` | modificar — ruta vista + endpoints update/destroy (y show/list admin si hace falta) |
| `resources/views/layouts/master.blade.php` | modificar — ítem menú Administración solo `root` |
| `resources/views/administrativas/admin-catalogo.blade.php` (nombre final a convenir) | nuevo — monta el Vue |
| `resources/js/admin/AdminCatalogo.vue` (o similar) | nuevo — UI CRUD jerárquica |
| `resources/js/app.js` | modificar — registrar componente |
| `app/Http/Controllers/FondoController.php` | modificar — `update`, `destroy`; ajustar alta para `root` + dep/depto |
| `app/Http/Controllers/SubfondoController.php` | modificar — idem |
| `app/Http/Controllers/SeccionController.php` | modificar — idem |
| `app/Http/Controllers/SerieController.php` | modificar — idem |
| `app/Http/Controllers/SubserieController.php` | modificar — idem |
| `app/Services/InstrumentoControlService.php` | modificar — actualizar/eliminar (o recrear) instrumento al editar/borrar |
| `app/Http/Middleware/...` o checks en controller | modificar/nuevo — gate `root` en endpoints del módulo |
| `tests/Feature/...` (opcional recomendado) | nuevo — permisos root y borrado bloqueado con hijos |

## Implementación

1. **Gate de acceso** — Helper/check `hasRole('root')` en controladores (y menú Blade). Respuestas 403 para no-root.
2. **Vista + ruta** — Blade en `administrativas/` + ruta web (p. ej. `/admin/catalogo`) + componente Vue registrado en `app.js`.
3. **UI** — Navegación por niveles (selects en cascada + panel del nivel actual, o árbol colapsable). Formulario alta/edición por tipo; confirmación SweetAlert2 en borrado. Para Fondo (y si aplica series), selector de dependencia/departamento cuando actúa `root`.
4. **Backend update** — Validar campos (código unique ignorando el propio id); actualizar registro; sincronizar `InstrumentoControl` (código/nombre/padre si aplica).
5. **Backend destroy** — Rechazar si existen hijos o expedientes; si OK, borrar instrumento asociado y el registro (transacción).
6. **Reutilizar stores** — Unificar contratos JSON donde convenga; corregir filtro de pertenencia para que `root` no quede bloqueado al crear bajo cualquier dependencia.
7. **Menú** — Enlace en `#collapseAdministracion` dentro de `@if(hasRole(['root']))`, etiqueta en español (p. ej. “Catálogo de clasificación”).
8. **Validación** — Build Vite; prueba manual como root vs administrador; comprobar que un nivel nuevo aparece en `/fondos`.

## Decisiones

- **Módulo nuevo en Administración, no reactivar `/series`** — esa pantalla mezcla expedientes y está fuera del menú; un módulo root-only de catálogo es más claro y evita devolver formularios a Crear Expedientes.
- **Solo rol `root`** — pedido explícito; alinea con Dependencias (también solo root).
- **Borrado restrictivo (sin cascade soft)** — no eliminar en cascada niveles con hijos/expedientes; evita huérfanos y pérdida de archivo. Alternativa descartada: soft-delete masivo.
- **Root elige dependencia/departamento al crear Fondo** — los `store` actuales copian del usuario; root puede no tenerlos o debe sembrar estructura de otras áreas. Descartado: forzar que root tenga siempre dep/depto fijos.
- **Extender controladores existentes** — no un “CatalogoAdminController” monolítico salvo que la repetición lo pida; mantener patrón por entidad del repo.
- **Sin migraciones de esquema** — se usa el modelo MySQL actual; solo lógica y UI.

## Riesgos

- **Root sin `id_dependencia`/`id_departamento`** — los stores actuales de serie/sección fallan o 403. Mitigación: rama `if ($user->hasRole('root'))` + campos explícitos en el request.
- **Desincronización Instrumento de Control** — editar código sin actualizar instrumento rompe cuadro/catálogos. Mitigación: métodos de sync en el service dentro de la misma transacción.
- **Borrar nivel con datos** — riesgo de integridad. Mitigación: comprobaciones explícitas + mensaje claro en español.
- **Códigos duplicados** — validar `unique` en update con `Rule::unique(...)->ignore($id)`.
- **Scope creep hacia legacy** — no reabrir `/series`/`/indexfondos` en esta feature (roadmap backlog sigue vigente).

## Impacto en datos / permisos

- **MySQL:** sin cambio de tablas; altas/ediciones/bajas de filas de catálogo + filas de `instrumentos_control` alineadas.
- **Permisos:** nuevo superficie solo `root`; no cambia roles de Crear Expedientes (`administrador`+`root`).
- **Mongo:** ninguno.
