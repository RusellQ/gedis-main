# 004 · Módulo de Correspondencia — Checklist de Implementación

> Tareas accionables, en orden recomendado. Marcar con `[x]` al completar.

## Fase 1: Base de datos y modelos

- [x] **1.1** Crear script SQL en `database/sql/004-correspondencias.sql` con tablas `correspondencias` y `correspondencia_firmas`.
- [x] **1.2** Ejecutar script manualmente en BD (no usar migraciones Laravel).
- [x] **1.3** Crear modelo `app/Models/Correspondencia.php` con relaciones a `Expediente`, `Usuario`, `CorrespondenciaFirma`.
- [x] **1.4** Crear modelo `app/Models/CorrespondenciaFirma.php` con relación a `Correspondencia`.
- [x] **1.5** Validar migraciones con `php artisan migrate:status` (no deben aparecer nuevas pendientes).

## Fase 2: Backend — Controlador y Servicios

- [x] **2.1** Crear `app/Http/Controllers/CorrespondenciaController.php` con acciones: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`, `firmar`.
- [x] **2.2** Implementar validación de permisos (solo `administrador`, `editor`, `root`; filtro por dependencia/departamento).
- [x] **2.3** Crear `app/Services/SATSigningService.php` con método `sign($correspondencia)` para invocar API SAT.
- [x] **2.4** Documentar variables de `.env` en `.env.example`: `SAT_SIGNING_ENDPOINT`, `SAT_SIGNING_API_KEY`, `SAT_SIGNING_CERT_PATH`.
- [x] **2.5** Endpoints POST/PUT/DELETE validan que solo `borrador` se puede modificar; `firmado` es readonly.
- [x] **2.6** Endpoint `firmar` valida estado actual, invoca `SATSigningService`, guarda firma en `correspondencia_firmas`.

## Fase 3: Rutas web

- [x] **3.1** Agregar rutas en `routes/web.php`:
  - `GET /correspondencia` → `CorrespondenciaController@index`
  - `POST /correspondencia` → `CorrespondenciaController@store`
  - `GET /correspondencia/{id}` → `CorrespondenciaController@show`
  - `PUT /correspondencia/{id}` → `CorrespondenciaController@update`
  - `DELETE /correspondencia/{id}` → `CorrespondenciaController@destroy`
  - `POST /correspondencia/{id}/firmar` → `CorrespondenciaController@firmar`
- [x] **3.2** Proteger todas las rutas con middleware `auth` + `can:` si aplica política.

## Fase 4: Frontend — Instalación de dependencias

- [x] **4.1** Instalar TinyMCE: `npm install tinymce @tinymce/tinymce-vue`.
- [x] **4.2** Confirmar en `package.json` que se agregó `tinymce ^6.7.0` (o compatible).
- [x] **4.3** Ejecutar `npm install` y build: `npm run dev` (verificar que TinyMCE se carga).

## Fase 5: Frontend — Componentes Vue

- [x] **5.1** Crear `resources/js/admin/Correspondencia.vue`:
  - Selector de expediente (buscar por fondo/sección/serie).
  - Tabla de correspondencias del expediente seleccionado.
  - Botones: crear, editar, eliminar, descargar PDF.
  - Integración con API backend (`axios.get/post/put/delete`).

- [x] **5.2** Crear `resources/js/admin/CorrespondenciaEditor.vue`:
  - Campos: remitente, destinatario, asunto, clasificación.
  - Editor TinyMCE embebido (textarea → TinyMCE).
  - Preview de documento (renderizar HTML contenido).
  - Botón "Guardar como borrador" → POST `/correspondencia` o PUT `/correspondencia/{id}`.
  - Botón "Firmar" → POST `/correspondencia/{id}/firmar` + modal de confirmación.

- [x] **5.3** Crear `resources/js/admin/CorrespondenciaExplorer.vue` (tab dentro de expediente):
  - Listar documentos de correspondencia del expediente.
  - Opción para crear nuevo (abrir `CorrespondenciaEditor`).
  - Listar unidades documentales (PDFs) asociados al expediente (reutilizar logica existente).
  - Botón para subir nuevos PDFs como unidades (reutilizar flujo actual).

- [x] **5.4** Registrar componentes en `resources/js/app.js`:
  ```javascript
  app.component('correspondencia-vue', Correspondencia);
  app.component('correspondencia-editor-vue', CorrespondenciaEditor);
  app.component('correspondencia-explorer-vue', CorrespondenciaExplorer);
  ```

## Fase 6: Frontend — Vista Blade

- [x] **6.1** Crear `resources/views/administrativas/correspondencia.blade.php`:
  - Blade shell estándar (SB Admin layout).
  - Montaje de componente Vue: `<div id="app"><correspondencia-vue></correspondencia-vue></div>`.

- [x] **6.2** Crear `resources/views/administrativas/correspondencia-editor.blade.php` (si se abre en página separada).
  - Shell + montaje `<correspondencia-editor-vue>`.

## Fase 7: Rutas de acceso a Blade

- [x] **7.1** Agregar rutas en `routes/web.php`:
  - `GET /correspondencia` → vista `administrativas/correspondencia.blade.php` (página principal).
  - `GET /correspondencia/crear` → vista `administrativas/correspondencia-editor.blade.php` (crear nuevo).
  - `GET /correspondencia/{id}/editar` → vista con datos del documento.

- [x] **7.2** Middleware de autenticación en rutas (verificar permisos en controller).

## Fase 8: Integración de Unidades Documentales

- [x] **8.1** Revisar `ArchExpedientesController::subirUnidad` (flujo actual de upload PDF).
- [x] **8.2** Verificar que unidades se suben a `unidades_documentales` con FK `id_expediente`.
- [x] **8.3** Integrar botón "Adjuntar PDF" en `CorrespondenciaExplorer.vue` → reutilizar endpoint POST `/expedientes/{id}/unidades-documentales`.

- [x] **8.4** Guardar referencia en `correspondencia_anexos` si se necesita relación explícita (Fase 1.5 futura).

## Fase 9: API SAT — Integración y Sandbox

- [x] **9.1** Obtener credenciales SAT (endpoint, API key, certificado).
- [x] **9.2** Documentar en `.env.example`.
- [x] **9.3** Implementar mock/sandbox en `SATSigningService` para desarrollo (respuesta hardcoded con firma válida).
- [x] **9.4** Prueba manual: crear documento → firmar → verificar `correspondencia_firmas` con `estado_firma = 'exitosa'`.

## Fase 10: Seguridad y Auditoría

- [x] **10.1** Implementar policy/gate Laravel para autorización (solo propietario de expediente o admin puede editar correspondencia).
- [x] **10.2** Validar que documento `firmado` no se puede modificar (readonly en API).
- [x] **10.3** Crear auditoría: registrar acceso/descarga de documentos sensibles (opcional: tabla `correspondencia_auditorias`).
- [x] **10.4** Cifrar certificado SAT en `correspondencia_firmas` usando `Illuminate\Support\Facades\Crypt::encrypt()`.

## Fase 11: Generación de PDF

- [x] **11.1** Crear vista Blade `resources/views/pdf/correspondencia.blade.php`:
  - Renderizar contenido HTML del documento.
  - Incluir metadatos: remitente, destinatario, fecha, firma/timestamp.
  - Logo AGQROO.

- [x] **11.2** Endpoint GET `/correspondencia/{id}/pdf` en controller → `response()->streamDownload()` con `barryvdh/laravel-dompdf`.

- [x] **11.3** Botón "Descargar PDF" en `Correspondencia.vue` → GET `/correspondencia/{id}/pdf`.

## Fase 12: Pruebas

- [x] **12.1** Tests unitarios para `Correspondencia` (validaciones de estado, permisos).
- [x] **12.2** Tests Feature para controller (crear, editar, firmar, verificar permisos).
- [x] **12.3** Tests manuales en UI:
  - Crear expediente → crear documento → editar → guardar borrador → firmar → verificar readonly.
  - Adjuntar PDF → verificar en tabla de unidades.
  - Descargar PDF → verificar contenido.

- [x] **12.4** Prueba con API SAT sandbox (si disponible).

## Fase 13: Documentación y Limpieza

- [x] **13.1** Documentar en `README.md`:
  - Cómo crear tablas de correspondencia (`database/sql/004-correspondencias.sql`).
  - Variables de entorno SAT.
  - Flujo de uso: crear expediente → crear correspondencia → firmar → descargar.

- [x] **13.2** Actualizar `AGQROO.md` o documentación de módulos con screenshot/descripción.

- [x] **13.3** Validar que no haya referencias `console.log`, `dd()`, variables no usadas.

- [x] **13.4** Confirmar build Vite sin errores: `npm run build`.

## Fase 14: Actualizar Roadmap

- [x] **14.1** Actualizar `spec/constitution/roadmap.md`: mover feature 004 de "Siguiente" a "Hecho" con ✅.

---

## Estimación de esfuerzo

| Fase | Horas estimadas | Notas |
|------|---|---|
| 1-2 (BD + modelos + controller) | 4-5h | Incluye SATSigningService mock |
| 3-4 (Rutas + dependencias npm) | 1-2h | Directo |
| 5-6 (Componentes Vue + Blade) | 6-8h | TinyMCE + preview + modal de firma |
| 7-8 (Rutas + unidades documentales) | 2-3h | Reutilización de lógica existente |
| 9-11 (API SAT + PDF + seguridad) | 4-6h | Depende de complejidad SAT + integración |
| 12-14 (Tests + docs + roadmap) | 3-4h | Incluye validación manual |
| **Total** | **20-30h** | MVP funcional sin workflow avanzado |

---

## Riesgos y notas

- **SAT API:** validar horarios de disponibilidad y latencia de respuesta. Si es lenta, considerar async.
- **TinyMCE:** puede ser pesado en build; considerar lazy loading si es necesario.
- **PDFs:** si los documentos de correspondencia incluyen imágenes pesadas, optimizar compresión.
- **Permisos:** revisar alcance por dependencia/departamento en consultas Eloquent; evitar SQL injection.
