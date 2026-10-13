# 006 · Contraseña temporal en el primer inicio de sesión

**Estado:** implementado ✅
**Ticket:** —

## Qué hace

Cuando un administrador da de alta a un usuario, la contraseña que captura es **temporal**. La primera vez que esa persona inicia sesión, GEDIS no la deja entrar al sistema: le muestra una pantalla para elegir su contraseña definitiva. Hasta que la cambia y la confirma, no puede usar el resto de la aplicación. En los inicios siguientes entra directo al tablero, como hoy.

Lo mismo ocurre si, más adelante, el administrador cambia la contraseña desde el listado de usuarios: esa clave vuelve a ser temporal y, en el siguiente inicio de sesión, hay que definir otra. Si el listado se guarda sin escribir una contraseña nueva, la cuenta no se vuelve a marcar.

Quienes ya tenían cuenta antes de este cambio siguen entrando con su contraseña actual, sin esta pantalla, hasta que un administrador les asigne una contraseña nueva desde el listado.

## Por qué

Hoy el alta guarda la contraseña que escribe el administrador y el primer inicio de sesión la deja vigente para siempre. Lo mismo pasa si después la cambia desde el listado: la clave que conoce el administrador queda como definitiva. El personal debe definir la suya antes de trabajar en expedientes, archivo o administración, tanto en el alta como cada vez que un administrador le asigne otra.

## Para quién

- **Administrador o `root`** — da de alta al usuario o le asigna otra contraseña desde el listado, y le entrega esa clave temporal.
- **Usuario afectado** (cualquier rol: `administrador`, `editor`, `lector`, u otro que se asigne en el alta) — la cambia en el primer inicio tras el alta, o en el siguiente inicio después de que un administrador se la haya cambiado.

## Criterios de aceptación

_Condiciones verificables que deben cumplirse para dar la feature por terminada. Redacta cada una de forma que se pueda comprobar con un sí/no. Marca `[x]` al cumplirse._

- [x] Al crear un usuario desde el alta administrativa, la contraseña capturada queda marcada como temporal (mínimo 8 caracteres, igual que hoy).
- [x] El primer inicio de sesión correcto con esa contraseña no abre el tablero: abre una pantalla en español para definir la contraseña nueva y confirmarla.
- [x] Mientras no la cambie, escribir a mano otra URL de la aplicación (tablero, expedientes, usuarios, etc.) lo regresa a esa pantalla. Puede cerrar sesión.
- [x] La contraseña nueva debe tener al menos 8 caracteres, coincidir con la confirmación y ser distinta de la temporal. Si falla alguna regla, permanece en la pantalla y ve el error en español.
- [x] Al guardar una contraseña válida, entra al tablero. El siguiente inicio de sesión ya no pide el cambio.
- [x] Un usuario que ya existía antes de esta feature inicia sesión y llega al tablero sin pasar por la pantalla de cambio.
- [x] En el formulario de alta, el campo se presenta como contraseña temporal, para que quien crea la cuenta sepa que no es la definitiva.
- [x] Si el administrador guarda una contraseña nueva desde el listado de usuarios, esa clave queda temporal (mínimo 8 caracteres) y el siguiente inicio de sesión de esa persona abre de nuevo la pantalla de cambio.
- [x] Si el administrador guarda el listado sin escribir contraseña, la marca no cambia: quien ya tenía contraseña definitiva sigue entrando al tablero.
- [x] En el modal de edición, el campo avisa que la contraseña que se escriba será temporal.

## Fuera de alcance

_Lo que esta feature NO incluye, para evitar que crezca. Si algo se difiere, enlaza a dónde (roadmap/backlog)._

- Recuperación por correo («¿Olvidaste tu contraseña?»). El enlace del login solo aparece si existiera la ruta; no se implementa aquí.
- Forzar el cambio a cuentas ya existentes que nadie ha reasignado desde el listado.
- Cambio voluntario de contraseña desde el perfil, historial de contraseñas, caducidad periódica o doble factor.
- Cerrar el hueco general de rutas web que hoy no llevan middleware `auth` (deuda previa). Esta feature solo impide usar la sesión mientras la contraseña siga siendo temporal.
