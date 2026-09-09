<template>
  <div class="correspondencia-explorer card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
      <ul class="nav nav-tabs card-header-tabs">
        <li class="nav-item">
          <button 
            class="nav-link" 
            :class="{ active: tabActiva === 'correspondencias' }" 
            @click="tabActiva = 'correspondencias'"
          >
            <i class="fas fa-envelope me-1"></i> Correspondencia ({{ correspondencias.length }})
          </button>
        </li>
        <li class="nav-item">
          <button 
            class="nav-link" 
            :class="{ active: tabActiva === 'unidades' }" 
            @click="tabActiva = 'unidades'"
          >
            <i class="fas fa-file-pdf me-1"></i> Unidades Documentales / Anexos ({{ unidades.length }})
          </button>
        </li>
      </ul>
      <div class="btn-group btn-group-sm">
        <button 
          v-if="tabActiva === 'correspondencias'" 
          @click="abrirCrearCorrespondencia" 
          class="btn btn-primary"
          :disabled="!idExpediente"
        >
          <i class="fas fa-plus me-1"></i> Nueva Correspondencia
        </button>
        <button 
          v-if="tabActiva === 'unidades'" 
          @click="mostrarModalSubirUnidad = true" 
          class="btn btn-success"
          :disabled="!idExpediente"
        >
          <i class="fas fa-upload me-1"></i> Subir Anexo PDF
        </button>
      </div>
    </div>

    <div class="card-body">
      <!-- Indicador de carga -->
      <div v-if="cargando" class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Cargando...</span>
        </div>
      </div>

      <!-- Tab 1: Correspondencias -->
      <div v-else-if="tabActiva === 'correspondencias'">
        <div v-if="correspondencias.length === 0" class="alert alert-info text-center my-3">
          <i class="fas fa-info-circle me-2"></i> No hay documentos de correspondencia registrados en este expediente.
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Asunto</th>
                <th>Remitente</th>
                <th>Destinatario</th>
                <th>Estado</th>
                <th>Clasificación</th>
                <th>Fecha</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="corr in correspondencias" :key="corr.id">
                <td><strong>#{{ corr.numero_consecutivo }}</strong></td>
                <td>{{ corr.asunto }}</td>
                <td>{{ corr.remitente || '-' }}</td>
                <td>{{ corr.destinatario || '-' }}</td>
                <td>
                  <span :class="getEstadoClass(corr.estado)" class="badge">
                    {{ corr.estado.toUpperCase() }}
                  </span>
                </td>
                <td>
                  <span :class="getClasificacionClass(corr.clasificacion)" class="badge">
                    {{ corr.clasificacion.toUpperCase() }}
                  </span>
                </td>
                <td><small>{{ formatDate(corr.fecha_creacion) }}</small></td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm" role="group">
                    <button @click="verDetalle(corr.id)" class="btn btn-outline-info" title="Ver Documento">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button @click="editar(corr.id)" class="btn btn-outline-warning" :disabled="corr.estado !== 'borrador'" title="Editar">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button @click="firmar(corr.id)" class="btn btn-outline-success" :disabled="corr.estado !== 'borrador'" title="Firmar con SAT">
                      <i class="fas fa-pen-fancy"></i>
                    </button>
                    <button @click="descargarPDF(corr.id)" class="btn btn-outline-secondary" title="Descargar PDF">
                      <i class="fas fa-download"></i>
                    </button>
                    <button @click="eliminar(corr.id)" class="btn btn-outline-danger" :disabled="corr.estado !== 'borrador'" title="Eliminar">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab 2: Unidades Documentales / Anexos -->
      <div v-else-if="tabActiva === 'unidades'">
        <div v-if="unidades.length === 0" class="alert alert-info text-center my-3">
          <i class="fas fa-info-circle me-2"></i> No hay anexos o unidades documentales PDF subidas a este expediente.
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Archivo</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(unidad, index) in unidades" :key="unidad.id || index">
                <td>{{ index + 1 }}</td>
                <td><strong>{{ unidad.nombre }}</strong></td>
                <td><span class="badge bg-secondary">{{ unidad.tipo || 'Anexo' }}</span></td>
                <td>{{ unidad.descripcion || '-' }}</td>
                <td>
                  <a 
                    v-if="unidad.archivo" 
                    :href="'/storage/' + unidad.archivo" 
                    target="_blank" 
                    class="btn btn-sm btn-outline-danger"
                  >
                    <i class="fas fa-file-pdf me-1"></i> Ver PDF
                  </a>
                  <span v-else class="text-muted">Sin archivo</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Subir Anexo PDF -->
    <div v-if="mostrarModalSubirUnidad" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-upload me-2"></i>Subir Anexo Documental (PDF)</h5>
            <button type="button" class="btn-close" @click="mostrarModalSubirUnidad = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="subirUnidadDocumental">
              <div class="mb-3">
                <label class="form-label">Nombre del Documento *</label>
                <input v-model="nuevaUnidad.nombre" type="text" class="form-control" required placeholder="Ej. Oficio de respuesta" />
              </div>
              <div class="mb-3">
                <label class="form-label">Tipo *</label>
                <input v-model="nuevaUnidad.tipo" type="text" class="form-control" required placeholder="Ej. Anexo / Acuse / Memoria" />
              </div>
              <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea v-model="nuevaUnidad.descripcion" class="form-control" rows="2" placeholder="Notas sobre el documento"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Archivo PDF *</label>
                <input type="file" ref="archivoInput" @change="alSeleccionarArchivo" class="form-control" accept="application/pdf" required />
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" @click="mostrarModalSubirUnidad = false">Cancelar</button>
                <button type="submit" class="btn btn-success" :disabled="subiendoUnidad">
                  <span v-if="subiendoUnidad" class="spinner-border spinner-border-sm me-1"></span>
                  Subir PDF
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Editor modal de Correspondencia -->
    <CorrespondenciaEditor ref="modalEditor" @guardado="cargarDatos" />
  </div>
