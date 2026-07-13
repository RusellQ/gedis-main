<template>
  <div class="form-container">
    <div class="caratula-background">
      <h1>Crear Expediente</h1>

      <div class="selector-expediente caratula-form mb-4">
        <h5 class="selector-titulo">Ubicación en el catálogo archivístico</h5>
        <p class="selector-ayuda text-muted">
          Seleccione la jerarquía donde se registrará el expediente: fondo, sección, serie y, si aplica, subfondo y subserie.
        </p>

        <div class="row g-3">
          <div class="col-md-4">
            <label>Fondo</label>
            <select v-model="selectedFondo" @change="onFondoChange" class="form-control">
              <option disabled value="">Selecciona un fondo</option>
              <option v-for="fondo in fondos" :key="fondo.id" :value="fondo.id">{{ fondo.nombre }}</option>
            </select>
          </div>

          <div class="col-md-4" v-if="selectedFondo">
            <label>Subfondo (opcional)</label>
            <select v-model="selectedSubfondo" @change="fetchSecciones" class="form-control">
              <option value="">Sin subfondo</option>
              <option v-for="subfondo in subfondos" :key="subfondo.id" :value="subfondo.id">
                {{ subfondo.nombre }}
              </option>
            </select>
          </div>

          <div class="col-md-4" v-if="selectedFondo">
            <label>Sección</label>
            <select v-model="selectedSeccion" @change="fetchSeries" class="form-control">
              <option disabled value="">Selecciona una sección</option>
              <option v-for="seccion in secciones" :key="seccion.id" :value="seccion.id">{{ seccion.nombre }}</option>
            </select>
            <p v-if="!secciones.length" class="text-muted small mt-1">Este fondo no tiene secciones.</p>
          </div>

          <div class="col-md-4" v-if="selectedSeccion">
            <label>Serie</label>
            <select v-model="selectedSerie" @change="handleSerieChange" class="form-control">
              <option disabled value="">Selecciona una serie</option>
              <option v-for="serie in series" :key="serie.id" :value="serie.id">{{ serie.nombre }}</option>
            </select>
            <p v-if="!series.length" class="text-muted small mt-1">Esta sección no tiene series.</p>
          </div>

          <div class="col-md-4" v-if="selectedSerie">
            <label>Subserie (opcional)</label>
            <select v-model="selectedSubserie" class="form-control">
              <option value="">Sin subserie</option>
              <option v-for="subserie in subseries" :key="subserie.id" :value="subserie.id">
                {{ subserie.nombre }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <form @submit.prevent="crearExpediente" class="caratula-form">
        <ReglasDocumentalesPanel
          :reglas="reglasDocumentales"
          :loading="cargandoReglas"
          empty-message="Seleccione una serie en el catálogo para ver las reglas documentales vinculadas."
        />

        <FormSectionHeader
          title="Datos del expediente a capturar"
          badge="Captura manual"
          description="Complete la información operativa del expediente. Las reglas CADIDO se aplican automáticamente desde el catálogo."
        />

        <div class="row g-3">
          <div class="col-md-6">
            <label>Nombre del expediente</label>
            <input v-model="nuevoExpediente.nombre" class="form-control" required />
          </div>
          <div class="col-md-6">
            <label>Código del expediente</label>
            <input v-model="nuevoExpediente.codigo" class="form-control" required />
          </div>

          <div class="col-md-6">
            <label>Estado</label>
            <select v-model="nuevoExpediente.estado" class="form-control">
              <option value="en_tramite">En trámite</option>
              <option value="en_concentracion">En concentración</option>
              <option value="en_historico">En histórico</option>
            </select>
          </div>

          <div class="col-md-6">
            <label>Fecha de captura</label>
            <input type="date" v-model="nuevoExpediente.fecha_apertura" class="form-control" disabled />
          </div>

          <div class="col-md-6">
            <label>Fecha de creación</label>
            <input type="date" v-model="nuevoExpediente.fecha_creacion" class="form-control" />
          </div>

          <div class="col-md-6">
            <label>Fecha de cierre</label>
            <input type="date" v-model="nuevoExpediente.fecha_cierre" class="form-control" />
          </div>

          <div class="col-md-4">
            <label>Número de legajos</label>
            <input type="number" v-model="nuevoExpediente.no_legajos" class="form-control" />
          </div>

          <div class="col-md-4">
            <label>Número de hojas</label>
            <input type="number" v-model="nuevoExpediente.no_hojas" class="form-control" />
          </div>

          <div class="col-md-4">
            <label>Número de caja</label>
            <input type="number" v-model="nuevoExpediente.no_caja" class="form-control" />
          </div>

          <div class="col-md-6">
            <label for="ubicacion_topografica">Ubicación Topográfica</label>
            <input
              v-model="nuevoExpediente.ubicacion_topografica"
              type="text"
              id="ubicacion_topografica"
              class="form-control"
              placeholder="Escribe la ubicación topográfica"
            />
          </div>

          <div class="col-md-6">
            <label>Preservación</label>
            <select v-model="nuevoExpediente.preservacion" class="form-control">
              <option value="Si">Si</option>
              <option value="No">No</option>
            </select>
          </div>

          <div class="col-12">
            <label>Observaciones</label>
            <input v-model="nuevoExpediente.observacion" class="form-control" />
          </div>

          <div class="col-md-6">
            <label>Información reservada y/o confidencial</label>
            <input v-model="nuevoExpediente.informacion_reservada_y_o_confidencial" class="form-control" />
          </div>

          <div class="col-md-6">
            <label>Carácter de la información</label>
            <input v-model="nuevoExpediente.caracter_de_la_informacion" class="form-control" />
          </div>

          <div class="col-md-6">
            <label>Fecha de clasificación</label>
            <input type="date" v-model="nuevoExpediente.fecha_clasificacion" class="form-control" />
          </div>

          <div class="col-md-6">
            <label>Rúbrica titular de la unidad</label>
            <input v-model="nuevoExpediente.rubrica_titular_de_la_unidad" class="form-control" />
          </div>

          <div class="col-md-6">
            <label>Fecha de desclasificación</label>
            <input type="date" v-model="nuevoExpediente.fecha_desclasificacion" class="form-control" />
          </div>
        </div>

        <button type="submit" class="btn btn-success" :disabled="!selectedSerie">
          Guardar expediente
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import FormSectionHeader from '../components/FormSectionHeader.vue'
import ReglasDocumentalesPanel from '../components/ReglasDocumentalesPanel.vue'

const fondos = ref([])
const subfondos = ref([])
const secciones = ref([])
const series = ref([])
const subseries = ref([])

const selectedFondo = ref('')
const selectedSubfondo = ref('')
const selectedSeccion = ref('')
const selectedSerie = ref('')
const selectedSubserie = ref('')
const reglasDocumentales = ref(null)
const cargandoReglas = ref(false)

const nuevoExpediente = ref({
  nombre: '',
  codigo: '',
  estado: 'en_tramite',
  fecha_apertura: '',
  fecha_cierre: '',
  fecha_creacion: '',
  no_legajos: '',
  no_hojas: '',
  no_caja: '',
  ubicacion_topografica: 'Dirección General',
  preservacion: 'Si',
  observacion: '',
  informacion_reservada_y_o_confidencial: '',
  caracter_de_la_informacion: '',
  fecha_clasificacion: '',
  rubrica_titular_de_la_unidad: '',
  fecha_desclasificacion: '',
})

const resetExpedienteForm = () => {
  nuevoExpediente.value = {
    nombre: '',
    codigo: '',
    estado: 'en_tramite',
    fecha_apertura: new Date().toISOString().split('T')[0],
    fecha_cierre: '',
    fecha_creacion: '',
    no_legajos: '',
    no_hojas: '',
    no_caja: '',
    ubicacion_topografica: 'Dirección General',
    preservacion: 'Si',
    observacion: '',
    informacion_reservada_y_o_confidencial: '',
    caracter_de_la_informacion: '',
    fecha_clasificacion: '',
    rubrica_titular_de_la_unidad: '',
    fecha_desclasificacion: '',
  }
}

const cargarReglasPorSerie = async (serieId) => {
  if (!serieId) {
    reglasDocumentales.value = null
    return
  }
  cargandoReglas.value = true
  try {
    const res = await axios.get(`/series/${serieId}/reglas-documentales`)
    reglasDocumentales.value = res.data.reglas
  } catch (error) {
    console.error('Error al cargar reglas documentales:', error)
    reglasDocumentales.value = null
  } finally {
    cargandoReglas.value = false
  }
}

const onFondoChange = async () => {
  selectedSubfondo.value = ''
  await fetchSubfondos()
  await fetchSecciones()
}

const handleSerieChange = async () => {
  selectedSubserie.value = ''
  await fetchSubseries()
  await cargarReglasPorSerie(selectedSerie.value)
}

const fetchFondos = async () => {
  const res = await axios.get('/mostrarfondos')
  fondos.value = res.data
}

const fetchSubfondos = async () => {
  selectedSubfondo.value = ''
  selectedSeccion.value = ''
  selectedSerie.value = ''
  selectedSubserie.value = ''
  subfondos.value = []
  secciones.value = []
  series.value = []
  subseries.value = []
  reglasDocumentales.value = null

  if (!selectedFondo.value) return
  const res = await axios.get(`/fondos/${selectedFondo.value}/subfondos`)
  subfondos.value = res.data
  if (!subfondos.value.length) await fetchSecciones()
}

const fetchSecciones = async () => {
  selectedSeccion.value = ''
  selectedSerie.value = ''
  selectedSubserie.value = ''
  secciones.value = []
  series.value = []
  subseries.value = []
  reglasDocumentales.value = null

  if (!selectedFondo.value) return

  const url = selectedSubfondo.value
    ? `/subfondos/${selectedSubfondo.value}/secciones`
    : `/fondos/${selectedFondo.value}/secciones`
  const res = await axios.get(url)
  secciones.value = res.data
}

const fetchSeries = async () => {
  selectedSerie.value = ''
  selectedSubserie.value = ''
  series.value = []
  subseries.value = []
  reglasDocumentales.value = null

  if (!selectedSeccion.value) return
  const res = await axios.get(`/secciones/${selectedSeccion.value}/series`)
  series.value = res.data
}

const fetchSubseries = async () => {
  selectedSubserie.value = ''
  subseries.value = []
  if (!selectedSerie.value) return
  const res = await axios.get(`/series/${selectedSerie.value}/subseries`)
  subseries.value = res.data.data
}

const crearExpediente = async () => {
  if (!selectedSerie.value) {
    alert('Seleccione una serie en el catálogo antes de guardar el expediente.')
    return
  }

  try {
    await axios.post('/guardarexpediente', {
      ...nuevoExpediente.value,
      id_serie: selectedSerie.value,
      id_subserie: selectedSubserie.value || null,
    })

    alert('Expediente creado correctamente.')
    resetExpedienteForm()
  } catch (e) {
    console.error('Error al crear el expediente:', e?.response?.data || e.message)
    alert('Hubo un error al guardar el expediente. Verifica los datos e intenta nuevamente.')
  }
}

onMounted(() => {
  fetchFondos()
  resetExpedienteForm()
})
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
</style>
