<template>
  <div class="container mt-4">
    <div class="card shadow mb-4">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Crear Nuevo Departamento</h5>
      </div>
      <div class="card-body">
        <form @submit.prevent="guardarDepartamento">
          <div class="mb-3">
            <label class="form-label">Dependencia</label>
            <select
              v-model="form.id_dependencia"
              class="form-select"
              :disabled="!esRoot"
            >
              <option value="">Seleccione una dependencia</option>
              <option v-for="dep in dependencias" :key="dep.id" :value="dep.id">
                {{ dep.nombre }} ({{ dep.clave }})
              </option>
            </select>
            <div class="text-danger" v-if="errores.id_dependencia">{{ errores.id_dependencia[0] }}</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input v-model="form.nombre" type="text" class="form-control" />
            <div class="text-danger" v-if="errores.nombre">{{ errores.nombre[0] }}</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Clave</label>
            <input v-model="form.clave" type="text" class="form-control" />
            <div class="text-danger" v-if="errores.clave">{{ errores.clave[0] }}</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea v-model="form.descripcion" class="form-control"></textarea>
          </div>

          <button type="submit" class="btn btn-success">Guardar</button>
        </form>
      </div>
    </div>

    <div class="card shadow">
      <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">Departamentos Registrados</h5>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Dependencia</th>
              <th>Nombre</th>
              <th>Clave</th>
              <th>Descripción</th>
              <th>Creado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="dep in departamentos" :key="dep.id">
              <td>{{ dep.id }}</td>
              <td>{{ dep.dependencia?.nombre }}</td>
              <td>{{ dep.nombre }}</td>
              <td>{{ dep.clave }}</td>
              <td>{{ dep.descripcion }}</td>
              <td>{{ new Date(dep.created_at).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
        <p v-if="departamentos.length === 0" class="text-muted">No hay departamentos registrados.</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import Swal from 'sweetalert2'

export default {
  data() {
    return {
      usuario: null,
      esRoot: false,
      dependencias: [],
      departamentos: [],
      form: {
        id_dependencia: '',
        nombre: '',
        clave: '',
        descripcion: ''
      },
      errores: {}
    }
  },
  mounted() {
    this.obtenerUsuario()
  },
  methods: {
    async obtenerUsuario() {
      try {
        const res = await axios.get('/currentuser')
        this.usuario = res.data
        this.esRoot = this.usuario.roles?.includes('root') // o según cómo manejas los roles

        await this.obtenerDependencias()

        if (!this.esRoot && this.usuario.dependencia) {
          this.form.id_dependencia = this.usuario.dependencia.id
        }
        this.obtenerDepartamentos()
      } catch (err) {
        console.error('Error al obtener el usuario', err)
      }
    },
    async obtenerDependencias() {
      try {
        const response = await axios.get('/indexdependencias')
        this.dependencias = response.data
      } catch (error) {
        console.error('Error al cargar dependencias', error)
      }
    },
    async obtenerDepartamentos() {
      try {
        const response = await axios.get('/indexdepartamentos')
        this.departamentos = response.data
      } catch (error) {
        console.error('Error al cargar departamentos', error)
      }
    },
    async guardarDepartamento() {
      this.errores = {}
      try {
        const response = await axios.post('/creardepartamento', this.form)
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: response.data.message,
        })
        this.form = { id_dependencia: this.esRoot ? '' : this.usuario.dependencia.id, nombre: '', clave: '', descripcion: '' }
        this.obtenerDepartamentos()
      } catch (error) {
        if (error.response && error.response.status === 422) {
          this.errores = error.response.data.errors
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error inesperado al guardar.',
          })
        }
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
