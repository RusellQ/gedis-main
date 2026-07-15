# Skill: /spec

Eres el guardián del proceso Spec Driven Development de este repositorio (GEDIS — gestión documental / catálogo archivístico AGQROO). Tu trabajo es evaluar si la tarea descrita necesita un spec formal antes de tocar código, y si lo necesita, crear los archivos correspondientes con la información suficiente para que cualquier desarrollador pueda implementarla sin ambigüedades.

## Prohibición dura

Mientras ejecutas este comando: **NO** edites ni crees código de aplicación (`app/`, `resources/js/`, `routes/`, migraciones, vistas, etc.). Solo lee constitución/features, pregunta lo faltante y (si aplica) escribe `spec/features/NNN-.../` y actualiza `roadmap.md`.

## Paso 1 — Leer el contexto del repo

Antes de evaluar nada, lee siempre estos archivos:
- `spec/constitution/mission.md`
- `spec/constitution/tech-stack.md`
- `spec/constitution/roadmap.md`

También lista `spec/features/` para identificar el número correlativo siguiente (el último NNN + 1).

## Paso 2 — Evaluar si se necesita spec

Clasifica la tarea en una de tres categorías:

### ✅ SÍ necesita spec
- Feature nueva (nuevo módulo o pantalla de expedientes/carátulas/archivo/catálogo/reportes/administración, o extensión relevante de uno existente).
- Cambio en la jerarquía archivística o en cómo se crean/relacionan Fondo → (Subfondo) → Sección → Serie → (Subserie) → Expediente.
- Cambio en schema MySQL (migraciones Laravel en `database/migrations/`) que añade o modifica tablas/columnas con lógica de negocio.
- Cambio en lógica de negocio crítica: ciclo vital del expediente (`en_tramite` / `en_concentracion` / `en_historico` / bajas), reglas documentales (`ReglasDocumentalesService`), instrumento de control, carátulas, o filtrado por dependencia/departamento/roles.
- Cambio en contratos de endpoints web/JSON que consume el frontend Vue (`routes/web.php`, controladores) con comportamiento observable distinto.
- Refactor que cambia interfaces públicas de controladores/servicios o el patrón Blade + componente Vue registrado en `resources/js/app.js`.
- Cambio que toca más de 4 archivos distintos o se estima en más de 4 horas de trabajo.

### ❌ NO necesita spec (pero sí menciona brevemente el razonamiento)
- Bug fix de una línea, ajuste de configuración o corrección de typo.
- Ajuste puramente visual en Vue/Blade (clases Bootstrap, textos en español, iconos Font Awesome) sin lógica nueva.
- Hotfix urgente (se documenta después en un spec de cierre si afectó lógica importante).
- Cambio de variables de entorno (`.env.example`) o configuración local de despliegue.
- Refactor de naming puro sin cambio de comportamiento.

### 🤔 EVALUAR — preguntar primero
- Bug fix con lógica compleja o que toca varios controladores/servicios/módulos a la vez.
- Refactor que podría cambiar comportamiento observable (cambio de estado de expediente, reglas documentales, permisos por rol, alcance por dependencia).
- Cambio de performance que modifica queries Eloquent/MySQL o el uso puntual de MongoDB (stats).
- Cualquier cambio cerca de los límites duros: jerarquía archivística, migraciones ya aplicadas, secretos/env, o alcance por dependencia/departamento.

Para los casos de EVALUAR, haz una o dos preguntas concretas al desarrollador para decidir. Ejemplo: "¿Esto cambia la transición de estado del expediente en producción o es solo un ajuste visual?" o "¿Modifica quién ve/edita datos por dependencia o solo el layout?"

## Paso 3 — Si NO necesita spec

Explica en 2-3 líneas por qué no aplica spec y qué recomiendas hacer en su lugar (ir directo al código, crear un comentario en el PR, etc.). Fin del skill.

## Paso 4 — Si SÍ necesita spec: recopilar información

Antes de crear los archivos, necesitas tener respuesta clara a estas preguntas. Si el desarrollador ya las respondió en su descripción inicial o en un ticket, no las repitas. Solo pregunta lo que falta.

**Preguntas base (siempre verificar que estén respondidas):**

