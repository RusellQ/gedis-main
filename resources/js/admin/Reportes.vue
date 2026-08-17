<template>
  <div class="reportes-page">
    <header class="page-head">
      <div>
        <h1 class="page-title">Reporte de expedientes</h1>
        <p class="page-sub">
          Filtre por estado, fechas de apertura y ubicación en el catálogo archivístico. El PDF usa los mismos criterios.
        </p>
      </div>
      <div class="head-actions">
        <span class="total-pill">{{ expedientesFiltrados.length }} expediente{{ expedientesFiltrados.length === 1 ? '' : 's' }}</span>
        <button type="button" class="btn-pdf" :disabled="descargando" @click="descargarPDF">
          {{ descargando ? 'Generando…' : 'Generar PDF' }}
        </button>
      </div>
    </header>

    <section class="filters-panel">
      <div class="filters-grid">
        <div class="filter-field">
          <label for="filtro-estado">Estado</label>
          <select id="filtro-estado" v-model="filtros.estado" class="control">
            <option value="">Todos</option>
            <option value="en_tramite">En trámite</option>
            <option value="en_concentracion">En concentración</option>
            <option value="en_historico">Histórico</option>
          </select>
        </div>

        <div class="filter-field">
          <label for="filtro-fecha-inicio">Apertura desde</label>
          <input id="filtro-fecha-inicio" v-model="filtros.fecha_inicio" type="date" class="control">
        </div>

        <div class="filter-field">
          <label for="filtro-fecha-fin">Apertura hasta</label>
          <input id="filtro-fecha-fin" v-model="filtros.fecha_fin" type="date" class="control">
        </div>

        <div class="filter-field">
          <label for="filtro-fondo">Fondo</label>
          <select id="filtro-fondo" v-model="filtros.id_fondo" class="control" @change="onFondoChange">
            <option value="">Todos</option>
            <option v-for="item in fondos" :key="item.id" :value="item.id">{{ etiquetaCatalogo(item) }}</option>
          </select>
        </div>

        <div class="filter-field">
          <label for="filtro-subfondo">Subfondo</label>
          <select id="filtro-subfondo" v-model="filtros.id_subfondo" class="control" @change="onSubfondoChange">
            <option value="">Todos</option>
            <option v-for="item in subfondosVisibles" :key="item.id" :value="item.id">{{ etiquetaCatalogo(item) }}</option>
          </select>
        </div>

        <div class="filter-field">
          <label for="filtro-seccion">Sección</label>
          <select id="filtro-seccion" v-model="filtros.id_seccion" class="control" @change="onSeccionChange">
            <option value="">Todas</option>
            <option v-for="item in seccionesVisibles" :key="item.id" :value="item.id">{{ etiquetaCatalogo(item) }}</option>
          </select>
        </div>

        <div class="filter-field">
          <label for="filtro-serie">Serie</label>
          <select id="filtro-serie" v-model="filtros.id_serie" class="control" @change="onSerieChange">
            <option value="">Todas</option>
            <option v-for="item in seriesVisibles" :key="item.id" :value="item.id">{{ etiquetaCatalogo(item) }}</option>
          </select>
        </div>

        <div class="filter-field">
          <label for="filtro-subserie">Subserie</label>
          <select id="filtro-subserie" v-model="filtros.id_subserie" class="control">
            <option value="">Todas</option>
            <option v-for="item in subseriesVisibles" :key="item.id" :value="item.id">{{ etiquetaCatalogo(item) }}</option>
          </select>
        </div>
      </div>

      <div class="filter-actions">
        <button type="button" class="btn-apply" :disabled="cargando" @click="obtenerExpedientes">
          {{ cargando ? 'Consultando…' : 'Aplicar filtros' }}
        </button>
        <button type="button" class="btn-ghost" :disabled="cargando" @click="limpiarFiltros">Limpiar</button>
      </div>
    </section>

    <div class="results-toolbar">
      <input
        v-model="busqueda"
        type="search"
        class="search-input"
        placeholder="Buscar en los resultados (clave, nombre, serie…)"
        aria-label="Buscar en los resultados"
        @input="paginaActual = 1"
      >
    </div>

    <p v-if="error" class="alert-error" role="alert">{{ error }}</p>

    <div v-if="cargando && !expedientes.length" class="empty">
      <p class="empty-title">Cargando expedientes…</p>
      <p class="empty-sub">Espere un momento.</p>
    </div>

    <div v-else-if="!expedientesFiltrados.length" class="empty">
      <p class="empty-title">Sin resultados</p>
      <p class="empty-sub">No hay expedientes con esos criterios. Ajuste los filtros y vuelva a consultar.</p>
    </div>

    <div v-else class="table-card">
      <div class="table-wrap">
        <table class="report-table">
          <thead>
            <tr>
              <th>Clave</th>
              <th>Nombre</th>
              <th>Estado</th>
              <th>Serie</th>
              <th>Clasificación</th>
              <th>Carácter</th>
              <th>Apertura</th>
              <th>Cierre</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="exp in expedientesPaginados" :key="exp.id">
              <td class="cell-clave">{{ exp.clave || '—' }}</td>
              <td>
                <strong class="cell-nombre">{{ exp.nombre || 'Sin nombre' }}</strong>
                <span v-if="exp.codigo" class="cell-code">{{ exp.codigo }}</span>
              </td>
              <td>
                <span class="estado-pill" :class="'estado-' + (exp.estado || '')">
                  {{ traducirEstado(exp.estado) }}
                </span>
              </td>
              <td>{{ nombreSerie(exp) }}</td>
              <td>{{ exp.clasificacion || '—' }}</td>
              <td>{{ exp.caracter || '—' }}</td>
              <td>{{ formatFecha(exp.fecha_apertura) }}</td>
              <td>{{ formatFecha(exp.fecha_cierre) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="totalPaginas > 1" class="pager">
        <button type="button" class="pager-btn" :disabled="paginaActual <= 1" @click="paginaActual--">‹ Anterior</button>
        <span class="pager-info">Página {{ paginaActual }} de {{ totalPaginas }}</span>
        <button type="button" class="pager-btn" :disabled="paginaActual >= totalPaginas" @click="paginaActual++">Siguiente ›</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

const FILTROS_VACIOS = {
  estado: '',
  fecha_inicio: '',
  fecha_fin: '',
  id_fondo: '',
  id_subfondo: '',
  id_seccion: '',
  id_serie: '',
  id_subserie: '',
}

export default {
  name: 'ReportesPage',
  data() {
    return {
      filtros: { ...FILTROS_VACIOS },
      expedientes: [],
      fondos: [],
      subfondos: [],
      secciones: [],
      series: [],
      subseries: [],
      busqueda: '',
      paginaActual: 1,
      porPagina: 15,
      cargando: false,
      descargando: false,
      error: '',
    }
  },
  computed: {
    subfondosVisibles() {
      if (!this.filtros.id_fondo) return this.subfondos
      return this.subfondos.filter((item) => String(item.id_fondo) === String(this.filtros.id_fondo))
    },
    seccionesVisibles() {
      let lista = this.secciones
      if (this.filtros.id_fondo) {
        lista = lista.filter((item) => String(item.id_fondo) === String(this.filtros.id_fondo))
      }
      if (this.filtros.id_subfondo) {
        lista = lista.filter((item) => String(item.id_subfondo) === String(this.filtros.id_subfondo))
      }
      return lista
    },
    seriesVisibles() {
      if (!this.filtros.id_seccion) return this.series
      return this.series.filter((item) => String(item.id_seccion) === String(this.filtros.id_seccion))
    },
    subseriesVisibles() {
      if (!this.filtros.id_serie) return this.subseries
      return this.subseries.filter((item) => String(item.id_serie) === String(this.filtros.id_serie))
    },
    expedientesFiltrados() {
      const texto = this.busqueda.trim().toLowerCase()
      if (!texto) return this.expedientes
      return this.expedientes.filter((exp) => {
        const campos = [
          exp.clave,
          exp.nombre,
          exp.codigo,
          exp.clasificacion,
          exp.caracter,
          this.traducirEstado(exp.estado),
          this.nombreSerie(exp),
        ]
        return campos.filter(Boolean).some((campo) => String(campo).toLowerCase().includes(texto))
      })
    },
    totalPaginas() {
      return Math.max(1, Math.ceil(this.expedientesFiltrados.length / this.porPagina))
    },
    expedientesPaginados() {
      const inicio = (this.paginaActual - 1) * this.porPagina
      return this.expedientesFiltrados.slice(inicio, inicio + this.porPagina)
    },
  },
  watch: {
    expedientesFiltrados() {
      if (this.paginaActual > this.totalPaginas) this.paginaActual = 1
    },
  },
  async mounted() {
    await this.cargarCatalogos()
    await this.obtenerExpedientes()
  },
  methods: {
    etiquetaCatalogo(item) {
      if (!item) return ''
      return item.codigo ? `${item.codigo} — ${item.nombre}` : item.nombre
    },
    nombreSerie(exp) {
      return exp?.serie?.nombre || exp?.subserie?.nombre || '—'
    },
    traducirEstado(estado) {
      if (estado === 'en_tramite') return 'En trámite'
      if (estado === 'en_concentracion') return 'En concentración'
      if (estado === 'en_historico') return 'Histórico'
      return estado || '—'
    },
    formatFecha(fecha) {
      if (!fecha) return '—'
      const date = new Date(`${fecha}T00:00:00`)
      if (Number.isNaN(date.getTime())) return fecha
      return date.toLocaleDateString('es-MX', { year: 'numeric', month: 'short', day: 'numeric' })
    },
    onFondoChange() {
      this.filtros.id_subfondo = ''
      this.filtros.id_seccion = ''
      this.filtros.id_serie = ''
      this.filtros.id_subserie = ''
    },
    onSubfondoChange() {
      this.filtros.id_seccion = ''
      this.filtros.id_serie = ''
      this.filtros.id_subserie = ''
    },
    onSeccionChange() {
      this.filtros.id_serie = ''
      this.filtros.id_subserie = ''
    },
    onSerieChange() {
      this.filtros.id_subserie = ''
    },
    async cargarCatalogos() {
      try {
        const [fondos, subfondos, secciones, series, subseries] = await Promise.all([
          axios.get('/catalogos/fondos'),
          axios.get('/catalogos/subfondos'),
          axios.get('/catalogos/secciones'),
          axios.get('/catalogos/series'),
          axios.get('/catalogos/subseries'),
        ])
        this.fondos = fondos.data
        this.subfondos = subfondos.data
        this.secciones = secciones.data
        this.series = series.data
        this.subseries = subseries.data
      } catch (error) {
        console.error('Error cargando catálogos:', error)
        this.error = 'No se pudieron cargar los catálogos. Los filtros de jerarquía pueden quedar incompletos.'
      }
    },
    async obtenerExpedientes() {
      this.cargando = true
      this.error = ''
      this.paginaActual = 1
      try {
        const { data } = await axios.get('/reportes/filtrar', { params: this.filtros })
        this.expedientes = Array.isArray(data) ? data : []
      } catch (error) {
        console.error('Error filtrando expedientes:', error)
        this.expedientes = []
        this.error = 'No se pudo consultar el reporte. Intente de nuevo.'
      } finally {
        this.cargando = false
      }
    },
    async limpiarFiltros() {
      this.filtros = { ...FILTROS_VACIOS }
      this.busqueda = ''
      await this.obtenerExpedientes()
    },
    descargarPDF() {
      this.descargando = true
      const form = document.createElement('form')
      form.method = 'GET'
      form.action = '/reportes/pdf'
      form.target = '_blank'

      Object.entries(this.filtros).forEach(([key, value]) => {
        if (value === '' || value === null || value === undefined) return
        const input = document.createElement('input')
        input.type = 'hidden'
        input.name = key
        input.value = value
        form.appendChild(input)
      })

      document.body.appendChild(form)
      form.submit()
      form.remove()
      window.setTimeout(() => {
        this.descargando = false
      }, 800)
    },
  },
}
</script>

<style scoped>
.reportes-page {
  --surface: #ffffff;
  --ink: #3D3935;
  --ink-soft: #78736a;
  --line: #e4e0d8;
  --brand: #AB0A3D;
  --brand-dark: #440412;
  --brand-soft: #f7e5eb;
  --gold: #B68400;
  --gold-soft: #faf1dd;

  max-width: 1180px;
  margin: 0 auto;
  padding: 1.75rem 1rem 3rem;
  color: var(--ink);
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

.page-head {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 1.25rem;
}
.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 0 .25rem;
  color: var(--brand-dark);
}
.page-sub {
  margin: 0;
  color: var(--ink-soft);
  font-size: .9rem;
  max-width: 42rem;
}

.head-actions {
  display: flex;
  flex-wrap: wrap;
  gap: .6rem;
  align-items: center;
}
.total-pill {
  font-size: .78rem;
  font-weight: 700;
  color: var(--brand);
  background: var(--brand-soft);
  border-radius: 999px;
  padding: .4rem .8rem;
}
.btn-pdf {
  border: none;
  background: linear-gradient(135deg, var(--brand), var(--brand-dark));
  color: #fff;
  font-size: .82rem;
  font-weight: 700;
  padding: .55rem .95rem;
  border-radius: .6rem;
  cursor: pointer;
  box-shadow: 0 8px 16px rgba(171, 10, 61, .2);
}
.btn-pdf:hover:not(:disabled) { filter: brightness(1.05); }
.btn-pdf:disabled { opacity: .65; cursor: not-allowed; }

.filters-panel {
  background: var(--surface);
  border: 1px solid var(--line);
  border-top: 4px solid var(--gold);
  border-radius: .95rem;
  padding: 1rem 1.1rem 1.15rem;
  margin-bottom: 1rem;
  box-shadow: 0 8px 20px rgba(68, 4, 18, .05);
}
.filters-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: .85rem .9rem;
}
.filter-field label {
  display: block;
  font-size: .72rem;
  font-weight: 700;
  color: var(--ink-soft);
  text-transform: uppercase;
  letter-spacing: .03em;
  margin-bottom: .3rem;
}
.control {
  width: 100%;
  border: 1px solid var(--line);
  border-radius: .55rem;
  background: #fff;
  padding: .5rem .7rem;
  font-size: .88rem;
  color: var(--ink);
}
.control:focus {
  outline: none;
  border-color: var(--brand);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand) 16%, transparent);
}

