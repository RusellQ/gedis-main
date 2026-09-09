# 004 · Módulo de Correspondencia — Plan Técnico

**Cómo se implementa lo descrito en `spec.md`. Debe respetar la `constitution/`.** 

## Enfoque

Crear un **módulo autónomo de Correspondencia** que:

1. Reutiliza expedientes ordinarios (tabla `expedientes` existente).
2. Introduce tabla `correspondencias` para documentos firmables con contenido HTML.
3. Introduce tabla `correspondencia_firmas` para guardar firmas SAT (certificado, timestamp, estado).
4. Reutiliza `unidades_documentales` para anexos PDF (lógica existente).
5. Integra editor WYSIWYG (TinyMCE) en frontend Vue.
6. Expone endpoints REST para firma SAT (cliente interno → API SAT externa).

**Sin migraciones Laravel.** Crearemos las tablas directamente en MySQL (script SQL + referencia en `.env.example`).

## Stack de tecnologías

- **Backend:** Laravel 10 / Eloquent; servicios de dominio.
- **BD:** MySQL 8.0+ (tablas nuevas: `correspondencias`, `correspondencia_firmas`).
- **Frontend:** Vue 3 + Vite; editor WYSIWYG (TinyMCE 6).
- **PDF/Firma:** TinyMCE → HTML → XML/XDP (preparación); invocación de API SAT externa.
- **Autenticación:** sesión Laravel existente + roles (`root`, `administrador`, `editor`, `lector`).
- **Alcance:** por `id_dependencia` / `id_departamento` del usuario.

## Hallazgos de investigación

### Tablas y modelos existentes

- `expedientes` — núcleo; cada correspondencia pertenece a un expediente único (FK `id_expediente`).
- `unidades_documentales` — PDFs asociados; reutilizaremos para anexos de correspondencia.
- `caratulas` — ficha descriptiva del expediente; no se modifica, solo se complementa con correspondencias.
- `usuarios` — autenticación; `roles` (JSON) ya soporta `root`, `administrador`, `editor`, `lector`.
- `series`, `secciones`, `fondos` — contexto de clasificación; consulta al crear correspondencia.

### Flujo de datos actual

- **Expediente:** se crea en `/admin/tramite` (`Tramite.vue`) o `/fondos` (`Fondos.vue`).
- **Carátula:** se edita en `DocumentoController` (POST `/caratula`, `/caratula-v2`).
- **Unidades:** se suben en `ArchExpedientesController` (POST `/expedientes/{id}/unidades-documentales`).

### Cambios mínimos requeridos

Nuevo módulo **sin tocar** expedientes, carátulas ni unidades existentes:

| Archivo | Cambio |
|---------|--------|
| `routes/web.php` | Agregar rutas `/correspondencia` (listar, crear, editar, eliminar, firmar) |
| `app/Http/Controllers/CorrespondenciaController.php` | **Nuevo** |
| `app/Models/Correspondencia.php` | **Nuevo** |
| `app/Models/CorrespondenciaFirma.php` | **Nuevo** |
| `app/Services/SATSigningService.php` | **Nuevo** — integración con API SAT |
| `resources/views/administrativas/correspondencia.blade.php` | **Nuevo** — shell Blade |
| `resources/js/admin/Correspondencia.vue` | **Nuevo** — UI principal |
| `resources/js/admin/CorrespondenciaEditor.vue` | **Nuevo** — editor WYSIWYG |
| `resources/js/app.js` | Registrar nuevos componentes Vue |
| `database/` (SQL script) | **Nuevo** — crear tablas sin migración |
| `.env.example` | Documentar variables de API SAT (si aplica) |

## Diseño de tablas MySQL

### `correspondencias`

```sql
CREATE TABLE correspondencias (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_expediente BIGINT UNSIGNED NOT NULL,
  numero_consecutivo INT DEFAULT 1,
  id_usuario_autor BIGINT UNSIGNED NOT NULL,
  remitente VARCHAR(255),
  destinatario VARCHAR(255),
  asunto TEXT NOT NULL,
  contenido LONGTEXT NOT NULL,  -- HTML del editor
  clasificacion ENUM('publica', 'reservada', 'confidencial') DEFAULT 'publica',
  estado ENUM('borrador', 'firmado', 'cancelado') DEFAULT 'borrador',
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  
  FOREIGN KEY (id_expediente) REFERENCES expedientes(id) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario_autor) REFERENCES usuarios(id),
  INDEX (id_expediente),
  INDEX (estado),
  INDEX (fecha_creacion)
);
```

