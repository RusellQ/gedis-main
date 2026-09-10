<template>
  <div class="corr-page">
    <header class="page-head">
      <div>
        <h1 class="page-title">Recepción / Archivo</h1>
        <p class="page-sub">
          Oficios remitidos a ti. Archívalos en un expediente existente o crea uno y agrégalos como unidad documental.
        </p>
      </div>
    </header>

    <div class="toolbar">
      <div class="search-box">
        <span class="search-ico" aria-hidden="true">⌕</span>
        <input
          v-model="busqueda"
          type="search"
          class="search-input"
          placeholder="Buscar por número, asunto o remitente…"
        >
      </div>
      <p class="results-count">
        {{ filtrados.length }} oficio{{ filtrados.length === 1 ? '' : 's' }}
      </p>
    </div>

    <section class="panel">
      <div v-if="cargando" class="state-box">
        <div class="spinner-border spinner-brand" role="status"></div>
        <p class="state-sub">Cargando recepción…</p>
      </div>

      <div v-else-if="filtrados.length === 0" class="state-box">
        <div class="empty-ico" aria-hidden="true">📥</div>
        <p class="empty-title">
          {{ items.length === 0 ? 'No tienes oficios pendientes' : 'Sin resultados' }}
        </p>
        <p class="empty-sub">
          {{ items.length === 0
            ? 'Cuando te remitan un oficio aparecerá aquí.'
            : 'Prueba con otro término de búsqueda.' }}
        </p>
      </div>

      <div v-else class="table-wrap">
        <table class="corr-table">
          <thead>
            <tr>
              <th>Número</th>
              <th>Asunto</th>
              <th>De</th>
              <th>Estado</th>
              <th>Remitido</th>
              <th class="col-actions">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in filtrados" :key="item.id">
              <td class="cell-mono">{{ item.numero_oficio || '—' }}</td>
              <td class="cell-asunto">{{ item.asunto }}</td>
              <td>{{ item.usuario_autor }}</td>
              <td>
                <span class="exp-badge" :class="'badge-' + item.estado">
                  <span class="exp-badge-dot"></span>{{ etiquetaEstado(item.estado) }}
                </span>
              </td>
              <td class="cell-muted">{{ item.remitido_at || '—' }}</td>
              <td class="col-actions">
                <div class="actions">
                  <button type="button" class="btn-icon" title="Ver" @click="ver(item.id)">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button type="button" class="btn-icon" title="PDF" @click="pdf(item.id)">
                    <i class="fas fa-download"></i>
                  </button>
                  <button
                    type="button"
                    class="btn-icon btn-icon-brand"
                    title="Archivar en expediente"
                    :disabled="item.estado === 'archivado'"
                    @click="abrirArchivar(item)"
                  >
                    <i class="fas fa-folder-open"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <OficioEditor ref="editor" />

    <div ref="modalArchivar" class="modal fade" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content corr-modal">
          <div class="modal-header">
            <h5 class="modal-title">Archivar oficio en expediente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="oficio-chip mb-3">
              <span class="oficio-chip-label">Oficio</span>
              <strong>{{ archivar.asunto }}</strong>
              <span v-if="archivar.numero_oficio" class="oficio-chip-num">{{ archivar.numero_oficio }}</span>
            </div>
            <SelectorExpedienteCascada v-model="archivar.idExpediente" :permitir-crear="true" />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancelar</button>
            <button
              type="button"
              class="btn-apply"
              :disabled="!archivar.idExpediente || archivando"
              @click="confirmarArchivar"
            >
              {{ archivando ? 'Archivando…' : 'Archivar como unidad documental' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from 'sweetalert2';
import { Modal } from 'bootstrap';
import OficioEditor from './OficioEditor.vue';
import SelectorExpedienteCascada from '../components/SelectorExpedienteCascada.vue';

const ETIQUETAS = {
  remitido: 'Remitido',
  archivado: 'Archivado',
  firmado: 'Firmado',
  emitido: 'Emitido',
  borrador: 'Borrador',
  cancelado: 'Cancelado',
};

export default {
  name: 'CorrespondenciaRecepcion',
  components: { OficioEditor, SelectorExpedienteCascada },
  data() {
    return {
      items: [],
      cargando: false,
      busqueda: '',
      modalArchivar: null,
      archivando: false,
      archivar: { id: null, asunto: '', numero_oficio: '', idExpediente: '' },
    };
  },
  computed: {
    filtrados() {
      const q = this.busqueda.trim().toLowerCase();
      if (!q) return this.items;
      return this.items.filter((item) => {
        const blob = [
          item.numero_oficio,
          item.asunto,
          item.usuario_autor,
          item.estado,
        ].join(' ').toLowerCase();
        return blob.includes(q);
      });
    },
  },
  mounted() {
    this.cargar();
    if (this.$refs.modalArchivar) {
      this.modalArchivar = new Modal(this.$refs.modalArchivar);
    }
  },
  methods: {
    etiquetaEstado(estado) {
      return ETIQUETAS[estado] || estado;
    },
    async cargar() {
      this.cargando = true;
      try {
        const { data } = await axios.get('/api/correspondencia/recepcion');
        this.items = Array.isArray(data) ? data : [];
      } catch (e) {
        this.items = [];
        this.alerta(e.response?.data?.error || 'Error al cargar recepción', 'error');
      } finally {
        this.cargando = false;
      }
    },
    ver(id) {
      this.$refs.editor.abrirVer(id);
    },
    pdf(id) {
      window.location.href = `/api/correspondencia/${id}/pdf`;
    },
    abrirArchivar(item) {
      this.archivar = {
        id: item.id,
        asunto: item.asunto,
        numero_oficio: item.numero_oficio,
        idExpediente: '',
      };
      this.modalArchivar?.show();
    },
    async confirmarArchivar() {
      this.archivando = true;
      try {
        await axios.post(`/api/correspondencia/${this.archivar.id}/archivar`, {
          id_expediente: this.archivar.idExpediente,
          tipo: 'Oficio',
          nombre: this.archivar.numero_oficio || this.archivar.asunto,
          descripcion: this.archivar.asunto,
        });
        this.modalArchivar?.hide();
        this.alerta('Oficio archivado como unidad documental', 'success');
        this.cargar();
      } catch (e) {
        this.alerta(e.response?.data?.error || 'No se pudo archivar', 'error');
      } finally {
        this.archivando = false;
      }
    },
    alerta(title, icon = 'info') {
      Swal.fire({ position: 'top-end', icon, title, showConfirmButton: false, timer: 2400 });
    },
  },
};
</script>

<style scoped>
.corr-page {
  --bg: #f7f5f2;
  --surface: #ffffff;
  --ink: #3D3935;
  --ink-soft: #78736a;
  --line: #e4e0d8;
  --brand: #AB0A3D;
  --brand-dark: #440412;
  --brand-soft: #f7e5eb;
  --gold: #B68400;
  --gold-soft: #faf1dd;
  --stone: #B0ABA1;

  max-width: 1240px;
  margin: 0 auto;
  padding: 1.75rem 1rem 3rem;
  color: var(--ink);
  font-family: Nunito, 'Segoe UI', sans-serif;
}

.page-head {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 1rem;
}
.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 0 .25rem;
  letter-spacing: -.01em;
  color: var(--ink);
}
.page-sub {
  margin: 0;
  color: var(--ink-soft);
  font-size: .9rem;
}

.toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: .75rem 1.25rem;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}
.search-box {
  position: relative;
  min-width: 260px;
  flex: 1;
  max-width: 420px;
}
.search-ico {
  position: absolute;
  left: .85rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--ink-soft);
}
.search-input {
  width: 100%;
  border: 1px solid var(--line);
  border-radius: .6rem;
  background: var(--surface);
  padding: .55rem .75rem .55rem 2.1rem;
  font-size: .88rem;
  color: var(--ink);
  font-family: inherit;
}
.search-input:focus {
  outline: none;
  border-color: var(--brand);
  box-shadow: 0 0 0 .2rem color-mix(in srgb, var(--brand) 18%, transparent);
}
.results-count {
  margin: 0;
  font-size: .8rem;
  color: var(--ink-soft);
}

