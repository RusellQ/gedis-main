<template>
  <div class="container py-4">

    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <label class="form-label">Estado</label>
        <select v-model="filtros.estado" class="form-select">
          <option value="">Todos</option>
          <option value="en_tramite">En trámite</option>
          <option value="en_concentracion">En concentración</option>
          <option value="en_historico">Histórico</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Fecha de apertura (inicio)</label>
        <input type="date" v-model="filtros.fecha_inicio" class="form-control" />
      </div>

      <div class="col-md-4">
        <label class="form-label">Fecha de apertura (fin)</label>
        <input type="date" v-model="filtros.fecha_fin" class="form-control" />
      </div>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <label class="form-label">Fondo</label>
        <select v-model="filtros.id_fondo" class="form-select">
          <option value="">Todos</option>
          <option v-for="item in fondos" :key="item.id" :value="item.id">{{ item.nombre }}</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Subfondo</label>
        <select v-model="filtros.id_subfondo" class="form-select">
          <option value="">Todos</option>
          <option v-for="item in subfondos" :key="item.id" :value="item.id">{{ item.nombre }}</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Sección</label>
        <select v-model="filtros.id_seccion" class="form-select">
          <option value="">Todos</option>
          <option v-for="item in secciones" :key="item.id" :value="item.id">{{ item.nombre }}</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Serie</label>
        <select v-model="filtros.id_serie" class="form-select">
          <option value="">Todos</option>
          <option v-for="item in series" :key="item.id" :value="item.id">{{ item.nombre }}</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Subserie</label>
        <select v-model="filtros.id_subserie" class="form-select">
          <option value="">Todos</option>
          <option v-for="item in subseries" :key="item.id" :value="item.id">{{ item.nombre }}</option>
        </select>
      </div>
    </div>

    <div class="mb-4">
      <button @click="obtenerExpedientes" class="btn btn-primary me-2">Filtrar</button>
      <button @click="descargarPDF" class="btn btn-success">Generar PDF</button>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-sm align-middle">
        <thead class="table-light">
          <tr>
            <th>Clave</th>
            <th>Nombre</th>
            <th>Estado</th>
            <th>Clasificación</th>
            <th>Caracter</th>
            <th>Fecha Apertura</th>
            <th>Fecha Cierre</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="exp in expedientes" :key="exp.id">
            <td>{{ exp.clave }}</td>
            <td>{{ exp.nombre }}</td>
            <td>{{ traducirEstado(exp.estado) }}</td>
            <td>{{ exp.clasificacion }}</td>
            <td>{{ exp.caracter }}</td>
            <td>{{ exp.fecha_apertura }}</td>
            <td>{{ exp.fecha_cierre }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      filtros: {
        estado: '',
        fecha_inicio: '',
        fecha_fin: '',
        id_fondo: '',
        id_subfondo: '',
        id_seccion: '',
        id_serie: '',
        id_subserie: ''
      },
      expedientes: [],
      fondos: [],
      subfondos: [],
      secciones: [],
      series: [],
      subseries: []
    }
  },
  methods: {
    async obtenerExpedientes() {
      const { data } = await axios.get('/reportes/filtrar', { params: this.filtros })
      this.expedientes = data
    },
    traducirEstado(estado) {
      if (estado === 'en_tramite') return 'En trámite'
      if (estado === 'en_concentracion') return 'En concentración'
      if (estado === 'en_historico') return 'Histórico'
      return estado
    },
    async descargarPDF() {
      const form = document.createElement('form')
      form.method = 'GET'
      form.action = '/reportes/pdf' 
      form.target = '_blank'

      const campos = { ...this.filtros }

      for (const key in campos) {
        const input = document.createElement('input')
        input.type = 'hidden'
        input.name = key
        input.value = campos[key]
        form.appendChild(input)
      }

      document.body.appendChild(form)
      form.submit()
      form.remove()
    }
  },
  async mounted() {
    const [fondos, subfondos, secciones, series, subseries] = await Promise.all([
      axios.get('/catalogos/fondos'),
      axios.get('/catalogos/subfondos'),
      axios.get('/catalogos/secciones'),
      axios.get('/catalogos/series'),
      axios.get('/catalogos/subseries')
    ])
    this.fondos = fondos.data
    this.subfondos = subfondos.data
    this.secciones = secciones.data
    this.series = series.data
    this.subseries = subseries.data
  }
}
</script>