### `correspondencia_firmas`

```sql
CREATE TABLE correspondencia_firmas (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_correspondencia BIGINT UNSIGNED NOT NULL UNIQUE,
  id_usuario_firmante BIGINT UNSIGNED NOT NULL,
  certificado_sat TEXT,  -- Base64 o referencia del certificado SAT
  timestamp_firma TIMESTAMP,  -- Hora de firma según SAT
  estado_firma ENUM('pendiente', 'exitosa', 'rechazada') DEFAULT 'pendiente',
  respuesta_sat TEXT,  -- JSON de respuesta de API SAT
  fecha_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (id_correspondencia) REFERENCES correspondencias(id) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario_firmante) REFERENCES usuarios(id),
  INDEX (id_correspondencia),
  INDEX (estado_firma)
);
```

### Relación con unidades documentales

Los PDFs anexados al expediente **ya existen** en `unidades_documentales` (FK `id_expediente`). Una correspondencia **no requiere tabla intermedia** nueva; los PDFs simplemente viven en el expediente. Si hubiera relación 1:N explícita correspondencia→PDFs, agregamos:

```sql
CREATE TABLE correspondencia_anexos (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_correspondencia BIGINT UNSIGNED NOT NULL,
  id_unidad_documental BIGINT UNSIGNED NOT NULL,
  orden INT DEFAULT 0,
  
  FOREIGN KEY (id_correspondencia) REFERENCES correspondencias(id) ON DELETE CASCADE,
  FOREIGN KEY (id_unidad_documental) REFERENCES unidades_documentales(id),
  UNIQUE KEY (id_correspondencia, id_unidad_documental)
);
```

**Decisión actual:** empezar sin `correspondencia_anexos` (unidades se adjuntan al expediente, punto); si se necesita relación explícita, crearla después.

## Implementación por capas

### 1. Backend — Controlador y Servicios

**`CorrespondenciaController`** (CRUD + firma):
- `GET /correspondencia` — listar por expediente/dependencia.
- `POST /correspondencia` — crear (con validación de permisos/dependencia).
- `GET /correspondencia/{id}` — detalle.
- `PUT /correspondencia/{id}` — editar (solo en estado `borrador`).
- `DELETE /correspondencia/{id}` — eliminar lógica.
- `POST /correspondencia/{id}/firmar` — invocar SAT, cambiar estado a `firmado`.

**`SATSigningService`** — wrapper de integración:
- Recibe documento HTML + metadatos.
- Convierte a XDP (XML Digital Properties) si aplica.
- Invoca API SAT (endpoint + credenciales en `.env`).
- Guarda firma en `correspondencia_firmas`.
- Retorna estado (`exitosa` / `rechazada`).

**Modelos Eloquent:**
- `Correspondencia` — atributos, relaciones a `Expediente` y `Usuario`.
- `CorrespondenciaFirma` — firma asociada.

### 2. Frontend — Componentes Vue

**`Correspondencia.vue`** (pantalla principal):
- Selector de expediente (buscar/filtrar por catálogo).
- Tabla de correspondencias del expediente.
- Botones: crear, editar, eliminar, descargar (PDF).

**`CorrespondenciaEditor.vue`** (modal / página):
- Campos: remitente, destinatario, asunto, clasificación.
- Editor TinyMCE (HTML con imágenes, tablas).
- Preview de documento.
- Botón "Guardar como borrador" y "Firmar electrónicamente".
- Modal de confirmación de firma (llamada a SAT).

**`CorrespondenciaExplorer.vue`** (tab dentro de expediente):
- Mostrar documentos del expediente; permitir crear/editar/firmar.
- Listar unidades documentales (PDFs) del expediente (reutilizar lógica actual).

### 3. Dependencia de TinyMCE

Agregar a `package.json`:
```json
"tinymce": "^6.7.0"
```

Registrar en `resources/js/app.js` (plugin global) o usarlo directamente en componente.

### 4. Integración SAT

**Flujo:**
1. Usuario edita documento en editor TinyMCE.
2. Guarda como borrador.
3. Usuario hace clic "Firmar".
4. Frontend POST `/correspondencia/{id}/firmar`.
5. Backend invoca `SATSigningService`.
6. SAT Service:
   - Prepara contenido HTML → XML/XDP.
   - Llamada HTTP a endpoint SAT (incluir credenciales de `.env`).
   - Recibe certificado + timestamp + firma.
   - Guarda en `correspondencia_firmas` con estado `exitosa`.
7. Frontend recibe respuesta; muestra confirmación.

