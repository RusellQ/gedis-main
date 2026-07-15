# 001 · Administración del catálogo de clasificación (CRUD)

**Estado:** implementado ✅
**Ticket:** —

## Qué hace

Módulo de administración donde un usuario con rol **root** puede **crear, consultar, editar y eliminar** los niveles de la jerarquía archivística:

**Fondo → (Subfondo) → Sección → Serie → (Subserie)**

La entrada aparece en el menú **Administración** (junto a Dependencias, Departamentos y usuarios). La pantalla de **Crear Expedientes** (`/fondos`) sigue usando selects en cascada sobre el catálogo ya existente; no vuelve a incluir formularios de alta de esos niveles.

## Por qué

Al refactorizar la creación de expedientes se perdieron los formularios embebidos para dar de alta fondo, sección, serie, etc. Hoy el personal root no tiene una UI activa para mantener el catálogo (los endpoints de alta existen en parte; edición y borrado no). Sin estructura en el catálogo no se pueden crear expedientes ni generar instrumentos coherentes.

## Para quién

- **Equipo técnico / administradores con rol `root`** — mantienen la estructura de clasificación por dependencia/departamento.
- No para `administrador`, `editor` ni `lector` (sin acceso al módulo ni a sus endpoints).

## Criterios de aceptación

_Condiciones verificables que deben cumplirse para dar la feature por terminada. Redacta cada una de forma que se pueda comprobar con un sí/no. Marca `[x]` al cumplirse._

- [x] Existe un ítem visible en **Administración** (p. ej. “Catálogo / Estructura”) **solo** para usuarios con rol `root`.
- [x] Un `root` puede dar de alta Fondo, Subfondo (opcional bajo un fondo), Sección, Serie y Subserie respetando la jerarquía (padre obligatorio cuando aplica).
- [x] Un `root` puede listar/consultar los niveles existentes (navegación en cascada o árbol) y **editar** nombre, código, descripción y, en serie, los campos de reglas/CADIDO que ya captura el `store` actual.
- [x] Un `root` puede **eliminar** un nivel solo si no tiene hijos ni expedientes asociados; si los tiene, la UI/API informa el motivo y no borra.
- [x] Usuarios sin rol `root` no ven el menú ni pueden usar los endpoints de este módulo (403 / sin enlace).
- [x] Al crear/editar se mantiene coherencia con **Instrumento de Control** (alta ya existente; actualización/borrado del instrumento alineado al nivel tocado).
- [x] La creación de expedientes en `/fondos` sigue funcionando: tras alta de un nivel, aparece en los selects en cascada (según dependencia/departamento correspondientes).
- [x] Textos de UI y mensajes en **español**.

## Fuera de alcance

- No reintroduce formularios de catálogo dentro de `Fondos.vue` / Crear Expedientes.
- No reactiva como producto las pantallas legacy `/series` ni `/indexfondos` (pueden usarse como referencia de UI; no es el entregable).
- No concede acceso a `administrador` / `editor` / `lector`.
- No incluye edición masiva, import Excel del catálogo, ni API pública.
- No cambia el ciclo vital de expedientes ni el módulo de carátulas.
- No migra el catálogo fuera de MySQL.
