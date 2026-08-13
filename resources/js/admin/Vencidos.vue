<template>
  <div class="vencidos-page">
    <header class="page-head">
      <div>
        <h1 class="page-title">Expedientes vencidos</h1>
        <p class="page-sub">Vigencia documental vencida según la serie asociada. Revise y priorice su atención.</p>
      </div>
      <div class="head-actions">
        <span class="total-pill">{{ expedientesFiltrados.length }} vencido{{ expedientesFiltrados.length === 1 ? '' : 's' }}</span>
        <button type="button" class="btn-pdf" @click="generarPDF">Exportar PDF</button>
      </div>
    </header>

    <section class="filters-panel">
      <div class="filters-grid">
        <div class="filter-field filter-wide">
          <label>Buscar</label>
          <input v-model="busqueda" type="text" placeholder="Nombre, código, clave, serie..." class="control">
        </div>
        <div class="filter-field">
          <label>Desde cierre</label>
          <input v-model="fechaDesde" type="date" class="control">
        </div>
        <div class="filter-field">
          <label>Hasta cierre</label>
          <input v-model="fechaHasta" type="date" class="control">
        </div>
        <div class="filter-field">
          <label>Serie</label>
          <select v-model="serieSeleccionada" class="control">
            <option value="">Todas</option>
            <option v-for="serie in seriesDisponibles" :key="serie.id" :value="serie.id">
              {{ serie.codigo }} - {{ serie.nombre }}
            </option>
          </select>
        </div>
        <div class="filter-field">
          <label>Valor documental</label>
          <select v-model="valorDocumentalSeleccionado" class="control">
            <option value="">Todos</option>
            <option value="Administrativo">Administrativo</option>
            <option value="Legal">Legal</option>
            <option value="Contable">Contable</option>
          </select>
        </div>
      </div>
    </section>

    <div v-if="expedientesFiltrados.length === 0" class="empty">
      <p class="empty-title">No hay expedientes vencidos</p>
      <p class="empty-sub">No se encontraron resultados con esos criterios.</p>
    </div>

    <div v-else class="timeline">
      <article
        v-for="expediente in expedientesFiltrados"
        :key="expediente.id"
        class="vencido-row"
        :class="urgenciaClass(expediente.fecha_cierre)"
      >
        <div class="urgency-rail" aria-hidden="true">
          <span class="urgency-dot"></span>
        </div>

        <div class="vencido-body">
          <div class="vencido-head">
            <div class="vencido-titles">
              <h2 class="vencido-title">{{ expediente.nombre }}</h2>
              <p class="vencido-clave">{{ expediente.clave || 'Sin clave' }}</p>
            </div>
            <div class="vencido-flags">
              <span class="pill-vencido">Vencido</span>
              <span class="pill-dias">{{ diasVencidos(expediente.fecha_cierre) }}</span>
            </div>
          </div>

          <div class="vencido-meta">
            <div class="meta-item">
              <span class="meta-label">Código</span>
              <strong>{{ expediente.codigo || '—' }}</strong>
            </div>
            <div class="meta-item">
              <span class="meta-label">Serie</span>
              <strong>{{ expediente.serie || 'N/A' }}</strong>
            </div>
            <div class="meta-item">
              <span class="meta-label">Subserie</span>
              <strong>{{ expediente.subserie || 'N/A' }}</strong>
            </div>
            <div class="meta-item">
              <span class="meta-label">Fondo</span>
              <strong>{{ expediente.fondo || '—' }}</strong>
            </div>
            <div class="meta-item">
              <span class="meta-label">Sección</span>
              <strong>{{ expediente.seccion || '—' }}</strong>
            </div>
            <div class="meta-item">
              <span class="meta-label">Valor documental</span>
              <strong>{{ formatearValorDocumental(expediente.serie_detalle) }}</strong>
            </div>
            <div class="meta-item">
              <span class="meta-label">Vigencia AT</span>
              <strong>{{ formatearValorSimple(expediente.serie_detalle?.plazo_at) }}</strong>
            </div>
            <div class="meta-item">
              <span class="meta-label">Vigencia AC</span>
              <strong>{{ formatearValorSimple(expediente.serie_detalle?.plazo_ac) }}</strong>
            </div>
            <div class="meta-item">
              <span class="meta-label">Fecha de cierre</span>
              <strong>{{ formatFecha(expediente.fecha_cierre) }}</strong>
            </div>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'VencidosPage',
  data() {
    return {
      expedientes: [],
      series: [],
      busqueda: '',
      fechaDesde: '',
      fechaHasta: '',
      serieSeleccionada: '',
      valorDocumentalSeleccionado: '',
    }
  },
  computed: {
    seriesDisponibles() {
      return this.series.slice().sort((a, b) => a.nombre.localeCompare(b.nombre))
    },
    expedientesFiltrados() {
      return this.expedientes.filter(expediente => {
        const texto = this.busqueda.trim().toLowerCase()
        const cumpleTexto = !texto || [
          expediente.nombre,
          expediente.codigo,
          expediente.clave,
          expediente.fondo,
          expediente.seccion,
          expediente.serie,
          expediente.subserie,
          expediente.ubicacion_topografica,
        ].filter(Boolean).some(c => c.toLowerCase().includes(texto))

        const fechaCierre = expediente.fecha_cierre ? new Date(expediente.fecha_cierre) : null
        const desdeOk = !this.fechaDesde || !fechaCierre || fechaCierre >= new Date(this.fechaDesde)
        const hastaOk = !this.fechaHasta || !fechaCierre || fechaCierre <= new Date(this.fechaHasta)

        const serieOk = !this.serieSeleccionada || expediente.id_serie === Number(this.serieSeleccionada)
        const valorOk = !this.valorDocumentalSeleccionado || this.formatearValorDocumental(expediente.serie_detalle).includes(this.valorDocumentalSeleccionado)

        return cumpleTexto && desdeOk && hastaOk && serieOk && valorOk
      })
    }
  },
  mounted() {
    this.cargarSeries()
    this.cargarExpedientes()
  },
  methods: {
    async cargarSeries() {
      try {
        const response = await axios.get('/catalogos/series')
        this.series = response.data
      } catch (error) {
        console.error('Error cargando series:', error)
      }
    },
    async cargarExpedientes() {
      try {
        const response = await axios.get('/expedientes/vencidos')
        this.expedientes = response.data
      } catch (error) {
        console.error('Error cargando expedientes vencidos:', error)
      }
    },
    generarPDF() {
      const form = document.createElement('form')
      form.method = 'GET'
      form.action = '/expedientes/vencidos/pdf'
      form.target = '_blank'

      const campos = {
        busqueda: this.busqueda,
        fecha_desde: this.fechaDesde,
        fecha_hasta: this.fechaHasta,
        serie_id: this.serieSeleccionada,
        valor_documental: this.valorDocumentalSeleccionado,
      }

      Object.keys(campos).forEach(key => {
        if (campos[key]) {
          const input = document.createElement('input')
          input.type = 'hidden'
          input.name = key
          input.value = campos[key]
          form.appendChild(input)
        }
      })

      document.body.appendChild(form)
      form.submit()
      form.remove()
    },
    formatFecha(fecha) {
      if (!fecha) return 'N/A'
      const date = new Date(fecha)
      return date.toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' })
    },
    formatearValorDocumental(serieDetalle) {
      if (!serieDetalle) return 'Sin información'
      const valores = []
      if (serieDetalle.vigencia_ad) valores.push('Administrativo')
      if (serieDetalle.vigencia_le) valores.push('Legal')
      if (serieDetalle.vigencia_confi) valores.push('Contable')
      return valores.length ? valores.join(', ') : 'Sin información'
    },
    formatearValorSimple(valor) {
      return valor === null || valor === undefined || valor === '' ? 'N/A' : valor
    },
    diasVencidosNumero(fechaCierre) {
      if (!fechaCierre) return 0
      const hoy = new Date()
      const cierre = new Date(fechaCierre)
      return Math.max(0, Math.floor((hoy - cierre) / (1000 * 60 * 60 * 24)))
    },
    diasVencidos(fechaCierre) {
      if (!fechaCierre) return 'N/A'
      return `${this.diasVencidosNumero(fechaCierre)} días`
    },
    urgenciaClass(fechaCierre) {
      const dias = this.diasVencidosNumero(fechaCierre)
      if (dias >= 365) return 'urgencia-alta'
      if (dias >= 90) return 'urgencia-media'
      return 'urgencia-baja'
    }
  }
}
</script>

