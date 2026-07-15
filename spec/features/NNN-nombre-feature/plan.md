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
