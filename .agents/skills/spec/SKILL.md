---
name: spec
description: >-
  Evalúa si una tarea de GEDIS necesita spec formal (SDD) y, si aplica, crea
  `spec/features/NNN-.../` con spec.md, plan.md y tasks.md. Usar cuando el
  usuario diga /spec, pida crear un spec, arranque una feature nueva, o un
  cambio no trivial antes de tocar código. NUNCA implementa código: solo
  evalúa y genera la carpeta de feature.
---

# Skill: /spec

Eres el guardián del proceso Spec Driven Development de este repositorio (GEDIS — gestión documental / catálogo archivístico AGQROO). Tu trabajo es evaluar si la tarea descrita necesita un spec formal antes de tocar código, y si lo necesita, crear los archivos correspondientes con la información suficiente para que cualquier desarrollador pueda implementarla sin ambigüedades.

## Prohibición dura

Mientras ejecutas este skill:

- **NO** edites ni crees código de aplicación (`app/`, `resources/js/`, `routes/`, migraciones, vistas, etc.).
- **NO** implementes la feature.
- Solo lee constitución / features existentes, pregunta lo faltante y (si aplica) escribe `spec/features/NNN-.../{spec,plan,tasks}.md` y actualiza `spec/constitution/roadmap.md`.

Si el usuario pidió `/spec` y también implementar: primero completa el spec; confirma; solo después pasa a implementar (fuera de este skill).

## Paso 1 — Leer el contexto del repo

Antes de evaluar nada, lee siempre estos archivos:

- `spec/constitution/mission.md`
- `spec/constitution/tech-stack.md`
- `spec/constitution/roadmap.md`

También lista `spec/features/` para identificar el número correlativo siguiente (el último NNN + 1).

## Paso 2 — Evaluar si se necesita spec

Clasifica la tarea en una de tres categorías:

### SÍ necesita spec

- Feature nueva (nuevo módulo o pantalla de expedientes/carátulas/archivo/catálogo/reportes/administración, o extensión relevante de uno existente).
- Cambio en la jerarquía archivística o en cómo se crean/relacionan Fondo → (Subfondo) → Sección → Serie → (Subserie) → Expediente.
- Cambio en schema MySQL (migraciones Laravel en `database/migrations/`) que añade o modifica tablas/columnas con lógica de negocio.
- Cambio en lógica de negocio crítica: ciclo vital del expediente (`en_tramite` / `en_concentracion` / `en_historico` / bajas), reglas documentales (`ReglasDocumentalesService`), instrumento de control, carátulas, o filtrado por dependencia/departamento/roles.
- Cambio en contratos de endpoints web/JSON que consume el frontend Vue (`routes/web.php`, controladores) con comportamiento observable distinto.
- Refactor que cambia interfaces públicas de controladores/servicios o el patrón Blade + componente Vue registrado en `resources/js/app.js`.
- Cambio que toca más de 4 archivos distintos o se estima en más de 4 horas de trabajo.

### NO necesita spec (pero sí menciona brevemente el razonamiento)

- Bug fix de una línea, ajuste de configuración o corrección de typo.
- Ajuste puramente visual en Vue/Blade (clases Bootstrap, textos en español, iconos Font Awesome) sin lógica nueva.
- Hotfix urgente (se documenta después en un spec de cierre si afectó lógica importante).
- Cambio de variables de entorno (`.env.example`) o configuración local de despliegue.
- Refactor de naming puro sin cambio de comportamiento.

### EVALUAR — preguntar primero

- Bug fix con lógica compleja o que toca varios controladores/servicios/módulos a la vez.
- Refactor que podría cambiar comportamiento observable (cambio de estado de expediente, reglas documentales, permisos por rol, alcance por dependencia).
- Cambio de performance que modifica queries Eloquent/MySQL o el uso puntual de MongoDB (stats).
- Cualquier cambio cerca de los límites duros: jerarquía archivística, migraciones ya aplicadas, secretos/env, o alcance por dependencia/departamento.

Para los casos de EVALUAR, haz una o dos preguntas concretas al desarrollador para decidir.

## Paso 3 — Si NO necesita spec

Explica en 2-3 líneas por qué no aplica spec y qué recomiendas hacer en su lugar (ir directo al código, crear un comentario en el PR, etc.). Fin del skill.

## Paso 4 — Si SÍ necesita spec: recopilar información

Antes de crear los archivos, necesitas tener respuesta clara a estas preguntas. Si el desarrollador ya las respondió en su descripción inicial o en un ticket, no las repitas. Solo pregunta lo que falta.

**Preguntas base (siempre verificar que estén respondidas):**

1. **¿Qué hace exactamente esta feature?** — Comportamiento observable desde el punto de vista de quien la consume. Sin detalles técnicos todavía.
2. **¿Por qué se hace ahora?** — Qué problema resuelve o qué valor aporta.
3. **¿Quién la usa?** — Personal operativo AGQROO, administradores, control archivístico, o equipo técnico.
4. **¿Cuáles son los criterios de aceptación?** — Condiciones verificables con sí/no. Al menos 2-3.
5. **¿Qué queda fuera de alcance?** — Qué cosas relacionadas NO entran en esta feature.

**Preguntas técnicas (preguntar solo si no son obvias del contexto):**

6. **¿Qué módulo se toca?** — Expedientes, Carátulas, Archivo, Catálogo, Instrumentos, Reportes, Administración, Estadísticas/Mongo, u otro.
7. **¿Hay cambios en MySQL (migraciones Laravel)?**
8. **¿Afecta datos o flujos que ya corren en producción o es función nueva?**
9. **¿Toca la jerarquía archivística o el instrumento de control?**
10. **¿Interactúa con roles o alcance por dependencia/departamento?**
11. **¿Hay algún riesgo o deuda técnica conocida?** — Revisar backlog de `roadmap.md`.

Agrupa las preguntas pendientes en un solo mensaje. Si la tarea viene de un ticket, alinea `NNN` y el slug con el ID del ticket.

## Paso 5 — Verificar coherencia con la constitution

Antes de crear los archivos, verifica que la feature encaja con `mission.md`, respeta `tech-stack.md` y es coherente con `roadmap.md`. Si hay conflicto, comunícalo y espera respuesta antes de crear archivos.

## Paso 6 — Determinar el número de feature

Lista los directorios en `spec/features/` y toma el número más alto (ignora la carpeta plantilla `NNN-nombre-feature`). El nuevo será ese número + 1, con padding de 3 dígitos.

Nombre de carpeta en kebab-case. Con ticket: `NNN-<ticket>-slug`; sin ticket: `NNN-slug`.

## Paso 7 — Crear los archivos

Crea `spec/features/NNN-nombre-feature/` con `spec.md`, `plan.md` y `tasks.md`.

Usa como plantilla los archivos en `spec/features/NNN-nombre-feature/`. Rellena el contenido real; no dejes placeholders. Estructura detallada también en `.claude/commands/spec.md` (misma guía).

Tras crear los archivos, añade la feature en **Siguiente** de `spec/constitution/roadmap.md` (una sola "en curso" a la vez).

## Paso 8 — Confirmar y orientar

Después de crear los archivos, muestra:

1. Las rutas de los tres archivos creados.
2. Un resumen en 3-4 líneas de lo que capturó el spec.
3. Si hay algo con poca información que conviene revisar.
4. El siguiente paso: "Puedes empezar con la tarea 1 de `tasks.md`."

## Regla de oro

Si tienes dudas sobre si necesita spec o no: **¿puede otra persona implementar esto correctamente sin hacer preguntas?** Si la respuesta es no, necesita spec.
