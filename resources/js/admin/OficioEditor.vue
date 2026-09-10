<template>
  <div>
    <div class="modal fade" id="oficioEditorModal" ref="modalEl" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ titulo }}</h5>
            <button type="button" class="btn-close" @click="cerrar"></button>
          </div>
          <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
            <div v-if="cargando" class="text-center py-4">
              <div class="spinner-border text-primary"></div>
            </div>
            <div v-else class="oficio-plantilla">
              <div class="oficio-header text-center mb-3">
                <div class="small text-uppercase fw-bold">Archivo General del Estado de Quintana Roo</div>
                <div class="text-muted small">Plantilla oficial — zonas fijas no editables</div>
              </div>

              <div class="row mb-2">
                <div class="col-md-6 offset-md-6">
                  <div class="campo-auto small mb-1">
                    <strong>Oficio No.</strong>
                    <span class="ms-1">{{ formulario.numero_oficio || '(se asigna al emitir)' }}</span>
                  </div>
                  <div class="mb-2">
                    <label class="form-label small fw-semibold campo-editable-label">ASUNTO</label>
                    <input
                      v-model="formulario.asunto"
                      type="text"
                      class="form-control form-control-sm"
                      :disabled="soloLectura"
                      required
                    />
                  </div>
                  <div class="campo-auto small mb-2">
                    {{ lugar }} a {{ fechaLegible }}.
                  </div>
                  <div class="mb-2">
                    <label class="form-label small fw-semibold campo-editable-label">Leyenda del año</label>
                    <input
                      v-model="formulario.leyenda_anio"
                      type="text"
                      class="form-control form-control-sm"
                      :disabled="soloLectura"
                    />
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-semibold campo-editable-label">Destinatario (nombre, cargo, área)</label>
                <textarea
                  v-model="formulario.destinatario"
                  class="form-control form-control-sm"
                  rows="3"
                  :disabled="soloLectura"
                  placeholder="C.P. Nombre Completo&#10;Cargo&#10;Dependencia"
                ></textarea>
                <div class="small text-muted mt-1">Se agregará «PRESENTE.» en el PDF.</div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-semibold campo-editable-label">Cuerpo del oficio</label>
                <textarea
                  v-if="!soloLectura"
                  id="oficio-editor-textarea"
                  ref="editorTextarea"
                  v-model="formulario.contenido"
                ></textarea>
                <div v-else class="border rounded p-3 bg-light" v-html="formulario.contenido"></div>
              </div>

              <div class="text-muted small border-top pt-2">
                Cierre fijo · ATENTAMENTE · {{ firmaNombre }} — {{ firmaCargo }}
              </div>
            </div>
          </div>
          <div class="modal-footer flex-wrap gap-2">
            <button type="button" class="btn btn-secondary" @click="cerrar">Cerrar</button>
            <template v-if="!soloLectura">
              <button type="button" class="btn btn-outline-primary" :disabled="guardando" @click="guardar(false)">
                Guardar borrador
              </button>
              <button type="button" class="btn btn-primary" :disabled="guardando" @click="guardar(true)">
                Guardar y emitir
              </button>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from 'sweetalert2';
import { Modal } from 'bootstrap';
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
import 'tinymce/skins/ui/oxide/skin.css';

window.tinymce = tinymce;

