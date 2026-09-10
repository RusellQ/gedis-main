<template>
  <div>
    <div class="modal fade" id="oficioEditorModal" ref="modalEl" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content oficio-modal">
          <div class="modal-header">
            <div class="modal-heading">
              <h5 class="modal-title">{{ titulo }}</h5>
              <span v-if="formulario.estado" class="modo-badge" :class="'modo-' + (soloLectura ? 'ver' : modo)">
                {{ soloLectura ? etiquetaEstado(formulario.estado) : (modo === 'crear' ? 'Nuevo' : 'Edición') }}
              </span>
            </div>
            <button type="button" class="btn-close" @click="cerrar"></button>
          </div>

          <div class="modal-body oficio-modal-body">
            <div v-if="cargando" class="state-box">
              <div class="spinner-border spinner-brand" role="status"></div>
              <p class="state-sub">Cargando oficio…</p>
            </div>

            <div v-else class="oficio-plantilla">
              <div class="oficio-sheet">
                <div class="oficio-header">
                  <div class="oficio-logos">
                    <img src="/assets/images/oficio/escudo.png" alt="Escudo" class="logo-escudo">
                    <img src="/assets/images/oficio/header-qroo.png" alt="Quintana Roo" class="logo-qroo">
                  </div>

                  <div class="meta-block">
                    <div class="meta-entidad">Archivo General del Estado de Quintana Roo.</div>

                    <div class="campo-fijo">
                      <span class="campo-fijo-k">Oficio No.</span>
                      <span class="campo-fijo-v">{{ formulario.numero_oficio || '(se asigna al emitir)' }}</span>
                    </div>

                    <div class="campo">
                      <label class="campo-label">
                        ASUNTO
                        <span v-if="!soloLectura" class="tag-edit">editable</span>
                      </label>
                      <input
                        v-if="!soloLectura"
                        v-model="formulario.asunto"
                        type="text"
                        class="campo-input"
                        required
                      >
                      <div v-else class="campo-lectura">{{ formulario.asunto || '—' }}</div>
                    </div>

                    <div class="campo-fijo">
                      {{ lugar }} a {{ fechaLegible }}.
                    </div>

                    <div class="campo">
                      <label class="campo-label">
                        Leyenda del año
                        <span v-if="!soloLectura" class="tag-edit">editable</span>
                      </label>
                      <input
                        v-if="!soloLectura"
                        v-model="formulario.leyenda_anio"
                        type="text"
                        class="campo-input"
                      >
                      <div v-else class="campo-lectura campo-lectura-italic">
                        "{{ formulario.leyenda_anio || '—' }}"
                      </div>
                    </div>
                  </div>
                </div>

                <div class="campo campo-full">
                  <label class="campo-label">
                    Destinatario
                    <span v-if="!soloLectura" class="tag-edit">editable</span>
                  </label>
                  <textarea
                    v-if="!soloLectura"
                    v-model="formulario.destinatario"
                    class="campo-input campo-textarea"
                    rows="3"
                    placeholder="C.P. Nombre Completo&#10;Cargo&#10;Dependencia"
                  ></textarea>
                  <div v-else class="destinatario-lectura">
                    <div v-html="destinatarioHtml"></div>
                    <div class="presente">PRESENTE.</div>
                  </div>
                  <p v-if="!soloLectura" class="campo-hint">Se agregará «PRESENTE.» en el PDF.</p>
                </div>

                <div class="campo campo-full">
                  <label class="campo-label">
                    Cuerpo del oficio
                    <span v-if="!soloLectura" class="tag-edit">editable</span>
                  </label>
                  <textarea
                    v-if="!soloLectura"
                    id="oficio-editor-textarea"
                    ref="editorTextarea"
                    v-model="formulario.contenido"
                  ></textarea>
                  <div v-else class="cuerpo-lectura" v-html="formulario.contenido"></div>
                </div>

                <div class="cierre-fijo">
                  <p class="cierre-texto">
                    Sin otro particular por el momento y agradeciendo de antemano su atención al presente,
                    aprovecho la ocasión para enviarle un cordial saludo.
                  </p>
                  <div class="atentamente">A T E N T A M E N T E</div>
                  <div class="firma-block">
                    <div class="firma-nombre">{{ firmaNombre }}</div>
                    <div class="firma-cargo">{{ firmaCargo }}</div>
                  </div>
                  <div class="ccp">C.c.p.- Archivo.</div>
                </div>
              </div>

              <div class="oficio-pie-preview">
                <img src="/assets/images/oficio/pie-agqroo.png" alt="Pie AGQROO">
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn-ghost" @click="cerrar">Cerrar</button>
            <template v-if="!soloLectura">
              <button type="button" class="btn-outline-brand" :disabled="guardando" @click="guardar(false)">
                Guardar borrador
              </button>
              <button type="button" class="btn-apply" :disabled="guardando" @click="guardar(true)">
                {{ guardando ? 'Guardando…' : 'Guardar y emitir' }}
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

