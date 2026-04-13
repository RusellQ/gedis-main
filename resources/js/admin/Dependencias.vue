<template>
  <div class="container mt-4">
    <div class="card shadow mb-4">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Crear Nueva Dependencia</h5>
      </div>
      <div class="card-body">
        <form @submit.prevent="guardarDependencia">
          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input v-model="form.nombre" type="text" class="form-control" id="nombre">
            <div class="text-danger" v-if="errores.nombre">{{ errores.nombre[0] }}</div>
          </div>

          <div class="mb-3">
            <label for="clave" class="form-label">Clave</label>
            <input v-model="form.clave" type="text" class="form-control" id="clave">
            <div class="text-danger" v-if="errores.clave">{{ errores.clave[0] }}</div>
          </div>

          <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea v-model="form.descripcion" class="form-control" id="descripcion"></textarea>
          </div>

          <button type="submit" class="btn btn-success">Guardar</button>
        </form>
      </div>
    </div>

    <div class="card shadow">
      <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">Dependencias Registradas</h5>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Clave</th>
              <th>Descripción</th>
              <th>Fecha de creación</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="dep in dependencias" :key="dep.id">
              <td>{{ dep.id }}</td>
              <td>{{ dep.nombre }}</td>
              <td>{{ dep.clave }}</td>
              <td>{{ dep.descripcion }}</td>
              <td>{{ new Date(dep.created_at).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
        <p v-if="dependencias.length === 0" class="text-muted">No hay dependencias registradas.</p>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from 'sweetalert2'
import axios from 'axios'

export default {
  data() {
    return {
      form: {
        nombre: '',
        clave: '',
        descripcion: ''
      },
      errores: {},
      dependencias: []
    }
  },
  mounted() {
    this.obtenerDependencias()
  },
  methods: {
    async guardarDependencia() {
      this.errores = {}
      try {
        const response = await axios.post('/creardependencia', this.form)
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: response.data.message,
        })
        this.form = { nombre: '', clave: '', descripcion: '' }
        this.obtenerDependencias()
      } catch (error) {
        if (error.response && error.response.status === 422) {
          this.errores = error.response.data.errors
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error inesperado.',
          })
        }
      }
    },
    async obtenerDependencias() {
      try {
        const response = await axios.get('/indexdependencias')
        this.dependencias = response.data
      } catch (error) {
        console.error('Error al cargar dependencias:', error)
      }
    }
  }
}
</script>

<style scoped>
.text-danger {
  font-size: 0.875rem;
}
</style>
