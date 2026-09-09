<template>
  <div class="catalogo-page">
    <header class="page-head">
      <div>
        <h1 class="page-title">Catálogo de clasificación</h1>
        <p class="page-sub">
          Administre la jerarquía Fondo → (Subfondo) → Sección → Serie → (Subserie) sin saltar niveles.
        </p>
      </div>
      <span class="total-pill">{{ listaFiltrada.length }} {{ tabLabel.toLowerCase() }}{{ listaFiltrada.length === 1 ? '' : 's' }}</span>
    </header>

    <section class="panel cascade-panel">
      <h2 class="panel-title">Ubicación en el catálogo</h2>
      <p class="panel-help">Elija el nivel a administrar y, si aplica, el padre en la cascada.</p>

      <div class="level-tabs" role="tablist" aria-label="Nivel del catálogo">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          type="button"
          class="level-tab"
          :class="{ 'tab-on': activeTab === tab.key }"
          @click="setActiveTab(tab.key)"
        >
          {{ tab.label }}
        </button>
      </div>

      <div class="field-grid">
        <div v-if="activeTab !== 'fondo'" class="field">
          <label>Fondo</label>
          <select v-model="selected.fondo" class="control" @change="onFondoChange">
            <option disabled value="">Selecciona un fondo</option>
            <option v-for="fondo in fondos" :key="fondo.id" :value="fondo.id">{{ fondo.nombre }}</option>
          </select>
        </div>

        <div v-if="selected.fondo && (activeTab === 'seccion' || needsSeccion)" class="field">
          <label>Subfondo (opcional)</label>
          <select v-model="selected.subfondo" class="control" @change="onSubfondoChange">
            <option value="">Sin subfondo</option>
            <option v-for="subfondo in subfondos" :key="subfondo.id" :value="subfondo.id">
              {{ subfondo.nombre }}
            </option>
          </select>
        </div>

        <div v-if="selected.fondo && needsSeccion" class="field">
          <label>Sección</label>
          <select v-model="selected.seccion" class="control" @change="onSeccionChange">
            <option disabled value="">Selecciona una sección</option>
            <option v-for="seccion in secciones" :key="seccion.id" :value="seccion.id">{{ seccion.nombre }}</option>
          </select>
          <p v-if="!secciones.length" class="field-hint">No hay secciones en este contexto.</p>
        </div>

        <div v-if="selected.seccion && needsSerie" class="field">
          <label>Serie</label>
          <select v-model="selected.serie" class="control" @change="onSerieChange">
            <option disabled value="">Selecciona una serie</option>
            <option v-for="serie in seriesList" :key="serie.id" :value="serie.id">{{ serie.nombre }}</option>
          </select>
          <p v-if="!seriesList.length" class="field-hint">Esta sección no tiene series.</p>
        </div>
      </div>
    </section>

    <section class="panel form-panel">
      <form @submit.prevent="guardar">
        <FormSectionHeader
          :title="formSectionTitle"
          :badge="editingId ? 'Edición' : 'Captura'"
          :badge-class="editingId ? 'bg-warning text-dark' : 'bg-primary'"
          variant="session"
          :description="formSectionDescription"
        />

        <div v-if="parentHint && !editingId" class="hint-box">
          {{ parentHint }}
        </div>

        <div class="field-grid">
          <div v-if="activeTab === 'fondo'" class="field">
            <label>Dependencia</label>
            <select
              v-model="form.id_dependencia"
              class="control"
              :disabled="!!editingId"
              required
              @change="cargarDepartamentos"
            >
              <option disabled value="">Seleccione una dependencia</option>
              <option v-for="d in dependencias" :key="d.id" :value="d.id">{{ d.nombre }}</option>
            </select>
          </div>

          <div v-if="activeTab === 'fondo'" class="field">
            <label>Departamento</label>
            <select
              v-model="form.id_departamento"
              class="control"
              :disabled="!!editingId || !form.id_dependencia"
              required
            >
              <option disabled value="">Seleccione un departamento</option>
              <option v-for="d in departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
            </select>
          </div>

          <div class="field">
            <label>Nombre</label>
            <input v-model="form.nombre" type="text" class="control" required>
          </div>

          <div class="field">
            <label>Código</label>
            <input v-model="form.codigo" type="text" class="control" required>
          </div>

          <div class="field field-span">
            <label>Descripción</label>
            <textarea v-model="form.descripcion" class="control" rows="2"></textarea>
          </div>
        </div>

        <template v-if="activeTab === 'serie'">
          <FormSectionHeader
            class="mt-4"
            title="Reglas documentales / CADIDO"
            badge="Serie"
            badge-class="bg-primary"
            variant="catalog"
            description="Defina vigencias, plazos de conservación y técnicas de selección para la serie."
          />

          <div class="check-group">
            <p class="check-group-title">Vigencia documental</p>
            <div class="checks">
              <label class="check">
                <input id="vigencia_ad" v-model="form.vigencia_ad" type="checkbox">
                Administrativo
              </label>
              <label class="check">
                <input id="vigencia_le" v-model="form.vigencia_le" type="checkbox">
                Legal
              </label>
              <label class="check">
                <input id="vigencia_confi" v-model="form.vigencia_confi" type="checkbox">
                Confidencial / contable
              </label>
            </div>
          </div>

          <div class="check-group">
            <p class="check-group-title">Valor documental</p>
            <div class="checks">
              <label class="check">
                <input id="tec_elim" v-model="form.tecnica_eliminacion" type="checkbox">
                Eliminación
              </label>
              <label class="check">
                <input id="tec_cons" v-model="form.tecnica_conservacion" type="checkbox">
                Conservación
              </label>
              <label class="check">
                <input id="tec_muest" v-model="form.tecnica_muestreo" type="checkbox">
                Muestreo
              </label>
            </div>
          </div>

          <div class="field-grid mt-3">
            <div class="field">
              <label>Plazo AT</label>
              <input v-model.number="form.plazo_at" type="number" min="0" class="control">
            </div>
            <div class="field">
              <label>Plazo AC</label>
              <input v-model.number="form.plazo_ac" type="number" min="0" class="control">
            </div>
            <div class="field">
              <label>Total años</label>
              <input v-model.number="form.plazo_ta" type="number" min="0" class="control">
            </div>
          </div>

          <div class="check-group">
            <div class="checks checks-2">
              <label class="check">
                <input id="clasif_r" v-model="form.clasificacion_r" type="checkbox">
                Clasificación de la información reservada
              </label>
              <label class="check">
                <input id="clasif_c" v-model="form.clasificacion_c" type="checkbox">
                Clasificación confidencial
              </label>
            </div>
          </div>

          <div class="field-grid mt-3">
            <div class="field field-span">
              <label>Observaciones</label>
              <textarea v-model="form.observaciones" class="control" rows="2"></textarea>
            </div>
          </div>
        </template>

        <div class="form-actions">
          <button type="submit" class="btn-apply" :disabled="saving || (!!parentHint && !editingId)">
            {{ submitLabel }}
          </button>
          <button v-if="editingId" type="button" class="btn-ghost" @click="resetForm">
            Cancelar edición
          </button>
        </div>
      </form>
    </section>

    <section class="panel list-panel">
      <FormSectionHeader
        :title="listSectionTitle"
        badge="Consulta"
        badge-class="bg-secondary"
        variant="catalog"
        :description="listSectionDescription"
      />

      <input
        v-model="busqueda"
        type="search"
        class="search-input"
        :placeholder="'Buscar ' + tabLabel.toLowerCase() + 's…'"
        :aria-label="'Buscar ' + tabLabel.toLowerCase() + 's'"
      >

      <div v-if="listaFiltrada.length === 0" class="empty">
        <p class="empty-title">No hay registros en este nivel</p>
        <p class="empty-sub">{{ parentHint || 'Capture un registro con el formulario o ajuste la búsqueda.' }}</p>
      </div>

      <div v-else class="table-card">
        <div class="table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in listaFiltrada" :key="item.id">
                <td class="cell-clave">{{ item.codigo || '—' }}</td>
                <td class="cell-nombre">{{ item.nombre }}</td>
                <td>{{ item.descripcion || '—' }}</td>
                <td class="cell-actions">
                  <button type="button" class="btn-action btn-edit" @click="editar(item)">Editar</button>
                  <button type="button" class="btn-action btn-delete" @click="eliminar(item)">Eliminar</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
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
  clasificacion_r: false,
  clasificacion_c: false,
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
      busqueda: '',
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
    listaFiltrada() {
      const texto = this.busqueda.trim().toLowerCase()
      if (!texto) return this.currentList
      return this.currentList.filter((item) =>
        [item.nombre, item.codigo, item.descripcion]
          .filter(Boolean)
          .some((campo) => String(campo).toLowerCase().includes(texto))
      )
    },
  },
  mounted() {
    this.cargarDependencias()
    this.cargarFondos()
  },
  methods: {
    setActiveTab(key) {
      if (this.activeTab === key) return
      this.activeTab = key
      this.busqueda = ''
      this.onTipoNivelChange()
    },
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
          clasificacion_r: !!item.clasificacion_r,
          clasificacion_c: !!item.clasificacion_c,
          observaciones: item.observaciones || '',
        })
      }
    },
    async guardar() {
      if (this.parentHint && !this.editingId) {
        Swal.fire({ icon: 'warning', title: 'Falta contexto', text: this.parentHint, confirmButtonColor: '#AB0A3D' })
        return
      }
      this.saving = true
      try {
        if (this.editingId) {
          await this.actualizar()
        } else {
          await this.crear()
        }
        Swal.fire({ icon: 'success', title: 'Éxito', text: 'Operación realizada correctamente.', confirmButtonColor: '#AB0A3D' })
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
        clasificacion_r: !!this.form.clasificacion_r,
        clasificacion_c: !!this.form.clasificacion_c,
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
        confirmButtonColor: '#AB0A3D',
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
        Swal.fire({ icon: 'success', title: 'Eliminado', text: 'Registro eliminado correctamente.', confirmButtonColor: '#AB0A3D' })
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
      Swal.fire({ icon: 'error', title: 'Error', text, confirmButtonColor: '#AB0A3D' })
    },
  },
}
</script>

