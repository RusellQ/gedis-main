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
        <div class="d-flex gap-2">
          <button
            v-if="filtros.idSerie"
            @click="abrirModalExpediente"
            class="btn btn-sm btn-primary"
            title="Crear un expediente nuevo dentro de la serie/subserie seleccionada"
          >
            <i class="fas fa-folder-plus me-1"></i> Crear nuevo expediente
          </button>
          <button 
            v-if="filtros.idFondo || filtros.idSubfondo || filtros.idSeccion || filtros.idSerie || filtros.idSubserie || filtros.idExpediente" 
            @click="limpiarFiltros" 
            class="btn btn-sm btn-outline-secondary"
          >
            <i class="fas fa-undo me-1"></i> Limpiar filtros
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold">1. Fondo</label>
            <select v-model="filtros.idFondo" @change="onFondoChange" class="form-select">
              <option value="">-- Seleccionar fondo --</option>
              <option v-for="fondo in fondos" :key="fondo.id" :value="fondo.id">
                {{ fondo.nombre }}
              </option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">2. Subfondo <span class="text-muted fw-normal">(opcional)</span></label>
            <select 
              v-model="filtros.idSubfondo" 
              @change="onSubfondoChange" 
              class="form-select" 
              :disabled="!filtros.idFondo"
            >
              <option value="">-- Sin subfondo / Todos --</option>
              <option v-for="subfondo in subfondos" :key="subfondo.id" :value="subfondo.id">
                {{ subfondo.nombre }}
              </option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">3. Sección</label>
            <select 
              v-model="filtros.idSeccion" 
              @change="onSeccionChange" 
              class="form-select" 
              :disabled="!filtros.idFondo"
            >
              <option value="">-- Seleccionar sección --</option>
              <option v-for="seccion in secciones" :key="seccion.id" :value="seccion.id">
                {{ seccion.nombre }}
              </option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">4. Serie</label>
            <select 
              v-model="filtros.idSerie" 
              @change="onSerieChange" 
              class="form-select" 
              :disabled="!filtros.idSeccion"
            >
              <option value="">-- Seleccionar serie --</option>
              <option v-for="serie in series" :key="serie.id" :value="serie.id">
                {{ serie.nombre }}
              </option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">5. Subserie <span class="text-muted fw-normal">(opcional)</span></label>
            <select 
              v-model="filtros.idSubserie" 
              @change="onSubserieChange" 
              class="form-select" 
              :disabled="!filtros.idSerie"
            >
              <option value="">-- Sin subserie / Todas --</option>
              <option v-for="subserie in subseries" :key="subserie.id" :value="subserie.id">
                {{ subserie.nombre }}
              </option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">6. Expediente *</label>
            <select 
              v-model="filtros.idExpediente" 
              @change="cargarCorrespondencias" 
              class="form-select"
              :disabled="!filtros.idSerie || cargandoExpedientes"
            >
              <option value="">
                {{ cargandoExpedientes ? '-- Cargando expedientes... --' : (!filtros.idSerie ? '-- Selecciona una serie primero --' : (expedientes.length > 0 ? `-- Seleccionar expediente (${expedientes.length}) --` : '-- No hay expedientes en esta serie/subserie --')) }}
              </option>
              <option v-for="expediente in expedientes" :key="expediente.id" :value="expediente.id">
                {{ expediente.nombre }} ({{ expediente.codigo || expediente.clave || 'Sin código' }})
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla / Tabs de Correspondencias y Unidades Documentales -->
    <div class="card">
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
              <i class="fas fa-file-pdf me-1"></i> Unidades Documentales ({{ unidades.length }})
            </button>
          </li>
        </ul>
      </div>
      <div class="card-body">
        <div v-if="cargando" class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
        </div>

        <!-- Tab 1: Correspondencias -->
        <div v-else-if="tabActiva === 'correspondencias'">
          <div v-if="!filtros.idExpediente" class="alert alert-secondary text-center py-3">
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

        <!-- Tab 2: Unidades Documentales de la Serie / Expediente -->
        <div v-else-if="tabActiva === 'unidades'">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div v-if="filtros.idExpediente && !puedeAgregarUnidad" class="alert alert-warning py-2 px-3 mb-0 small">
              <i class="fas fa-lock me-1"></i> Solo se pueden añadir unidades documentales a expedientes en trámite.
            </div>
            <div v-else></div>
            <button
              v-if="puedeAgregarUnidad"
              @click="abrirModalUnidad"
              class="btn btn-sm btn-success"
            >
              <i class="fas fa-file-circle-plus me-1"></i> Añadir Unidad Documental
            </button>
          </div>
          <div v-if="!filtros.idExpediente" class="alert alert-secondary text-center py-3">
            <i class="fas fa-info-circle me-1"></i> Selecciona un Expediente en los filtros para ver sus unidades documentales.
          </div>
          <div v-else-if="unidades.length === 0" class="alert alert-info text-center py-3">
            <i class="fas fa-folder-open me-1"></i> No hay Unidades Documentales
          </div>
          <div v-else class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-dark">
                <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Tipo</th>
                  <th>Descripción</th>
                  <th>Archivo PDF</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(unidad, idx) in unidades" :key="unidad.id || idx">
                  <td>{{ idx + 1 }}</td>
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
    </div>

    <!-- Modal para crear/editar -->
    <CorrespondenciaEditor 
      ref="modalEditor"
      @guardado="cargarCorrespondencias"
    />

    <!-- Modal: Añadir Unidad Documental al expediente seleccionado -->
    <div ref="modalUnidadDocumental" class="modal fade" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Añadir unidad documental</h5>
            <button type="button" class="btn-close" @click="modalUnidad.hide()"></button>
          </div>
          <form @submit.prevent="guardarUnidadDocumental">
            <div class="modal-body">
              <label class="form-label fw-semibold">Tipo</label>
              <select v-model="nuevaUnidad.tipo" class="form-select mb-2" required>
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
              </select>

              <label class="form-label fw-semibold">Nombre</label>
              <input v-model="nuevaUnidad.nombre" placeholder="Nombre" type="text" required class="form-control mb-2" />

              <label class="form-label fw-semibold">Descripción</label>
              <textarea v-model="nuevaUnidad.descripcion" placeholder="Descripción" class="form-control mb-2"></textarea>

              <label class="form-label fw-semibold">Archivo PDF</label>
              <input @change="handleArchivoUnidad" type="file" class="form-control" accept="application/pdf,.pdf" />
              <small class="text-muted">Solo se permiten archivos PDF (máx. 20 MB).</small>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" @click="modalUnidad.hide()">Cancelar</button>
              <button type="submit" class="btn btn-primary" :disabled="guardandoUnidad">
                {{ guardandoUnidad ? 'Guardando...' : 'Guardar' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal: Crear nuevo Expediente para adjuntar unidades documentales entrantes -->
    <div ref="modalNuevoExpediente" class="modal fade" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Crear nuevo expediente</h5>
            <button type="button" class="btn-close" @click="modalExpediente.hide()"></button>
          </div>
          <form @submit.prevent="guardarNuevoExpediente">
            <div class="modal-body">
              <p class="text-muted small mb-3">
                Se creará dentro de la serie/subserie seleccionada en los filtros. El expediente quedará en estado <strong>en trámite</strong> para poder añadirle las unidades documentales que lleguen.
              </p>
              <label class="form-label fw-semibold">Nombre</label>
              <input v-model="nuevoExpediente.nombre" placeholder="Nombre del expediente" type="text" required class="form-control mb-2" />

              <label class="form-label fw-semibold">Código</label>
              <input v-model="nuevoExpediente.codigo" placeholder="Código" type="text" required class="form-control mb-2" />
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" @click="modalExpediente.hide()">Cancelar</button>
              <button type="submit" class="btn btn-primary" :disabled="guardandoExpediente">
                {{ guardandoExpediente ? 'Creando...' : 'Crear expediente' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from 'sweetalert2';
import { Modal } from 'bootstrap';
import CorrespondenciaEditor from './CorrespondenciaEditor.vue';

export default {
  name: 'Correspondencia',
  components: {
    CorrespondenciaEditor,
  },
  data() {
    return {
      tabActiva: 'correspondencias',
      filtros: {
        idFondo: '',
        idSubfondo: '',
        idSeccion: '',
        idSerie: '',
        idSubserie: '',
        idExpediente: '',
      },
      fondos: [],
      subfondos: [],
      secciones: [],
      series: [],
      subseries: [],
      expedientes: [],
      correspondencias: [],
      unidades: [],
      cargando: false,
      cargandoExpedientes: false,
      modalUnidad: null,
      modalExpediente: null,
      nuevaUnidad: {
        tipo: '',
        nombre: '',
        descripcion: '',
        archivo: null,
      },
      guardandoUnidad: false,
      nuevoExpediente: {
        nombre: '',
        codigo: '',
      },
      guardandoExpediente: false,
    };
  },
  computed: {
    expedienteSeleccionado() {
      return this.expedientes.find((e) => String(e.id) === String(this.filtros.idExpediente)) || null;
    },
    puedeAgregarUnidad() {
      return !!this.filtros.idExpediente && this.expedienteSeleccionado?.estado === 'en_tramite';
    },
  },
  mounted() {
    this.cargarFondos();
    if (this.$refs.modalUnidadDocumental) {
      this.modalUnidad = new Modal(this.$refs.modalUnidadDocumental);
    }
    if (this.$refs.modalNuevoExpediente) {
      this.modalExpediente = new Modal(this.$refs.modalNuevoExpediente);
    }
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
      this.filtros.idSubfondo = '';
      this.filtros.idSeccion = '';
      this.filtros.idSerie = '';
      this.filtros.idSubserie = '';
      this.filtros.idExpediente = '';
      this.subfondos = [];
      this.secciones = [];
      this.series = [];
      this.subseries = [];
      this.expedientes = [];
      this.correspondencias = [];
      this.unidades = [];

      if (!this.filtros.idFondo) return;

      try {
        const resSub = await axios.get(`/fondos/${this.filtros.idFondo}/subfondos`);
        this.subfondos = Array.isArray(resSub.data) ? resSub.data : [];
      } catch (e) {
        console.error('Error cargando subfondos:', e);
        this.subfondos = [];
      }

      await this.cargarSecciones();
    },
    async onSubfondoChange() {
      this.filtros.idSeccion = '';
      this.filtros.idSerie = '';
      this.filtros.idSubserie = '';
      this.filtros.idExpediente = '';
      this.secciones = [];
      this.series = [];
      this.subseries = [];
      this.expedientes = [];
      this.correspondencias = [];
      this.unidades = [];

      if (!this.filtros.idFondo) return;
      await this.cargarSecciones();
    },
    async cargarSecciones() {
      try {
        const url = this.filtros.idSubfondo
          ? `/subfondos/${this.filtros.idSubfondo}/secciones`
          : `/fondos/${this.filtros.idFondo}/secciones`;
        const resDirect = await axios.get(url);
        let listaSecciones = Array.isArray(resDirect.data) ? [...resDirect.data] : [];

        if (listaSecciones.length === 0 && !this.filtros.idSubfondo && this.subfondos.length > 0) {
          for (const sf of this.subfondos) {
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
      this.filtros.idSubserie = '';
      this.filtros.idExpediente = '';
      this.series = [];
      this.subseries = [];
      this.expedientes = [];
      this.correspondencias = [];
      this.unidades = [];

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
      this.filtros.idSubserie = '';
      this.filtros.idExpediente = '';
      this.subseries = [];
      this.expedientes = [];
      this.correspondencias = [];
      this.unidades = [];

      if (!this.filtros.idSerie) return;

      try {
        const resSub = await axios.get(`/series/${this.filtros.idSerie}/subseries`);
        this.subseries = Array.isArray(resSub.data?.data)
          ? resSub.data.data
          : (Array.isArray(resSub.data) ? resSub.data : []);
      } catch (e) {
        console.error('Error cargando subseries:', e);
        this.subseries = [];
      }

      await this.cargarExpedientes();
    },
    async onSubserieChange() {
      this.filtros.idExpediente = '';
      this.expedientes = [];
      this.correspondencias = [];

      if (!this.filtros.idSerie) return;
      await this.cargarExpedientes();
    },
    async cargarExpedientes() {
      if (!this.filtros.idSerie) {
        this.expedientes = [];
        return;
      }

      this.cargandoExpedientes = true;
      try {
        let listaSerie = [];

        if (this.filtros.idSubserie) {
          const response = await axios.get(`/subseries/${this.filtros.idSubserie}/expedientes`);
          listaSerie = Array.isArray(response.data) ? [...response.data] : [];
        } else {
          const response = await axios.get(`/series/${this.filtros.idSerie}/expedientes`);
          listaSerie = Array.isArray(response.data) ? [...response.data] : [];

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
            if (this.subseries.length > 0) {
              for (const sub of this.subseries) {
                if (!sub || !sub.id) continue;
                const resSubExp = await axios.get(`/subseries/${sub.id}/expedientes`);
                const subExpData = Array.isArray(resSubExp.data) ? resSubExp.data : [];
                for (const item of subExpData) {
                  if (!listaSerie.some((e) => e.id === item.id)) {
                    listaSerie.push(item);
                  }
                }
              }
            }
          } catch (e) {}
        }

        this.expedientes = listaSerie;
      } catch (error) {
        console.error('Error cargando expedientes:', error);
        this.expedientes = [];
      } finally {
        this.cargandoExpedientes = false;
      }
    },
    async cargarUnidadesExpediente() {
      if (!this.filtros.idExpediente) {
        this.unidades = [];
        return;
      }
      try {
        const response = await axios.get(`/expedientes/${this.filtros.idExpediente}/unidades-documentales`);
        this.unidades = Array.isArray(response.data) ? response.data : [];
      } catch (error) {
        console.error('Error cargando unidades documentales del expediente:', error);
        this.unidades = [];
      }
    },
    async cargarCorrespondencias() {
      this.unidades = [];

      if (!this.filtros.idExpediente) {
        this.correspondencias = [];
        return;
      }

      // La carga de unidades documentales es independiente: un fallo aquí
      // no debe impedir ni ensuciar la pestaña de unidades documentales.
      this.cargarUnidadesExpediente();

      this.cargando = true;
      try {
        const response = await axios.get(`/api/correspondencia?id_expediente=${this.filtros.idExpediente}`);
        this.correspondencias = Array.isArray(response.data) ? response.data : [];
      } catch (error) {
        console.error('Error cargando correspondencias:', error);
        const mensaje = error.response?.data?.error || error.response?.data?.message || 'Error al cargar correspondencias';
        this.mostrarAlerta(mensaje, 'error');
        this.correspondencias = [];
      } finally {
        this.cargando = false;
      }
    },
    limpiarFiltros() {
      this.filtros = {
        idFondo: '',
        idSubfondo: '',
        idSeccion: '',
        idSerie: '',
        idSubserie: '',
        idExpediente: '',
      };
      this.subfondos = [];
      this.secciones = [];
      this.series = [];
      this.subseries = [];
      this.expedientes = [];
      this.correspondencias = [];
      this.unidades = [];
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
    abrirModalUnidad() {
      if (!this.puedeAgregarUnidad) return;
      this.nuevaUnidad = { tipo: '', nombre: '', descripcion: '', archivo: null };
      this.modalUnidad.show();
    },
    handleArchivoUnidad(event) {
      const file = event.target.files[0] || null;
      if (file && file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
        this.mostrarAlerta('Solo se permiten archivos PDF.', 'error');
        event.target.value = '';
        this.nuevaUnidad.archivo = null;
        return;
      }
      this.nuevaUnidad.archivo = file;
    },
    async guardarUnidadDocumental() {
      if (!this.filtros.idExpediente) return;
      this.guardandoUnidad = true;
      const formData = new FormData();
      formData.append('id_expediente', this.filtros.idExpediente);
      formData.append('tipo', this.nuevaUnidad.tipo);
      formData.append('nombre', this.nuevaUnidad.nombre);
      formData.append('descripcion', this.nuevaUnidad.descripcion || '');
      if (this.nuevaUnidad.archivo) {
        formData.append('archivo', this.nuevaUnidad.archivo);
      }
      try {
        await axios.post('/unidades-documentales', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        this.mostrarAlerta('Unidad documental guardada', 'success');
        this.modalUnidad.hide();
        await this.cargarUnidadesExpediente();
      } catch (error) {
        console.error('Error al guardar unidad documental:', error);
        const mensaje =
          error.response?.data?.message ||
          error.response?.data?.errors?.archivo?.[0] ||
          error.response?.data?.errors?.id_expediente?.[0] ||
          'Error al guardar la unidad documental.';
        this.mostrarAlerta(mensaje, 'error');
      } finally {
        this.guardandoUnidad = false;
      }
    },
    abrirModalExpediente() {
      if (!this.filtros.idSerie) return;
      this.nuevoExpediente = { nombre: '', codigo: '' };
      this.modalExpediente.show();
    },
    async guardarNuevoExpediente() {
      if (!this.filtros.idSerie) return;
      this.guardandoExpediente = true;
      try {
        const response = await axios.post('/guardarexpediente', {
          nombre: this.nuevoExpediente.nombre,
          codigo: this.nuevoExpediente.codigo,
          id_serie: this.filtros.idSerie,
          id_subserie: this.filtros.idSubserie || null,
          estado: 'en_tramite',
        });
        const expediente = response.data.expediente;
        this.expedientes.push(expediente);
        this.filtros.idExpediente = expediente.id;
        this.modalExpediente.hide();
        this.mostrarAlerta('Expediente creado correctamente', 'success');
        this.tabActiva = 'unidades';
        await this.cargarCorrespondencias();
        this.abrirModalUnidad();
      } catch (error) {
        console.error('Error al crear expediente:', error);
        const mensaje =
          error.response?.data?.message ||
          Object.values(error.response?.data?.errors || {})[0]?.[0] ||
          'Error al crear el expediente.';
        this.mostrarAlerta(mensaje, 'error');
      } finally {
        this.guardandoExpediente = false;
      }
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