<style scoped>
.vencidos-page {
  --surface: #ffffff;
  --ink: #3D3935;
  --ink-soft: #78736a;
  --line: #e4e0d8;
  --brand: #AB0A3D;
  --brand-dark: #440412;
  --brand-soft: #f7e5eb;
  --gold: #B68400;
  --gold-soft: #faf1dd;

  background: transparent;
  min-height: 100vh;
  max-width: 1100px;
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
.btn-pdf:hover { filter: brightness(1.05); }

.filters-panel {
  background: var(--surface);
  border: 1px solid var(--line);
  border-top: 4px solid var(--gold);
  border-radius: .95rem;
  padding: 1rem 1.1rem 1.15rem;
  margin-bottom: 1.35rem;
  box-shadow: 0 8px 20px rgba(68, 4, 18, .05);
}
.filters-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: .85rem .9rem;
}
.filter-wide { grid-column: 1 / -1; }
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

.timeline {
  display: flex;
  flex-direction: column;
  gap: .85rem;
}

.vencido-row {
  --urgency: var(--gold);
  display: grid;
  grid-template-columns: 28px 1fr;
  gap: .75rem;
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: .95rem;
  padding: 1rem 1.1rem;
  box-shadow: 0 6px 16px rgba(68, 4, 18, .05);
}
.urgencia-baja { --urgency: var(--gold); }
.urgencia-media { --urgency: #c45a1a; }
.urgencia-alta { --urgency: var(--brand); }

.urgency-rail {
  position: relative;
  display: flex;
  justify-content: center;
  padding-top: .35rem;
}
.urgency-rail::before {
  content: "";
  position: absolute;
  top: .9rem;
  bottom: -.2rem;
  width: 2px;
  background: color-mix(in srgb, var(--urgency) 35%, var(--line));
}
.urgency-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: var(--urgency);
  box-shadow: 0 0 0 4px color-mix(in srgb, var(--urgency) 18%, #fff);
  z-index: 1;
}

.vencido-head {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: .75rem;
  margin-bottom: .85rem;
}
.vencido-title {
  margin: 0 0 .2rem;
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--brand-dark);
}
.vencido-clave {
  margin: 0;
  font-size: .8rem;
  font-weight: 600;
  color: var(--brand);
  word-break: break-word;
}
.vencido-flags {
  display: flex;
  flex-wrap: wrap;
  gap: .4rem;
  align-items: flex-start;
}
.pill-vencido,
.pill-dias {
  font-size: .72rem;
  font-weight: 700;
  border-radius: 999px;
  padding: .28rem .65rem;
}
.pill-vencido {
  color: var(--brand);
  background: var(--brand-soft);
}
.pill-dias {
  color: #7a5a00;
  background: var(--gold-soft);
}
.urgencia-alta .pill-dias {
  color: var(--brand);
  background: var(--brand-soft);
}

.vencido-meta {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: .65rem .9rem;
}
.meta-label {
  display: block;
  font-size: .66rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .03em;
  color: var(--ink-soft);
  margin-bottom: .12rem;
}
.meta-item strong {
  font-size: .84rem;
  font-weight: 600;
  color: var(--ink);
  overflow-wrap: break-word;
}

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
  .filters-grid {
    grid-template-columns: 1fr 1fr;
  }
  .vencido-meta {
    grid-template-columns: 1fr 1fr;
  }
}
@media (max-width: 640px) {
  .filters-grid,
  .vencido-meta {
    grid-template-columns: 1fr;
  }
  .filter-wide { grid-column: auto; }
  .vencido-row {
    grid-template-columns: 1fr;
  }
  .urgency-rail { display: none; }
}
</style>