<style scoped>
.catalogo-page {
  --surface: #ffffff;
  --ink: #3D3935;
  --ink-soft: #78736a;
  --line: #e4e0d8;
  --brand: #AB0A3D;
  --brand-dark: #440412;
  --brand-soft: #f7e5eb;
  --gold: #B68400;
  --gold-soft: #faf1dd;

  max-width: 1100px;
  margin: 0 auto;
  padding: 1.75rem 1rem 3rem;
  color: var(--ink);
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

.page-head {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 1.25rem;
}
.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 0 .25rem;
  color: var(--brand-dark);
}
.page-sub {
  margin: 0;
  color: var(--ink-soft);
  font-size: .9rem;
  max-width: 42rem;
}
.total-pill {
  font-size: .78rem;
  font-weight: 700;
  color: var(--brand);
  background: var(--brand-soft);
  border-radius: 999px;
  padding: .4rem .8rem;
}

.panel {
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: .95rem;
  padding: 1.1rem 1.15rem 1.2rem;
  margin-bottom: 1.15rem;
  box-shadow: 0 8px 20px rgba(68, 4, 18, .05);
}
.cascade-panel { border-top: 4px solid var(--gold); }
.form-panel { border-top: 4px solid var(--brand); }
.list-panel { border-top: 4px solid var(--line); }

