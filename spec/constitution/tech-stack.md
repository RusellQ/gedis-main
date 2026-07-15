# Tech stack y convenciones

> Cómo está construido el proyecto y las reglas que todo el código debe respetar. Ningún plan de feature debería contradecir esto.

## Tecnologías

- **Backend:** PHP 8.1+ / Laravel 10 (`laravel/framework ^10.10`). App monolítica: rutas web + controladores + vistas Blade.
- **ORM / datos:** Eloquent. Conexión por defecto **MySQL** (`DB_CONNECTION=mysql`) para el dominio archivístico (expedientes, carátulas, catálogo, usuarios). **MongoDB** (`mongodb/laravel-mongodb` 4.0) solo para colecciones puntuales de estadísticas/recursos (`recursos_collection`, `desechados_collection` vía `DB::connection('mongodb')`).
- **PDF / Excel:** `barryvdh/laravel-dompdf` (instrumentos y reportes), `maatwebsite/excel` (exports).
- **HTTP client:** Guzzle 7 (incluido con Laravel).
- **Auth:** sesión web Laravel (`Auth::attempt` / middleware `auth`). Roles en JSON en `users.roles` (`root`, `administrador`, `editor`, `lector`) vía `User::hasRole()`. Sanctum presente pero `routes/api.php` casi vacío (solo `/api/user`).
- **Frontend:** Vue 3 + Vite 5 + plugin Vue, Bootstrap 5, Axios, SweetAlert2, Chart.js / vue-chartjs, jsPDF (+ autotable), html2canvas, bwip-js, Fancybox. Vuex está en dependencias; el patrón dominante es Options API + componentes globales (hay algunos `<script setup>`).
- **Tests:** PHPUnit 10 (`tests/Unit`, `tests/Feature`). Sin suite formal de frontend; validación típica: build Vite + pruebas manuales en UI.
- **Infra / despliegue:** sin Docker en el repo. Desarrollo local clásico: PHP artisan + Vite. Sail está en require-dev pero no es el flujo documentado aquí.
- **Colas / jobs:** config Laravel estándar; no hay jobs de dominio pesados como patrón actual (sin Hangfire ni equivalente).

## Archivos / módulos clave

- `routes/web.php` — rutas de negocio (vistas + JSON/POST). Casi toda la app vive aquí.
- `routes/api.php` — API mínima (Sanctum); no expandir como API pública sin feature dedicada.
- `app/Http/Controllers/` — controladores por dominio (`ExpedienteController`, `DocumentoController`/carátulas, `FondoController`, `CatalogoDocumentalController`, `CuadroGeneralController`, `ReporteController`, `UsersController`, …).
- `app/Models/` — entidades Eloquent (`Expediente`, `Caratula`, `Fondo`, `Subfondo`, `Seccion`, `Serie`, `Subserie`, `InstrumentoControl`, `UnidadDocumental`, `ExpedienteBaja`, `User`, …).
- `app/Services/` — lógica de dominio reutilizable (`InstrumentoControlService`, `ReglasDocumentalesService`).
- `app/Exports/` — exports Excel (p. ej. inventario).
- `database/migrations/` — migraciones Laravel (MySQL + stubs de colecciones).
- `resources/views/layouts/master.blade.php` — shell SB Admin, sidebar y montaje `#app`.
- `resources/views/administrativas/` — vistas Blade por módulo que montan componentes Vue.
- `resources/views/pdf/` — plantillas DomPDF (cuadro general, catálogos, expedientes).
- `resources/js/app.js` — registro global de componentes Vue (`*-vue`) y `app.mount('#app')`.
- `resources/js/admin/` — pantallas Vue por módulo (`CaratulaV2.vue`, `Tramite.vue`, `Fondos.vue`, …).
- `resources/js/components/` — piezas reutilizables (`ReglasDocumentalesPanel.vue`, `TreeNode.vue`, …).
- `resources/js/utils/` — utilidades JS (p. ej. reglas documentales).
- `public/assets/` — estáticos del tema/admin (incl. `gedis.png`).

## Comandos

