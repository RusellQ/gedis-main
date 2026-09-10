# 005 · Correspondencia: oficios, remisión y archivo

**Estado:** en curso  
**Ticket:** —  
**Relacionada:** retoma y rediseña [004 · Módulo de Correspondencia](../004-modulo-correspondencia/)

## Qué hace

Replantea Correspondencia como un flujo real de **oficios** (no solo un editor genérico ligado a un expediente):

1. **Menú lateral “Correspondencia”** con **dos entradas** separadas:
   - **Bandeja / remisión** — la encargada ve oficios recibidos, los **remite** a la persona/área designada, y puede **crear oficios propios** con plantilla oficial AGQROO.
   - **Recepción / archivo** — la persona designada ve lo remitido; si el oficio pertenece a un expediente lo agrega como **unidad documental**; si no, **crea el expediente** en ese momento (respetando Fondo → … → Expediente) y luego agrega el oficio como unidad documental.

2. **Creación de oficio con plantilla** (ejemplo oficial SEGOB/AGE):
   - Partes **fijas** (logos, membrete, cierre, ATENTAMENTE, bloque de firma/pie).
   - Partes **automáticas (azul):** fecha del momento; **número de oficio** según quien genera + consecutivo.
   - Partes **editables (verde):** asunto, leyenda del año, destinatario, y **cuerpo** (texto enriquecido + tablas + imágenes/figuras, tipo Word).

3. **Limpieza del 004** — elimina o deja de usar la cascada de jerarquía duplicada en una sola pantalla y el código muerto (Explorer, ruta huérfana, anexos sin uso). La **firma SAT en modo sandbox** (sin credenciales reales) **se conserva** como función de prueba; no es el núcleo del flujo remitir/archivar, pero sigue disponible.

## Por qué

El 004 no cubre el proceso operativo: recibir → remitir → archivar en expediente. Hoy mezcla selección de catálogo, HTML genérico y “firma SAT” simulada. AGQROO necesita oficios oficiales editables y un circuito claro entre encargada y área receptora, reutilizando expedientes y unidades documentales existentes.

## Para quién

- **Encargada de correspondencia** — bandeja, remisión y emisión de oficios.
- **Personal designado / área receptora** — recibe oficios y los incorpora al expediente correcto.
- **Administradores / root** — supervisión y alcance por dependencia.
- **Equipo técnico** — mantiene el módulo alineado a la jerarquía y al stack GEDIS.

## Criterios de aceptación

_Condiciones verificables. Marca `[x]` al cumplirse._

- [ ] El sidebar muestra **Correspondencia** como sección propia (no bajo Expedientes) con **dos ítems** (bandeja/remisión y recepción/archivo).
- [ ] La encargada puede **crear un oficio** con plantilla: campos fijos visibles; fecha y número de oficio **autogenerados**; asunto, leyenda, destinatario y cuerpo **editables**.
- [ ] El cuerpo del oficio permite **texto** formateado, **tablas**, **imágenes** (y figuras/medios equivalentes soportados por el editor), no solo texto plano.
- [ ] Se puede **generar/descargar PDF** del oficio alineado a la plantilla (membrete y zonas fijas + contenido editable).
- [ ] La encargada puede **remitir** un oficio a un usuario/área designada; el destinatario lo ve en su vista de recepción.
- [ ] Desde recepción, si el oficio corresponde a un expediente existente, se agrega como **unidad documental** (PDF) a ese expediente.
- [ ] Si no corresponde a ninguno, el usuario puede **crear el expediente** respetando Fondo → (Subfondo) → Sección → Serie → (Subserie) → Expediente **reutilizando** el flujo/componentes existentes (sin cascada duplicada solo-en-Correspondencia como hoy).
- [ ] Tras crear el expediente, puede agregar el mismo oficio como unidad documental en esa misma operación o paso inmediato.
- [ ] Listados y acciones respetan **roles** y **alcance por dependencia/departamento** (salvo `root` donde aplique).
- [ ] Se eliminan o desconectan del menú/rutas activas: `CorrespondenciaExplorer` no usado, `/correspondencia/crear` huérfana, y la UX actual que fuerza la cascada duplicada como flujo principal.
- [ ] La acción **Firmar (SAT sandbox)** sigue disponible en prueba: sin credenciales reales simula firma exitosa, marca el oficio como firmado/no editable y guarda el registro de firma; no exige API SAT real.
- [ ] UI y mensajes en **español**.

## Fuera de alcance

- Integración SAT/FIEL **con credenciales reales** en producción (el sandbox de prueba del 004 se mantiene; la firma real sería feature aparte).
- Notificaciones por correo/SMTP o importación automática de oficios desde buzón.
- Workflow multi-nivel de aprobación (solo remisión encargada → designado).
- Editor 100 % idéntico a Microsoft Word (se exige paridad práctica: texto, tablas, imágenes).
- Portal público o API externa de oficios.
- Rediseño de Trámite/Fondos más allá de **reutilizar** su patrón de jerarquía/alta de expediente y unidades.
