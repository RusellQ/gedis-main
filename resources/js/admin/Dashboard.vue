<template>
  <div class="dashboard-stats">
    <div class="text-center mb-4">
      <h1 class="h3 mb-2">Bienvenid@, {{ userName }}</h1>
      <p class="text-muted mb-0">
        Jerarquía: Fondo → (Subfondo) → Sección → Serie → (Subserie) → Expediente.
        Subfondo y subserie son opcionales. Elija niveles con casillas y los tipos de expediente a mostrar.
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
            Uno, dos o los tres. Varios tipos → la gráfica se parte por tipo; un solo tipo → según el nivel de catálogo.
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Fondos</label>
            <button type="button" class="btn btn-outline-primary w-100 text-start" @click="abrirModal('fondos')">
              {{ resumenFondos }}
            </button>
            <div class="form-text">Vacío = todos los fondos de su alcance.</div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Subfondos <span class="text-muted fw-normal">(opcional)</span></label>
            <button
              type="button"
              class="btn btn-outline-primary w-100 text-start"
              :disabled="!idsFondos.length"
              @click="abrirModal('subfondos')"
            >
              {{ resumenSubfondos }}
            </button>
            <div class="form-text">
              {{ idsFondos.length ? 'Vacío = todas las rutas bajo los fondos.' : 'Primero elija fondo(s).' }}
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Secciones</label>
            <button
              type="button"
              class="btn btn-outline-primary w-100 text-start"
              :disabled="!idsFondos.length"
              @click="abrirModal('secciones')"
            >
              {{ resumenSecciones }}
            </button>
            <div class="form-text">
              {{ textoAyudaSecciones }}
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Series</label>
            <button
              type="button"
              class="btn btn-outline-primary w-100 text-start"
              :disabled="!idsSecciones.length"
              @click="abrirModal('series')"
            >
              {{ resumenSeries }}
            </button>
            <div class="form-text">
              {{ idsSecciones.length ? 'Solo de las secciones elegidas. Vacío = todas.' : 'Primero elija sección(es).' }}
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Subseries <span class="text-muted fw-normal">(opcional)</span></label>
            <button
              type="button"
              class="btn btn-outline-primary w-100 text-start"
              :disabled="!idsSeries.length"
              @click="abrirModal('subseries')"
            >
              {{ resumenSubseries }}
            </button>
            <div class="form-text">
              {{ idsSeries.length ? 'Vacío = todas (incluye sin subserie).' : 'Primero elija serie(s).' }}
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

    <!-- Modal selección con checkboxes -->
    <div
      v-if="modalAbierto"
      class="modal-backdrop-custom"
      @click.self="cerrarModal"
    >
      <div class="modal-panel card shadow" role="dialog" aria-modal="true">
        <div class="card-header d-flex justify-content-between align-items-center">
          <strong>{{ modalTitulo }}</strong>
          <button type="button" class="btn-close" aria-label="Cerrar" @click="cerrarModal"></button>
        </div>
        <div class="card-body modal-body-scroll">
          <div v-if="!modalOpciones.length" class="text-muted">
            No hay opciones disponibles para este nivel.
          </div>
          <div v-else class="d-flex flex-column gap-2">
            <div class="form-check" v-for="item in modalOpciones" :key="item.id">
              <input
                :id="'modal-item-' + item.id"
                v-model="modalDraft"
                class="form-check-input"
                type="checkbox"
                :value="String(item.id)"
              >
              <label class="form-check-label" :for="'modal-item-' + item.id">
                {{ item.nombre }}
                <span v-if="item.codigo" class="text-muted small"> ({{ item.codigo }})</span>
              </label>
            </div>
          </div>
        </div>
        <div class="card-footer d-flex flex-wrap gap-2 justify-content-between">
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="modalSeleccionarTodo">
              Marcar todos
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="modalDraft = []">
              Limpiar
            </button>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-secondary" @click="cerrarModal">Cancelar</button>
            <button type="button" class="btn btn-sm btn-primary" @click="aplicarModal">Aplicar</button>
          </div>
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
      idsSubfondos: [],
      idsSecciones: [],
      idsSeries: [],
      idsSubseries: [],
      fondos: [],
      subfondos: [],
      secciones: [],
      series: [],
      subseries: [],
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
      modalNivel: null,
      modalDraft: [],
    }
  },
  computed: {
    modalAbierto() {
      return !!this.modalNivel
    },
    modalTitulo() {
      const titulos = {
        fondos: 'Seleccionar fondos',
        subfondos: 'Seleccionar subfondos (opcional)',
        secciones: 'Seleccionar secciones',
        series: 'Seleccionar series',
        subseries: 'Seleccionar subseries (opcional)',
      }
      return titulos[this.modalNivel] || ''
    },
    modalOpciones() {
      const map = {
        fondos: this.fondos,
        subfondos: this.subfondos,
        secciones: this.secciones,
        series: this.series,
        subseries: this.subseries,
      }
      return map[this.modalNivel] || []
    },
    resumenFondos() {
      return this.resumenSeleccion(this.idsFondos, this.fondos, 'Todos los fondos')
    },
    resumenSubfondos() {
      if (!this.idsFondos.length) return 'Elija primero fondo(s)'
      return this.resumenSeleccion(this.idsSubfondos, this.subfondos, 'Todas las rutas (opcional)')
    },
    resumenSecciones() {
      if (!this.idsFondos.length) return 'Elija primero fondo(s)'
      return this.resumenSeleccion(this.idsSecciones, this.secciones, 'Todas las secciones disponibles')
    },
    resumenSeries() {
      if (!this.idsSecciones.length) return 'Elija primero sección(es)'
      return this.resumenSeleccion(this.idsSeries, this.series, 'Todas las series de las secciones')
    },
    resumenSubseries() {
      if (!this.idsSeries.length) return 'Elija primero serie(s)'
      return this.resumenSeleccion(this.idsSubseries, this.subseries, 'Todas (incluye sin subserie)')
    },
    textoAyudaSecciones() {
      if (!this.idsFondos.length) return 'Primero elija fondo(s).'
      if (this.idsSubfondos.length) return 'Solo de los subfondos elegidos.'
      return 'Directas del fondo y vía subfondos.'
    },
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
    resumenSeleccion(ids, catalogo, vacioLabel) {
      if (!ids.length) return vacioLabel
      if (ids.length === 1) {
        const item = catalogo.find((c) => String(c.id) === String(ids[0]))
        return item?.nombre || `1 seleccionado`
      }
      return `${ids.length} seleccionados`
    },
    abrirModal(nivel) {
      this.modalNivel = nivel
      const draftMap = {
        fondos: this.idsFondos,
        subfondos: this.idsSubfondos,
        secciones: this.idsSecciones,
        series: this.idsSeries,
        subseries: this.idsSubseries,
      }
      this.modalDraft = [...(draftMap[nivel] || [])]
    },
    cerrarModal() {
      this.modalNivel = null
      this.modalDraft = []
    },
    modalSeleccionarTodo() {
      this.modalDraft = this.modalOpciones.map((i) => String(i.id))
    },
    limpiarDesdeSubfondos() {
      this.idsSubfondos = []
      this.subfondos = []
      this.limpiarDesdeSecciones()
    },
    limpiarDesdeSecciones() {
      this.idsSecciones = []
      this.secciones = []
      this.limpiarDesdeSeries()
    },
    limpiarDesdeSeries() {
      this.idsSeries = []
      this.series = []
      this.limpiarDesdeSubseries()
    },
    limpiarDesdeSubseries() {
      this.idsSubseries = []
      this.subseries = []
    },
    async aplicarModal() {
      const nivel = this.modalNivel
      const draft = [...this.modalDraft]
      this.cerrarModal()

      if (nivel === 'fondos') {
        this.idsFondos = draft
        this.limpiarDesdeSubfondos()
        if (this.idsFondos.length) {
          await this.cargarSubfondos()
          await this.cargarSecciones()
        }
        await this.cargarEstadisticas()
        return
      }

      if (nivel === 'subfondos') {
        this.idsSubfondos = draft
        this.limpiarDesdeSecciones()
        if (this.idsFondos.length) {
          await this.cargarSecciones()
        }
        await this.cargarEstadisticas()
        return
      }

      if (nivel === 'secciones') {
        this.idsSecciones = draft
        this.limpiarDesdeSeries()
        if (this.idsSecciones.length) {
          await this.cargarSeries()
        }
        await this.cargarEstadisticas()
        return
      }

      if (nivel === 'series') {
        this.idsSeries = draft
        this.limpiarDesdeSubseries()
        if (this.idsSeries.length) {
          await this.cargarSubseries()
        }
        await this.cargarEstadisticas()
        return
      }

      if (nivel === 'subseries') {
        this.idsSubseries = draft
        await this.cargarEstadisticas()
      }
    },
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
      if (this.idsSubfondos.length) appendArray(params, 'id_subfondos', this.idsSubfondos)
      if (this.idsSecciones.length) appendArray(params, 'id_secciones', this.idsSecciones)
      if (this.idsSeries.length) appendArray(params, 'id_series', this.idsSeries)
      if (this.idsSubseries.length) appendArray(params, 'id_subseries', this.idsSubseries)
      return params
    },
    bodyFiltros() {
      const body = { estados: [...this.estados] }
      if (this.idsFondos.length) body.id_fondos = this.idsFondos.map(Number)
      if (this.idsSubfondos.length) body.id_subfondos = this.idsSubfondos.map(Number)
      if (this.idsSecciones.length) body.id_secciones = this.idsSecciones.map(Number)
      if (this.idsSeries.length) body.id_series = this.idsSeries.map(Number)
      if (this.idsSubseries.length) body.id_subseries = this.idsSubseries.map(Number)
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
    async cargarFondos() {
      try {
        const { data } = await axios.get('/mostrarfondos')
        this.fondos = Array.isArray(data) ? data : []
      } catch (_) {
        this.fondos = []
      }
    },
    async cargarSubfondos() {
      if (!this.idsFondos.length) {
        this.subfondos = []
        return
      }
      try {
        const batches = await Promise.all(
          this.idsFondos.map((fid) => axios.get(`/fondos/${fid}/subfondos`))
        )
        this.subfondos = this.uniqueById(
          batches.flatMap((r) => (Array.isArray(r.data) ? r.data : []))
        )
      } catch (_) {
        this.subfondos = []
      }
    },
    async cargarSecciones() {
      if (!this.idsFondos.length) {
        this.secciones = []
        return
      }
      try {
        // Con subfondos elegidos: solo secciones de esos subfondos.
        if (this.idsSubfondos.length) {
          const batches = await Promise.all(
            this.idsSubfondos.map((sfid) => axios.get(`/subfondos/${sfid}/secciones`))
          )
          this.secciones = this.uniqueById(
            batches.flatMap((r) => (Array.isArray(r.data) ? r.data : []))
          )
          return
        }

        // Sin subfondos: secciones directas del fondo + vía todos sus subfondos.
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
    async cargarSubseries() {
      if (!this.idsSeries.length) {
        this.subseries = []
        return
      }
      try {
        const batches = await Promise.all(
          this.idsSeries.map(async (sid) => {
            const items = []
            let page = 1
            let lastPage = 1
            do {
              const { data } = await axios.get(`/series/${sid}/subseries`, { params: { page } })
              const chunk = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : [])
              items.push(...chunk)
              lastPage = Number(data?.last_page || 1)
              page += 1
            } while (page <= lastPage)
            return items
          })
        )
        this.subseries = this.uniqueById(batches.flat())
      } catch (_) {
        this.subseries = []
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

.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  z-index: 1055;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal-panel {
  width: min(520px, 100%);
  max-height: min(80vh, 640px);
  display: flex;
  flex-direction: column;
}

.modal-body-scroll {
  overflow-y: auto;
  max-height: 50vh;
}
</style>