.filter-actions {
  display: flex;
  flex-wrap: wrap;
  gap: .55rem;
  margin-top: 1rem;
}
.btn-apply {
  border: none;
  background: var(--brand);
  color: #fff;
  font-size: .82rem;
  font-weight: 700;
  padding: .5rem .95rem;
  border-radius: .55rem;
  cursor: pointer;
}
.btn-apply:hover:not(:disabled) { background: var(--brand-dark); }
.btn-apply:disabled { opacity: .65; cursor: not-allowed; }
.btn-ghost {
  border: 1px solid var(--line);
  background: #fff;
  color: var(--ink);
  font-size: .82rem;
  font-weight: 600;
  padding: .5rem .95rem;
  border-radius: .55rem;
  cursor: pointer;
}
.btn-ghost:hover:not(:disabled) { border-color: var(--brand); background: #f5f7fb; }

.results-toolbar { margin-bottom: .85rem; }
.search-input {
  width: 100%;
  max-width: 28rem;
  border: 1px solid var(--line);
  border-radius: .55rem;
  padding: .5rem .75rem;
  font-size: .88rem;
}
.search-input:focus {
  outline: none;
  border-color: var(--brand);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand) 16%, transparent);
}

.alert-error {
  background: var(--brand-soft);
  color: var(--brand-dark);
  border-radius: .6rem;
  padding: .7rem .9rem;
  font-size: .88rem;
  margin-bottom: .85rem;
}