</template>

<script>
import CorrespondenciaEditor from './CorrespondenciaEditor.vue';

export default {
  name: 'CorrespondenciaExplorer',
  components: {
    CorrespondenciaEditor,
  },
  props: {
    idExpediente: {
      type: [Number, String],
      default: null,
    },
  },
  data() {
    return {
      tabActiva: 'correspondencias',
      correspondencias: [],
      unidades: [],
      cargando: false,
      mostrarModalSubirUnidad: false,
      subiendoUnidad: false,
      nuevaUnidad: {
        nombre: '',
        tipo: 'Anexo',
        descripcion: '',
        archivo: null,
      },
    };
  },
  watch: {
    idExpediente: {
      immediate: true,
      handler(nuevoId) {
        if (nuevoId) {
          this.cargarDatos();
        }
      },
    },
  },
  methods: {
    async cargarDatos() {
      if (!this.idExpediente) return;
      this.cargando = true;
      try {
        const [resCorr, resUnidades] = await Promise.all([
          axios.get(`/api/correspondencia?id_expediente=${this.idExpediente}`),
          axios.get(`/expedientes/${this.idExpediente}/unidades-documentales`),
        ]);
        this.correspondencias = Array.isArray(resCorr.data) ? resCorr.data : [];
        this.unidades = Array.isArray(resUnidades.data) ? resUnidades.data : [];
      } catch (error) {
        console.error('Error al cargar explorer de correspondencia:', error);
      } finally {
        this.cargando = false;
      }
    },
    abrirCrearCorrespondencia() {
      this.$refs.modalEditor.abrirCrear(this.idExpediente);
    },
    verDetalle(id) {
      this.$refs.modalEditor.abrirVer(id);
    },
    editar(id) {
      this.$refs.modalEditor.abrirEditar(id);
    },
    async firmar(id) {
      const confirm = await Swal.fire({
        title: '¿Firmar documento?',
        text: 'Esta acción no podrá revertirse. El documento se marcará como firmado en SAT.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, firmar',
        cancelButtonText: 'Cancelar',
      });
      if (!confirm.isConfirmed) return;

      try {
        await axios.post(`/api/correspondencia/${id}/firmar`);
        Swal.fire('Firmado', 'El documento se firmó exitosamente.', 'success');
        this.cargarDatos();
      } catch (error) {
        const msg = error.response?.data?.error || 'Error al firmar';
        Swal.fire('Error', msg, 'error');
      }
    },
    descargarPDF(id) {
      window.location.href = `/api/correspondencia/${id}/pdf`;
    },
    async eliminar(id) {
      const confirm = await Swal.fire({
        title: '¿Eliminar correspondencia?',
        text: 'Se realizará borrado lógico del borrador.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
      });
      if (!confirm.isConfirmed) return;

      try {
        await axios.delete(`/api/correspondencia/${id}`);
        Swal.fire('Eliminado', 'La correspondencia fue eliminada.', 'success');
        this.cargarDatos();
      } catch (error) {
        Swal.fire('Error', 'No se pudo eliminar el documento', 'error');
      }
    },
    alSeleccionarArchivo(event) {
      const files = event.target.files;
      if (files.length > 0) {
        this.nuevaUnidad.archivo = files[0];
      }
    },
    async subirUnidadDocumental() {
      if (!this.nuevaUnidad.archivo) {
        Swal.fire('Atención', 'Selecciona un archivo PDF', 'warning');
        return;
      }

      this.subiendoUnidad = true;
      const formData = new FormData();
      formData.append('id_expediente', this.idExpediente);
      formData.append('nombre', this.nuevaUnidad.nombre);
      formData.append('tipo', this.nuevaUnidad.tipo);
      formData.append('descripcion', this.nuevaUnidad.descripcion || '');
      formData.append('archivo', this.nuevaUnidad.archivo);

      try {
        await axios.post('/unidades-documentales', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        Swal.fire('Éxito', 'Anexo PDF subido correctamente', 'success');
        this.mostrarModalSubirUnidad = false;
        this.nuevaUnidad = { nombre: '', tipo: 'Anexo', descripcion: '', archivo: null };
        this.cargarDatos();
      } catch (error) {
        const msg = error.response?.data?.message || 'Error al subir la unidad documental';
        Swal.fire('Error', msg, 'error');
      } finally {
        this.subiendoUnidad = false;
      }
    },
    formatDate(dateStr) {
      if (!dateStr) return '-';
      return new Date(dateStr).toLocaleDateString('es-MX', {
        year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'
      });
    },
    getEstadoClass(estado) {
      return { borrador: 'bg-warning text-dark', firmado: 'bg-success', cancelado: 'bg-secondary' }[estado] || 'bg-light';
    },
    getClasificacionClass(clasificacion) {
      return { publica: 'bg-info', reservada: 'bg-warning text-dark', confidencial: 'bg-danger' }[clasificacion] || 'bg-light';
    },
  },
};
</script>

<style scoped>
.correspondencia-explorer {
  border-radius: 0.5rem;
}
</style>