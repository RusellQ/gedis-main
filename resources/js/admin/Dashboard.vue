<template>
  <div class="dashboard-stats">
    <div class="text-center mb-4">
      <h1 class="h3 mb-2">Bienvenid@, {{ userName }}</h1>
      <p class="text-muted mb-0">
        Jerarquía fija: Fondo → Sección → Serie → Expediente. Combine fondos, secciones y series; marque uno, dos o los tres tipos de expediente.
      </p>
    </div>

    <div class="card shadow-sm">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <strong>Estadísticas de expedientes</strong>
        <button
          type="button"
          class="btn btn-success btn-sm"
          :disabled="!puedeDescargar || descargando"
          @click="descargarPDF"
        >
          <span v-if="descargando" class="spinner-border spinner-border-sm me-1" role="status"></span>
          Descargar PDF
        </button>
      </div>

      <div class="card-body">
        <div class="mb-3">
          <label class="form-label fw-semibold">Tipos de expediente a mostrar</label>
          <div class="d-flex flex-wrap gap-3">
            <div class="form-check" v-for="opt in opcionesEstado" :key="opt.value">
              <input
                :id="'estado-' + opt.value"
                v-model="estados"
                class="form-check-input"
                type="checkbox"
                :value="opt.value"
                @change="onEstadosChange"
              >
              <label class="form-check-label" :for="'estado-' + opt.value">{{ opt.label }}</label>
            </div>
          </div>
          <div class="form-text">
            Uno, dos o los tres. Si marca más de uno, la gráfica se parte por tipo; si marca solo uno, se parte según el nivel de catálogo elegido.
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Fondos</label>
            <select
              v-model="idsFondos"
              class="form-select"
              multiple
              size="5"
              @change="onFondosChange"
            >
              <option v-for="f in fondos" :key="f.id" :value="String(f.id)">{{ f.nombre }}</option>
            </select>
            <div class="form-text">Vacío = todos. Ctrl/Cmd + clic para varios.</div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Secciones</label>
            <select
              v-model="idsSecciones"
              class="form-select"
              multiple
              size="5"
              :disabled="!idsFondos.length"
              @change="onSeccionesChange"
            >
              <option v-for="s in secciones" :key="s.id" :value="String(s.id)">{{ s.nombre }}</option>
            </select>
            <div class="form-text">
              {{ idsFondos.length ? 'Solo de los fondos elegidos.' : 'Primero elija fondo(s).' }}
            </div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Series</label>
            <select
              v-model="idsSeries"
              class="form-select"
              multiple
              size="5"
              :disabled="!idsSecciones.length"
              @change="cargarEstadisticas"
            >
              <option v-for="s in series" :key="s.id" :value="String(s.id)">{{ s.nombre }}</option>
            </select>
            <div class="form-text">
              {{ idsSecciones.length ? 'Solo de las secciones elegidas. Vacío = todas.' : 'Primero elija sección(es).' }}
            </div>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label" for="tipo-select">Tipo de gráfica</label>
            <select id="tipo-select" v-model="tipoGrafica" class="form-select">
              <option value="pie">Pastel</option>
              <option value="bar">Barras</option>
              <option value="polarArea">Área polar</option>
            </select>
          </div>
        </div>

        <div v-if="error" class="alert alert-danger" role="alert">{{ error }}</div>

        <div v-if="cargando" class="text-center py-5 text-muted">
          <div class="spinner-border text-primary mb-2" role="status"></div>
          <div>Cargando estadísticas…</div>
        </div>

        <div v-else-if="!tieneDatos" class="alert alert-info mb-0" role="alert">
          No hay datos para la combinación seleccionada.
        </div>

        <div v-else>
          <p class="small text-muted mb-2">
            {{ dimensionLabel }}
            <span v-if="filtroResumen"> · {{ filtroResumen }}</span>
            <span v-if="alcance === 'global'"> · Alcance global</span>
            <span v-else> · Su dependencia / departamento</span>
            · Total: {{ total }}
          </p>
          <DashboardStatsChart
            :key="tipoGrafica + '-' + chartKey"
            ref="chartCmp"
            :type="tipoGrafica"
            :chart-data="chartData"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import DashboardStatsChart from '../components/DashboardStatsChart.vue'

