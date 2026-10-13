# 006 · Contraseña temporal en el primer inicio de sesión — Tareas

**Ticket:** —

_Checklist accionable derivada del `plan.md`. Tareas pequeñas y concretas; marca `[x]` al completarlas._

## Investigación

- [x] Releer `UsersController::store`, `::update` y `::login`, `User`, `Listuser.vue`, `routes/web.php` (login, logout, `/users`) y `resources/views/auth/login.blade.php` antes de implementar.
- [x] Confirmar que no se agrega ni se edita ninguna migración Laravel. La columna ya está en MySQL por el `ALTER` manual.

## Implementación

- [x] No dejar migración en el repo. La columna `debe_cambiar_password` ya se aplicó a mano con el `ALTER TABLE`.
- [x] Añadir `debe_cambiar_password` a `$fillable` y al cast booleano de `app/Models/User.php`.
- [x] En `UsersController::store`, guardar la marca en `true` al crear el usuario.
- [x] En `UsersController::update`, si llega `password`, validar mínimo 8, hashearla y poner la marca en `true`. Si no llega, no tocar contraseña ni marca.
- [x] En `Listuser.vue`, etiqueta «Contraseña temporal» y texto de que, si se escribe una, deberá cambiarla en el siguiente inicio.
- [x] En `UsersController::login`, si la marca está activa, redirigir a la pantalla de cambio; si no, mantener el redirect al tablero.
- [x] Crear `GET`/`POST /cambiar-contrasena` (rutas con `auth`), vista Blade en español y validación: mínimo 8, `confirmed`, distinta de la temporal.
- [x] Al guardar, hashear la nueva contraseña, poner la marca en `false` y redirigir a `/dashboard`.
- [x] Middleware `DebeCambiarPassword` en el grupo `web` (`app/Http/Kernel.php`): redirigir al formulario salvo la propia pantalla, su `POST` y `logout`.
- [x] En `Createuser.vue`, etiqueta «Contraseña temporal» y texto de que deberá cambiarla en el primer inicio.

## Validación

- [x] Alta de un usuario nuevo → primer login muestra solo la pantalla de cambio; otra URL autenticada regresa ahí; logout sí sale.
- [x] Contraseña corta, confirmación distinta o igual a la temporal: error en español y sigue en la pantalla.
- [x] Contraseña válida → tablero; cerrar sesión y volver a entrar ya no pide el cambio.
- [x] Un usuario anterior (marca en falso) entra al tablero sin la pantalla nueva.
- [x] Desde el listado, asignar otra contraseña a ese usuario → su siguiente inicio abre la pantalla de cambio. Guardar el mismo usuario sin contraseña no vuelve a pedirla.
- [x] `npm run build` (o `npm run dev`) sin errores en `Createuser.vue` y `Listuser.vue`.
- [x] `php artisan test` si se añade cobertura del login/alta; si no, dejar constancia de la prueba manual.
- [x] Validar contra los criterios de aceptación de `spec.md`.
- [x] Mover la feature a «Hecho» en `../../constitution/roadmap.md` (y quitarla de «Siguiente» si estaba). No desplazar otra feature que siga en curso hasta cerrar esta.

## Mantenimiento (checklist recurrente)

- [ ] Cualquier alta o reasignación de contraseña hecha por un tercero (otro controlador o seeder de cuentas operativas) debe dejar `debe_cambiar_password` en `true`.
