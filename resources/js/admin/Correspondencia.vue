<template>
  <div class="correspondencia-container">
    <!-- Encabezado -->
    <div class="row mb-4">
      <div class="col-md-6">
        <h2>Gestión de Correspondencia</h2>
      </div>
      <div class="col-md-6 text-end">
        <button 
          @click="abrirCrear" 
          class="btn btn-primary"
          title="Nueva Correspondencia"
        >
          <i class="fas fa-plus me-1"></i> Nueva Correspondencia
        </button>
      </div>
    </div>

    <!-- Selector de Expediente -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Seleccionar Expediente</h5>
        <button 
          v-if="filtros.idFondo || filtros.idSeccion || filtros.idSerie || filtros.idExpediente" 
          @click="limpiarFiltros" 
          class="btn btn-sm btn-outline-secondary"
        >
          <i class="fas fa-undo me-1"></i> Limpiar filtros
        </button>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label fw-semibold">1. Fondo</label>
            <select v-model="filtros.idFondo" @change="onFondoChange" class="form-select">
              <option value="">-- Todos los fondos --</option>
              <option v-for="fondo in fondos" :key="fondo.id" :value="fondo.id">
                {{ fondo.nombre }}
              </option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">2. Sección</label>
            <select v-model="filtros.idSeccion" @change="onSeccionChange" class="form-select" :disabled="!filtros.idFondo">
              <option value="">-- Seleccionar sección --</option>
              <option v-for="seccion in secciones" :key="seccion.id" :value="seccion.id">
                {{ seccion.nombre }}
              </option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">3. Serie</label>
            <select v-model="filtros.idSerie" @change="onSerieChange" class="form-select" :disabled="!filtros.idSeccion">
              <option value="">-- Seleccionar serie --</option>
              <option v-for="serie in series" :key="serie.id" :value="serie.id">
                {{ serie.nombre }}
              </option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">4. Expediente *</label>
            <select 
              v-model="filtros.idExpediente" 
              @change="cargarCorrespondencias" 
              class="form-select"
              :disabled="!filtros.idSerie || cargandoExpedientes"
            >
              <option value="">
                {{ cargandoExpedientes ? '-- Cargando expedientes... --' : (!filtros.idSerie ? '-- Selecciona una serie primero --' : (expedientes.length > 0 ? `-- Seleccionar expediente (${expedientes.length}) --` : '-- No hay expedientes en esta serie --')) }}
              </option>
              <option v-for="expediente in expedientes" :key="expediente.id" :value="expediente.id">
                {{ expediente.nombre }} ({{ expediente.codigo || expediente.clave || 'Sin código' }})
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla de Correspondencias -->
    <div class="card">
      <div class="card-header">
        <h5>Documentos de Correspondencia</h5>
      </div>
      <div class="card-body">
        <div v-if="cargando" class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
        </div>
        <div v-else-if="!filtros.idExpediente" class="alert alert-secondary text-center py-3">
          <i class="fas fa-info-circle me-1"></i> Selecciona Fondo → Sección → Serie → Expediente para ver o crear correspondencias.
        </div>
        <div v-else-if="correspondencias.length === 0" class="alert alert-info text-center py-3">
          <i class="fas fa-folder-open me-1"></i> El expediente seleccionado no tiene documentos de correspondencia registrados. Haz clic en <strong>"Nueva Correspondencia"</strong> para redactar un documento.
        </div>
        <table v-else class="table table-hover table-striped">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Asunto</th>
              <th>Remitente</th>
              <th>Destinatario</th>
              <th>Estado</th>
              <th>Clasificación</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="corr in correspondencias" :key="corr.id">
              <td>{{ corr.numero_consecutivo }}</td>
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
              <td>{{ formatDate(corr.fecha_creacion) }}</td>
              <td>
                <div class="btn-group btn-group-sm" role="group">
                  <button @click="verDetalle(corr.id)" class="btn btn-info" title="Ver">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button @click="editar(corr.id)" class="btn btn-warning" :disabled="corr.estado !== 'borrador'" title="Editar">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="firmar(corr.id)" class="btn btn-success" :disabled="corr.estado !== 'borrador'" title="Firmar">
                    <i class="fas fa-pen-fancy"></i>
                  </button>
                  <button @click="descargarPDF(corr.id)" class="btn btn-secondary" title="Descargar PDF">
                    <i class="fas fa-download"></i>
                  </button>
                  <button @click="eliminar(corr.id)" class="btn btn-danger" :disabled="corr.estado !== 'borrador'" title="Eliminar">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal para crear/editar -->
    <CorrespondenciaEditor 
      ref="modalEditor"
      @guardado="cargarCorrespondencias"
    />
  </div>
