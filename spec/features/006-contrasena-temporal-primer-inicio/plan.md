# 006 · Contraseña temporal en el primer inicio de sesión — Plan

**Ticket:** —

_Cómo se implementa lo descrito en `spec.md`. Debe respetar la `constitution/`._

## Enfoque

Marcar en MySQL que la contraseña es temporal cuando la define un administrador: en el alta y también cuando la reemplaza desde el listado. Tras un `Auth::attempt` correcto, si esa marca sigue activa, la sesión existe pero un middleware del grupo `web` solo deja ver la pantalla de cambio y el cierre de sesión. Al guardar una contraseña nueva válida se apaga la marca y se redirige a `/dashboard`. Guardar el listado sin contraseña no toca la marca.

Encaja con el stack actual: sesión Laravel, Eloquent/MySQL y formulario Blade (la pantalla de login ya es Blade, no Vue). No toca jerarquía archivística, Mongo ni `routes/api.php`. El esquema no lleva migración Laravel: la columna se agregó a mano.

## Hallazgos de investigación

- Alta: `POST /users` → `UsersController::store`. Valida `password` con `required|string|min:8`, la hashea y no distingue temporal de definitiva. El formulario está en `resources/js/admin/Createuser.vue` (etiqueta «Contraseña» + confirmación en cliente).
- Login: `POST /login` → `UsersController::login`. Si las credenciales coinciden, regenera la sesión y hace `redirect()->intended('/dashboard')`. Vista: `resources/views/auth/login.blade.php` (HTML propio, Tailwind por CDN, textos en español).
- Edición posterior: `PUT /users/{id}` → `UsersController::update` reemplaza la contraseña solo si el listado (`Listuser.vue`) envía `password` (el modal la deja vacía al abrir). Hoy no valida mínimo de 8 caracteres ni marca la clave como temporal. **En alcance:** si viene contraseña, validar `min:8`, hashear y poner `debe_cambiar_password` en `true`. Si no viene, no modificar ni la clave ni la marca.
- `users` no traía columna de «debe cambiar contraseña». Este entorno no usa migraciones ni seeders: la columna `debe_cambiar_password` se agregó con `ALTER TABLE` a mano (`TINYINT(1) NOT NULL DEFAULT 0`). No hay archivo de migración en el repo.
- Muchas rutas de `routes/web.php` no usan `middleware('auth')`. Por eso el bloqueo no puede colgarse solo de `auth`: va en el grupo `web`, después de la sesión, y redirige a quien ya esté autenticado y siga con contraseña temporal.
- No hay flujo de restablecimiento (`password.request` no está registrado; el enlace del login está condicionado a `Route::has`).

## Archivos afectados

| Archivo | Tipo de cambio |
|---|---|
| `database/migrations/` | sin cambio — no se agrega migración |
| `app/Models/User.php` | modificar — `fillable` y cast booleano |
| `app/Http/Controllers/UsersController.php` | modificar — marcar en `store` y en `update` si cambia la contraseña; redirigir en `login`; acción de cambio |
| `app/Http/Middleware/DebeCambiarPassword.php` | nuevo |
| `app/Http/Kernel.php` | modificar — registrar el middleware en el grupo `web` |
| `routes/web.php` | modificar — `GET`/`POST /cambiar-contrasena` |
| `resources/views/auth/cambiar-contrasena.blade.php` | nuevo — misma línea visual que el login |
| `resources/js/admin/Createuser.vue` | modificar — etiqueta y texto de ayuda: contraseña temporal |
| `resources/js/admin/Listuser.vue` | modificar — el campo del modal avisa que la contraseña escrita será temporal |

## Implementación

