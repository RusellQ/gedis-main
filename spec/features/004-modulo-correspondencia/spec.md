# 004 · Módulo de Correspondencia — Recepción y Control de Documentos

**Estado:** especificación 📋  
**Ticket:** —

## Qué hace

El **módulo de Correspondencia** permite al personal archivístico de AGQROO crear y gestionar **documentos de correspondencia** dentro de expedientes, con las siguientes capacidades:

1. **Crear expediente de Correspondencia** — El usuario abre un nuevo expediente aplicando las reglas de jerarquía archivística (Fondo → Sección → Serie) y designando el tipo/asunto de correspondencia.

2. **Redactar / recibir documentos** — Dentro del expediente, el usuario puede:
   - Crear nuevos documentos con un editor WYSIWYG (texto con formato, imágenes, tablas).
   - Especificar metadatos (remitente, destinatario, fecha, asunto, clasificación, etc.) siguiendo el catálogo de disposición.

3. **Firmar electrónicamente** — Mediante integración con **API externa SAT** (Servicio de Administración Tributaria):
   - El documento se prepara con estructura XDP/XML compatible con firma digital avanzada.
   - Se invoca la API SAT para obtener/validar la firma del autor (servidor o usuario con certificado).
   - El documento firmado se guarda en la BD con referencia a la firma y timestamp.

4. **Adjuntar unidades documentales** — El usuario puede subir **PDFs escaneados** como anexos del expediente:
   - Los PDFs se registran como **Unidades Documentales** asociadas al expediente (reutilizando la lógica existente).
   - Cada PDF heredará la clasificación y reglas documentales de la serie.
   - Un mismo expediente puede tener múltiples documentos de Correspondencia y múltiples unidades documentales.

5. **Consulta y trazabilidad** — El usuario ve el histórico de documentos creados/modificados en el expediente; la firma electrónica deja constancia de autoría y fecha.

## Jerarquía (respetada, sin modificar el catálogo)

**Fondo → (Subfondo) → Sección → Serie → (Subserie) → Expediente → Documentos de Correspondencia + Unidades Documentales.**

- Expediente de Correspondencia = expediente ordinario regido por las reglas de la serie.
- Documentos de Correspondencia = artefactos dentro del expediente (análogos a carátulas, pero con contenido HTML/firmado).
- Unidades Documentales = anexos PDF al expediente (lógica existente).

## Alcance de usuario (por dependencia/departamento)

- **root** — acceso global a todos los módulos y expedientes.
- **administrador / editor** — crear y editar correspondencia dentro de su dependencia/departamento.
- **lector** — consultar expedientes y documentos, sin crear ni firmar.

## Datos alineados a normas

- **Catálogo de disposición documental** — cada documento de correspondencia respeta la clasificación de la serie.
- **Cuadro general de clasificación** — la jerarquía Fondo → Sección → Serie determina el código de clasificación.
- **LINEAMIENTOS SAT** — documento preparado para firma avanzada; metadatos ISO 27001 en el almacenamiento.

## Criterios de aceptación

- [x] Crear expediente de Correspondencia respetando Fondo → Sección → Serie y reglas documentales.
- [x] Crear documento de Correspondencia dentro de expediente con editor WYSIWYG (texto, imágenes, tablas).
- [x] Guardar metadatos: remitente, destinatario, fecha, asunto, clasificación (pública/reservada/confidencial).
- [x] Integración con API SAT para firma electrónica avanzada; documento preparado con estructura XDP.
- [x] Guardar firma digital (certificado, timestamp) en la BD; trazabilidad de autor y fecha.
- [x] Adjuntar PDFs escaneados como Unidades Documentales al expediente.
- [x] Múltiples documentos de Correspondencia por expediente; múltiples PDFs por expediente.
- [x] Consulta de histórico de documentos y firma; acceso restringido por rol y dependencia/departamento.
- [x] Validación de integridad y seguridad: no permitir modificación de documento firmado sin re-firma; auditoría de cambios.

## Qué NO entra en alcance

- No incluye **notificaciones automáticas** (emails de alerta); eso es feature separada.
- No modifica **catálogo ni estructura archivística** (fondos, secciones, series); solo consume la estructura existente.
- No introduce **workflow de aprobación multi-paso**; la firma SAT es el hito único de validación.
- No añade **integración con plataforma externa de correo** (POP3/SMTP para importación automática); entrada manual o upload de PDFs solamente.
