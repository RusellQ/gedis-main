# 003 · Estadísticas en inicio (gráficas + PDF)

**Estado:** en curso
**Ticket:** —

## Qué hace

En `/dashboard`, el personal ve estadísticas de **expedientes** en gráfica (pastel / barras / polar area) y puede descargar un PDF con encabezado oficial AGQROO + la gráfica.

### Jerarquía (invariable)

**Fondo → Sección → Serie → Expediente.** La flexibilidad es cuántos elementos y tipos elige; nunca se rompe ese orden.

### Selección libre (multi)

1. **Fondos:** uno, varios, o ninguno (= todos del alcance).
2. **Secciones:** solo de los fondos elegidos; uno, varios, o ninguno tras haber fondos.
3. **Series:** solo de las secciones elegidas; uno, varios, o ninguno (= todas esas series).
4. **Tipos de expediente:** checkboxes — En trámite, Concentración, Histórico. Puede marcar **uno, dos o los tres**.

### Agrupación de la gráfica (automática)

No hay selector “agrupar por”. Se decide así:

- Si marca **más de un** tipo de expediente → la gráfica se parte **por tipo** (trámite / concentración / histórico).
- Si marca **un solo** tipo → se parte según el nivel de catálogo: series → por serie; secciones → por serie; fondos → por sección; sin catálogo → por fondo.

### Alcance

Por dependencia/departamento; `root` = global.

## Por qué

Visibilidad operativa en el inicio sin aplanar la jerarquía archivística ni forzar un solo estado o un solo nodo del catálogo.

## Para quién

Personal autenticado (`root`, `administrador`, `editor`, `lector`).

## Criterios de aceptación

- [ ] Multi-selección de fondos, secciones y series respetando Fondo → Sección → Serie.
- [ ] No se pueden elegir secciones sin fondos ni series sin secciones.
- [ ] Tipos de expediente: uno, dos o tres a la vez (trámite / concentración / histórico).
- [ ] La consulta solo incluye expedientes bajo los nodos elegidos y con los estados marcados.
- [ ] “Agrupar gráfica por” permite desplegar por nivel jerárquico o por tipo de expediente.
- [ ] PDF con logos/encabezado oficial y la gráfica elegida.
- [ ] Alcance por dependencia/departamento; UI en español.

## Fuera de alcance

- Depurar `/estadisticas` Mongo.
- Contar “series creadas” como modo aparte (diferible).
- Excel / API pública / migraciones nuevas.