</template>

<script>
import Swal from 'sweetalert2';
import CorrespondenciaEditor from './CorrespondenciaEditor.vue';

export default {
  name: 'Correspondencia',
  components: {
    CorrespondenciaEditor,
  },
  data() {
    return {
      filtros: {
        idFondo: '',
        idSeccion: '',
        idSerie: '',
        idExpediente: '',
      },
      fondos: [],
      secciones: [],
      series: [],
      expedientes: [],
      correspondencias: [],
      cargando: false,
      cargandoExpedientes: false,
    };
  },
  mounted() {
    this.cargarFondos();
  },
  methods: {
    async cargarFondos() {
      try {
        const response = await axios.get('/mostrarfondos');
        this.fondos = Array.isArray(response.data) ? response.data : [];
      } catch (error) {
        console.error('Error cargando fondos:', error);
        this.fondos = [];
      }
    },
    async onFondoChange() {
      this.filtros.idSeccion = '';
      this.filtros.idSerie = '';
      this.filtros.idExpediente = '';
      this.secciones = [];
      this.series = [];
      this.expedientes = [];
      this.correspondencias = [];

      if (!this.filtros.idFondo) return;

      try {
        const resDirect = await axios.get(`/fondos/${this.filtros.idFondo}/secciones`);
        let listaSecciones = Array.isArray(resDirect.data) ? [...resDirect.data] : [];

        if (listaSecciones.length === 0) {
          const resSub = await axios.get(`/fondos/${this.filtros.idFondo}/subfondos`);
          const subfondos = Array.isArray(resSub.data) ? resSub.data : [];
          for (const sf of subfondos) {
            const resSubSec = await axios.get(`/subfondos/${sf.id}/secciones`);
            if (Array.isArray(resSubSec.data) && resSubSec.data.length > 0) {
              listaSecciones = listaSecciones.concat(resSubSec.data);
            }
          }
        }
        this.secciones = listaSecciones;
      } catch (error) {
        console.error('Error cargando secciones:', error);
        this.secciones = [];
      }
    },
    async onSeccionChange() {
      this.filtros.idSerie = '';
      this.filtros.idExpediente = '';
      this.series = [];
      this.expedientes = [];
      this.correspondencias = [];

      if (!this.filtros.idSeccion) return;

      try {
        const response = await axios.get(`/secciones/${this.filtros.idSeccion}/series`);
        this.series = Array.isArray(response.data) ? response.data : [];
      } catch (error) {
        console.error('Error cargando series:', error);
        this.series = [];
      }
    },
    async onSerieChange() {
      this.filtros.idExpediente = '';
      this.expedientes = [];
      this.correspondencias = [];

      if (!this.filtros.idSerie) return;

      this.cargandoExpedientes = true;
      try {
        const response = await axios.get(`/series/${this.filtros.idSerie}/expedientes`);
        let listaSerie = Array.isArray(response.data) ? [...response.data] : [];

        // Fallback: consultar /expedientes global si la serie directa no arrojó resultados
        if (listaSerie.length === 0) {
          try {
            const resAll = await axios.get('/expedientes');
            const todos = Array.isArray(resAll.data) ? resAll.data : [];
            listaSerie = todos.filter(
              (exp) => String(exp.id_serie || exp.serie?.id) === String(this.filtros.idSerie)
            );
          } catch (e) {
            console.error('Error fallback expedientes:', e);
          }
        }

        try {
          const resSub = await axios.get(`/series/${this.filtros.idSerie}/subseries`);
          const subseries = Array.isArray(resSub.data?.data)
            ? resSub.data.data
            : (Array.isArray(resSub.data) ? resSub.data : []);

          for (const sub of subseries) {
            if (!sub || !sub.id) continue;
            const resSubExp = await axios.get(`/subseries/${sub.id}/expedientes`);
            const subExpData = Array.isArray(resSubExp.data) ? resSubExp.data : [];
            for (const item of subExpData) {
              if (!listaSerie.some((e) => e.id === item.id)) {
                listaSerie.push(item);
              }
            }
          }
        } catch (e) {}

        this.expedientes = listaSerie;
      } catch (error) {
        console.error('Error cargando expedientes de la serie:', error);
        this.expedientes = [];
      } finally {
        this.cargandoExpedientes = false;
      }
    },
    async cargarCorrespondencias() {
      if (!this.filtros.idExpediente) {
        this.correspondencias = [];
        return;
      }

      this.cargando = true;
      try {
        const response = await axios.get(`/api/correspondencia?id_expediente=${this.filtros.idExpediente}`);
        this.correspondencias = Array.isArray(response.data) ? response.data : [];
      } catch (error) {
        console.error('Error cargando correspondencias:', error);
        this.mostrarAlerta('Error al cargar correspondencias', 'error');
        this.correspondencias = [];
      } finally {
        this.cargando = false;
      }
    },
    limpiarFiltros() {
      this.filtros = {
        idFondo: '',
        idSeccion: '',
        idSerie: '',
        idExpediente: '',
      };
      this.secciones = [];
      this.series = [];
      this.expedientes = [];
      this.correspondencias = [];
    },
    verDetalle(id) {
      this.$refs.modalEditor.abrirVer(id);
    },
    editar(id) {
      this.$refs.modalEditor.abrirEditar(id);
    },
    abrirCrear() {
      if (!this.filtros.idExpediente) {
        Swal.fire({
          icon: 'warning',
          title: 'Selecciona un expediente',
          text: 'Por favor selecciona un expediente en el selector desplegable antes de crear un documento de correspondencia.',
          confirmButtonColor: '#3085d6',
        });
        return;
      }
      this.$refs.modalEditor.abrirCrear(this.filtros.idExpediente);
    },
    async firmar(id) {
      const confirm = await Swal.fire({
        title: '¿Firmar documento?',
        text: 'Esta acción no podrá revertirse. El documento se marcará como firmado.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, firmar',
        cancelButtonText: 'Cancelar',
      });

      if (!confirm.isConfirmed) return;

      try {
        const response = await axios.post(`/api/correspondencia/${id}/firmar`);
        this.mostrarAlerta('Documento firmado exitosamente', 'success');
        this.cargarCorrespondencias();
      } catch (error) {
        console.error('Error al firmar:', error);
        const mensaje = error.response?.data?.error || 'Error al firmar el documento';
        this.mostrarAlerta(mensaje, 'error');
      }
    },
    async descargarPDF(id) {
      try {
        window.location.href = `/api/correspondencia/${id}/pdf`;
      } catch (error) {
        console.error('Error al descargar PDF:', error);
        this.mostrarAlerta('Error al descargar PDF', 'error');
      }
    },
    async eliminar(id) {
      const confirm = await Swal.fire({
        title: '¿Eliminar correspondencia?',
        text: 'Esta acción no podrá revertirse.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
      });

      if (!confirm.isConfirmed) return;

      try {
        await axios.delete(`/api/correspondencia/${id}`);
        this.mostrarAlerta('Correspondencia eliminada', 'success');
        this.cargarCorrespondencias();
      } catch (error) {
        console.error('Error al eliminar:', error);
        this.mostrarAlerta('Error al eliminar', 'error');
      }
    },
    formatDate(dateString) {
      if (!dateString) return '-';
      const date = new Date(dateString);
      return date.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      });
    },
    getEstadoClass(estado) {
      const classes = {
        borrador: 'bg-warning text-dark',
        firmado: 'bg-success',
        cancelado: 'bg-secondary',
      };
      return classes[estado] || 'bg-light';
    },
    getClasificacionClass(clasificacion) {
      const classes = {
        publica: 'bg-info',
        reservada: 'bg-warning text-dark',
        confidencial: 'bg-danger',
      };
      return classes[clasificacion] || 'bg-light';
    },
    mostrarAlerta(mensaje, icono = 'info') {
      Swal.fire({
        position: 'top-end',
        icon: icono,
        title: mensaje,
        showConfirmButton: false,
        timer: 2000,
      });
    },
  },
};
</script>

<style scoped>
.correspondencia-container {
  padding: 20px;
}

.btn-group-sm button {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.table-hover tbody tr:hover {
  background-color: #f8f9fa;
  cursor: pointer;
}
</style>