.panel-title {
  margin: 0 0 .35rem;
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--brand-dark);
}
.panel-help {
  margin: 0 0 1rem;
  color: var(--ink-soft);
  font-size: .88rem;
}

.level-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: .4rem;
  margin-bottom: 1rem;
}
.level-tab {
  border: 1px solid var(--line);
  background: #fff;
  color: var(--ink-soft);
  font-size: .8rem;
  font-weight: 700;
  padding: .4rem .75rem;
  border-radius: 999px;
  cursor: pointer;
}
.level-tab.tab-on {
  background: var(--brand-soft);
  border-color: color-mix(in srgb, var(--brand) 35%, transparent);
  color: var(--brand);
}

.field-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .85rem .9rem;
}
.field-span { grid-column: 1 / -1; }
.field label {
  display: block;
  font-size: .72rem;
  font-weight: 700;
  color: var(--ink-soft);
  text-transform: uppercase;
  letter-spacing: .03em;
  margin-bottom: .3rem;
}
.control {
  width: 100%;
  border: 1px solid var(--line);
  border-radius: .55rem;
  background: #fff;
  padding: .5rem .7rem;
  font-size: .88rem;
  color: var(--ink);
}
.control:focus {
  outline: none;
  border-color: var(--brand);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand) 16%, transparent);
}
.control:disabled {
  background: #faf8f5;
  color: var(--ink-soft);
  cursor: not-allowed;
}
.field-hint {
  margin: .3rem 0 0;
  font-size: .78rem;
  color: var(--ink-soft);
}