.panel {
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: .85rem;
  box-shadow: 0 1px 2px rgba(61, 57, 53, .04);
  overflow: hidden;
}

.state-box {
  text-align: center;
  padding: 3rem 1.5rem;
}
.spinner-brand {
  color: var(--brand);
  width: 2rem;
  height: 2rem;
}
.empty-ico {
  font-size: 2.5rem;
  color: var(--stone);
  margin-bottom: .5rem;
}
.empty-title {
  margin: 0 0 .35rem;
  font-weight: 700;
  color: var(--ink);
}
.empty-sub,
.state-sub {
  margin: .5rem 0 0;
  color: var(--ink-soft);
  font-size: .85rem;
}

.table-wrap {
  overflow-x: auto;
}
.corr-table {
  width: 100%;
  border-collapse: collapse;
  font-size: .88rem;
}
.corr-table thead th {
  text-align: left;
  font-size: .72rem;
  font-weight: 700;
  letter-spacing: .04em;
  text-transform: uppercase;
  color: var(--ink-soft);
  background: #faf8f5;
  border-bottom: 1px solid var(--line);
  padding: .75rem 1rem;
  white-space: nowrap;
}
.corr-table tbody td {
  padding: .85rem 1rem;
  border-bottom: 1px solid var(--line);
  vertical-align: middle;
}
.corr-table tbody tr:last-child td {
  border-bottom: none;
}
.corr-table tbody tr:hover td {
  background: color-mix(in srgb, var(--brand-soft) 55%, #fff);
}
.cell-mono {
  font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
  font-size: .8rem;
  color: var(--brand-dark);
}
.cell-asunto {
  font-weight: 600;
  max-width: 280px;
}
.cell-muted {
  color: var(--ink-soft);
  font-size: .82rem;
  white-space: nowrap;
}
.col-actions {
  text-align: right;
  white-space: nowrap;
}

.exp-badge {
  --badge-color: var(--brand);
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  padding: .2rem .55rem;
  border-radius: 999px;
  background: color-mix(in srgb, var(--badge-color) 12%, #fff);
  color: var(--badge-color);
  font-size: .72rem;
  font-weight: 700;
  letter-spacing: .02em;
  text-transform: uppercase;
}
.exp-badge-dot {
  width: .45rem;
  height: .45rem;
  border-radius: 50%;
  background: var(--badge-color);
}
.badge-borrador { --badge-color: var(--stone); }
.badge-emitido { --badge-color: var(--brand); }
.badge-remitido { --badge-color: #1d6f8a; }
.badge-archivado { --badge-color: #2f6b3a; }
.badge-firmado { --badge-color: var(--brand-dark); }
.badge-cancelado { --badge-color: #9b1c1c; }

.actions {
  display: inline-flex;
  flex-wrap: wrap;
  gap: .25rem;
  justify-content: flex-end;
}
.btn-icon {
  width: 2rem;
  height: 2rem;
  border-radius: .45rem;
  border: 1px solid var(--line);
  background: var(--surface);
  color: var(--ink-soft);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: .78rem;
  transition: .15s ease;
}
.btn-icon:hover:not(:disabled) {
  border-color: var(--brand);
  color: var(--brand);
  background: var(--brand-soft);
}
.btn-icon:disabled {
  opacity: .35;
  cursor: not-allowed;
}
.btn-icon-brand {
  color: var(--brand);
  border-color: color-mix(in srgb, var(--brand) 35%, var(--line));
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
  transition: background .15s ease;
}
.btn-apply:hover:not(:disabled) {
  background: var(--brand-dark);
}
.btn-apply:disabled {
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
.btn-ghost:hover:not(:disabled) {
  background: #f5f7fb;
  border-color: var(--brand);
}

.corr-modal {
  border: 1px solid var(--line);
  border-radius: .85rem;
  color: var(--ink);
  font-family: Nunito, 'Segoe UI', sans-serif;
}
.corr-modal .modal-header {
  border-bottom: 1px solid var(--line);
  background: #faf8f5;
}
.corr-modal .modal-title {
  font-weight: 700;
  color: var(--ink);
}
.corr-modal .modal-footer {
  border-top: 1px solid var(--line);
  gap: .5rem;
}

.oficio-chip {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: .4rem .75rem;
  padding: .75rem 1rem;
  border-radius: .65rem;
  background: var(--brand-soft);
  border: 1px solid color-mix(in srgb, var(--brand) 18%, var(--line));
}
.oficio-chip-label {
  font-size: .7rem;
  font-weight: 700;
  letter-spacing: .04em;
  text-transform: uppercase;
  color: var(--brand);
}
.oficio-chip-num {
  font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
  font-size: .8rem;
  color: var(--brand-dark);
}
</style>
