<template>
  <div class="corr-page">
    <header class="page-head">
      <div>
        <h1 class="page-title">Bandeja / Remisión</h1>
        <p class="page-sub">Crea oficios, emítelos y remítelos al área designada.</p>
      </div>
      <button type="button" class="btn-apply" @click="$refs.editor.abrirCrear()">
        <i class="fas fa-plus me-1"></i> Nuevo oficio
      </button>
    </header>

    <div class="toolbar">
      <div class="search-box">
        <span class="search-ico" aria-hidden="true">⌕</span>
        <input
          v-model="busqueda"
          type="search"
          class="search-input"
          placeholder="Buscar por número, asunto o destinatario…"
        >
      </div>
      <p class="results-count">
        {{ filtrados.length }} oficio{{ filtrados.length === 1 ? '' : 's' }}
      </p>
    </div>

    <section class="panel">
      <div v-if="cargando" class="state-box">
        <div class="spinner-border spinner-brand" role="status"></div>
        <p class="state-sub">Cargando bandeja…</p>
      </div>

      <div v-else-if="filtrados.length === 0" class="state-box">
        <div class="empty-ico" aria-hidden="true">✉</div>
        <p class="empty-title">{{ items.length === 0 ? 'No hay oficios en la bandeja' : 'Sin resultados' }}</p>
        <p class="empty-sub">
          {{ items.length === 0 ? 'Crea el primero con «Nuevo oficio».' : 'Prueba con otro término de búsqueda.' }}
        </p>
      </div>

      <div v-else class="table-wrap">
        <table class="corr-table">
          <thead>
            <tr>
              <th>Número</th>
              <th>Asunto</th>
              <th>Estado</th>
              <th>Destinatario interno</th>
              <th>Fecha</th>
              <th class="col-actions">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in filtrados" :key="item.id">
              <td class="cell-mono">{{ item.numero_oficio || '—' }}</td>
              <td class="cell-asunto">{{ item.asunto }}</td>
              <td>
                <span class="exp-badge" :class="'badge-' + item.estado">
                  <span class="exp-badge-dot"></span>{{ etiquetaEstado(item.estado) }}
                </span>
              </td>
              <td>{{ item.usuario_destinatario || '—' }}</td>
              <td class="cell-muted">{{ item.fecha_oficio || item.fecha_creacion }}</td>
              <td class="col-actions">
                <div class="actions">
                  <button type="button" class="btn-icon" title="Ver" @click="$refs.editor.abrirVer(item.id)">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button
                    type="button"
                    class="btn-icon"
                    title="Editar"
                    :disabled="item.estado !== 'borrador'"
                    @click="$refs.editor.abrirEditar(item.id)"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button
                    type="button"
                    class="btn-icon btn-icon-brand"
                    title="Emitir"
                    :disabled="item.estado !== 'borrador'"
                    @click="emitir(item.id)"
                  >
                    <i class="fas fa-file-signature"></i>
                  </button>
                  <button
                    type="button"
                    class="btn-icon btn-icon-brand"
                    title="Remitir"
                    :disabled="!puedeRemitir(item)"
                    @click="abrirRemitir(item)"
                  >
                    <i class="fas fa-share"></i>
                  </button>
                  <button
                    type="button"
                    class="btn-icon"
                    title="Firmar (prueba SAT)"
                    :disabled="['archivado','cancelado'].includes(item.estado) || item.tiene_firma_valida"
                    @click="firmar(item.id)"
                  >
                    <i class="fas fa-pen-fancy"></i>
                  </button>
                  <button type="button" class="btn-icon" title="PDF" @click="pdf(item.id)">
                    <i class="fas fa-download"></i>
                  </button>
                  <button
                    type="button"
                    class="btn-icon btn-icon-danger"
                    title="Eliminar"
                    :disabled="item.estado !== 'borrador'"
                    @click="eliminar(item.id)"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <OficioEditor ref="editor" @guardado="cargar" />

    <div ref="modalRemitir" class="modal fade" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content corr-modal">
          <div class="modal-header">
            <h5 class="modal-title">Remitir oficio</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p class="page-sub mb-3">Selecciona a la persona designada de tu dependencia.</p>
            <label class="field-label">Usuario destinatario</label>
            <select v-model="remision.idUsuario" class="field-select">
              <option value="">— Seleccionar —</option>
              <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.nombre }} ({{ u.email }})</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancelar</button>
            <button
              type="button"
              class="btn-apply"
              :disabled="!remision.idUsuario || remitiendo"
              @click="confirmarRemitir"
            >
              {{ remitiendo ? 'Remitiendo…' : 'Remitir' }}
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

