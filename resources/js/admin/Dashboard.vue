<template>
  <div class="dashboard-stats">
    <!-- Encabezado -->
    <header class="page-head">
      <div>
        <h1 class="page-title">Estadísticas de expedientes</h1>
        <p class="page-sub">
          Bienvenid@, <strong>{{ userName }}</strong> · Jerarquía Fondo → (Subfondo) → Sección → Serie → (Subserie) → Expediente.
        </p>
      </div>
      <button
        type="button"
        class="btn btn-download"
        :disabled="!puedeDescargar || descargando"
        @click="descargarPDF"
      >
        <span v-if="descargando" class="spinner-border spinner-border-sm me-2" role="status"></span>
        <span v-else class="ico">↓</span>
        Descargar PDF
      </button>
    </header>

    <div v-if="error" class="alert alert-danger" role="alert">{{ error }}</div>

    <!-- KPIs por tipo de expediente -->
    <section class="kpi-grid" aria-label="Resumen">
      <article class="kpi kpi-total">
        <span class="kpi-label">Total de expedientes</span>
        <span class="kpi-value">{{ total.toLocaleString('es-MX') }}</span>
        <span class="kpi-foot">{{ dimensionLabel || 'Sin dimensión' }}</span>
      </article>
      <article
        v-for="opt in opcionesEstado"
        :key="opt.value"
        class="kpi"
        :class="{ 'kpi-off': !estados.includes(opt.value) }"
        :style="{ '--kpi-color': opt.color }"
      >
        <span class="kpi-dot"></span>
        <span class="kpi-label">{{ opt.label }}</span>
        <span class="kpi-value">{{ (kpiPorEstado[opt.value] ?? 0).toLocaleString('es-MX') }}</span>
        <span class="kpi-foot">{{ porcentaje(kpiPorEstado[opt.value]) }}</span>
      </article>
    </section>

    <div class="layout">
      <!-- Panel de filtros -->
      <aside class="filters card-surface">
        <h2 class="panel-title">Filtros</h2>

        <!-- Tipos de expediente -->
        <div class="field">
          <span class="field-label">Tipos de expediente</span>
          <div class="chips">
            <label
              v-for="opt in opcionesEstado"
              :key="opt.value"
              class="chip"
              :class="{ 'chip-on': estados.includes(opt.value) }"
              :style="{ '--chip-color': opt.color }"
            >
              <input
                class="chip-input"
                type="checkbox"
                :value="opt.value"
                v-model="estados"
                @change="onEstadosChange"
              >
              <span class="chip-dot"></span>{{ opt.label }}
            </label>
          </div>
          <p class="field-help">
            Varios tipos → la gráfica se parte por tipo; uno solo → según el nivel de catálogo.
          </p>
        </div>

        <hr class="sep">

        <!-- Jerarquía tipo stepper -->
        <div class="steps">
          <!-- 1. Fondos -->
          <div class="step">
            <span class="step-num">1</span>
            <div class="step-body">
              <div class="step-top">
                <span class="step-name">Fondos</span>
                <button
                  v-if="idsFondos.length"
                  class="clear-badge"
                  type="button"
                  @click="limpiarNivel('fondos')"
                  aria-label="Limpiar fondos"
                >{{ idsFondos.length }} ×</button>
              </div>
              <button type="button" class="picker" @click="abrirModal('fondos')">
                <span class="picker-text">{{ resumenFondos }}</span>
                <span class="picker-caret">▾</span>
              </button>
              <p class="step-help">Vacío = todos los fondos de su alcance.</p>
            </div>
          </div>

          <!-- 2. Subfondos (opcional) -->
          <div class="step" :class="{ 'step-disabled': !idsFondos.length }">
            <span class="step-num">2</span>
            <div class="step-body">
              <div class="step-top">
                <span class="step-name">Subfondos <em class="step-optional">opcional</em></span>
                <button
                  v-if="idsSubfondos.length"
                  class="clear-badge"
                  type="button"
                  @click="limpiarNivel('subfondos')"
                  aria-label="Limpiar subfondos"
                >{{ idsSubfondos.length }} ×</button>
              </div>
              <button
                type="button"
                class="picker"
                :disabled="!idsFondos.length"
                @click="abrirModal('subfondos')"
              >
                <span class="picker-text">{{ resumenSubfondos }}</span>
                <span class="picker-caret">▾</span>
              </button>
              <p class="step-help">
                {{ idsFondos.length ? 'Vacío = todas las rutas bajo los fondos.' : 'Primero elija fondo(s).' }}
              </p>
            </div>
          </div>

          <!-- 3. Secciones -->
          <div class="step" :class="{ 'step-disabled': !idsFondos.length }">
            <span class="step-num">3</span>
            <div class="step-body">
              <div class="step-top">
                <span class="step-name">Secciones</span>
                <button
                  v-if="idsSecciones.length"
                  class="clear-badge"
                  type="button"
                  @click="limpiarNivel('secciones')"
                  aria-label="Limpiar secciones"
                >{{ idsSecciones.length }} ×</button>
              </div>
              <button
                type="button"
                class="picker"
                :disabled="!idsFondos.length"
                @click="abrirModal('secciones')"
              >
                <span class="picker-text">{{ resumenSecciones }}</span>
                <span class="picker-caret">▾</span>
              </button>
              <p class="step-help">{{ textoAyudaSecciones }}</p>
            </div>
          </div>

          <!-- 4. Series -->
          <div class="step" :class="{ 'step-disabled': !idsSecciones.length }">
            <span class="step-num">4</span>
            <div class="step-body">
              <div class="step-top">
                <span class="step-name">Series</span>
                <button
                  v-if="idsSeries.length"
                  class="clear-badge"
                  type="button"
                  @click="limpiarNivel('series')"
                  aria-label="Limpiar series"
                >{{ idsSeries.length }} ×</button>
              </div>
              <button
                type="button"
                class="picker"
                :disabled="!idsSecciones.length"
                @click="abrirModal('series')"
              >
                <span class="picker-text">{{ resumenSeries }}</span>
                <span class="picker-caret">▾</span>
              </button>
              <p class="step-help">
                {{ idsSecciones.length ? 'Solo de las secciones elegidas. Vacío = todas.' : 'Primero elija sección(es).' }}
              </p>
            </div>
          </div>

          <!-- 5. Subseries (opcional) -->
          <div class="step" :class="{ 'step-disabled': !idsSeries.length }">
            <span class="step-num">5</span>
            <div class="step-body">
              <div class="step-top">
                <span class="step-name">Subseries <em class="step-optional">opcional</em></span>
                <button
                  v-if="idsSubseries.length"
                  class="clear-badge"
                  type="button"
                  @click="limpiarNivel('subseries')"
                  aria-label="Limpiar subseries"
                >{{ idsSubseries.length }} ×</button>
              </div>
              <button
                type="button"
                class="picker"
                :disabled="!idsSeries.length"
                @click="abrirModal('subseries')"
              >
                <span class="picker-text">{{ resumenSubseries }}</span>
                <span class="picker-caret">▾</span>
              </button>
              <p class="step-help">
                {{ idsSeries.length ? 'Vacío = todas (incluye sin subserie).' : 'Primero elija serie(s).' }}
              </p>
            </div>
          </div>
        </div>
      </aside>

      <!-- Resultado -->
      <section class="result card-surface">
        <div class="result-head">
          <div class="result-meta">
            <span class="result-scope" :class="alcance === 'global' ? 'scope-global' : 'scope-dep'">
              {{ alcance === 'global' ? 'Alcance global' : 'Su dependencia' }}
            </span>
            <span v-if="filtroResumen" class="result-filtro">{{ filtroResumen }}</span>
          </div>
          <!-- Segmented control tipo de gráfica -->
          <div class="segmented" role="group" aria-label="Tipo de gráfica">
            <button
              v-for="t in tiposGrafica"
              :key="t.value"
              type="button"
              class="seg-btn"
              :class="{ 'seg-on': tipoGrafica === t.value }"
              @click="tipoGrafica = t.value"
            >
              <span class="seg-ico">{{ t.ico }}</span>{{ t.label }}
            </button>
          </div>
        </div>

        <!-- Loading skeleton -->
        <div v-if="cargando" class="chart-wrap">
          <div class="skeleton-chart"></div>
        </div>

        <!-- Empty state -->
        <div v-else-if="!tieneDatos" class="empty">
          <div class="empty-ico">▤</div>
          <p class="empty-title">No hay datos para esta combinación</p>
          <p class="empty-sub">Ajuste los tipos de expediente o cambie los niveles de catálogo seleccionados.</p>
        </div>

        <!-- Gráfica + tabla -->
        <div v-else class="chart-and-legend">
          <div class="chart-wrap">
            <DashboardStatsChart
              :key="tipoGrafica + '-' + chartKey"
              ref="chartCmp"
              :type="tipoGrafica"
              :chart-data="chartData"
            />
          </div>

          <div class="legend">
            <div class="legend-head">
              <span>Categoría</span>
              <span class="legend-num">Cantidad</span>
              <span class="legend-num">%</span>
            </div>
            <ul class="legend-list">
              <li v-for="(lab, i) in labels" :key="i" class="legend-row">
                <span class="legend-name">
                  <span class="legend-swatch" :style="{ background: colorFor(lab, i) }"></span>
                  {{ lab }}
                </span>
                <span class="legend-num">{{ (data[i] ?? 0).toLocaleString('es-MX') }}</span>
                <span class="legend-num legend-pct">{{ porcentaje(data[i]) }}</span>
              </li>
            </ul>
            <div class="legend-total">
              <span>Total</span>
              <span class="legend-num">{{ total.toLocaleString('es-MX') }}</span>
              <span class="legend-num">100%</span>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Modal selección -->
    <div v-if="modalAbierto" class="modal-backdrop-custom" @click.self="cerrarModal">
      <div class="modal-panel card-surface" role="dialog" aria-modal="true">
        <div class="modal-head">
          <strong>{{ modalTitulo }}</strong>
          <button type="button" class="btn-close-x" aria-label="Cerrar" @click="cerrarModal">×</button>
        </div>

        <div class="modal-search">
          <span class="search-ico">⌕</span>
          <input
            v-model="modalBusqueda"
            type="search"
            class="search-input"
            placeholder="Buscar por nombre o código…"
          >
        </div>

        <div class="modal-body-scroll">
          <p v-if="!modalOpcionesFiltradas.length" class="empty-mini">
            {{ modalOpciones.length ? 'Sin coincidencias para la búsqueda.' : 'No hay opciones disponibles para este nivel.' }}
          </p>
          <label
            v-for="item in modalOpcionesFiltradas"
            :key="item.id"
            class="opt-row"
            :class="{ 'opt-on': modalDraft.includes(String(item.id)) }"
          >
            <input
              class="opt-check"
              type="checkbox"
              :value="String(item.id)"
              v-model="modalDraft"
            >
            <span class="opt-name">
              {{ item.nombre }}
              <span v-if="item.codigo" class="opt-code">{{ item.codigo }}</span>
            </span>
          </label>
        </div>

        <div class="modal-foot">
          <div class="foot-left">
            <button type="button" class="btn-ghost" @click="modalSeleccionarTodo">Marcar todos</button>
            <button type="button" class="btn-ghost" @click="modalDraft = []">Limpiar</button>
            <span class="foot-count">{{ modalDraft.length }} seleccionado(s)</span>
          </div>
          <div class="foot-right">
            <button type="button" class="btn-ghost" @click="cerrarModal">Cancelar</button>
            <button type="button" class="btn-apply" @click="aplicarModal">Aplicar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import DashboardStatsChart from '../components/DashboardStatsChart.vue'

