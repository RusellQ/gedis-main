<template>
  <div class="form-container">
    <div class="caratula-background">
      <h1>Catálogo de clasificación</h1>

      <div class="selector-expediente caratula-form mb-4">
        <h5 class="selector-titulo">Ubicación en el catálogo archivístico</h5>
        <p class="selector-ayuda text-muted">
          Elija el tipo de nivel a administrar y, si aplica, el padre en la cascada fondo, sección, serie y subfondo/subserie.
        </p>

        <div class="row g-3">
          <div class="col-md-4">
            <label>Tipo de nivel</label>
            <select v-model="activeTab" class="form-control" @change="onTipoNivelChange">
              <option v-for="tab in tabs" :key="tab.key" :value="tab.key">{{ tab.label }}</option>
            </select>
          </div>

          <div class="col-md-4" v-if="activeTab !== 'fondo'">
            <label>Fondo</label>
            <select v-model="selected.fondo" class="form-control" @change="onFondoChange">
              <option disabled value="">Selecciona un fondo</option>
              <option v-for="fondo in fondos" :key="fondo.id" :value="fondo.id">{{ fondo.nombre }}</option>
            </select>
          </div>

          <div class="col-md-4" v-if="selected.fondo && (activeTab === 'seccion' || needsSeccion)">
            <label>Subfondo (opcional)</label>
            <select v-model="selected.subfondo" class="form-control" @change="onSubfondoChange">
              <option value="">Sin subfondo</option>
              <option v-for="subfondo in subfondos" :key="subfondo.id" :value="subfondo.id">
                {{ subfondo.nombre }}
              </option>
            </select>
          </div>

          <div class="col-md-4" v-if="selected.fondo && needsSeccion">
            <label>Sección</label>
            <select v-model="selected.seccion" class="form-control" @change="onSeccionChange">
              <option disabled value="">Selecciona una sección</option>
              <option v-for="seccion in secciones" :key="seccion.id" :value="seccion.id">{{ seccion.nombre }}</option>
            </select>
            <p v-if="!secciones.length" class="text-muted small mt-1">No hay secciones en este contexto.</p>
          </div>

          <div class="col-md-4" v-if="selected.seccion && needsSerie">
            <label>Serie</label>
            <select v-model="selected.serie" class="form-control" @change="onSerieChange">
              <option disabled value="">Selecciona una serie</option>
              <option v-for="serie in seriesList" :key="serie.id" :value="serie.id">{{ serie.nombre }}</option>
            </select>
            <p v-if="!seriesList.length" class="text-muted small mt-1">Esta sección no tiene series.</p>
          </div>
        </div>
      </div>

      <div class="caratula-form">
        <form @submit.prevent="guardar">
          <FormSectionHeader
            :title="formSectionTitle"
            :badge="editingId ? 'Edición' : 'Captura manual'"
            :badge-class="editingId ? 'bg-warning text-dark' : 'bg-primary'"
            variant="session"
            :description="formSectionDescription"
          />

          <div v-if="parentHint && !editingId" class="alert alert-warning small py-2 mb-3">
            {{ parentHint }}
          </div>

          <div class="row g-3">
            <div class="col-md-6" v-if="activeTab === 'fondo'">
              <label>Dependencia</label>
              <select
                v-model="form.id_dependencia"
                class="form-control"
                :disabled="!!editingId"
                @change="cargarDepartamentos"
                required
              >
                <option disabled value="">Seleccione una dependencia</option>
                <option v-for="d in dependencias" :key="d.id" :value="d.id">{{ d.nombre }}</option>
              </select>
            </div>

            <div class="col-md-6" v-if="activeTab === 'fondo'">
              <label>Departamento</label>
              <select
                v-model="form.id_departamento"
                class="form-control"
                :disabled="!!editingId || !form.id_dependencia"
                required
              >
                <option disabled value="">Seleccione un departamento</option>
                <option v-for="d in departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
              </select>
            </div>

            <div class="col-md-6">
              <label>Nombre</label>
              <input v-model="form.nombre" type="text" class="form-control" required />
            </div>

            <div class="col-md-6">
              <label>Código</label>
              <input v-model="form.codigo" type="text" class="form-control" required />
            </div>

            <div class="col-12">
              <label>Descripción</label>
              <textarea v-model="form.descripcion" class="form-control" rows="2"></textarea>
            </div>
          </div>

          <template v-if="activeTab === 'serie'">
            <FormSectionHeader
              class="mt-4"
              title="Reglas documentales / CADIDO"
              badge="Captura manual"
              badge-class="bg-primary"
              variant="catalog"
              description="Defina vigencias, plazos de conservación y técnicas de selección para la serie."
            />

            <div class="row g-3">
              <div class="col-md-4">
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" id="vigencia_ad" v-model="form.vigencia_ad" />
                  <label class="form-check-label" for="vigencia_ad">Vigencia administrativo</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" id="vigencia_le" v-model="form.vigencia_le" />
                  <label class="form-check-label" for="vigencia_le">Legal</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" id="vigencia_confi" v-model="form.vigencia_confi" />
                  <label class="form-check-label" for="vigencia_confi">Confidencial / contable</label>
                </div>
              </div>
              <div class="col-md-4">
                <label>Plazo AT</label>
                <input v-model.number="form.plazo_at" type="number" min="0" class="form-control" />
              </div>
              <div class="col-md-4">
                <label>Plazo AC</label>
                <input v-model.number="form.plazo_ac" type="number" min="0" class="form-control" />
              </div>
              <div class="col-md-4">
                <label>Total años</label>
                <input v-model.number="form.plazo_ta" type="number" min="0" class="form-control" />
              </div>
              <div class="col-md-4">
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" id="tec_elim" v-model="form.tecnica_eliminacion" />
                  <label class="form-check-label" for="tec_elim">Eliminación</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" id="tec_cons" v-model="form.tecnica_conservacion" />
                  <label class="form-check-label" for="tec_cons">Conservación</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" id="tec_muest" v-model="form.tecnica_muestreo" />
                  <label class="form-check-label" for="tec_muest">Muestreo</label>
                </div>
              </div>
              <div class="col-md-6">
                <label>Clasificación reservada</label>
                <input v-model="form.clasificacion_r" type="text" class="form-control" />
              </div>
              <div class="col-md-6">
                <label>Clasificación confidencial</label>
                <input v-model="form.clasificacion_c" type="text" class="form-control" />
              </div>
              <div class="col-12">
                <label>Observaciones</label>
                <textarea v-model="form.observaciones" class="form-control" rows="2"></textarea>
              </div>
            </div>
          </template>

          <button type="submit" class="btn btn-success me-2" :disabled="saving || (!!parentHint && !editingId)">
            {{ submitLabel }}
          </button>
          <button v-if="editingId" type="button" class="btn btn-secondary" @click="resetForm">
            Cancelar edición
          </button>
        </form>

        <hr class="my-4" />

        <FormSectionHeader
          :title="listSectionTitle"
          badge="Consulta"
          badge-class="bg-secondary"
          variant="catalog"
          :description="listSectionDescription"
        />

        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="table-light">
              <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th style="width: 180px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in currentList" :key="item.id">
                <td>{{ item.codigo }}</td>
                <td>{{ item.nombre }}</td>
                <td>{{ item.descripcion || '—' }}</td>
                <td>
                  <button type="button" class="btn btn-sm btn-primary me-1" @click="editar(item)">Editar</button>
                  <button type="button" class="btn btn-sm btn-danger" @click="eliminar(item)">Eliminar</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-if="currentList.length === 0" class="text-muted mb-0">No hay registros en este nivel.</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import Swal from 'sweetalert2'