const ETIQUETAS = {
  borrador: 'Borrador',
  emitido: 'Emitido',
  remitido: 'Remitido',
  archivado: 'Archivado',
  firmado: 'Firmado',
  cancelado: 'Cancelado',
};

export default {
  name: 'CorrespondenciaBandeja',
  components: { OficioEditor },
  data() {
    return {
      items: [],
      cargando: false,
      busqueda: '',
      usuarios: [],
      remision: { id: null, idUsuario: '' },
      remitiendo: false,
      modalRemitir: null,
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
          item.usuario_destinatario,
          item.estado,
        ].join(' ').toLowerCase();
        return blob.includes(q);
      });
    },
  },
  mounted() {
    this.cargar();
    this.cargarUsuarios();
    if (this.$refs.modalRemitir) {
      this.modalRemitir = new Modal(this.$refs.modalRemitir);
    }
  },
  methods: {
    etiquetaEstado(estado) {
      return ETIQUETAS[estado] || estado;
    },
    puedeRemitir(item) {
      return ['emitido', 'firmado'].includes(item.estado) && !item.usuario_destinatario;
    },
    async cargar() {
      this.cargando = true;
      try {
        const { data } = await axios.get('/api/correspondencia/bandeja');
        this.items = Array.isArray(data) ? data : [];
      } catch (e) {
        this.items = [];
        this.alerta(e.response?.data?.error || 'Error al cargar bandeja', 'error');
      } finally {
        this.cargando = false;
      }
    },
    async cargarUsuarios() {
      try {
        const { data } = await axios.get('/api/correspondencia/usuarios-remision');
        this.usuarios = Array.isArray(data) ? data : [];
      } catch (e) {
        this.usuarios = [];
      }
    },
    async emitir(id) {
      try {
        await axios.post(`/api/correspondencia/${id}/emitir`);
        this.alerta('Oficio emitido', 'success');
        this.cargar();
      } catch (e) {
        this.alerta(e.response?.data?.error || 'No se pudo emitir', 'error');
      }
    },
    abrirRemitir(item) {
      this.remision = { id: item.id, idUsuario: '' };
      this.modalRemitir?.show();
    },
    async confirmarRemitir() {
      this.remitiendo = true;
      try {
        await axios.post(`/api/correspondencia/${this.remision.id}/remitir`, {
          id_usuario_destinatario: this.remision.idUsuario,
        });
        this.modalRemitir?.hide();
        this.alerta('Oficio remitido', 'success');
        this.cargar();
      } catch (e) {
        this.alerta(e.response?.data?.error || 'No se pudo remitir', 'error');
      } finally {
        this.remitiendo = false;
      }
    },
    async firmar(id) {
      const conf = await Swal.fire({
        title: 'Firmar (prueba SAT)',
        text: 'Modo sandbox sin credenciales reales. ¿Continuar?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, firmar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#AB0A3D',
      });
      if (!conf.isConfirmed) return;
      try {
        const { data } = await axios.post(`/api/correspondencia/${id}/firmar`);
        this.alerta(data.mensaje || 'Firmado', 'success');
        this.cargar();
      } catch (e) {
        this.alerta(e.response?.data?.error || 'Error al firmar', 'error');
      }
    },
    pdf(id) {
      window.location.href = `/api/correspondencia/${id}/pdf`;
    },
    async eliminar(id) {
      const conf = await Swal.fire({
        title: '¿Eliminar borrador?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#AB0A3D',
      });
      if (!conf.isConfirmed) return;
      try {
        await axios.delete(`/api/correspondencia/${id}`);
        this.alerta('Eliminado', 'success');
        this.cargar();
      } catch (e) {
        this.alerta(e.response?.data?.error || 'No se pudo eliminar', 'error');
      }
    },
    alerta(title, icon = 'info') {
      Swal.fire({ position: 'top-end', icon, title, showConfirmButton: false, timer: 2200 });
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
.btn-icon-danger:hover:not(:disabled) {
  border-color: #9b1c1c;
  color: #9b1c1c;
  background: #fde8e8;
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
.field-label {
  display: block;
  font-size: .78rem;
  font-weight: 700;
  color: var(--ink-soft);
  text-transform: uppercase;
  letter-spacing: .03em;
  margin-bottom: .35rem;
}
.field-select {
  width: 100%;
  border: 1px solid var(--line);
  border-radius: .55rem;
  padding: .55rem .75rem;
  font-size: .9rem;
  color: var(--ink);
  background: var(--surface);
  font-family: inherit;
}
.field-select:focus {
  outline: none;
  border-color: var(--brand);
  box-shadow: 0 0 0 .2rem color-mix(in srgb, var(--brand) 18%, transparent);
}
</style>