.hint-box {
  background: var(--gold-soft);
  color: #7a5a00;
  border-radius: .55rem;
  padding: .65rem .8rem;
  font-size: .84rem;
  margin-bottom: 1rem;
}

.check-group { margin-top: .85rem; }
.check-group-title {
  display: block;
  font-size: .72rem;
  font-weight: 700;
  color: var(--ink-soft);
  text-transform: uppercase;
  letter-spacing: .03em;
  margin: 0 0 .35rem;
}
.checks {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: .55rem .75rem;
}
.checks-2 { grid-template-columns: 1fr 1fr; }
.check {
  display: flex;
  align-items: center;
  gap: .4rem;
  font-size: .84rem;
  font-weight: 600;
  color: var(--ink);
  text-transform: none;
  letter-spacing: 0;
  margin: 0;
}
.check input { accent-color: var(--brand); width: auto; }

.form-actions {
  display: flex;
  flex-wrap: wrap;
  gap: .55rem;
  margin-top: 1.1rem;
}
.btn-apply {
  border: none;
  background: linear-gradient(135deg, var(--brand), var(--brand-dark));
  color: #fff;
  font-size: .82rem;
  font-weight: 700;
  padding: .55rem 1rem;
  border-radius: .6rem;
  cursor: pointer;
  box-shadow: 0 8px 16px rgba(171, 10, 61, .2);
}
.btn-apply:hover:not(:disabled) { filter: brightness(1.05); }
.btn-apply:disabled { opacity: .55; cursor: not-allowed; box-shadow: none; }
.btn-ghost {
  border: 1px solid var(--line);
  background: #fff;
  color: var(--ink);
  font-size: .82rem;
  font-weight: 600;
  padding: .55rem 1rem;
  border-radius: .6rem;
  cursor: pointer;
}
.btn-ghost:hover { border-color: var(--brand); background: #f5f7fb; }

.search-input {
  width: 100%;
  max-width: 28rem;
  border: 1px solid var(--line);
  border-radius: .55rem;
  padding: .5rem .75rem;
  font-size: .88rem;
  margin: .35rem 0 1rem;
}
.search-input:focus {
  outline: none;
  border-color: var(--brand);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand) 16%, transparent);
}

.table-card {
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: .75rem;
  overflow: hidden;
}
.table-wrap { overflow-x: auto; }
.admin-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 640px;
}
.admin-table th {
  text-align: left;
  font-size: .7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .04em;
  color: var(--ink-soft);
  background: #faf8f5;
  padding: .7rem .8rem;
  border-bottom: 1px solid var(--line);
}
.admin-table td {
  padding: .7rem .8rem;
  font-size: .84rem;
  border-bottom: 1px solid var(--line);
  vertical-align: top;
}
.admin-table tbody tr:last-child td { border-bottom: none; }
.admin-table tbody tr:hover { background: #fcfbf9; }
.cell-clave { font-weight: 700; color: var(--brand); white-space: nowrap; }
.cell-nombre { font-weight: 700; color: var(--brand-dark); }
.cell-actions {
  white-space: nowrap;
  display: flex;
  gap: .4rem;
}
.btn-action {
  border: 1px solid var(--line);
  background: #fff;
  border-radius: .45rem;
  padding: .28rem .6rem;
  font-size: .75rem;
  font-weight: 700;
  cursor: pointer;
}
.btn-edit { color: var(--brand); }
.btn-edit:hover { border-color: var(--brand); background: var(--brand-soft); }
.btn-delete { color: var(--brand-dark); }
.btn-delete:hover { border-color: var(--brand-dark); background: var(--brand-soft); }

.empty {
  text-align: center;
  padding: 2rem 1rem;
  border: 1px dashed var(--line);
  border-radius: .75rem;
}
.empty-title { margin: 0 0 .3rem; font-weight: 700; color: var(--brand-dark); }
.empty-sub { margin: 0; color: var(--ink-soft); font-size: .88rem; }

.mt-3 { margin-top: 1rem; }
.mt-4 { margin-top: 1.35rem; }

@media (max-width: 800px) {
  .field-grid,
  .checks { grid-template-columns: 1fr; }
  .field-span { grid-column: auto; }
  .search-input { max-width: none; }
}
</style>
