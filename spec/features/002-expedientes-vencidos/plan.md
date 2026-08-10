# 002 · Expedientes vencidos — Plan

**Ticket:** —

## Enfoque

Implementar el módulo como una pantalla Blade/Vue 3 integrada al panel administrativo existente, respetando la convención de `resources/views/administrativas` y `resources/js/admin`. La lógica de selección de expedientes vencidos se hará en el backend con Eloquent y la lógica de reglas documentales de la serie, manteniendo el alcance por rol y dependencia/departamento.

## Hallazgos de investigación

- Ya existe una lógica de alertas de expedientes vencidos en `app/Providers/AppServiceProvider.php` que usa `fecha_cierre` y alcance por dependencia/departamento.
- `ExpedienteController@index()` devuelve expedientes con reglas documentales formateadas desde `ReglasDocumentalesService`.
- Las vistas Blade de módulo siguen el patrón `resources/views/administrativas/*.blade.php` con un solo componente Vue montado en `#app`.
- `resources/js/app.js` registra componentes globales Vue usados en el panel.
- `routes/web.php` define rutas de vista simples y endpoints REST para expedientes.

## Archivos afectados

| Archivo | Tipo de cambio |
|---|---|
| `routes/web.php` | nuevo / modificar |
| `app/Http/Controllers/ExpedienteController.php` | modificar |
| `app/Services/ReglasDocumentalesService.php` | posible modificación si se requiere lógica de vencimiento adicional |
| `resources/views/administrativas/vencidos.blade.php` | nuevo |
| `resources/js/admin/Vencidos.vue` | nuevo |
| `resources/js/app.js` | modificar |

## Implementación

1. Añadir ruta de vista `/vencidos` en `routes/web.php` que retorne `administrativas.vencidos`.
2. Crear nueva Blade `resources/views/administrativas/vencidos.blade.php` con `vencidos-vue` montado.
3. Crear nuevo componente Vue `resources/js/admin/Vencidos.vue` que consuma un endpoint backend para obtener expedientes vencidos.
4. Registrar `Vencidos` en `resources/js/app.js` con `app.component('vencidos-vue', Vencidos)`.
5. Agregar método `ExpedienteController::vencidos()` que devuelva expedientes vencidos según reglas documentales y alcance del usuario.
6. (Opcional) Si es necesario, extender `ReglasDocumentalesService` con un método `expedienteVencido(Expediente $expediente)` o `vencimientoExpediente(Expediente $expediente)`.
7. Implementar filtros en el componente Vue: texto libre, rango de fecha de cierre y selección de serie/valor documental.
8. Añadir enlace en el menú lateral en `resources/views/layouts/master.blade.php` bajo el grupo `Archivo` o `Expedientes` según coherencia del panel.

## Decisiones

- **Pantalla Blade + Vue** — Se mantiene el patrón actual de módulos Vue montados desde Blade.
- **Endpoint en `ExpedienteController`** — Reusa el mismo controlador de expediente existente para lógica de negocio y permisos.
- **Reglas documentales de serie** — La validación de vencimiento debe basarse en la serie asociada y la fecha de cierre; no se filtra solo por `fecha_cierre <= hoy`.

## Riesgos

- **Criterio de vencimiento mal definido** — Se mitigará implementando la lógica de términos documentales existentes y no solo la fecha de cierre.
- **Permisos incorrectos** — Revisar que `root` obtenga todo y `administrador` solo su dependencia/departamento.
- **Duplicación de lógica de alertas** — El módulo debe reusar el patrón existente de alertas para no introducir diferenciales con el menú superior.

## Impacto en datos / permisos

- No se requiere migración MySQL nueva.
- Afecta el alcance de consulta de expedientes por rol y dependencia/departamento.
- No hay cambios en MongoDB.