const PALETTE = [
  '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796',
  '#5a5c69', '#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617',
]

function appendArray(params, key, values) {
  values.forEach((v) => params.append(`${key}[]`, String(v)))
}

export default {
  name: 'Dashboard',
  components: { DashboardStatsChart },
  props: {
    userName: { type: String, default: '' },
  },
  data() {
    return {
      opcionesEstado: [
        { value: 'en_tramite', label: 'En trámite' },
        { value: 'en_concentracion', label: 'Concentración' },
        { value: 'en_historico', label: 'Histórico' },
      ],
      estados: ['en_tramite'],
      idsFondos: [],
      idsSecciones: [],
      idsSeries: [],
      fondos: [],
      secciones: [],
      series: [],
      tipoGrafica: 'pie',
      labels: [],
      data: [],
      total: 0,
      alcance: 'dependencia',
      dimensionLabel: '',
      filtroResumen: '',
      cargando: false,
      descargando: false,
      error: null,
      chartKey: 0,
    }
  },
  computed: {
    tieneDatos() {
      return this.total > 0 && this.labels.length > 0
    },
    puedeDescargar() {
      return this.tieneDatos && !this.cargando && this.estados.length > 0
    },
    chartData() {
      const colors = this.labels.map((_, i) => PALETTE[i % PALETTE.length])
      return {
        labels: this.labels,
        datasets: [{
          label: this.dimensionLabel || 'Cantidad',
          data: this.data,
          backgroundColor: colors,
          borderColor: this.tipoGrafica === 'bar' ? colors : '#ffffff',
          borderWidth: this.tipoGrafica === 'bar' ? 0 : 2,
        }],
      }
    },
  },
  async mounted() {
    await this.cargarFondos()
    await this.cargarEstadisticas()
  },
  methods: {
    uniqueById(items) {
      const map = new Map()
      for (const item of items) {
        if (item?.id != null) map.set(String(item.id), item)
      }
      return Array.from(map.values())
    },
    buildQueryParams() {
      const params = new URLSearchParams()
      appendArray(params, 'estados', this.estados)
      if (this.idsFondos.length) appendArray(params, 'id_fondos', this.idsFondos)
      if (this.idsSecciones.length) appendArray(params, 'id_secciones', this.idsSecciones)
      if (this.idsSeries.length) appendArray(params, 'id_series', this.idsSeries)
      return params
    },
    bodyFiltros() {
      const body = { estados: [...this.estados] }
      if (this.idsFondos.length) body.id_fondos = this.idsFondos.map(Number)
      if (this.idsSecciones.length) body.id_secciones = this.idsSecciones.map(Number)
      if (this.idsSeries.length) body.id_series = this.idsSeries.map(Number)
      return body
    },
    async onEstadosChange() {
      if (!this.estados.length) {
        this.error = 'Seleccione al menos un tipo de expediente.'
        this.labels = []
        this.data = []
        this.total = 0
        return
      }
      await this.cargarEstadisticas()
    },
    async onFondosChange() {
      this.idsSecciones = []
      this.idsSeries = []
      this.series = []
      this.secciones = []
      if (this.idsFondos.length) {
        await this.cargarSecciones()
      }
      await this.cargarEstadisticas()
    },
    async onSeccionesChange() {
      this.idsSeries = []
      this.series = []
      if (this.idsSecciones.length) {
        await this.cargarSeries()
      }
      await this.cargarEstadisticas()
    },
    async cargarFondos() {
      try {
        const { data } = await axios.get('/mostrarfondos')
        this.fondos = Array.isArray(data) ? data : []
      } catch (_) {
        this.fondos = []
      }
    },
    async cargarSecciones() {
      if (!this.idsFondos.length) {
        this.secciones = []
        return
      }
      try {
        const batches = await Promise.all(this.idsFondos.map(async (fid) => {
          const [direct, subfondosRes] = await Promise.all([
            axios.get(`/fondos/${fid}/secciones`),
            axios.get(`/fondos/${fid}/subfondos`),
          ])
          const subfondos = Array.isArray(subfondosRes.data) ? subfondosRes.data : []
          const fromSubs = await Promise.all(
            subfondos.map((sf) => axios.get(`/subfondos/${sf.id}/secciones`))
          )
          return [
            ...(Array.isArray(direct.data) ? direct.data : []),
            ...fromSubs.flatMap((r) => (Array.isArray(r.data) ? r.data : [])),
          ]
        }))
        this.secciones = this.uniqueById(batches.flat())
      } catch (_) {
        this.secciones = []
      }
    },
    async cargarSeries() {
      if (!this.idsSecciones.length) {
        this.series = []
        return
      }
      try {
        const batches = await Promise.all(
          this.idsSecciones.map((sid) => axios.get(`/secciones/${sid}/series`))
        )
        this.series = this.uniqueById(
          batches.flatMap((r) => (Array.isArray(r.data) ? r.data : []))
        )
      } catch (_) {
        this.series = []
      }
    },
    async cargarEstadisticas() {
      if (!this.estados.length) {
        this.error = 'Seleccione al menos un tipo de expediente.'
        return
      }

      this.cargando = true
      this.error = null
      try {
        const { data } = await axios.get(`/dashboard/estadisticas?${this.buildQueryParams().toString()}`)
        this.labels = data.labels || []
        this.data = data.data || []
        this.total = data.total || 0
        this.alcance = data.alcance || 'dependencia'
        this.dimensionLabel = data.dimension_label || ''
        this.filtroResumen = data.filtro_resumen || ''
        this.chartKey += 1
      } catch (e) {
        this.labels = []
        this.data = []
        this.total = 0
        const errors = e.response?.data?.errors
        if (errors) {
          this.error = Object.values(errors).flat().join(' ')
        } else {
          this.error = e.response?.data?.message
            || 'No se pudieron cargar las estadísticas.'
        }
      } finally {
        this.cargando = false
      }
    },
    async descargarPDF() {
      if (!this.puedeDescargar) return

      this.descargando = true
      this.error = null
      try {
        await this.$nextTick()
        await new Promise((resolve) => requestAnimationFrame(() => setTimeout(resolve, 150)))
        const chartImage = this.$refs.chartCmp?.toBase64Image?.()
        if (!chartImage) {
          this.error = 'No se pudo capturar la gráfica para el PDF. Intente de nuevo.'
          return
        }

        const response = await axios.post(
          '/dashboard/estadisticas/pdf',
          {
            ...this.bodyFiltros(),
            tipo_grafica: this.tipoGrafica,
            chart_image: chartImage,
          },
          { responseType: 'blob' }
        )

        const disposition = response.headers['content-disposition'] || ''
        const match = disposition.match(/filename="?([^"]+)"?/)
        const filename = match?.[1] || 'estadisticas.pdf'
        const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', filename)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
      } catch (e) {
        let message = 'No se pudo generar el PDF.'
        const blob = e.response?.data
        if (blob instanceof Blob) {
          try {
            const text = await blob.text()
            const json = JSON.parse(text)
            if (json.message) message = json.message
            if (json.errors) message = Object.values(json.errors).flat().join(' ')
          } catch (_) { /* ignore */ }
        } else if (e.response?.data?.message) {
          message = e.response.data.message
        }
        this.error = message
      } finally {
        this.descargando = false
      }
    },
  },
}
</script>

<style scoped>
.dashboard-stats {
  max-width: 1100px;
  margin: 0 auto;
  padding: 1.5rem 1rem 2rem;
}
</style>