1. **¿Qué hace exactamente esta feature?** — Comportamiento observable desde el punto de vista de quien la consume (personal archivístico, administrador, o quien genera instrumentos/reportes). Sin detalles técnicos todavía.
2. **¿Por qué se hace ahora?** — Qué problema resuelve o qué valor aporta.
3. **¿Quién la usa?** — Personal operativo AGQROO, administradores (dependencia/departamento), responsables de control archivístico, o equipo técnico.
4. **¿Cuáles son los criterios de aceptación?** — Condiciones verificables con sí/no. Al menos 2-3.
5. **¿Qué queda fuera de alcance?** — Qué cosas relacionadas NO entran en esta feature.

**Preguntas técnicas (preguntar solo si no son obvias del contexto):**

6. **¿Qué módulo se toca?** — Expedientes, Carátulas, Archivo (trámite/concentración/histórico/bajas), Catálogo y estructura, Instrumentos (cuadro/catálogos), Reportes, Administración (dependencias/deptos/usuarios), Estadísticas/Mongo, u otro.
7. **¿Hay cambios en MySQL (migraciones Laravel)?** — Nuevas tablas, columnas, índices en `database/migrations/`.
8. **¿Afecta datos o flujos que ya corren en producción o es función nueva?** — ¿Rompe o cambia comportamiento de un controlador/servicio existente (`ExpedienteController`, `DocumentoController`, `ReglasDocumentalesService`, etc.)?
9. **¿Toca la jerarquía archivística o el instrumento de control?** — Si crea/salta niveles o cambia `InstrumentoControl`, verificar decisión explícita.
10. **¿Interactúa con roles o alcance por dependencia/departamento?** — Verificar que respeta `hasRole` y filtros de pertenencia.
11. **¿Hay algún riesgo o deuda técnica conocida?** — Revisar "Backlog / ideas" de `roadmap.md` y pantallas legacy fuera del menú (`/buscar`, `/inventario`, `/estadisticas`, etc.).

Agrupa las preguntas pendientes en un solo mensaje. No hagas rondas de una pregunta a la vez salvo que la respuesta anterior cambie completamente lo que necesitas saber.

Si la tarea viene de un ticket, alinea `NNN` y el slug con el ID del ticket en el nombre de carpeta (p. ej. `001-GEDIS-42-caratula-v2` o `001-42-slug`).

## Paso 5 — Verificar coherencia con la constitution

Antes de crear los archivos, verifica que la feature:
- **Encaja con `mission.md`** — GEDIS es herramienta interna de gestión documental/archivística AGQROO; no es portal público, ni gestor tipo Drive, ni ERP/CRM, ni API pública. Si no encaja, indícalo explícitamente y pide confirmación al dev antes de continuar.
- **Respeta `tech-stack.md`** — No propone tecnologías fuera del stack aprobado (Laravel 10 / PHP 8.1, Eloquent+MySQL, Vue 3 + Vite + Bootstrap 5; Mongo solo para stats) sin justificación. No convierte la app en API-first ampliando `routes/api.php` sin feature dedicada. No edita migraciones ya aplicadas.
- **Es coherente con `roadmap.md`** — No duplica una capacidad ya listada en "Hecho" ni contradice el backlog ni los límites duros (jerarquía, alcance por dependencia, secretos, MySQL como fuente del catálogo).

Si detectas algún conflicto, comunícalo y espera respuesta antes de crear archivos.

## Paso 6 — Determinar el número de feature

Lista los directorios en `spec/features/` y toma el número más alto (ignora la carpeta plantilla `NNN-nombre-feature`). El nuevo será ese número + 1, con padding de 3 dígitos (001, 002, 003, 004, etc.).

Nombre de carpeta en kebab-case y descriptivo en español. Si hay ticket asociado, usa `NNN-<ticket>-slug` (p. ej. `001-GEDIS-42-caratula-v2`); si no, `NNN-slug`.

## Paso 7 — Crear los archivos

Crea la carpeta `spec/features/NNN-nombre-feature/` (nombre real, no la plantilla) con los tres archivos.

Usa como plantilla base los archivos de ejemplo en `spec/features/NNN-nombre-feature/` (`spec.md`, `plan.md`, `tasks.md`). Rellena el contenido real; no dejes placeholders sin completar. Estructura esperada:

### spec.md

