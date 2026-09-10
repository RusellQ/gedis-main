<template>
  <div class="p-3">
    <div class="mb-3">
      <h2 class="h4 mb-0">Recepción / Archivo</h2>
      <p class="text-muted small mb-0">
        Oficios remitidos a ti. Archívalos en un expediente existente o crea uno y agrégalos como unidad documental.
      </p>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="cargando" class="text-center py-4">
          <div class="spinner-border text-primary"></div>
        </div>
        <div v-else-if="items.length === 0" class="alert alert-info mb-0">
          No tienes oficios remitidos pendientes.
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Número</th>
                <th>Asunto</th>
                <th>De</th>
                <th>Estado</th>
                <th>Remitido</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td><small>{{ item.numero_oficio || '—' }}</small></td>
                <td>{{ item.asunto }}</td>
                <td>{{ item.usuario_autor }}</td>
                <td><span class="badge" :class="badgeEstado(item.estado)">{{ item.estado }}</span></td>
                <td><small>{{ item.remitido_at || '—' }}</small></td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-info" title="Ver" @click="ver(item.id)">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-secondary" title="PDF" @click="pdf(item.id)">
                      <i class="fas fa-download"></i>
                    </button>
                    <button
                      class="btn btn-outline-success"
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
      </div>
    </div>

    <OficioEditor ref="editor" />

    <div ref="modalArchivar" class="modal fade" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Archivar oficio en expediente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p class="small mb-3">
              Oficio: <strong>{{ archivar.asunto }}</strong>
              <span v-if="archivar.numero_oficio" class="text-muted">({{ archivar.numero_oficio }})</span>
            </p>
            <SelectorExpedienteCascada v-model="archivar.idExpediente" :permitir-crear="true" />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button
              type="button"
              class="btn btn-success"
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

export default {
  name: 'CorrespondenciaRecepcion',
  components: { OficioEditor, SelectorExpedienteCascada },
  data() {
    return {
      items: [],
      cargando: false,
      modalArchivar: null,
      archivando: false,
      archivar: { id: null, asunto: '', numero_oficio: '', idExpediente: '' },
    };
  },
  mounted() {
    this.cargar();
    if (this.$refs.modalArchivar) {
      this.modalArchivar = new Modal(this.$refs.modalArchivar);
    }
  },
  methods: {
    badgeEstado(estado) {
      return {
        remitido: 'bg-info text-dark',
        archivado: 'bg-success',
        firmado: 'bg-dark',
      }[estado] || 'bg-secondary';
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