const ETIQUETAS = {
  borrador: 'Borrador',
  emitido: 'Emitido',
  remitido: 'Remitido',
  archivado: 'Archivado',
  firmado: 'Firmado',
  cancelado: 'Cancelado',
};

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
    destinatarioHtml() {
      const t = (this.formulario.destinatario || '').trim();
      if (!t) return '—';
      return t.replace(/\n/g, '<br>');
    },
  },
  methods: {
    etiquetaEstado(estado) {
      return ETIQUETAS[estado] || estado;
    },
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
          'body { font-family:Nunito,Segoe UI,sans-serif; font-size:14px; color:#3D3935; margin:1rem; line-height:1.5 }'
          + ' table { border-collapse:collapse; width:100% }'
          + ' table th, table td { border:1px solid #3D3935; padding:6px 8px }'
          + ' table th { background:#f7e5eb }',
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
      Swal.fire({
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer: 2200,
        confirmButtonColor: '#AB0A3D',
      });
    },
  },
};
</script>

<style scoped>
.oficio-modal {
  --surface: #ffffff;
  --ink: #3D3935;
  --ink-soft: #78736a;
  --line: #e4e0d8;
  --brand: #AB0A3D;
  --brand-dark: #440412;
  --brand-soft: #f7e5eb;
  --stone: #B0ABA1;
  --bg: #f7f5f2;

  border: 1px solid var(--line);
  border-radius: .85rem;
  color: var(--ink);
  font-family: Nunito, 'Segoe UI', sans-serif;
  overflow: hidden;
}

