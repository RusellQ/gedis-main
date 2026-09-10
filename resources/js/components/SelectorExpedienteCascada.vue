<template>
  <div class="selector-expediente">
    <div class="row g-2">
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Fondo</label>
        <select v-model="local.idFondo" class="form-select form-select-sm" @change="onFondo">
          <option value="">— Seleccionar —</option>
          <option v-for="f in fondos" :key="f.id" :value="f.id">{{ f.nombre }}</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Subfondo <span class="text-muted">(opc.)</span></label>
        <select v-model="local.idSubfondo" class="form-select form-select-sm" :disabled="!local.idFondo" @change="onSubfondo">
          <option value="">— Sin subfondo —</option>
          <option v-for="s in subfondos" :key="s.id" :value="s.id">{{ s.nombre }}</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Sección</label>
        <select v-model="local.idSeccion" class="form-select form-select-sm" :disabled="!local.idFondo" @change="onSeccion">
          <option value="">— Seleccionar —</option>
          <option v-for="s in secciones" :key="s.id" :value="s.id">{{ s.nombre }}</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Serie</label>
        <select v-model="local.idSerie" class="form-select form-select-sm" :disabled="!local.idSeccion" @change="onSerie">
          <option value="">— Seleccionar —</option>
          <option v-for="s in series" :key="s.id" :value="s.id">{{ s.nombre }}</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Subserie <span class="text-muted">(opc.)</span></label>
        <select v-model="local.idSubserie" class="form-select form-select-sm" :disabled="!local.idSerie" @change="onSubserie">
          <option value="">— Sin subserie —</option>
          <option v-for="s in subseries" :key="s.id" :value="s.id">{{ s.nombre }}</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Expediente</label>
        <select v-model="local.idExpediente" class="form-select form-select-sm" :disabled="!local.idSerie || cargandoExp" @change="emitir">
          <option value="">{{ cargandoExp ? 'Cargando…' : '— Seleccionar —' }}</option>
          <option v-for="e in expedientes" :key="e.id" :value="e.id">
            {{ e.nombre }} ({{ e.codigo || e.clave || e.id }}) — {{ e.estado }}
          </option>
        </select>
      </div>
    </div>

    <div v-if="permitirCrear && local.idSerie" class="mt-3 border-top pt-3">
      <p class="small text-muted mb-2">Si no existe el expediente, créalo aquí (quedará en trámite):</p>
      <div class="row g-2">
        <div class="col-md-6">
          <input v-model="nuevo.nombre" type="text" class="form-control form-control-sm" placeholder="Nombre del expediente" />
        </div>
        <div class="col-md-4">
          <input v-model="nuevo.codigo" type="text" class="form-control form-control-sm" placeholder="Código (opcional)" />
        </div>
        <div class="col-md-2">
          <button type="button" class="btn btn-sm btn-outline-primary w-100" :disabled="creando" @click="crearExpediente">
            {{ creando ? '…' : 'Crear' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SelectorExpedienteCascada',
  props: {
    modelValue: { type: [Number, String], default: '' },
    permitirCrear: { type: Boolean, default: true },
  },
  emits: ['update:modelValue', 'expediente'],
  data() {
    return {
      local: {
        idFondo: '',
        idSubfondo: '',
        idSeccion: '',
        idSerie: '',
        idSubserie: '',
        idExpediente: this.modelValue || '',
      },
      fondos: [],
      subfondos: [],
      secciones: [],
      series: [],
      subseries: [],
      expedientes: [],
      cargandoExp: false,
      creando: false,
      nuevo: { nombre: '', codigo: '' },
    };
  },
  watch: {
    modelValue(v) {
      this.local.idExpediente = v || '';
    },
  },
  mounted() {
    this.cargarFondos();
  },
  methods: {
    emitir() {
      this.$emit('update:modelValue', this.local.idExpediente);
      const exp = this.expedientes.find((e) => String(e.id) === String(this.local.idExpediente));
      this.$emit('expediente', exp || null);
    },
    async cargarFondos() {
      try {
        const { data } = await axios.get('/mostrarfondos');
        this.fondos = Array.isArray(data) ? data : [];
      } catch (e) {
        this.fondos = [];
      }
    },
    async onFondo() {
      this.local.idSubfondo = '';
      this.local.idSeccion = '';
      this.local.idSerie = '';
      this.local.idSubserie = '';
      this.local.idExpediente = '';
      this.subfondos = [];
      this.secciones = [];
      this.series = [];
      this.subseries = [];
      this.expedientes = [];
      this.emitir();
      if (!this.local.idFondo) return;
      try {
        const { data } = await axios.get(`/fondos/${this.local.idFondo}/subfondos`);
        this.subfondos = Array.isArray(data) ? data : [];
      } catch (e) {
        this.subfondos = [];
      }
      await this.cargarSecciones();
    },
    async onSubfondo() {
      this.local.idSeccion = '';
      this.local.idSerie = '';
      this.local.idSubserie = '';
      this.local.idExpediente = '';
      this.series = [];
      this.subseries = [];
      this.expedientes = [];
      this.emitir();
      await this.cargarSecciones();
    },
    async cargarSecciones() {
      this.secciones = [];
      if (!this.local.idFondo) return;
      try {
        if (this.local.idSubfondo) {
          const { data } = await axios.get(`/subfondos/${this.local.idSubfondo}/secciones`);
          this.secciones = Array.isArray(data) ? data : [];
        } else {
          const { data } = await axios.get(`/fondos/${this.local.idFondo}/secciones`);
          this.secciones = Array.isArray(data) ? data : [];
        }
      } catch (e) {
        this.secciones = [];
      }
    },
    async onSeccion() {
      this.local.idSerie = '';
      this.local.idSubserie = '';
      this.local.idExpediente = '';
      this.series = [];
      this.subseries = [];
      this.expedientes = [];
      this.emitir();
      if (!this.local.idSeccion) return;
      try {
        const { data } = await axios.get(`/secciones/${this.local.idSeccion}/series`);
        this.series = Array.isArray(data) ? data : [];
      } catch (e) {
        this.series = [];
      }
    },
    async onSerie() {
      this.local.idSubserie = '';
      this.local.idExpediente = '';
      this.subseries = [];
      this.expedientes = [];
      this.emitir();
      if (!this.local.idSerie) return;
      try {
        const { data } = await axios.get(`/series/${this.local.idSerie}/subseries`);
        this.subseries = Array.isArray(data) ? data : [];
      } catch (e) {
        this.subseries = [];
      }
      await this.cargarExpedientes();
    },
    async onSubserie() {
      this.local.idExpediente = '';
      this.emitir();
      await this.cargarExpedientes();
    },
    async cargarExpedientes() {
      this.cargandoExp = true;
      this.expedientes = [];
      try {
        if (this.local.idSubserie) {
          const { data } = await axios.get(`/subseries/${this.local.idSubserie}/expedientes`);
          this.expedientes = Array.isArray(data) ? data : [];
        } else if (this.local.idSerie) {
          const { data } = await axios.get(`/series/${this.local.idSerie}/expedientes`);
          this.expedientes = Array.isArray(data) ? data : [];
        }
      } catch (e) {
        this.expedientes = [];
      } finally {
        this.cargandoExp = false;
      }
    },
    async crearExpediente() {
      if (!this.nuevo.nombre?.trim() || !this.local.idSerie) return;
      this.creando = true;
      try {
        const { data } = await axios.post('/guardarexpediente', {
          nombre: this.nuevo.nombre.trim(),
          codigo: this.nuevo.codigo || null,
          id_serie: this.local.idSerie,
          id_subserie: this.local.idSubserie || null,
          estado: 'en_tramite',
        });
        await this.cargarExpedientes();
        const id = data?.id || data?.expediente?.id;
        if (id) {
          this.local.idExpediente = id;
          this.emitir();
        }
        this.nuevo = { nombre: '', codigo: '' };
      } catch (e) {
        const msg = e.response?.data?.message || e.response?.data?.error || 'No se pudo crear el expediente';
        alert(msg);
      } finally {
        this.creando = false;
      }
    },
  },
};
</script>