**Variables de entorno (`.env.example`):**
```
SAT_SIGNING_ENDPOINT=https://api.sat.gob.mx/firmar
SAT_SIGNING_API_KEY=your_api_key
SAT_SIGNING_CERT_PATH=/path/to/cert.pem
```

### 5. Seguridad

- **Validación de permisos:** endpoint verifica `Auth::user()->hasRole(['root', 'administrador', 'editor'])`.
- **Filtro por dependencia:** solo expedientes de la dependencia/departamento del usuario (excepto `root`).
- **No modificar si está firmado:** estado `firmado` → readonly; cambios requieren nueva correspondencia + re-firma.
- **Auditoría:** `fecha_creacion`, `fecha_modificacion`, `id_usuario_autor` en cada registro.
- **Datos sensibles:** certificado SAT no se expone en API; se guarda cifrado (opcional: usar Laravel encryptable).

## Decisiones

1. **Usar TinyMCE 6** (popular, bien soportado, libre con licencia AGPL/comercial) vs. CKEditor 5 (más moderno pero más corpulento). TinyMCE es más rápido para MVP.

2. **Firmas SAT:** integración síncrona (POST → respuesta). Si SAT es async, usar cola Laravel + webhook (feature futura).

3. **PDFs anexados:** reutilizar `unidades_documentales` sin tabla intermedia. Si luego se necesita relación explícita, crear `correspondencia_anexos`.

4. **Expediente de Correspondencia:** es un expediente ordinario (tipo = "correspondencia" en metadato, o clasificado en una serie dedicada). No crear tabla especial.

5. **Sin migración Laravel:** tablas se crean via script SQL directo en `database/sql/` y se documentan en `.env.example` y README.

## Archivo de BD (script SQL)

Crear `database/sql/004-correspondencias.sql`:
```sql
-- Base de datos para Módulo de Correspondencia
-- Ejecutar manualmente: mysql -u user -p database < database/sql/004-correspondencias.sql

-- Tabla de documentos de correspondencia
CREATE TABLE IF NOT EXISTS correspondencias (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_expediente BIGINT UNSIGNED NOT NULL,
  numero_consecutivo INT DEFAULT 1,
  id_usuario_autor BIGINT UNSIGNED NOT NULL,
  remitente VARCHAR(255),
  destinatario VARCHAR(255),
  asunto TEXT NOT NULL,
  contenido LONGTEXT NOT NULL,
  clasificacion ENUM('publica', 'reservada', 'confidencial') DEFAULT 'publica',
  estado ENUM('borrador', 'firmado', 'cancelado') DEFAULT 'borrador',
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  
  FOREIGN KEY (id_expediente) REFERENCES expedientes(id) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario_autor) REFERENCES usuarios(id),
  INDEX idx_expediente (id_expediente),
  INDEX idx_estado (estado),
  INDEX idx_fecha (fecha_creacion)
);

-- Tabla de firmas electrónicas SAT
CREATE TABLE IF NOT EXISTS correspondencia_firmas (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_correspondencia BIGINT UNSIGNED NOT NULL UNIQUE,
  id_usuario_firmante BIGINT UNSIGNED NOT NULL,
  certificado_sat TEXT,
  timestamp_firma TIMESTAMP,
  estado_firma ENUM('pendiente', 'exitosa', 'rechazada') DEFAULT 'pendiente',
  respuesta_sat TEXT,
  fecha_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (id_correspondencia) REFERENCES correspondencias(id) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario_firmante) REFERENCES usuarios(id),
  INDEX idx_correspondencia (id_correspondencia),
  INDEX idx_estado (estado_firma)
);
```

Documentar en `README.md`:
```
### Configuración inicial de base de datos (Correspondencia)

Crear tablas de correspondencia:
\`\`\`bash
mysql -u user -p database < database/sql/004-correspondencias.sql
\`\`\`
```

## Pruebas

- **Unit**: validación de reglas de negocio (solo `administrador`/`editor` pueden firmar; firma SAT retorna estado válido).
- **Feature**: flujo crear → editar → firmar; verificar que documento `firmado` es readonly.
- **Manual**: UI de editor, preview, llamada a API SAT (sandbox si es posible).

## Puntos de integración futuros

- **Notificaciones:** cuando se firma, notificar a remitentes/destinatarios registrados.
- **Workflow de aprobación:** agregar estado intermedio (`en_revision`) con aprobador explícito.
- **Importación automática:** consumidor de correo / FTP para recibir PDFs sin upload manual.