1. **Esquema** — columna `debe_cambiar_password` (`TINYINT(1) NOT NULL DEFAULT 0`) después de `password`, aplicada a mano. No crear migración Laravel ni correr `php artisan migrate`.
2. **Modelo** — incluir el campo en `$fillable` y castearlo a `boolean` en `User`.
3. **Alta** — en `UsersController::store`, guardar `debe_cambiar_password => true` junto con el hash. No cambiar reglas de validación ni roles/dependencia.
4. **Listado** — en `UsersController::update`, si `$request->filled('password')`, validar `string|min:8`, hashear y poner `debe_cambiar_password` en `true`. Si el campo no viene o va vacío, no tocar contraseña ni marca. En `Listuser.vue`, etiqueta «Contraseña temporal» y una línea de ayuda: si se escribe una, la persona deberá cambiarla en su siguiente inicio.
5. **Login** — si `Auth::attempt` tiene éxito y `debe_cambiar_password` es verdadero, redirigir a la ruta de cambio (no a `/dashboard` ni a `intended`, para no saltarse la pantalla). Si es falso, conservar `redirect()->intended('/dashboard')`.
6. **Pantalla** — Blade `auth/cambiar-contrasena.blade.php`, en español, con contraseña nueva, confirmación y enlace o botón de cerrar sesión. Misma familia visual que `login.blade.php`. El mismo formulario sirve tras el alta y tras un cambio hecho desde el listado.
7. **Guardar** — `POST` autenticado: validar `required|string|min:8|confirmed` y que no coincida con la temporal (`Hash::check`). Hashear, poner `debe_cambiar_password` en `false`, redirigir a `/dashboard`. Errores de validación en español, sin cerrar la sesión.
8. **Middleware** — en el grupo `web`, si hay usuario autenticado con la marca activa y la ruta no es el formulario de cambio, su `POST`, ni `logout`, redirigir a `GET /cambiar-contrasena`. Así no se evade escribiendo otra URL. Si el administrador se cambia la contraseña a sí mismo estando en sesión, la siguiente petición lo lleva a esa pantalla.
9. **Alta en Vue** — en `Createuser.vue`, renombrar la etiqueta a «Contraseña temporal» y una línea de ayuda: la persona deberá cambiarla en su primer inicio de sesión. La confirmación del alta se mantiene.

## Decisiones

- **Columna booleana en `users`, default `false`** — la misma marca cubre el alta y la reasignación desde el listado. Se descartó una tabla de tokens o el flujo de reset por correo de Laravel: no hay correo transaccional.
- **Bloqueo en el grupo `web`, no solo en el login** — el redirect del login no impide abrir otra ruta con la sesión ya creada. El middleware cierra ese hueco sin exigir, en esta feature, poner `auth` en todo `web.php`.
- **Pantalla Blade, no componente Vue del admin** — el usuario aún no debe entrar al shell SB Admin. El login ya es Blade.
- **La sesión se conserva al cambiar la clave** — no se pide volver a escribir la temporal. Se descartó cerrar sesión y obligar un segundo login.
- **Reasignar contraseña en el listado reactiva la marca** — solo cuando el `PUT` trae `password` con valor. Editar nombre, correo o roles deja la marca como estaba. No hace falta otro flujo: el login y el middleware ya obligan el cambio en el siguiente acceso.

## Riesgos

- **Cuentas ya en producción** — el `ALTER` deja la columna en `false` (`DEFAULT 0`). No actualizar filas existentes a `true`. Solo quedan obligadas si un administrador les escribe una contraseña nueva en el listado.
- **Sin migración en el repo** — la base real se actualizó con el `ALTER` manual. Si esa columna no existe, el login falla al leerla.
- **Edición sin contraseña** — `Listuser.vue` ya omite `password` si el campo va vacío. El backend debe ignorar la marca en ese caso para no bloquear a quien solo cambió datos de perfil.
- **Bucle de redirección** — excluir de forma explícita `GET`/`POST` de cambio y `POST /logout`.
- **`intended`** — no usarlo mientras la marca esté activa; si no, una URL guardada podría saltarse la pantalla antes de que el middleware esté en todas las peticiones del mismo request.
- **JSON de usuarios** — `listUsers` / `currentUser` pueden devolver el booleano. No es secreto. No añadir la contraseña en claro a ninguna respuesta.
- **Rutas sin `auth`** — siguen abiertas para quien no tiene sesión (deuda previa). Con sesión y marca activa, el middleware del grupo `web` sí las intercepta.

## Impacto en datos / permisos

Columna nueva en MySQL: `users.debe_cambiar_password` (boolean, default `false`), aplicada a mano con el SQL que se entregue al implementar. Sin Mongo. Sin seeders. Sin cambio de roles ni de filtro por dependencia/departamento. La marca se enciende en `store` y en `update` cuando hay contraseña nueva, para cualquier rol.
