<template>
  <div class="p-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <div>
        <h2 class="h4 mb-0">Bandeja / Remisión</h2>
        <p class="text-muted small mb-0">Crea oficios, emítelos y remítelos al área designada.</p>
      </div>
      <button class="btn btn-primary" @click="$refs.editor.abrirCrear()">
        <i class="fas fa-plus me-1"></i> Nuevo oficio
      </button>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="cargando" class="text-center py-4">
          <div class="spinner-border text-primary"></div>
        </div>
        <div v-else-if="items.length === 0" class="alert alert-info mb-0">
          No hay oficios en la bandeja. Crea el primero con «Nuevo oficio».
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Número</th>
                <th>Asunto</th>
                <th>Estado</th>
                <th>Destinatario interno</th>
                <th>Fecha</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td><small>{{ item.numero_oficio || '—' }}</small></td>
                <td>{{ item.asunto }}</td>
                <td><span class="badge" :class="badgeEstado(item.estado)">{{ item.estado }}</span></td>
                <td>{{ item.usuario_destinatario || '—' }}</td>
                <td><small>{{ item.fecha_oficio || item.fecha_creacion }}</small></td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-info" title="Ver" @click="$refs.editor.abrirVer(item.id)">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button
                      class="btn btn-outline-warning"
                      title="Editar"
                      :disabled="item.estado !== 'borrador'"
                      @click="$refs.editor.abrirEditar(item.id)"
                    >
                      <i class="fas fa-edit"></i>
                    </button>
                    <button
                      class="btn btn-outline-primary"
                      title="Emitir"
                      :disabled="item.estado !== 'borrador'"
                      @click="emitir(item.id)"
                    >
                      <i class="fas fa-file-signature"></i>
                    </button>
                    <button
                      class="btn btn-outline-success"
                      title="Remitir"
                      :disabled="!puedeRemitir(item)"
                      @click="abrirRemitir(item)"
                    >
                      <i class="fas fa-share"></i>
                    </button>
                    <button
                      class="btn btn-outline-dark"
                      title="Firmar (prueba SAT)"
                      :disabled="['archivado','cancelado'].includes(item.estado) || item.tiene_firma_valida"
                      @click="firmar(item.id)"
                    >
                      <i class="fas fa-pen-fancy"></i>
                    </button>
                    <button class="btn btn-outline-secondary" title="PDF" @click="pdf(item.id)">
                      <i class="fas fa-download"></i>
                    </button>
                    <button
                      class="btn btn-outline-danger"
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
      </div>
    </div>

    <OficioEditor ref="editor" @guardado="cargar" />

    <div ref="modalRemitir" class="modal fade" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Remitir oficio</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p class="small text-muted">Selecciona a la persona designada de tu dependencia.</p>
            <select v-model="remision.idUsuario" class="form-select">
              <option value="">— Usuario —</option>
              <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.nombre }} ({{ u.email }})</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-success" :disabled="!remision.idUsuario || remitiendo" @click="confirmarRemitir">
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

export default {
  name: 'CorrespondenciaBandeja',
  components: { OficioEditor },
  data() {
    return {
      items: [],
      cargando: false,
      usuarios: [],
      remision: { id: null, idUsuario: '' },
      remitiendo: false,
      modalRemitir: null,
    };
  },
  mounted() {
    this.cargar();
    this.cargarUsuarios();
    if (this.$refs.modalRemitir) {
      this.modalRemitir = new Modal(this.$refs.modalRemitir);
    }
  },
  methods: {
    puedeRemitir(item) {
      return ['emitido', 'firmado'].includes(item.estado) && !item.usuario_destinatario;
    },
    badgeEstado(estado) {
      return {
        borrador: 'bg-secondary',
        emitido: 'bg-primary',
        remitido: 'bg-info text-dark',
        archivado: 'bg-success',
        firmado: 'bg-dark',
        cancelado: 'bg-danger',
      }[estado] || 'bg-secondary';
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