.oficio-modal .modal-header {
  border-bottom: 1px solid var(--line);
  background: #faf8f5;
  align-items: center;
}
.modal-heading {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: .55rem .85rem;
}
.oficio-modal .modal-title {
  margin: 0;
  font-weight: 700;
  font-size: 1.15rem;
  color: var(--ink);
  letter-spacing: -.01em;
}
.modo-badge {
  display: inline-flex;
  align-items: center;
  padding: .18rem .55rem;
  border-radius: 999px;
  font-size: .7rem;
  font-weight: 700;
  letter-spacing: .03em;
  text-transform: uppercase;
  background: var(--brand-soft);
  color: var(--brand);
}
.modo-ver {
  background: color-mix(in srgb, #1d6f8a 12%, #fff);
  color: #1d6f8a;
}
.modo-editar,
.modo-crear {
  background: var(--brand-soft);
  color: var(--brand);
}

.oficio-modal-body {
  max-height: 75vh;
  overflow-y: auto;
  background: var(--bg);
  padding: 1rem 1.1rem 1.25rem;
}

.state-box {
  text-align: center;
  padding: 3rem 1rem;
}
.spinner-brand {
  color: var(--brand);
  width: 2rem;
  height: 2rem;
}
.state-sub {
  margin: .65rem 0 0;
  color: var(--ink-soft);
  font-size: .85rem;
}

.oficio-plantilla {
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: .75rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(61, 57, 53, .06);
}
.oficio-sheet {
  padding: 1.25rem 1.35rem 1rem;
}

.oficio-header {
  margin-bottom: 1.15rem;
}
.oficio-logos {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: .85rem;
  min-height: 96px;
}
.logo-escudo {
  width: auto;
  height: 96px;
  max-width: 120px;
  object-fit: contain;
  object-position: left center;
  display: block;
}
.logo-qroo {
  width: auto;
  height: 68px;
  max-width: 220px;
  object-fit: contain;
  object-position: right center;
  display: block;
}
.meta-block {
  max-width: 400px;
  margin-left: auto;
  text-align: right;
}
.meta-entidad {
  font-weight: 700;
  text-transform: uppercase;
  font-size: .82rem;
  margin-bottom: .55rem;
  color: var(--ink);
}

.campo {
  margin-bottom: .7rem;
  text-align: left;
}
.campo-full {
  margin-bottom: 1rem;
}
.campo-label {
  display: flex;
  align-items: center;
  gap: .45rem;
  font-size: .72rem;
  font-weight: 700;
  letter-spacing: .04em;
  text-transform: uppercase;
  color: var(--ink-soft);
  margin-bottom: .3rem;
}
.tag-edit {
  font-size: .65rem;
  font-weight: 700;
  letter-spacing: .02em;
  text-transform: uppercase;
  color: var(--brand);
  background: var(--brand-soft);
  border-radius: 999px;
  padding: .1rem .4rem;
}
.campo-input {
  width: 100%;
  border: 1px solid var(--line);
  border-radius: .5rem;
  background: #fff;
  padding: .5rem .7rem;
  font-size: .9rem;
  color: var(--ink);
  font-family: inherit;
}
.campo-input:focus {
  outline: none;
  border-color: var(--brand);
  box-shadow: 0 0 0 .2rem color-mix(in srgb, var(--brand) 18%, transparent);
}
.campo-input:disabled {
  background: #f4f2ee;
  color: var(--ink-soft);
}
.campo-textarea {
  resize: vertical;
  min-height: 4.5rem;
  line-height: 1.4;
}
.campo-hint {
  margin: .35rem 0 0;
  font-size: .78rem;
  color: var(--ink-soft);
}
.campo-fijo {
  background: var(--brand-soft);
  border-left: 3px solid var(--brand);
  padding: .4rem .65rem;
  border-radius: .35rem;
  font-size: .85rem;
  text-align: left;
  margin-bottom: .65rem;
  color: var(--ink);
}
.campo-fijo-k {
  font-weight: 700;
  margin-right: .35rem;
}
.campo-fijo-v {
  font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
  font-size: .8rem;
  color: var(--brand-dark);
}
.campo-lectura {
  font-size: .95rem;
  font-weight: 600;
  color: var(--ink);
  padding: .15rem 0;
}
.campo-lectura-italic {
  font-weight: 500;
  font-style: italic;
  font-size: .88rem;
  color: var(--ink-soft);
}

.destinatario-lectura {
  font-weight: 700;
  text-transform: uppercase;
  font-size: .92rem;
  line-height: 1.4;
  color: var(--ink);
}
.presente {
  margin-top: .35rem;
}

.cuerpo-lectura {
  border: 1px solid var(--line);
  border-radius: .55rem;
  background: #faf8f5;
  padding: 1rem 1.1rem;
  font-size: .92rem;
  line-height: 1.5;
  color: var(--ink);
}
.cuerpo-lectura :deep(table) {
  width: 100%;
  border-collapse: collapse;
  margin: .75rem 0;
  font-size: .85rem;
}
.cuerpo-lectura :deep(th),
.cuerpo-lectura :deep(td) {
  border: 1px solid var(--ink);
  padding: .4rem .55rem;
}
.cuerpo-lectura :deep(th) {
  background: var(--brand-soft);
}

.cierre-fijo {
  margin-top: 1.25rem;
  padding-top: 1rem;
  border-top: 1px dashed var(--line);
}
.cierre-texto {
  font-size: .9rem;
  text-align: justify;
  color: var(--ink);
  margin: 0 0 1.25rem;
}
.atentamente {
  text-align: center;
  font-weight: 800;
  letter-spacing: .28em;
  font-size: .95rem;
  margin-bottom: 2rem;
  color: var(--ink);
}
.firma-block {
  text-align: center;
  margin-bottom: 1.25rem;
}
.firma-nombre {
  font-weight: 700;
  font-size: .95rem;
}
.firma-cargo {
  font-size: .82rem;
  color: var(--ink-soft);
  margin-top: .15rem;
}
.ccp {
  font-size: .78rem;
  color: var(--ink-soft);
}

.oficio-pie-preview {
  margin-top: .5rem;
  line-height: 0;
}
.oficio-pie-preview img {
  width: 100%;
  height: auto;
  display: block;
}

.oficio-modal .modal-footer {
  border-top: 1px solid var(--line);
  background: #faf8f5;
  gap: .5rem;
  flex-wrap: wrap;
}

.btn-apply {
  border: none;
  border-radius: .55rem;
  background: var(--brand);
  color: #fff;
  font-weight: 700;
  font-size: .88rem;
  font-family: inherit;
  padding: .55rem 1.1rem;
}
.btn-apply:hover:not(:disabled) {
  background: var(--brand-dark);
}
.btn-apply:disabled {
  opacity: .55;
  cursor: not-allowed;
}
.btn-outline-brand {
  border: 1px solid color-mix(in srgb, var(--brand) 45%, var(--line));
  border-radius: .55rem;
  background: #fff;
  color: var(--brand);
  font-weight: 700;
  font-size: .88rem;
  font-family: inherit;
  padding: .5rem 1rem;
}
.btn-outline-brand:hover:not(:disabled) {
  background: var(--brand-soft);
}
.btn-outline-brand:disabled {
  opacity: .55;
  cursor: not-allowed;
}
.btn-ghost {
  border: 1px solid var(--line);
  border-radius: .55rem;
  background: var(--surface);
  color: var(--ink);
  font-weight: 600;
  font-size: .88rem;
  font-family: inherit;
  padding: .5rem 1rem;
}
.btn-ghost:hover {
  background: #f5f7fb;
  border-color: var(--brand);
}

@media (max-width: 768px) {
  .oficio-logos {
    flex-direction: column;
    align-items: center;
    min-height: 0;
  }
  .meta-block {
    max-width: none;
    margin-left: 0;
    text-align: left;
  }
  .logo-escudo {
    height: 88px;
  }
  .logo-qroo {
    height: 58px;
  }
}
</style>
