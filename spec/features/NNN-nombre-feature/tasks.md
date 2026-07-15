# NNN · <Nombre de la feature> — Tareas

**Ticket:** <ID del ticket (si aplica) o "—">

_Checklist accionable derivada del `plan.md`. Tareas pequeñas y concretas; marca `[x]` al completarlas._

## Investigación

- [ ] <Archivo/patrón existente a revisar antes de implementar.>

## Implementación

- [ ] <Tarea concreta de implementación backend.>
- [ ] <Tarea concreta de implementación frontend.>
- [ ] Migración Laravel (`php artisan make:migration ...`) si aplica — **no** editar migraciones ya aplicadas.
- [ ] Documentar nuevas variables de entorno en `.env.example` (si aplica).

## Validación

- [ ] `npm run build` (o `npm run dev`) sin errores en lo tocado.
- [ ] `php artisan test` / PHPUnit para lo modificado (si aplica).
- [ ] Validación manual por UI según roles afectados.
- [ ] Validar contra los criterios de aceptación de `spec.md`.
- [ ] Mover la feature a "Hecho" en `../../constitution/roadmap.md` (y quitarla de "Siguiente" si estaba).

## Mantenimiento (checklist recurrente)

_Opcional. Pasos a repetir cada vez que se toque esta feature en el futuro. Borra esta sección si no aplica._

- [ ] <Acción recurrente.>
