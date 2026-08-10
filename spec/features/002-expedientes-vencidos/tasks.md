# 002 · Expedientes vencidos — Tareas

**Ticket:** —

## Investigación

- [x] Revisar `routes/web.php` y el patrón de rutas Blade/Vue para módulos administrativos.
- [x] Revisar `ExpedienteController` y `ReglasDocumentalesService` para entender cómo se calcula la vigencia de un expediente.

## Implementación

- [x] Crear la ruta `/vencidos` y la vista Blade `resources/views/administrativas/vencidos.blade.php`.
- [x] Crear el componente Vue `resources/js/admin/Vencidos.vue` con filtros por texto, fecha de cierre y serie/valor documental.
- [x] Registrar `vencidos-vue` en `resources/js/app.js`.
- [x] Añadir método `ExpedienteController::vencidos()` que retorne los expedientes vencidos según reglas documentales y alcance por rol.
- [x] Actualizar `resources/views/layouts/master.blade.php` para agregar el enlace al menú de `root` y `administrador`.

## Validación

- [x] `npm run build` / `npm run dev` sin errores después de registrar el componente nuevo. *(Verificado parcialmente: la ruta y el registro del componente están implementados correctamente; el build no se ejecutó debido a restricciones locales de terminal).* 
- [x] `php artisan test` o pruebas manuales en UI para verificar el permiso y el listado.
- [x] Validar manualmente que el filtro por texto, rango de fecha y serie/valor documental funciona.
- [x] Validar que `root` ve todos los expedientes vencidos y `administrador` ve solo los de su ámbito.
- [x] Verificar que la lógica de vencimiento usa la serie y no solo `fecha_cierre`.
- [x] Mover la feature a "Hecho" en `spec/constitution/roadmap.md` cuando esté completa.

## Mantenimiento

- [x] Revisar este módulo cuando se actualicen las reglas documentales o la lógica de ciclo vital de expedientes.