import FormSectionHeader from '../components/FormSectionHeader.vue'

const emptySerieExtra = () => ({
  vigencia_ad: false,
  vigencia_le: false,
  vigencia_confi: false,
  plazo_at: null,
  plazo_ac: null,
  plazo_ta: null,
  tecnica_eliminacion: false,
  tecnica_conservacion: false,
  tecnica_muestreo: false,
  clasificacion_r: '',
  clasificacion_c: '',
  observaciones: '',
})

export default {
  name: 'AdminCatalogo',
  components: { FormSectionHeader },
  data() {
    return {
      activeTab: 'fondo',
      tabs: [
        { key: 'fondo', label: 'Fondo' },
        { key: 'subfondo', label: 'Subfondo' },
        { key: 'seccion', label: 'Sección' },
        { key: 'serie', label: 'Serie' },
        { key: 'subserie', label: 'Subserie' },
      ],
      dependencias: [],
      departamentos: [],
      fondos: [],
      subfondos: [],
      secciones: [],
      seriesList: [],
      subseries: [],
      selected: {
        fondo: '',
        subfondo: '',
        seccion: '',
        serie: '',
      },
      form: {
        nombre: '',
        codigo: '',
        descripcion: '',
        id_dependencia: '',
        id_departamento: '',
        ...emptySerieExtra(),
      },
      editingId: null,
      saving: false,
    }
  },
  computed: {
    tabLabel() {
      const map = {
        fondo: 'Fondo',
        subfondo: 'Subfondo',
        seccion: 'Sección',
        serie: 'Serie',
        subserie: 'Subserie',
      }
      return map[this.activeTab]
    },
    needsSeccion() {
      return this.activeTab === 'serie' || this.activeTab === 'subserie'
    },
    needsSerie() {
      return this.activeTab === 'subserie'
    },
    currentList() {
      if (this.activeTab === 'fondo') return this.fondos
      if (this.activeTab === 'subfondo') return this.subfondos
      if (this.activeTab === 'seccion') return this.secciones
      if (this.activeTab === 'serie') return Array.isArray(this.seriesList) ? this.seriesList : []
      return this.subseries
    },
    listContext() {
      if (this.activeTab === 'subfondo' && this.selected.fondo) return ' del fondo seleccionado'
      if (this.activeTab === 'seccion' && this.selected.fondo) return ' del contexto seleccionado'
      if (this.activeTab === 'serie' && this.selected.seccion) return ' de la sección seleccionada'
      if (this.activeTab === 'subserie' && this.selected.serie) return ' de la serie seleccionada'
      return ''
    },
    parentHint() {
      if (this.activeTab === 'subfondo' && !this.selected.fondo) {
        return 'Seleccione un fondo antes de crear un subfondo.'
      }
      if (this.activeTab === 'seccion' && !this.selected.fondo) {
        return 'Seleccione un fondo (y opcionalmente un subfondo) antes de crear una sección.'
      }
      if (this.activeTab === 'serie' && !this.selected.seccion) {
        return 'Seleccione una sección antes de crear una serie.'
      }
      if (this.activeTab === 'subserie' && !this.selected.serie) {
        return 'Seleccione una serie antes de crear una subserie.'
      }
      return ''
    },
    formSectionTitle() {
      return this.editingId ? `Editar ${this.tabLabel.toLowerCase()}` : `Datos del ${this.tabLabel.toLowerCase()} a capturar`
    },
    formSectionDescription() {
      if (this.editingId) {
        return `Modifique los campos del ${this.tabLabel.toLowerCase()} seleccionado.`
      }
      return `Complete la información del ${this.tabLabel.toLowerCase()} en el catálogo archivístico.`
    },
    listSectionTitle() {
      return `${this.tabLabel}s registrados${this.listContext}`
    },
    listSectionDescription() {
      return `Listado de ${this.tabLabel.toLowerCase()}s existentes en el contexto seleccionado.`
    },
    submitLabel() {
      if (this.editingId) {
        return `Actualizar ${this.tabLabel.toLowerCase()}`
      }
      return `Guardar ${this.tabLabel.toLowerCase()}`
    },
  },
  mounted() {
    this.cargarDependencias()
    this.cargarFondos()
  },
  methods: {
    onTipoNivelChange() {
      this.resetForm()
      this.selected = { fondo: '', subfondo: '', seccion: '', serie: '' }
      this.subfondos = []
      this.secciones = []
      this.seriesList = []
      this.subseries = []
      if (this.activeTab === 'fondo') {
        this.cargarFondos()
      }
    },
    async cargarDependencias() {
      const res = await axios.get('/indexdependencias')
      this.dependencias = res.data
    },
    async cargarDepartamentos() {
      this.form.id_departamento = ''
      this.departamentos = []
      if (!this.form.id_dependencia) return
      const res = await axios.get('/indexdepartamentos', {
        params: { dependencia_id: this.form.id_dependencia },
      })
      this.departamentos = res.data
    },
    async cargarFondos() {
      const res = await axios.get('/mostrarfondos')
      this.fondos = res.data
    },
    async onFondoChange() {
      this.selected.subfondo = ''
      this.selected.seccion = ''
      this.selected.serie = ''
      this.subfondos = []
      this.secciones = []
      this.seriesList = []
      this.subseries = []
      if (!this.selected.fondo) return
      const res = await axios.get(`/fondos/${this.selected.fondo}/subfondos`)
      this.subfondos = res.data
      if (this.activeTab === 'subfondo') return
      if (this.activeTab === 'seccion' || this.needsSeccion) {
        await this.cargarSecciones()
      }
    },
    async onSubfondoChange() {
      this.selected.seccion = ''
      this.selected.serie = ''
      this.seriesList = []
      this.subseries = []
      if (this.activeTab === 'seccion' || this.needsSeccion) {
        await this.cargarSecciones()
      }
    },
    async cargarSecciones() {
      this.secciones = []
      if (!this.selected.fondo) return
      const url = this.selected.subfondo
        ? `/subfondos/${this.selected.subfondo}/secciones`
        : `/fondos/${this.selected.fondo}/secciones`
      const res = await axios.get(url)
      this.secciones = res.data
    },
    async onSeccionChange() {
      this.selected.serie = ''
      this.seriesList = []
      this.subseries = []
      if (!this.selected.seccion) return
      const res = await axios.get(`/secciones/${this.selected.seccion}/series`)
      this.seriesList = Array.isArray(res.data) ? res.data : []
      if (this.activeTab === 'subserie' && this.selected.serie) {
        await this.onSerieChange()
      }
    },
    async onSerieChange() {
      this.subseries = []
      if (!this.selected.serie) return
      const res = await axios.get(`/series/${this.selected.serie}/subseries`)
      this.subseries = res.data.data || res.data || []
    },
    resetForm() {
      this.editingId = null
      this.form = {
        nombre: '',
        codigo: '',
        descripcion: '',
        id_dependencia: '',
        id_departamento: '',
        ...emptySerieExtra(),
      }
      this.departamentos = []
    },
    editar(item) {
      this.editingId = item.id
      this.form.nombre = item.nombre || ''
      this.form.codigo = item.codigo || ''
      this.form.descripcion = item.descripcion || ''
      if (this.activeTab === 'fondo') {
        this.form.id_dependencia = item.id_dependencia || ''
        this.form.id_departamento = item.id_departamento || ''
        if (this.form.id_dependencia) {
          axios.get('/indexdepartamentos', {
            params: { dependencia_id: this.form.id_dependencia },
          }).then((res) => {
            this.departamentos = res.data
          })
        }
      }
      if (this.activeTab === 'serie') {
        Object.assign(this.form, {
          vigencia_ad: !!item.vigencia_ad,
          vigencia_le: !!item.vigencia_le,
          vigencia_confi: !!item.vigencia_confi,
          plazo_at: item.plazo_at,
          plazo_ac: item.plazo_ac,
          plazo_ta: item.plazo_ta,
          tecnica_eliminacion: !!item.tecnica_eliminacion,
          tecnica_conservacion: !!item.tecnica_conservacion,
          tecnica_muestreo: !!item.tecnica_muestreo,
          clasificacion_r: item.clasificacion_r || '',
          clasificacion_c: item.clasificacion_c || '',
          observaciones: item.observaciones || '',
        })
      }
    },
    async guardar() {
      if (this.parentHint && !this.editingId) {
        Swal.fire({ icon: 'warning', title: 'Falta contexto', text: this.parentHint })
        return
      }
      this.saving = true
      try {
        if (this.editingId) {
          await this.actualizar()
        } else {
          await this.crear()
        }
        Swal.fire({ icon: 'success', title: 'Éxito', text: 'Operación realizada correctamente.' })
        this.resetForm()
        await this.refrescarNivelActual()
      } catch (error) {
        this.mostrarError(error)
      } finally {
        this.saving = false
      }
    },
    async crear() {
      if (this.activeTab === 'fondo') {
        await axios.post('/guardarfondos', {
          nombre: this.form.nombre,
          codigo: this.form.codigo,
          descripcion: this.form.descripcion,
          id_dependencia: this.form.id_dependencia,
          id_departamento: this.form.id_departamento,
        })
        return
      }
      if (this.activeTab === 'subfondo') {
        await axios.post('/guardarsubfondo', {
          nombre: this.form.nombre,
          codigo: this.form.codigo,
          descripcion: this.form.descripcion,
          id_fondo: this.selected.fondo,
        })
        return
      }
      if (this.activeTab === 'seccion') {
        await axios.post(`/fondos/${this.selected.fondo}/secciones`, {
          nombre: this.form.nombre,
          codigo: this.form.codigo,
          descripcion: this.form.descripcion,
          id_subfondo: this.selected.subfondo || null,
        })
        return
      }
      if (this.activeTab === 'serie') {
        await axios.post('/guardarserie', {
          ...this.payloadSerie(),
          id_seccion: this.selected.seccion,
        })
        return
      }
      await axios.post('/guardarSubserie', {
        nombre: this.form.nombre,
        codigo: this.form.codigo,
        descripcion: this.form.descripcion,
        id_serie: this.selected.serie,
      })
    },
    async actualizar() {
      const id = this.editingId
      if (this.activeTab === 'fondo') {
        await axios.put(`/admin/catalogo/fondos/${id}`, {
          nombre: this.form.nombre,
          codigo: this.form.codigo,
          descripcion: this.form.descripcion,
          id_dependencia: this.form.id_dependencia,
          id_departamento: this.form.id_departamento,
        })
        return
      }
      if (this.activeTab === 'subfondo') {
        await axios.put(`/admin/catalogo/subfondos/${id}`, {
          nombre: this.form.nombre,
          codigo: this.form.codigo,
          descripcion: this.form.descripcion,
        })
        return
      }
      if (this.activeTab === 'seccion') {
        await axios.put(`/admin/catalogo/secciones/${id}`, {
          nombre: this.form.nombre,
          codigo: this.form.codigo,
          descripcion: this.form.descripcion,
        })
        return
      }
      if (this.activeTab === 'serie') {
        await axios.put(`/admin/catalogo/series/${id}`, this.payloadSerie())
        return
      }
      await axios.put(`/admin/catalogo/subseries/${id}`, {
        nombre: this.form.nombre,
        codigo: this.form.codigo,
        descripcion: this.form.descripcion,
      })
    },
    payloadSerie() {
      return {
        nombre: this.form.nombre,
        codigo: this.form.codigo,
        descripcion: this.form.descripcion,
        vigencia_ad: !!this.form.vigencia_ad,
        vigencia_le: !!this.form.vigencia_le,
        vigencia_confi: !!this.form.vigencia_confi,
        plazo_at: this.form.plazo_at,
        plazo_ac: this.form.plazo_ac,
        plazo_ta: this.form.plazo_ta,
        tecnica_eliminacion: !!this.form.tecnica_eliminacion,
        tecnica_conservacion: !!this.form.tecnica_conservacion,
        tecnica_muestreo: !!this.form.tecnica_muestreo,
        clasificacion_r: this.form.clasificacion_r,
        clasificacion_c: this.form.clasificacion_c,
        observaciones: this.form.observaciones,
      }
    },
    async eliminar(item) {
      const confirm = await Swal.fire({
        icon: 'warning',
        title: '¿Eliminar?',
        text: `Se eliminará ${this.tabLabel.toLowerCase()} "${item.nombre}". Esta acción no se puede deshacer.`,
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
      })
      if (!confirm.isConfirmed) return

      try {
        const paths = {
          fondo: `/admin/catalogo/fondos/${item.id}`,
          subfondo: `/admin/catalogo/subfondos/${item.id}`,
          seccion: `/admin/catalogo/secciones/${item.id}`,
          serie: `/admin/catalogo/series/${item.id}`,
          subserie: `/admin/catalogo/subseries/${item.id}`,
        }
        await axios.delete(paths[this.activeTab])
        Swal.fire({ icon: 'success', title: 'Eliminado', text: 'Registro eliminado correctamente.' })
        if (this.editingId === item.id) this.resetForm()
        await this.refrescarNivelActual()
      } catch (error) {
        this.mostrarError(error)
      }
    },
    async refrescarNivelActual() {
      if (this.activeTab === 'fondo') {
        await this.cargarFondos()
        return
      }
      if (this.activeTab === 'subfondo') {
        if (this.selected.fondo) {
          const res = await axios.get(`/fondos/${this.selected.fondo}/subfondos`)
          this.subfondos = res.data
        }
        return
      }
      if (this.activeTab === 'seccion') {
        await this.cargarSecciones()
        return
      }
      if (this.activeTab === 'serie') {
        if (!this.selected.seccion) return
        const res = await axios.get(`/secciones/${this.selected.seccion}/series`)
        this.seriesList = Array.isArray(res.data) ? res.data : []
        return
      }
      if (this.selected.serie) {
        const res = await axios.get(`/series/${this.selected.serie}/subseries`)
        this.subseries = res.data.data || res.data || []
      }
    },
    mostrarError(error) {
      const data = error.response?.data
      let text = data?.message || 'Ocurrió un error inesperado.'
      if (data?.errors) {
        text = Object.values(data.errors).flat().join(' ')
      }
      Swal.fire({ icon: 'error', title: 'Error', text })
    },
  },
}
</script>

<style scoped>
.form-container {
  position: relative;
  width: 100%;
  min-height: 100vh;
}

.caratula-background {
  background-size: cover;
  background-position: center;
  padding: 40px;
  position: relative;
  color: #000;
}

h1 {
  color: #000;
  text-align: center;
  font-size: 2em;
  margin-bottom: 30px;
}

.caratula-form {
  background: rgba(255, 255, 255, 0.7);
  padding: 40px;
  border-radius: 8px;
  max-width: 8000px;
  margin: 0 auto;
  box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
}

.selector-expediente {
  border: 1px dashed #6969f4;
}

.selector-titulo {
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.selector-ayuda {
  font-size: 0.95rem;
  margin-bottom: 1rem;
}

.form-control {
  width: 100%;
  padding: 8px;
  margin-top: 8px;
  border-radius: 4px;
}

.btn {
  margin-top: 20px;
}

.btn-sm {
  margin-top: 0;
}
</style>
