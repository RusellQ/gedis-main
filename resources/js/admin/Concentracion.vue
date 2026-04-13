<template>
  <div class="p-4 bg-light min-vh-100">
    <!-- Buscador -->
    <input v-model="busqueda" type="text" placeholder="Buscar expediente..." class="form-control mb-4" />

    <!-- Tarjetas de Expedientes -->
    <div class="row g-3">
      <div v-for="expediente in paginatedExpedientes" :key="expediente.id" class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border p-3 position-relative">
          <!-- Etiqueta de estado -->
          <span v-if="expediente.estado === 'en_tramite'" class="badge bg-primary position-absolute top-0 end-0 m-2">En
            trámite</span>
          <span v-else-if="expediente.estado === 'en_concentracion'"
            class="badge bg-warning text-dark position-absolute top-0 end-0 m-2">En concentración</span>
          <span v-else-if="expediente.estado === 'en_historico'"
            class="badge bg-success position-absolute top-0 end-0 m-2">Histórico</span>

          <!-- Contenido -->
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ expediente.nombre }}</h5>
            <p class="card-text mb-1"><strong>Fondo:</strong> {{ expediente.fondo }}</p>
            <p class="card-text mb-1"><strong>Subfondo:</strong> {{ expediente.subfondo ?? 'N/A' }}</p> <!-- NUEVO -->
            <p class="card-text mb-1"><strong>Sección:</strong> {{ expediente.seccion }}</p>
            <p class="card-text mb-1"><strong>Serie:</strong> {{ expediente.serie }}</p>
            <p class="card-text mb-1"><strong>Subserie:</strong> {{ expediente.subserie ?? 'N/A' }}</p> <!-- NUEVO -->
            <p class="card-text mb-1"><strong>Fecha Apertura:</strong> {{ expediente.fecha_apertura }}</p>
            <p class="card-text mb-1"><strong>Fecha Cierre:</strong> {{ expediente.fecha_cierre }}</p>
            <p class="card-text mb-1"><strong>Clave:</strong> {{ expediente.clave }}</p>
            <p class="card-text mb-1"><strong>Tiempo de Conservación:</strong> {{ expediente.tiempo_conservacion }}</p>
            <p class="card-text mb-1"><strong>No. de Legajos:</strong> {{ expediente.no_legajos }}</p>
            <p class="card-text mb-1"><strong>No. de Hojas:</strong> {{ expediente.no_hojas }}</p>
            <p class="card-text mb-1"><strong>No. de Caja:</strong> {{ expediente.no_caja }}</p>
            <p class="card-text mb-1"><strong>Ubicación Topográfica:</strong> {{ expediente.ubicacion_topografica }}</p>
            <p class="card-text mb-1"><strong>Clasificación:</strong> {{ expediente.clasificacion }}</p>
            <p class="card-text mb-1"><strong>Carácter:</strong> {{ expediente.caracter }}</p>
            <p class="card-text mb-1"><strong>Preservación:</strong> {{ expediente.preservacion }}</p>
            <p class="card-text mb-1"><strong>Observaciones:</strong> {{ expediente.observacion }}</p>

            <button v-if="!currentUserRoles.includes('lector')" @click="abrirModal(expediente)"
              class="btn btn-success btn-sm mt-4">
              Añadir Unidad Documental
            </button>

            <button class="btn btn-primary btn-sm mt-3" @click="mostrarModalUnidades(expediente)">
              Ver Unidades Documentales
            </button>
            <button v-if="!currentUserRoles.includes('lector')" @click="abrirModalEditar(expediente)"
              class="btn btn-secondary btn-sm mt-2">
              Editar Expediente
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modales (sin cambios) -->

    <!-- Modal para nueva unidad (estilo modal Serie) -->
    <div ref="modalUnidadDocumental" class="modal fade" tabindex="-1" aria-labelledby="modalUnidadDocumentalLabel"
      aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 id="modalUnidadDocumentalLabel" class="modal-title">Añadir Unidad Documental</h5>
            <button type="button" class="btn-close" @click="cerrarModal('unidad')" aria-label="Cerrar"></button>
          </div>
          <form @submit.prevent="guardarUnidadDocumental">
            <div class="modal-body">
              <select v-model="unidad.tipo" class="form-control mb-2" required>
                <option value="" disabled>Selecciona tipo</option>
                <option>Caratula del expediente</option>
                <option>Oficio</option>
                <option>Memorándum</option>
                <option>Informe</option>
                <option>Acta</option>
                <option>Carta</option>
                <option>Solicitud</option>
                <option>Contrato</option>
                <option>Dictamen</option>
                <option>Resolución</option>
                <option>Acuerdo</option>
                <option>Circular</option>
                <option>Certificado</option>
                <option>Factura</option>
                <option>Plano</option>
                <option>Fotografía</option>
                <option>Video</option>
                <option>Audio</option>
                <option>Anexo</option>
              </select> <input v-model="unidad.nombre" placeholder="Nombre" type="text" required
                class="form-control mb-2" />
              <textarea v-model="unidad.descripcion" placeholder="Descripción" class="form-control mb-2"></textarea>
              <input @change="handleArchivo" type="file" class="form-control" />
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success">
                Guardar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Visual de Unidades Documentales -->
    <div ref="modalVerUnidades" class="modal fade" tabindex="-1" aria-labelledby="modalVerUnidadesLabel"
      aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 id="modalVerUnidadesLabel" class="modal-title">Unidades Documentales</h5>
            <button type="button" class="btn-close" @click="cerrarModal('ver')" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div v-if="expedienteActual?.unidades_documentales?.length">
              <ul class="list-group list-group-flush small">
                <li v-for="unidad in expedienteActual.unidades_documentales" :key="unidad.id"
                  class="list-group-item px-0">
                  <strong>{{ unidad.tipo }}:</strong> {{ unidad.nombre }}
                  <span v-if="unidad.descripcion"> - {{ unidad.descripcion }}</span>
                  <a v-if="unidad.archivo" :href="`/storage/${unidad.archivo}`" target="_blank"
                    class="link-primary ms-2">
                    Ver archivo
                  </a>
                </li>
              </ul>
            </div>
            <div v-else>
              <p class="text-muted">Este expediente no tiene unidades documentales.</p>
            </div>
          </div>
        </div>
      </div>
    </div>


    <!-- Modal para editar expediente -->
    <div class="modal fade" ref="modalEditarExpediente" tabindex="-1" aria-labelledby="modalEditarLabel"
      aria-hidden="true" v-if="expedienteActual">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalEditarLabel">Editar Expediente </h5>
            <button type="button" class="btn-close" @click="cerrarModal('editar')" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="guardarCambios">

              <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control" v-model="expedienteActual.nombre" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Código</label>
                <input type="text" class="form-control" v-model="expedienteActual.codigo" required>
              </div>


              <div class="mb-3">
                <label class="form-label">Tiempo de Conservación (años)</label>
                <input type="number" class="form-control" v-model.number="expedienteActual.tiempo_conservacion" min="0">
              </div>

              <div class="mb-3">
                <label class="form-label">Número de Legajos</label>
                <input type="number" class="form-control" v-model.number="expedienteActual.no_legajos" min="0">
              </div>

              <div class="mb-3">
                <label class="form-label">Número de Hojas</label>
                <input type="number" class="form-control" v-model.number="expedienteActual.no_hojas" min="0">
              </div>

              <div class="mb-3">
                <label class="form-label">Ubicación Topográfica</label>
                <select v-model="expedienteActual.ubicacion_topografica" class="form-select" required>
                  <option value="">Selecciona una opción</option>
                  <option value="Dirección General">Dirección General</option>
                  <option value="Dirección Archivo del Ejecutivo">Dirección Archivo del Ejecutivo</option>
                  <option value="Departamento Administrativo">Departamento Administrativo</option>
                  <option value="Departamento de Normatividad Jurídica y Unidad de Transparencia">
                    Departamento de Normatividad Jurídica y Unidad de Transparencia
                  </option>
                  <option value="Departamento de Archivo Histórico">Departamento de Archivo Histórico</option>
                  <option value="Departamento de Biblioteca y Hemeroteca">Departamento de Biblioteca y Hemeroteca
                  </option>
                  <option value="Departamento de Conservación, Restauración y Encuadernación">
                    Departamento de Conservación, Restauración y Encuadernación
                  </option>
                  <option value="Departamento de Tecnologías de la Información y Comunicaciones">
                    Departamento de Tecnologías de la Información y Comunicaciones
                  </option>
                </select>
              </div>


              <div class="mb-3">
                <label class="form-label">Número de Caja</label>
                <input type="number" class="form-control" v-model.number="expedienteActual.no_caja" min="0">
              </div>

              <div class="mb-3">
                <label>Clasificacion</label>
                <select v-model="expedienteActual.clasificacion" class="form-select">
                  <option value="Pública">Pública</option>
                  <option value="Confidencial">Confidencial</option>
                  <option value="Reservada">Reservada</option>
                </select>
              </div>

              <div class="mb-3">
                <label>Caracter</label>
                <select v-model="expedienteActual.caracter" class="form-select">
                  <option value="Administrativo">Administrativo</option>
                  <option value="Legal">Legal</option>
                  <option value="Contable">Contable</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Observación</label>
                <input type="text" class="form-control" v-model="expedienteActual.observacion">
              </div>

              <div class="mb-3">
                <label class="form-label">Estado</label>
                <select class="form-select" v-model="expedienteActual.estado">
                  <option value="en_tramite">En trámite</option>
                  <option value="en_concentracion">En concentración</option>
                  <option value="en_historico">En histórico</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Fecha de Creación</label>
                <input type="date" class="form-control" v-model="expedienteActual.fecha_creacion">
              </div>

              <div class="mb-3">
                <label class="form-label">Fecha de Apertura</label>
                <input type="date" class="form-control" v-model="expedienteActual.fecha_apertura">
              </div>

              <div class="mb-3">
                <label class="form-label">Fecha de Cierre</label>
                <input type="date" class="form-control" v-model="expedienteActual.fecha_cierre">
              </div>

              <div class="mb-3">
                <label>Preservación</label>
                <select v-model="expedienteActual.preservacion" class="form-select">
                  <option value="Si">Si</option>
                  <option value="No">No</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Clave</label>
                <input type="text" class="form-control" v-model="expedienteActual.clave">
              </div>

              <!-- Footer -->
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="cerrarModal('editar')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Paginación -->
    <nav aria-label="Paginación" class="mt-4 d-flex justify-content-center align-items-center gap-3">
      <button @click="paginaActual--" :disabled="paginaActual === 1" class="btn btn-outline-secondary btn-sm">
        Anterior
      </button>
      <span>Página {{ paginaActual }} de {{ totalPaginas }}</span>
      <button @click="paginaActual++" :disabled="paginaActual === totalPaginas"
        class="btn btn-outline-secondary btn-sm">
        Siguiente
      </button>
    </nav>
  </div>