// Colores semánticos fijos por tipo de expediente (consistentes en KPIs, chips, leyenda y gráfica)
// Paleta corporativa: 7421C #440412 · 1945C #AB0A3D · Black 7C #3D3935 · 125C #B68400 · 401C #B0ABA1
const ESTADO_COLORS = {
  en_tramite: '#AB0A3D',      // 1945 C
  en_concentracion: '#B68400', // 125 C
  en_historico: '#440412',     // 7421 C
}
// Paleta para dimensiones de catálogo (fondos/subfondos/secciones/series/subseries)
const PALETTE = [
  '#AB0A3D', '#B68400', '#440412', '#3D3935', '#8F8A80',
  '#D9A94A', '#7A1F33', '#6B675F', '#D4667E', '#B0ABA1',
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
        { value: 'en_tramite', label: 'En trámite', color: ESTADO_COLORS.en_tramite },
        { value: 'en_concentracion', label: 'Concentración', color: ESTADO_COLORS.en_concentracion },
        { value: 'en_historico', label: 'Histórico', color: ESTADO_COLORS.en_historico },
      ],
      tiposGrafica: [
        { value: 'pie', label: 'Pastel', ico: '◕' },
        { value: 'bar', label: 'Barras', ico: '▮' },
        { value: 'polarArea', label: 'Polar', ico: '◎' },
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
      modalBusqueda: '',
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
    modalOpcionesFiltradas() {
      const q = this.modalBusqueda.trim().toLowerCase()
      if (!q) return this.modalOpciones
      return this.modalOpciones.filter((i) =>
        String(i.nombre || '').toLowerCase().includes(q) ||
        String(i.codigo || '').toLowerCase().includes(q)
      )
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
    // Desglose por estado para las KPIs (cuando la gráfica viene partida por tipo)
    kpiPorEstado() {
      const map = {}
      this.opcionesEstado.forEach((opt) => {
        const idx = this.labels.findIndex((l) =>
          String(l).toLowerCase().includes(opt.label.toLowerCase())
        )
        if (idx !== -1) map[opt.value] = this.data[idx] ?? 0
      })
      // Si solo hay un estado activo y la dimensión no es por estado, todo el total es de ese estado
      if (Object.keys(map).length === 0 && this.estados.length === 1) {
        map[this.estados[0]] = this.total
      }
      return map
    },
    chartData() {
      const colors = this.labels.map((lab, i) => this.colorFor(lab, i))
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
    // Color consistente: por estado si el label coincide con un tipo, si no, paleta por índice
    colorFor(label, index) {
      const opt = this.opcionesEstado.find((o) =>
        String(label).toLowerCase().includes(o.label.toLowerCase())
      )
      return opt ? opt.color : PALETTE[index % PALETTE.length]
    },
    porcentaje(valor) {
      if (!this.total || !valor) return '0%'
      return `${Math.round((valor / this.total) * 100)}%`
    },
    resumenSeleccion(ids, catalogo, vacioLabel) {
      if (!ids.length) return vacioLabel
      if (ids.length === 1) {
        const item = catalogo.find((c) => String(c.id) === String(ids[0]))
        return item?.nombre || '1 seleccionado'
      }
      return `${ids.length} seleccionados`
    },
    limpiarNivel(nivel) {
      if (nivel === 'fondos') {
        this.idsFondos = []
        this.limpiarDesdeSubfondos()
      } else if (nivel === 'subfondos') {
        this.limpiarDesdeSubfondos()
      } else if (nivel === 'secciones') {
        this.limpiarDesdeSecciones()
      } else if (nivel === 'series') {
        this.limpiarDesdeSeries()
      } else if (nivel === 'subseries') {
        this.limpiarDesdeSubseries()
      }
      this.cargarEstadisticas()
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
    abrirModal(nivel) {
      this.modalNivel = nivel
      this.modalBusqueda = ''
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
      this.modalBusqueda = ''
    },
    modalSeleccionarTodo() {
      this.modalDraft = this.modalOpcionesFiltradas.map((i) => String(i.id))
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
  --bg: #f7f5f2;
  --surface: #ffffff;
  --ink: #3D3935;
  --ink-soft: #78736a;
  --line: #e4e0d8;
  --brand: #AB0A3D;
  --brand-dark: #440412;
  --brand-soft: #f7e5eb;
  --gold: #B68400;
  --gold-soft: #faf1dd;
  --stone: #B0ABA1;
  max-width: 1240px;
  margin: 0 auto;
  padding: 1.75rem 1rem 3rem;
  color: var(--ink);
}

/* Encabezado */
.page-head {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 1.5rem;
}
.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 0 .25rem;
  letter-spacing: -.01em;
}
.page-sub { margin: 0; color: var(--ink-soft); font-size: .9rem; line-height: 1.5; }
.btn-download {
  display: inline-flex; align-items: center; gap: .1rem;
  background: var(--brand); color: #fff; border: none;
  padding: .6rem 1.1rem; border-radius: .6rem; font-weight: 600; font-size: .9rem;
  cursor: pointer; transition: background .15s, opacity .15s;
}
.btn-download .ico { font-size: 1rem; margin-right: .4rem; }
.btn-download:hover:not(:disabled) { background: var(--brand-dark); }
.btn-download:disabled { opacity: .45; cursor: not-allowed; }

/* KPIs */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.kpi {
  --kpi-color: var(--brand);
  position: relative;
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: .85rem;
  padding: 1rem 1.1rem;
  display: flex; flex-direction: column; gap: .15rem;
  box-shadow: 0 1px 2px rgba(20, 30, 60, .04);
}
.kpi-total {
  background: linear-gradient(135deg, var(--brand-dark), var(--brand));
  border: none; color: #fff;
}
.kpi-total .kpi-label, .kpi-total .kpi-foot { color: rgba(255,255,255,.82); }
.kpi-dot {
  width: 10px; height: 10px; border-radius: 3px; background: var(--kpi-color);
  position: absolute; top: 1.15rem; right: 1.1rem;
}
.kpi-label { font-size: .78rem; color: var(--ink-soft); font-weight: 600; text-transform: uppercase; letter-spacing: .03em; }
.kpi-value { font-size: 1.7rem; font-weight: 700; line-height: 1.1; }
.kpi-foot { font-size: .78rem; color: var(--ink-soft); }
.kpi-off { opacity: .5; }

/* Layout principal */
.layout {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 1rem;
  align-items: start;
}
.card-surface {
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: .95rem;
  box-shadow: 0 1px 2px rgba(20, 30, 60, .04);
}
.filters { padding: 1.25rem; }
.panel-title { font-size: 1rem; font-weight: 700; margin: 0 0 1rem; }
.field-label { display: block; font-size: .82rem; font-weight: 600; margin-bottom: .55rem; }
.field-help { font-size: .76rem; color: var(--ink-soft); margin: .55rem 0 0; line-height: 1.4; }

/* Chips de estado */
.chips { display: flex; flex-wrap: wrap; gap: .5rem; }
.chip {
  --chip-color: var(--brand);
  display: inline-flex; align-items: center; gap: .45rem;
  border: 1px solid var(--line); border-radius: 2rem;
  padding: .4rem .8rem; font-size: .82rem; font-weight: 600;
  cursor: pointer; user-select: none; color: var(--ink-soft);
  transition: all .15s;
  position: relative;
}
.chip-input { position: absolute; opacity: 0; width: 0; height: 0; }
.chip-dot { width: 9px; height: 9px; border-radius: 50%; background: var(--chip-color); opacity: .35; }
.chip-on {
  color: var(--chip-color); border-color: var(--chip-color);
  background: color-mix(in srgb, var(--chip-color) 10%, #fff);
}
.chip-on .chip-dot { opacity: 1; }

.sep { border: none; border-top: 1px solid var(--line); margin: 1.25rem 0; }

/* Stepper jerárquico */
.steps { display: flex; flex-direction: column; gap: .9rem; position: relative; }
.step { display: flex; gap: .7rem; }
.step-num {
  flex: none;
  width: 26px; height: 26px; border-radius: 50%;
  background: var(--brand-soft); color: var(--brand);
  display: grid; place-items: center; font-size: .8rem; font-weight: 700;
}
.step-body { flex: 1; min-width: 0; }
.step-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: .35rem; gap: .5rem; }
.step-name { font-size: .85rem; font-weight: 600; }
.step-optional {
  font-style: normal; font-weight: 500; font-size: .68rem; color: var(--ink-soft);
  margin-left: .35rem; text-transform: uppercase; letter-spacing: .02em;
}
.step-help { font-size: .74rem; color: var(--ink-soft); margin: .35rem 0 0; line-height: 1.4; }
.step-disabled { opacity: .5; }
.step-disabled .step-num { background: #ece8e1; color: var(--ink-soft); }
.clear-badge {
  flex: none;
  border: none; background: var(--brand-soft); color: var(--brand);
  font-size: .72rem; font-weight: 700; border-radius: 1rem;
  padding: .1rem .5rem; cursor: pointer;
}
.clear-badge:hover { background: #dbe6fb; }
.picker {
  width: 100%; text-align: left;
  border: 1px solid var(--line); background: #fbfcfe; border-radius: .6rem;
  padding: .55rem .75rem; font-size: .85rem; color: var(--ink);
  display: flex; align-items: center; justify-content: space-between; gap: .5rem;
  cursor: pointer; transition: border-color .15s;
}
.picker:hover:not(:disabled) { border-color: var(--brand); }
.picker:disabled { cursor: not-allowed; color: var(--ink-soft); background: #f5f6f9; }
.picker-text { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.picker-caret { color: var(--ink-soft); font-size: .7rem; }

/* Resultado */
.result { padding: 1.25rem; }
.result-head {
  display: flex; flex-wrap: wrap; gap: .75rem;
  justify-content: space-between; align-items: center; margin-bottom: 1rem;
}
.result-meta { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
.result-scope { font-size: .74rem; font-weight: 700; padding: .2rem .6rem; border-radius: 1rem; }
.scope-global { background: var(--gold-soft); color: var(--gold); }
.scope-dep { background: var(--brand-soft); color: var(--brand); }
.result-filtro { font-size: .8rem; color: var(--ink-soft); }

/* Segmented control */
.segmented { display: inline-flex; background: #ece8e1; border-radius: .6rem; padding: .2rem; gap: .15rem; }
.seg-btn {
  border: none; background: transparent; color: var(--ink-soft);
  font-size: .82rem; font-weight: 600; padding: .35rem .75rem; border-radius: .45rem;
  cursor: pointer; display: inline-flex; align-items: center; gap: .35rem;
}
.seg-ico { font-size: .9rem; }
.seg-on { background: #fff; color: var(--brand); box-shadow: 0 1px 2px rgba(20,30,60,.1); }

/* Gráfica + leyenda */
.chart-and-legend {
  display: grid; grid-template-columns: 1fr 300px; gap: 1.5rem; align-items: center;
}
.chart-wrap { min-height: 340px; display: flex; align-items: center; justify-content: center; }
.skeleton-chart {
  width: 260px; height: 260px; border-radius: 50%;
  background: linear-gradient(100deg, #ece8e1 30%, #f7f5f2 50%, #ece8e1 70%);
  background-size: 200% 100%; animation: shimmer 1.3s infinite;
}
@keyframes shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }

.legend { border: 1px solid var(--line); border-radius: .7rem; overflow: hidden; }
.legend-head, .legend-row, .legend-total {
  display: grid; grid-template-columns: 1fr auto auto; gap: .75rem; align-items: center;
  padding: .55rem .85rem;
}
.legend-head { background: #f7f9fc; font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; color: var(--ink-soft); font-weight: 700; }
.legend-list { list-style: none; margin: 0; padding: 0; max-height: 260px; overflow-y: auto; }
.legend-row { font-size: .84rem; border-top: 1px solid var(--line); }
.legend-name { display: flex; align-items: center; gap: .5rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.legend-swatch { width: 11px; height: 11px; border-radius: 3px; flex: none; }
.legend-num { text-align: right; font-variant-numeric: tabular-nums; min-width: 44px; }
.legend-pct { color: var(--ink-soft); }
.legend-total { border-top: 2px solid var(--line); font-weight: 700; background: #f7f9fc; }

/* Empty state */
.empty { text-align: center; padding: 3rem 1rem; }
.empty-ico { font-size: 2.5rem; color: var(--stone); margin-bottom: .5rem; }
.empty-title { font-weight: 600; margin: 0 0 .25rem; }
.empty-sub { color: var(--ink-soft); font-size: .85rem; margin: 0; }
.empty-mini { color: var(--ink-soft); text-align: center; padding: 1.5rem 0; margin: 0; }

/* Modal */
.modal-backdrop-custom {
  position: fixed; inset: 0; z-index: 1055;
  background: rgba(15, 22, 40, .5); backdrop-filter: blur(2px);
  display: flex; align-items: center; justify-content: center; padding: 1rem;
}
.modal-panel { width: min(540px, 100%); max-height: min(82vh, 680px); display: flex; flex-direction: column; }
.modal-head { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--line); }
.btn-close-x { border: none; background: none; font-size: 1.5rem; line-height: 1; color: var(--ink-soft); cursor: pointer; }
.modal-search { position: relative; padding: .85rem 1.25rem; border-bottom: 1px solid var(--line); }
.search-ico { position: absolute; left: 1.7rem; top: 50%; transform: translateY(-50%); color: var(--ink-soft); }
.search-input {
  width: 100%; border: 1px solid var(--line); border-radius: .55rem;
  padding: .5rem .75rem .5rem 2rem; font-size: .88rem;
}
.search-input:focus { outline: none; border-color: var(--brand); }
.modal-body-scroll { overflow-y: auto; padding: .5rem .75rem; flex: 1; }
.opt-row {
  display: flex; align-items: center; gap: .65rem;
  padding: .55rem .6rem; border-radius: .5rem; cursor: pointer; font-size: .88rem;
}
.opt-row:hover { background: #f5f7fb; }
.opt-on { background: var(--brand-soft); }
.opt-check { width: 16px; height: 16px; accent-color: var(--brand); }
.opt-name { display: flex; align-items: baseline; gap: .4rem; }
.opt-code { font-size: .75rem; color: var(--ink-soft); }
.modal-foot { display: flex; flex-wrap: wrap; gap: .5rem; justify-content: space-between; align-items: center; padding: .85rem 1.25rem; border-top: 1px solid var(--line); }
.foot-left, .foot-right { display: flex; align-items: center; gap: .5rem; }
.foot-count { font-size: .78rem; color: var(--ink-soft); }
.btn-ghost {
  border: 1px solid var(--line); background: #fff; color: var(--ink);
  font-size: .82rem; font-weight: 600; padding: .45rem .8rem; border-radius: .5rem; cursor: pointer;
}
.btn-ghost:hover { background: #f5f7fb; }
.btn-apply {
  border: none; background: var(--brand); color: #fff;
  font-size: .82rem; font-weight: 600; padding: .45rem 1rem; border-radius: .5rem; cursor: pointer;
}
.btn-apply:hover { background: var(--brand-dark); }

/* Responsive */
@media (max-width: 992px) {
  .kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .layout { grid-template-columns: 1fr; }
  .chart-and-legend { grid-template-columns: 1fr; }
}
@media (max-width: 520px) {
  .kpi-grid { grid-template-columns: 1fr; }
}
</style>