export default {
  name: 'OficioEditor',
  emits: ['guardado'],
  data() {
    return {
      modo: 'crear',
      cargando: false,
      guardando: false,
      id: null,
      bsModal: null,
      lugar: 'Chetumal, Quintana Roo',
      firmaNombre: 'Licdo. Gabriel Caamal Pérez',
      firmaCargo: 'Director General del Archivo General del Estado de Quintana Roo',
      formulario: this.formVacio(),
    };
  },
  computed: {
    soloLectura() {
      return this.modo === 'ver';
    },
    titulo() {
      if (this.modo === 'crear') return 'Nuevo oficio';
      if (this.modo === 'editar') return 'Editar oficio';
      return 'Ver oficio';
    },
    fechaLegible() {
      const d = this.formulario.fecha_oficio ? new Date(this.formulario.fecha_oficio + 'T12:00:00') : new Date();
      return d.toLocaleDateString('es-MX', { day: 'numeric', month: 'long', year: 'numeric' });
    },
  },
  methods: {
    formVacio() {
      const y = new Date().getFullYear();
      return {
        numero_oficio: null,
        asunto: '',
        leyenda_anio: `${y}, Año del 40 Aniversario de la Creación del Himno del Estado Libre y Soberano de Quintana Roo`,
        fecha_oficio: new Date().toISOString().slice(0, 10),
        destinatario: '',
        contenido: '<p></p>',
        clasificacion: 'publica',
        estado: 'borrador',
      };
    },
    abrirCrear() {
      this.modo = 'crear';
      this.id = null;
      this.formulario = this.formVacio();
      this.mostrar();
    },
    async abrirEditar(id) {
      this.modo = 'editar';
      this.id = id;
      await this.cargar(id);
      this.mostrar();
    },
    async abrirVer(id) {
      this.modo = 'ver';
      this.id = id;
      await this.cargar(id);
      this.mostrar();
    },
    async cargar(id) {
      this.cargando = true;
      try {
        const { data } = await axios.get(`/api/correspondencia/${id}`);
        this.formulario = {
          numero_oficio: data.numero_oficio,
          asunto: data.asunto || '',
          leyenda_anio: data.leyenda_anio || '',
          fecha_oficio: data.fecha_oficio || new Date().toISOString().slice(0, 10),
          destinatario: data.destinatario || '',
          contenido: data.contenido || '<p></p>',
          clasificacion: data.clasificacion || 'publica',
          estado: data.estado,
        };
        this.lugar = data.lugar || this.lugar;
        this.firmaNombre = data.firma_nombre || this.firmaNombre;
        this.firmaCargo = data.firma_cargo || this.firmaCargo;
        if (data.estado !== 'borrador') this.modo = 'ver';
      } catch (e) {
        this.alerta('Error al cargar el oficio', 'error');
      } finally {
        this.cargando = false;
      }
    },
    mostrar() {
      this.$nextTick(() => {
        const el = this.$refs.modalEl;
        if (!el) return;
        this.bsModal = Modal.getOrCreateInstance(el);
        this.bsModal.show();
        if (!this.soloLectura) {
          setTimeout(() => this.initTiny(), 300);
        }
      });
    },
    initTiny() {
      if (!window.tinymce || !this.$refs.editorTextarea) return;
      window.tinymce.remove('#oficio-editor-textarea');
      window.tinymce.init({
        target: this.$refs.editorTextarea,
        height: 360,
        menubar: true,
        skin: false,
        content_css: false,
        plugins: [
          'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor',
          'searchreplace', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media',
          'table', 'help', 'wordcount',
        ],
        toolbar:
          'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media table | code preview',
        language: 'es',
        content_style:
          'body { font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif; font-size:14px; margin:1rem; line-height:1.45 }',
        images_upload_handler: (blobInfo) =>
          new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(blobInfo.blob());
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
          }),
        setup: (editor) => {
          editor.on('change keyup', () => {
            this.formulario.contenido = editor.getContent();
          });
        },
      });
    },
    syncTiny() {
      if (window.tinymce?.activeEditor) {
        this.formulario.contenido = window.tinymce.activeEditor.getContent();
      }
    },
    async guardar(emitir) {
      this.syncTiny();
      if (!this.formulario.asunto?.trim() || this.formulario.asunto.trim().length < 5) {
        this.alerta('El asunto debe tener al menos 5 caracteres', 'warning');
        return;
      }
      if (!this.formulario.contenido?.replace(/<[^>]+>/g, '').trim()) {
        this.alerta('El cuerpo del oficio es requerido', 'warning');
        return;
      }
      this.guardando = true;
      const payload = {
        asunto: this.formulario.asunto,
        contenido: this.formulario.contenido,
        destinatario: this.formulario.destinatario,
        leyenda_anio: this.formulario.leyenda_anio,
        clasificacion: this.formulario.clasificacion,
        emitir: !!emitir,
      };
      try {
        if (this.modo === 'crear') {
          await axios.post('/api/correspondencia', payload);
        } else {
          await axios.put(`/api/correspondencia/${this.id}`, payload);
        }
        this.alerta(emitir ? 'Oficio emitido' : 'Borrador guardado', 'success');
        this.$emit('guardado');
        this.cerrar();
      } catch (e) {
        this.alerta(e.response?.data?.error || e.response?.data?.message || 'Error al guardar', 'error');
      } finally {
        this.guardando = false;
      }
    },
    cerrar() {
      try {
        window.tinymce?.remove('#oficio-editor-textarea');
      } catch (e) {}
      const el = this.$refs.modalEl;
      if (el) Modal.getOrCreateInstance(el).hide();
    },
    alerta(title, icon = 'info') {
      Swal.fire({ position: 'top-end', icon, title, showConfirmButton: false, timer: 2200 });
    },
  },
};
</script>

<style scoped>
.oficio-plantilla {
  background: #fff;
  border: 1px solid #e4e0d8;
  border-radius: 8px;
  padding: 1rem 1.25rem;
}
.campo-auto {
  background: #e8f1fb;
  border-left: 3px solid #3b82f6;
  padding: 0.35rem 0.6rem;
  border-radius: 4px;
}
.campo-editable-label::after {
  content: ' (editable)';
  color: #15803d;
  font-weight: 500;
  font-size: 0.75rem;
}
</style>