</template>

<script>
import axios from 'axios'
import { Modal } from 'bootstrap'

export default {
  name: 'ExpedientesPage',
  data() {
    return {
      currentUserRoles: [], // inicial vacío, luego carga real


      expedientes: [],
      busqueda: '',
      paginaActual: 1,
      porPagina: 3,
      modalUnidad: null,
      modalVerUnidades: null,
      expedienteActual: null,
      modalEditarExpediente: null,

      unidad: {
        tipo: '',
        nombre: '',
        descripcion: '',
        archivo: null
      }
    }
  },
  created() {
    this.cargarUsuarioActual();
  },
  computed: {
    expedientesFiltrados() {
      const texto = this.busqueda.toLowerCase()
      return this.expedientes
        .filter(e => e.estado === 'en_concentracion') // FILTRO
        .filter(e =>
          [e.nombre, e.codigo, e.fondo, e.seccion, e.serie]
            .filter(Boolean)
            .some(campo => campo.toLowerCase().includes(texto))
        )
    },
    totalPaginas() {
      return Math.ceil(this.expedientesFiltrados.length / this.porPagina)
    },
    paginatedExpedientes() {
      const start = (this.paginaActual - 1) * this.porPagina
      return this.expedientesFiltrados.slice(start, start + this.porPagina)
    }
  },
  watch: {
    busqueda() {
      this.paginaActual = 1
    }
  },
  methods: {
    abrirModal(expediente) {
      this.expedienteActual = expediente
      this.unidad = { tipo: '', nombre: '', descripcion: '', archivo: null }
      this.modalUnidad.show()
    },
    async cargarUsuarioActual() {
      try {
        const response = await axios.get('/currentuser');
        // Verifica si roles vienen como JSON string o array
        if (typeof response.data.roles === 'string') {
          this.currentUserRoles = JSON.parse(response.data.roles);
        } else {
          this.currentUserRoles = response.data.roles || [];
        }
        console.log('Roles del usuario:', this.currentUserRoles);
      } catch (error) {
        console.error('Error cargando usuario:', error);
      }
    },
    mostrarModalUnidades(expediente) {
      this.expedienteActual = expediente
      this.modalVerUnidades.show()
    },
    cerrarModal(tipo) {
      if (tipo === 'unidad') {
        this.modalUnidad.hide()
      } else if (tipo === 'ver') {
        this.modalVerUnidades.hide()
      } else if (tipo === 'editar') {
        this.modalEditar.hide()
      }
    },
    abrirModalEditar(expediente) {
      this.expedienteActual = expediente;
      this.$nextTick(() => {
        if (!this.modalEditar) {
          this.modalEditar = new bootstrap.Modal(this.$refs.modalEditarExpediente);
        }
        this.modalEditar.show();
      });
    },
    guardarCambios() {
      axios
        .put(`/editarexpedientes/${this.expedienteActual.id}`, this.expedienteActual)
        .then((response) => {
          alert('Expediente actualizado correctamente');
          this.modalEditar.hide();

        })
        .catch((error) => {
          console.error(error);
          alert('Error al actualizar el expediente');
        });
    },
    handleArchivo(event) {
      this.unidad.archivo = event.target.files[0] || null
    },
    async guardarUnidadDocumental() {
      const formData = new FormData()
      formData.append('id_expediente', this.expedienteActual.id)
      formData.append('tipo', this.unidad.tipo)
      formData.append('nombre', this.unidad.nombre)
      formData.append('descripcion', this.unidad.descripcion)
      if (this.unidad.archivo) {
        formData.append('archivo', this.unidad.archivo)
      }

      try {
        const response = await axios.post('/unidades-documentales', formData)

        if (!this.expedienteActual.unidades_documentales) {
          this.$set(this.expedienteActual, 'unidades_documentales', [])
        }
        this.expedienteActual.unidades_documentales.push(response.data)
        this.cerrarModal('unidad')
      } catch (error) {
        console.error('Error al guardar unidad documental:', error)
        alert('Ocurrió un error al guardar la unidad documental.')
      }
    }
  },
  async mounted() {
    if (this.$refs.modalUnidadDocumental) {
      this.modalUnidad = new Modal(this.$refs.modalUnidadDocumental)
    } else {
      console.error('No se encontró ref modalUnidadDocumental')
    }

    if (this.$refs.modalVerUnidades) {
      this.modalVerUnidades = new Modal(this.$refs.modalVerUnidades)
    } else {
      console.error('No se encontró ref modalVerUnidades')
    }

    try {
      const response = await axios.get('/expedientes')
      this.expedientes = response.data
    } catch (error) {
      console.error('Error al cargar expedientes:', error)
    }
  }
}
</script>

<style scoped>
.modal-open {
  overflow: hidden;
}
</style>