- `composer install` — dependencias PHP.
- `cp .env.example .env` luego `php artisan key:generate` — bootstrap local (**crear/mantener `.env.example`** si falta).
- `php artisan migrate` — migraciones.
- `php artisan serve` — servidor HTTP local.
- `npm install` — dependencias JS.
- `npm run dev` — Vite en desarrollo (HMR).
- `npm run build` — build de assets a `public/build`.
- `php artisan test` / `./vendor/bin/phpunit` — tests PHP.
- `vendor/bin/pint` — estilo PHP (Laravel Pint), si se usa en la feature.

## Modelo de datos / dominio

- **Fuente de verdad del catálogo y expedientes:** MySQL (tablas Eloquent: `expedientes`, `caratulas`, `fondos`, `secciones`, `series`, `instrumentos_control`, `users`, …).
- **MongoDB:** uso acotado a estadísticas/recursos (`EstadisticasController` → `recursos_collection` / `desechados_collection`). No migrar el catálogo a Mongo “por defecto”.
- **Jerarquía:** Fondo → (Subfondo) → Sección → Serie → (Subserie) → Expediente. `InstrumentoControl` enlaza niveles (`tipo_nivel`, `codigo_clasificacion`, `id_padre`) y alimenta clasificación/reglas.
- **Ciclo vital del expediente:** `estado` ∈ `en_tramite` | `en_concentracion` | `en_historico`; bajas vía `ExpedienteBaja` / endpoints de baja.
- **Carátula:** ficha descriptiva del expediente (`Caratula` / `DocumentoController`), con reglas documentales derivadas de la serie (`ReglasDocumentalesService`).
- **Multitenancy suave:** `id_dependencia` + `id_departamento` en usuarios y en entidades de negocio; los listados filtran por pertenencia (salvo `root` donde aplique).
- **Roles:** array JSON en `users.roles` — `root`, `administrador`, `editor`, `lector`.

## Convenciones

- **Idioma:** UI y mensajes de cara al usuario en **español**. Sin i18n multi-idioma formal hoy; no introducir otro idioma sin feature dedicada.
- **Nombres PHP:** clases/métodos en PascalCase/camelCase Laravel; namespaces `App\…`. Controladores por entidad/dominio.
- **Nombres Vue:** archivos en PascalCase (`CaratulaV2.vue`); registro global kebab (`caratula-v2-vue`) en `app.js`. Preferir el estilo del módulo tocado (Options API o `<script setup>`), sin reescribir pantallas enteras solo por estilo.
- **Rutas:** nuevas pantallas = Blade en `resources/views/administrativas/` + ruta en `web.php` + componente en `resources/js/admin/` registrado en `app.js`. Endpoints AJAX en `web.php` junto al resto.
- **Servicios:** lógica de reglas/instrumentos en `app/Services/`, no duplicar en cada controlador.
- **Variables de entorno:** documentar claves nuevas en `.env.example` (nunca valores secretos).
- **Migraciones:** un cambio de esquema = migración nueva con nombre descriptivo. No editar migraciones ya aplicadas.
- **Tickets / ramas (si aplica):** alinear `spec/features/NNN-...` y nombre de rama con el ID del ticket.

## Límites duros

- **No subir secretos:** `.env`, credenciales reales ni dumps con datos sensibles al repo. Usar `.env.example`.
- **No tratar MongoDB como BD principal del catálogo:** el dominio archivístico es MySQL/Eloquent salvo decisión explícita en `plan.md`.
- **No aplanar ni saltar la jerarquía archivística** sin decisión en el plan.
- **No convertir la app en API pública** ampliando `routes/api.php` sin feature SDD dedicada.
- **No agregar dependencias** (Composer/npm) sin justificarlas en el `plan.md` de la feature.
- **No editar migraciones ya aplicadas:** crear una nueva.
- **No introducir otro framework UI / SPA router / ORM** (Pinia-only rewrite, Inertia, etc.) sin actualizar esta constitución.
- **Respetar alcance por dependencia/departamento** en consultas y altas.

## Estilo visual

- **UI:** Bootstrap 5 + tema SB Admin del layout (`master.blade.php`): sidebar, cards, forms, tablas.
- **Iconos:** Font Awesome (`fas fa-*`) en el shell Blade.
- **Feedback:** SweetAlert2 para confirmaciones/errores destacados; mensajes/alerts Bootstrap donde ya existan.
- **PDF:** plantillas Blade en `resources/views/pdf/` coherentes con los instrumentos oficiales.
- **Idioma de la UI:** español.
