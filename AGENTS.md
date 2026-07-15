# GEDIS — instrucciones para agentes

La carpeta `spec/` es la fuente de verdad de producto y técnica. Aplica en Cursor, VS Code (Copilot) y otros agentes.

## Antes de trabajar

Lee y respeta `spec/constitution/`:

- `mission.md` — qué construimos, para quién, qué NO es.
- `tech-stack.md` — stack, convenciones y límites duros.
- `roadmap.md` — qué está hecho y qué sigue.

La constitución manda: si una petición choca con ella, avísalo y replantea la feature, no la constitución.

## Feature nueva (cambio no trivial)

**Antes de tocar código**, usa el skill `/spec` (`.agents/skills/spec/SKILL.md`):

1. `spec/features/NNN-nombre/spec.md` — qué hace + criterios de aceptación medibles.
2. `plan.md` — enfoque técnico y decisiones (respetando `tech-stack.md`).
3. `tasks.md` — checklist accionable.
4. Implementar y validar; marcar tareas `[x]`.
5. Mover la feature a "Hecho" en `spec/constitution/roadmap.md`.

Bugs y chores pequeños se trabajan directo, sin carpeta de feature.

Si el usuario dice `/spec` o pide crear un spec: **no implementes código**; solo evalúa y genera (o actualiza) la carpeta de feature.

## Límites duros

- Stack: Laravel 10 / PHP 8.1, Vue 3 + Vite, Bootstrap 5. Catálogo/expedientes en **MySQL** (Eloquent); MongoDB solo para stats/recursos.
- No subir secretos (`.env`). Variables nuevas en `.env.example`.
- No editar migraciones ya aplicadas: crear una migración nueva.
- Jerarquía: Fondo → (Subfondo) → Sección → Serie → (Subserie) → Expediente. No aplanar ni saltar niveles sin decisión explícita en `plan.md`.
- Contenido de cara al usuario en **español**.
- Backend en `app/`; frontend admin en `resources/js/admin/`; rutas de negocio en `routes/web.php`.

## Skills del repo

| Skill | Cuándo |
|---|---|
| `.agents/skills/spec/` | `/spec`, feature nueva, cambio no trivial |
| `.agents/skills/git-commit/` | `/commit`, pedir commit |