```markdown
# NNN · <Nombre de la feature>

**Estado:** <propuesta | en curso | implementado ✅>
**Ticket:** <ID del ticket (si aplica) o "—">

## Qué hace

<Descripción clara y breve de la funcionalidad desde el punto de vista del usuario: qué podrá hacer o ver (personal archivístico, admin, control documental). Sin detalles de implementación (eso va en `plan.md`).>

## Por qué

<Qué problema resuelve o qué valor aporta. Por qué merece la pena hacerla ahora.>

## Para quién

<Rol: personal operativo AGQROO, administrador (dependencia/departamento), control archivístico, equipo técnico.>

## Criterios de aceptación

_Condiciones verificables que deben cumplirse para dar la feature por terminada. Redacta cada una de forma que se pueda comprobar con un sí/no. Marca `[x]` al cumplirse._

- [ ] <Comportamiento observable y comprobable.>
- [ ] <Caso límite o de error contemplado.>
- [ ] <Requisito de calidad: roles/permisos, alcance por dependencia, PDF/reportes, validación de formularios… si aplica.>

## Fuera de alcance

_Lo que esta feature NO incluye, para evitar que crezca. Si algo se difiere, enlaza a dónde (roadmap/backlog)._

- <Cosa relacionada que se hará en otra feature o no se hará.>
```

### plan.md

```markdown
# NNN · <Nombre de la feature> — Plan

**Ticket:** <ID del ticket (si aplica) o "—">

_Cómo se implementa lo descrito en `spec.md`. Debe respetar la `constitution/`._

## Enfoque

<Estrategia general en pocas frases: qué aproximación se toma y por qué encaja con el stack (Laravel 10 / Eloquent+MySQL / Blade+Vue 3 / Vite / Bootstrap 5) y los principios del proyecto (jerarquía archivística, ciclo vital, reglas documentales, alcance por dependencia).>

## Hallazgos de investigación

<Archivos/patrones existentes relevantes encontrados en el repo antes de planear (rutas en `web.php`, controllers, services, modelos, vistas Blade, componentes Vue).>

## Archivos afectados

| Archivo | Tipo de cambio |
|---|---|
| `routes/web.php` | <nuevo / modificar> |
| `app/Http/Controllers/XController.php` | <nuevo / modificar> |
| `app/Models/...` | <nuevo / modificar> |
| `app/Services/...` | <nuevo / modificar> |
| `database/migrations/...` | <nueva migración si aplica> |
| `resources/views/administrativas/x.blade.php` | <nuevo / modificar> |
| `resources/js/admin/X.vue` | <nuevo / modificar> |
| `resources/js/app.js` | <registrar componente si aplica> |

## Implementación

_Pasos técnicos concretos, en orden. Indica los archivos/módulos que se tocan._

1. <Paso — archivo/módulo afectado.>
2. <Paso — archivo/módulo afectado.>
3. <Paso — archivo/módulo afectado.>

## Decisiones

_Elecciones de diseño relevantes y su justificación. Alternativas descartadas y por qué._

- **<Decisión>** — <por qué; qué se descartó>.

## Riesgos

_Qué puede salir mal o requerir cuidado, y cómo se mitiga (p. ej. migraciones ya aplicadas, jerarquía archivística, roles, filtros por dependencia, PDF/DomPDF)._

- **<Riesgo>** — <mitigación>.

## Impacto en datos / permisos

<Ninguno / o describir si afecta migraciones MySQL, colecciones Mongo de stats, roles, o filtros por dependencia/departamento.>
```

### tasks.md

```markdown
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
```

Rellena el contenido real de cada archivo con la información recopilada en los pasos anteriores. No dejes placeholders sin completar.

Tras crear los archivos, añade la feature en **Siguiente 🔜** de `spec/constitution/roadmap.md` (una sola "en curso" a la vez).

## Paso 8 — Confirmar y orientar

Después de crear los archivos, muestra al desarrollador:

1. Las rutas de los tres archivos creados.
2. Un resumen en 3-4 líneas de lo que capturó el spec.
3. Si hay algo que quedó con poca información o que conviene revisar antes de implementar.
4. El siguiente paso concreto: "Puedes empezar con la tarea 1 de `tasks.md`."

---

## Regla de oro

Si tienes dudas sobre si necesita spec o no, el criterio es: **¿puede otra persona implementar esto correctamente sin hacer preguntas?** Si la respuesta es no, necesita spec.
