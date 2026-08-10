# 002 · Expedientes vencidos

**Estado:** propuesta
**Ticket:** —

## Qué hace

Agrega un módulo nuevo llamado **Expedientes Vencidos** donde los usuarios con rol **root** y **administrador** pueden ver un listado de expedientes cuya vigencia documental ya ha vencido, calculada según las reglas documentales de la serie asociada.

La pantalla debe ofrecer:
- listado de expedientes vencidos con datos clave de la serie y del expediente,
- filtros por texto, fecha de cierre y serie/valor documental,
- alcance de datos según el usuario: `root` ve todos; `administrador` ve solo expedientes de su dependencia y departamento.

## Por qué

Permite identificar de forma clara y operativa los expedientes con vigencia documentala vencida, lo que ayuda a tomar decisiones de conservación, transferencia o baja conforme a las reglas del instrumento de control. Evita depender de vistas de alerta indirectas y centraliza este conjunto de expedientes en un módulo específico.

## Para quién

- **Administradores** de dependencia/departamento, que necesitan ver los expedientes vencidos en su ámbito.
- **Usuarios root**, que necesitan visibilidad global de expedientes vencidos para supervisión y control archivístico.

## Criterios de aceptación

- [ ] Hay un ítem de menú visible para `root` y `administrador` que abre la pantalla **Expedientes Vencidos**.
- [ ] La pantalla lista solo expedientes cuyo estado de vigencia documental ya venció según la serie asociada y la fecha de cierre del expediente.
- [ ] La lista incluye al menos: nombre, código, clave, serie, subserie, fecha de cierre, dependencia/área, y tipo de vigencia documental.
- [ ] Hay filtros de búsqueda por texto libre, por rango de fecha de cierre y por serie/valor documental.
- [ ] `root` ve expedientes de todas las dependencias; `administrador` ve solo los de su dependencia/departamento.
- [ ] El backend utiliza la lógica de reglas documentales de la serie, no solo la fecha de cierre, para determinar vencimientos.
- [ ] No se requiere migración de base de datos nueva.

## Fuera de alcance

- No incluye una API pública separada ni un endpoint en `routes/api.php`.
- No cambia el ciclo vital del expediente ni los permisos de baja existentes.
- No añade importación/exportación de Excel o PDF para el listado de vencidos.
- No modifica la jerarquía archivística ni introduce nuevos niveles de catálogo.
