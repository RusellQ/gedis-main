<template>
  <div>
    <!-- Modal del Editor -->
    <div class="modal fade" id="editorModal" ref="editorModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
          <!-- Encabezado -->
          <div class="modal-header">
            <h5 class="modal-title">
              {{ modo === 'crear' ? 'Nueva Correspondencia' : modo === 'editar' ? 'Editar Correspondencia' : 'Ver Correspondencia' }}
            </h5>
            <button type="button" class="btn-close" @click="cerrar" aria-label="Close"></button>
          </div>

          <!-- Cuerpo -->
          <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
            <div v-if="cargando" class="text-center">
              <div class="spinner-border" role="status">
                <span class="visually-hidden">Cargando...</span>
              </div>
            </div>
            <form v-else @submit.prevent="guardar">
              <!-- Fila 1: Remitente y Destinatario -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Remitente</label>
                  <input
                    v-model="formulario.remitente"
                    type="text"
                    class="form-control"
                    placeholder="Nombre o entidad que envía"
                    :disabled="modo === 'ver'"
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Destinatario</label>
                  <input
                    v-model="formulario.destinatario"
                    type="text"
                    class="form-control"
                    placeholder="Nombre o entidad que recibe"
                    :disabled="modo === 'ver'"
                  />
                </div>
              </div>

              <!-- Fila 2: Asunto -->
              <div class="mb-3">
                <label class="form-label">Asunto *</label>
                <input
                  v-model="formulario.asunto"
                  type="text"
                  class="form-control"
                  placeholder="Asunto del documento"
                  required
                  :disabled="modo === 'ver'"
                />
              </div>

              <!-- Fila 3: Clasificación -->
              <div class="mb-3">
                <label class="form-label">Clasificación *</label>
                <select
                  v-model="formulario.clasificacion"
                  class="form-select"
                  required
                  :disabled="modo === 'ver'"
                >
                  <option value="publica">Pública</option>
                  <option value="reservada">Reservada</option>
                  <option value="confidencial">Confidencial</option>
                </select>
              </div>

              <!-- Editor de contenido -->
              <div class="mb-3">
                <label class="form-label">Contenido del Documento *</label>
                <div class="card">
                  <div class="card-body">
                    <!-- Vista para edición -->
                    <textarea
                      v-if="modo !== 'ver'"
                      id="correspondencia-editor-textarea"
                      ref="editorTextarea"
                      v-model="formulario.contenido"
                      style="height: 400px"
                    ></textarea>
                    <!-- Vista para lectura -->
                    <div v-else class="preview-html" v-html="formulario.contenido"></div>
                  </div>
                </div>
              </div>

              <!-- Preview de documento firmado -->
              <div v-if="modo === 'ver' && correspondencia && correspondencia.firma" class="alert alert-success">
                <h6>
                  <i class="fas fa-check-circle"></i> Documento Firmado
                </h6>
                <p class="mb-1">
                  <strong>Autor:</strong> {{ correspondencia.usuario_autor.nombre }}
                </p>
                <p class="mb-1">
                  <strong>Fecha de firma:</strong> {{ formatDate(correspondencia.firma.timestamp_firma) }}
                </p>
                <p class="mb-0">
                  <strong>Estado:</strong>
                  <span class="badge bg-success">{{ correspondencia.firma.estado_firma }}</span>
                </p>
              </div>
            </form>
          </div>

          <!-- Pie del modal -->
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="cerrar">Cerrar</button>
            <div v-if="modo !== 'ver'" class="btn-group">
              <button
                type="button"
                class="btn btn-primary"
                @click="guardar"
                :disabled="guardando"
              >
                <span v-if="guardando" class="spinner-border spinner-border-sm me-2"></span>
                Guardar como Borrador
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from 'sweetalert2';
import { Modal } from 'bootstrap';

// TinyMCE self-hosted (npm) — sin esto, `window.tinymce` nunca existe y el
// editor queda como un <textarea> plano sin soporte de imágenes/tablas.
import tinymce from 'tinymce/tinymce';
import 'tinymce/models/dom/model';
import 'tinymce/themes/silver';
import 'tinymce/icons/default';
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/image';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/anchor';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/visualblocks';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/insertdatetime';
import 'tinymce/plugins/media';
import 'tinymce/plugins/table';
import 'tinymce/plugins/help';
import 'tinymce/plugins/wordcount';
// Solo el skin de la UI (.tox). NO importar content.css aquí: estiliza
// `body { margin: 1rem }` y, al ir en el bundle global de app.js, rompe
// el layout del sidebar en toda la app.
import 'tinymce/skins/ui/oxide/skin.css';

window.tinymce = tinymce;