.table-card {
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: .95rem;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(68, 4, 18, .05);
}
.table-wrap { overflow-x: auto; }
.report-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 860px;
}
.report-table th {
  text-align: left;
  font-size: .7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .04em;
  color: var(--ink-soft);
  background: #faf8f5;
  padding: .7rem .8rem;
  border-bottom: 1px solid var(--line);
  white-space: nowrap;
}
.report-table td {
  padding: .7rem .8rem;
  font-size: .84rem;
  border-bottom: 1px solid var(--line);
  vertical-align: top;
}
.report-table tbody tr:last-child td { border-bottom: none; }
.report-table tbody tr:hover { background: #fcfbf9; }

.cell-clave {
  font-weight: 700;
  color: var(--brand);
  white-space: nowrap;
}
.cell-nombre {
  display: block;
  color: var(--brand-dark);
  font-weight: 700;
}
.cell-code {
  display: block;
  margin-top: .15rem;
  font-size: .75rem;
  color: var(--ink-soft);
}

.estado-pill {
  display: inline-block;
  font-size: .72rem;
  font-weight: 700;
  border-radius: 999px;
  padding: .22rem .6rem;
  white-space: nowrap;
}
.estado-en_tramite { color: var(--brand); background: var(--brand-soft); }
.estado-en_concentracion { color: #7a5a00; background: var(--gold-soft); }
.estado-en_historico { color: #fff; background: var(--brand-dark); }

.pager {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .75rem;
  padding: .85rem;
  border-top: 1px solid var(--line);
}
.pager-btn {
  border: 1px solid var(--line);
  background: #fff;
  border-radius: .5rem;
  padding: .35rem .7rem;
  font-size: .8rem;
  font-weight: 600;
  cursor: pointer;
}
.pager-btn:disabled { opacity: .45; cursor: not-allowed; }
.pager-info { font-size: .8rem; color: var(--ink-soft); }

.empty {
  text-align: center;
  padding: 3rem 1rem;
  background: var(--surface);
  border: 1px dashed var(--line);
  border-radius: .95rem;
}
.empty-title {
  margin: 0 0 .3rem;
  font-weight: 700;
  color: var(--brand-dark);
}
.empty-sub {
  margin: 0;
  color: var(--ink-soft);
  font-size: .88rem;
}

@media (max-width: 900px) {
  .filters-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 640px) {
  .filters-grid { grid-template-columns: 1fr; }
  .search-input { max-width: none; }
}
</style>
