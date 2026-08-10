<template>
  <div class="p-4 bg-light min-vh-100">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
      <div>
        <h2 class="h4">Expedientes Vencidos</h2>
        <p class="text-muted mb-0">Lista de expedientes cuya vigencia documental ha vencido según la serie asociada.</p>
      </div>
      <div class="text-end">
        <button @click="generarPDF" class="btn btn-danger btn-sm me-2">Exportar PDF</button>
        <span class="badge bg-danger">Total: {{ expedientesFiltrados.length }}</span>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-12 col-md-4">
        <label class="form-label">Buscar</label>
        <input v-model="busqueda" type="text" placeholder="Nombre, código, clave, serie..." class="form-control" />
      </div>

      <div class="col-12 col-md-2">
        <label class="form-label">Desde cierre</label>
        <input v-model="fechaDesde" type="date" class="form-control" />
      </div>

      <div class="col-12 col-md-2">
        <label class="form-label">Hasta cierre</label>
        <input v-model="fechaHasta" type="date" class="form-control" />
      </div>

      <div class="col-12 col-md-2">
        <label class="form-label">Serie</label>
        <select v-model="serieSeleccionada" class="form-select">
          <option value="">Todas</option>
          <option v-for="serie in seriesDisponibles" :key="serie.id" :value="serie.id">
            {{ serie.codigo }} - {{ serie.nombre }}
          </option>
        </select>
      </div>

      <div class="col-12 col-md-2">
        <label class="form-label">Valor documental</label>
        <select v-model="valorDocumentalSeleccionado" class="form-select">
          <option value="">Todos</option>
          <option value="Administrativo">Administrativo</option>
          <option value="Legal">Legal</option>
          <option value="Contable">Contable</option>
        </select>
      </div>
    </div>

    <div class="row g-3">
      <div v-if="expedientesFiltrados.length === 0" class="col-12">
        <div class="alert alert-warning">No se encontraron expedientes vencidos con esos criterios.</div>
      </div>

      <div v-for="expediente in expedientesFiltrados" :key="expediente.id" class="col-12">
        <div class="card shadow-sm border">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2 gap-3">
              <div>
                <h5 class="card-title mb-1">{{ expediente.nombre }}</h5>
                <p class="mb-0 text-muted">{{ expediente.clave }}</p>
              </div>
              <div class="text-end">
                <span class="badge bg-danger">Vencido</span>
                <div class="small text-muted">Cierre: {{ formatFecha(expediente.fecha_cierre) }}</div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <p class="mb-1"><strong>Código:</strong> {{ expediente.codigo }}</p>
                <p class="mb-1"><strong>Serie:</strong> {{ expediente.serie || 'N/A' }}</p>
                <p class="mb-1"><strong>Subserie:</strong> {{ expediente.subserie || 'N/A' }}</p>
              </div>
              <div class="col-md-4">
                <p class="mb-1"><strong>Fondo:</strong> {{ expediente.fondo }}</p>
                <p class="mb-1"><strong>Sección:</strong> {{ expediente.seccion }}</p>
                <p class="mb-1"><strong>Valor documental:</strong> {{ formatearValorDocumental(expediente.serie_detalle) }}</p>
              </div>
              <div class="col-md-4">
                <p class="mb-1"><strong>Vigencia AT:</strong> {{ formatearValorSimple(expediente.serie_detalle?.plazo_at) }}</p>
                <p class="mb-1"><strong>Vigencia AC:</strong> {{ formatearValorSimple(expediente.serie_detalle?.plazo_ac) }}</p>
                <p class="mb-1"><strong>Días vencidos:</strong> {{ diasVencidos(expediente.fecha_cierre) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
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
    diasVencidos(fechaCierre) {
      if (!fechaCierre) return 'N/A'
      const hoy = new Date()
      const cierre = new Date(fechaCierre)
      const diff = hoy - cierre
      const dias = Math.floor(diff / (1000 * 60 * 60 * 24))
      return dias >= 0 ? `${dias} días` : '0 días'
    }
  }
}
</script>

<style scoped>
.card-title {
  font-size: 1.05rem;
}
</style>