export default {
  name: 'CorrespondenciaEditor',
  data() {
    return {
      modo: null, // 'crear', 'editar', 'ver'
      cargando: false,
      guardando: false,
      idExpediente: null,
      idCorrespondencia: null,
      correspondencia: null,
      bsModal: null,
      formulario: {
        remitente: '',
        destinatario: '',
        asunto: '',
        contenido: '',
        clasificacion: 'publica',
      },
    };
  },
  watch: {
    // Reinicializar editor cuando cambia el modo
    modo(nuevoModo) {
      if (nuevoModo && nuevoModo !== 'ver') {
        this.$nextTick(() => {
          this.inicializarEditor();
        });
      }
    },
  },
  methods: {
    abrirCrear(idExpediente) {
      this.modo = 'crear';
      this.idExpediente = idExpediente;
      this.idCorrespondencia = null;
      this.formulario = {
        remitente: '',
        destinatario: '',
        asunto: '',
        contenido: '',
        clasificacion: 'publica',
      };
      this.mostrarModal();
    },
    async abrirEditar(id) {
      this.modo = 'editar';
      this.idCorrespondencia = id;
      await this.cargarCorrespondencia(id);
      this.mostrarModal();
    },
    async abrirVer(id) {
      this.modo = 'ver';
      this.idCorrespondencia = id;
      await this.cargarCorrespondencia(id);
      this.mostrarModal();
    },
    async cargarCorrespondencia(id) {
      this.cargando = true;
      try {
        const response = await axios.get(`/api/correspondencia/${id}`);
        this.correspondencia = response.data;
        this.formulario = {
          remitente: response.data.remitente || '',
          destinatario: response.data.destinatario || '',
          asunto: response.data.asunto || '',
          contenido: response.data.contenido || '',
          clasificacion: response.data.clasificacion || 'publica',
        };
      } catch (error) {
        console.error('Error cargando correspondencia:', error);
        this.mostrarAlerta('Error al cargar documento', 'error');
      } finally {
        this.cargando = false;
      }
    },
    inicializarEditor() {
      // Inicializar TinyMCE cuando el modal esté visible
      if (window.tinymce && this.$refs.editorTextarea) {
        // Evitar doble inicialización si el modal se reabre sin destruir el editor previo.
        window.tinymce.remove('#correspondencia-editor-textarea');
        window.tinymce.init({
          target: this.$refs.editorTextarea,
          height: 400,
          menubar: true,
          // Skin ya cargado vía import; estilos de contenido solo en el iframe.
          skin: false,
          content_css: false,
          plugins: [
            'advlist',
            'autolink',
            'lists',
            'link',
            'image',
            'charmap',
            'preview',
            'anchor',
            'searchreplace',
            'visualblocks',
            'code',
            'fullscreen',
            'insertdatetime',
            'media',
            'table',
            'help',
            'wordcount',
          ],
          toolbar:
            'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | code preview | help',
          language: 'es',
          content_style:
            'body { font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif; font-size:14px; margin:1rem; line-height:1.4 }',
          images_upload_handler: this.uploadImagen,
          file_picker_types: 'image',
          setup: (editor) => {
            editor.on('change', () => {
              this.formulario.contenido = editor.getContent();
            });
          },
        });
      }
    },
    async guardar() {
      // Obtener contenido de TinyMCE si existe
      if (typeof window !== 'undefined' && window.tinymce && window.tinymce.activeEditor) {
        try {
          const content = window.tinymce.activeEditor.getContent();
          if (content) {
            this.formulario.contenido = content;
          }
        } catch (e) {
          console.warn('TinyMCE content read warning:', e);
        }
      }

      // Validar campos requeridos
      if (!this.formulario.asunto || !this.formulario.asunto.trim()) {
        this.mostrarAlerta('El asunto es requerido', 'warning');
        return;
      }

      if (this.formulario.asunto.trim().length < 5) {
        this.mostrarAlerta('El asunto debe tener al menos 5 caracteres', 'warning');
        return;
      }

      if (!this.formulario.contenido || !this.formulario.contenido.trim()) {
        this.mostrarAlerta('El contenido del documento es requerido', 'warning');
        return;
      }

      this.guardando = true;
      try {
        if (this.modo === 'crear') {
          await axios.post('/api/correspondencia', {
            id_expediente: this.idExpediente,
            remitente: this.formulario.remitente,
            destinatario: this.formulario.destinatario,
            asunto: this.formulario.asunto,
            contenido: this.formulario.contenido,
            clasificacion: this.formulario.clasificacion,
          });
          this.mostrarAlerta('Correspondencia creada como borrador', 'success');
        } else if (this.modo === 'editar') {
          await axios.put(`/api/correspondencia/${this.idCorrespondencia}`, {
            remitente: this.formulario.remitente,
            destinatario: this.formulario.destinatario,
            asunto: this.formulario.asunto,
            contenido: this.formulario.contenido,
            clasificacion: this.formulario.clasificacion,
          });
          this.mostrarAlerta('Correspondencia actualizada', 'success');
        }
        this.$emit('guardado');
        this.cerrar();
      } catch (error) {
        console.error('Error al guardar:', error);
        const mensaje = error.response?.data?.message || error.response?.data?.error || 'Error al guardar el documento';
        this.mostrarAlerta(mensaje, 'error');
      } finally {
        this.guardando = false;
      }
    },
    uploadImagen(blobInfo, progress) {
      // Convertir imagen a data URL
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(blobInfo.blob());
        reader.onload = () => {
          resolve(reader.result);
        };
        reader.onerror = reject;
      });
    },
    mostrarModal() {
      this.$nextTick(() => {
        const el = this.$refs.editorModal || document.getElementById('editorModal');
        if (el) {
          const modalInstance = Modal.getOrCreateInstance(el);
          modalInstance.show();
        }
      });
    },
    cerrar() {
      // Destruir editor TinyMCE
      if (typeof window !== 'undefined' && window.tinymce && window.tinymce.activeEditor) {
        try {
          window.tinymce.activeEditor.destroy();
        } catch (e) {}
      }

      const el = this.$refs.editorModal || document.getElementById('editorModal');
      if (el) {
        const modalInstance = Modal.getInstance(el) || Modal.getOrCreateInstance(el);
        if (modalInstance) {
          modalInstance.hide();
        }
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
.preview-html {
  min-height: 300px;
  padding: 15px;
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 0.25rem;
  line-height: 1.6;
}
</style>
