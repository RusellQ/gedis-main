# 003 · Estadísticas en inicio (gráficas + PDF)

**Estado:** implementado ✅
**Ticket:** —

## Qué hace

En `/dashboard`, el personal ve estadísticas de **expedientes** en gráfica (pastel / barras / polar area) y puede descargar un PDF con encabezado oficial AGQROO + la gráfica.

### Jerarquía (respetada, sin modificar el catálogo)

**Fondo → (Subfondo) → Sección → Serie → (Subserie) → Expediente.**

Subfondo y Subserie son **opcionales** en la selección (como en Carátula v2). Esta feature **no crea ni altera** niveles del catálogo: solo filtra y agrupa expedientes respetando esa jerarquía.

### Selección libre (multi)

1. **Fondos:** uno, varios, o ninguno (= todos del alcance).
2. **Subfondos (opcional):** solo de los fondos elegidos; uno, varios, o ninguno. Vacío = todas las rutas bajo esos fondos (secciones directas del fondo y las que cuelgan de cualquier subfondo).
3. **Secciones:** solo de la ruta elegida (fondos ± subfondos); uno, varios, o ninguno tras haber fondos.
4. **Series:** solo de las secciones elegidas; uno, varios, o ninguno (= todas esas series).
5. **Subseries (opcional):** solo de las series elegidas; uno, varios, o ninguno. Vacío = todas las subseries (y expedientes sin subserie) bajo esas series.
6. **Tipos de expediente:** checkboxes — En trámite, Concentración, Histórico. Puede marcar **uno, dos o los tres**.

### Agrupación de la gráfica (automática)

No hay selector “agrupar por”. Se decide así:

- Si marca **más de un** tipo de expediente → la gráfica se parte **por tipo** (trámite / concentración / histórico).
- Si marca **un solo** tipo → según el nivel **explícitamente elegido**:
  - con **subseries** elegidas → **por subserie**;
  - con **series** (aunque sea una) y sin subseries → **por serie** (no salta a subserie sola);
  - con **secciones** (sin series) → **por serie**;
  - con **subfondos** elegidos (sin secciones) → **por subfondo**;
  - con **fondos** (sin subfondos ni inferiores) → **por sección**;
  - sin catálogo → **por fondo**.

Subfondo y subserie solo intervienen en la agrupación cuando el usuario los selecciona.

### Alcance

Por dependencia/departamento; `root` = global.

### Expedientes sin nivel opcional

Al filtrar por Fondo/Serie (sin elegir subfondo/subserie), los expedientes **sin** subfondo o **sin** subserie **sí entran** en el conteo.

## Por qué

Visibilidad operativa en el inicio sin aplanar la jerarquía archivística ni forzar un solo estado o un solo nodo del catálogo. La v1 omitió los niveles opcionales en la UI de filtros; hay que completarlos antes de cerrar la feature.

## Para quién

Personal autenticado (`root`, `administrador`, `editor`, `lector`).

## Criterios de aceptación

- [x] Multi-selección de fondos, subfondos (opc.), secciones, series y subseries (opc.) respetando Fondo → (Subfondo) → Sección → Serie → (Subserie).
- [x] No se pueden elegir subfondos sin fondos, secciones sin fondos, series sin secciones ni subseries sin series.
- [x] Con subfondos vacíos tras elegir fondos, las secciones listadas incluyen las directas del fondo y las de sus subfondos.
- [x] Con subfondos elegidos, las secciones listadas pertenecen solo a esos subfondos.
- [x] Tipos de expediente: uno, dos o tres a la vez (trámite / concentración / histórico).
- [x] La consulta solo incluye expedientes bajo los nodos elegidos y con los estados marcados; sin subfondo/subserie elegidos, siguen contando los que no tienen ese nivel.
- [x] Agrupación automática por nivel elegido: series (1 o más) → por serie hasta elegir subserie; subfondos → por subfondo; subserie solo si se seleccionó.
- [x] PDF con logos/encabezado oficial y la gráfica elegida.
- [x] Alcance por dependencia/departamento; UI en español.

## Fuera de alcance

- Depurar `/estadisticas` Mongo.
- Contar “series creadas” como modo aparte (diferible).
- Excel / API pública / migraciones nuevas.
- Alta/edición/baja de nodos del catálogo (eso es 001).
- Selector manual “agrupar por”.
