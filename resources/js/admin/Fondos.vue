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

      <form @submit.prevent="crearExpediente" class="caratula-form caratula-form--captura">
        <ReglasDocumentalesPanel
          :reglas="reglasDocumentales"
          :loading="cargandoReglas"
          empty-message="Seleccione una serie en el catálogo para ver las reglas documentales vinculadas."
        />

        <section class="form-block form-block--capture">
          <FormSectionHeader
            title="Datos del expediente a capturar"
            badge="Captura manual"
            badge-class="bg-warning text-dark"
            variant="capture"
            description="Complete la información operativa del expediente. Las reglas CADIDO se aplican automáticamente desde el catálogo."
          />

          <div class="field-grid">
            <div class="form-group">
              <label>Nombre del expediente</label>
              <input v-model="nuevoExpediente.nombre" class="form-control" required />
            </div>
            <div class="form-group">
              <label>Código del expediente</label>
              <input v-model="nuevoExpediente.codigo" class="form-control" required />
            </div>

            <div class="form-group">
              <label>Estado</label>
              <select v-model="nuevoExpediente.estado" class="form-control">
                <option value="en_tramite">En trámite</option>
                <option value="en_concentracion">En concentración</option>
                <option value="en_historico">En histórico</option>
              </select>
            </div>

            <div class="form-group">
              <label>Fecha de captura</label>
              <input type="date" v-model="nuevoExpediente.fecha_apertura" class="form-control campo-solo-lectura" disabled />
            </div>

            <div class="form-group">
              <label>Fecha de creación</label>
              <input type="date" v-model="nuevoExpediente.fecha_creacion" class="form-control" />
            </div>

            <div class="form-group">
              <label>Fecha de cierre</label>
              <input type="date" v-model="nuevoExpediente.fecha_cierre" class="form-control" />
            </div>

            <div class="form-group">
              <label>Número de legajos</label>
              <input type="number" v-model="nuevoExpediente.no_legajos" class="form-control" />
            </div>

            <div class="form-group">
              <label>Número de hojas</label>
              <input type="number" v-model="nuevoExpediente.no_hojas" class="form-control" />
            </div>

            <div class="form-group">
              <label>Número de caja</label>
              <input type="number" v-model="nuevoExpediente.no_caja" class="form-control" />
            </div>

            <div class="form-group">
              <label for="ubicacion_topografica">Ubicación Topográfica</label>
              <input
                v-model="nuevoExpediente.ubicacion_topografica"
                type="text"
                id="ubicacion_topografica"
                class="form-control"
                placeholder="Escribe la ubicación topográfica"
              />
            </div>

            <div class="form-group">
              <label>Preservación</label>
              <select v-model="nuevoExpediente.preservacion" class="form-control">
                <option value="Si">Si</option>
                <option value="No">No</option>
              </select>
            </div>

            <div class="form-group field-span-2">
              <label>Observaciones</label>
              <input v-model="nuevoExpediente.observacion" class="form-control" />
            </div>
          </div>
        </section>

        <section class="form-block form-block--reservada">
          <div class="reservada-head">
            <label class="subtitulo">Información Reservada y/o Confidencial</label>
            <p class="text-muted small mb-0">
              La clasificación, fundamento legal y periodo de reserva se muestran arriba en las reglas del catálogo.
              Aquí capture los datos operativos del expediente.
            </p>
          </div>

          <div class="field-grid">
            <div class="form-group">
              <label>Información reservada y/o confidencial</label>
              <input v-model="nuevoExpediente.informacion_reservada_y_o_confidencial" class="form-control" />
            </div>

            <div class="form-group">
              <label>Carácter de la información</label>
              <input v-model="nuevoExpediente.caracter_de_la_informacion" class="form-control" />
            </div>

            <div class="form-group">
              <label>Fecha de clasificación</label>
              <input type="date" v-model="nuevoExpediente.fecha_clasificacion" class="form-control" />
            </div>

            <div class="form-group">
              <label>Fecha de desclasificación</label>
              <input type="date" v-model="nuevoExpediente.fecha_desclasificacion" class="form-control" />
            </div>

            <div class="form-group field-span-2">
              <label>Rúbrica titular de la unidad</label>
              <input v-model="nuevoExpediente.rubrica_titular_de_la_unidad" class="form-control" />
            </div>
          </div>
        </section>

        <button type="submit" class="btn btn-primary" :disabled="!selectedSerie">
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
/* ===== Paleta compartida (misma que Carátula v2) ===== */
.form-container {
  --brand: #ab0a3d;
  --brand-dark: #440412;
  --gold: #b68400;
  --bg: #f6f1ee;
  --surface: #ffffff;
  --surface-soft: #faf6f4;
  --line: #e7d9dc;
  --ink: #2a1015;
  --ink-soft: #7a6a6d;
  --ring: rgba(171, 10, 61, .18);

  position: relative;
  width: 100%;
  min-height: 100vh;
  background: transparent;
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  color: var(--ink);
}

.caratula-background {
  max-width: 1040px;
  margin: 0 auto;
  padding: 48px 24px 24px;
  position: relative;
  color: var(--ink);
}

h1 {
  color: var(--brand-dark);
  text-align: center;
  font-size: 2rem;
  font-weight: 800;
  letter-spacing: -.02em;
  margin-bottom: 8px;
  text-wrap: balance;
}
h1::after {
  content: "";
  display: block;
  width: 72px;
  height: 4px;
  margin: 14px auto 32px;
  border-radius: 999px;
  background: linear-gradient(90deg, var(--brand), var(--gold));
}

.subtitulo {
  display: block;
  text-align: left;
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--brand-dark);
  padding-left: 12px;
  border-left: 3px solid var(--gold);
  margin-bottom: 6px;
}

.caratula-form {
  background: var(--surface);
  padding: 32px;
  border: 1px solid var(--line);
  border-radius: 16px;
  max-width: 100%;
  margin: 0 auto 24px;
  box-shadow: 0 12px 30px rgba(68, 4, 18, .07);
}

.caratula-form--captura {
  border-top: 4px solid var(--brand);
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
}

.form-block {
  padding: 1.1rem 1.15rem 1.2rem;
  border: 1px solid var(--line);
  border-radius: 14px;
  background: transparent;
}

.form-block--capture {
  background: transparent;
}

.form-block--reservada {
  border-left: 4px solid var(--gold);
  background: transparent;
}

.reservada-head {
  margin-bottom: 1rem;
}

.field-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem 1.1rem;
}

@media (min-width: 720px) {
  .field-grid {
    grid-template-columns: 1fr 1fr;
  }
  .field-span-2 {
    grid-column: 1 / -1;
  }
}

.selector-expediente {
  position: relative;
  border: 1px solid var(--line);
  border-top: 4px solid var(--brand);
  background: var(--surface);
  overflow: hidden;
}

.selector-titulo {
  font-weight: 700;
  font-size: 1.15rem;
  color: var(--brand-dark);
  margin-bottom: .5rem;
}

.selector-ayuda,
.text-muted {
  font-size: .9rem;
  color: var(--ink-soft) !important;
  margin-bottom: 1rem;
  line-height: 1.55;
}

label {
  display: block;
  font-size: .82rem;
  font-weight: 600;
  color: var(--brand-dark);
  margin-bottom: 2px;
  letter-spacing: .01em;
}

.form-group {
  width: 100%;
  margin-bottom: 0;
}

.form-block .form-group label {
  margin-bottom: 0;
}

.form-control {
  width: 100%;
  height: auto;
  min-height: 44px;
  padding: 10px 13px;
  margin-top: 6px;
  border-radius: 10px;
  border: 1px solid var(--line);
  background: var(--surface);
  color: var(--ink);
  font-size: .95rem;
  line-height: 1.4;
  box-sizing: border-box;
  transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
}
.form-control::placeholder { color: #b7a7aa; }
.form-control:hover { border-color: #d9c4c8; }
.form-control:focus {
  outline: none;
  border-color: var(--brand);
  box-shadow: 0 0 0 4px var(--ring);
  background: var(--surface);
}
select.form-control {
  cursor: pointer;
  display: block;
  padding: 10px 36px 10px 13px;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background-color: var(--surface);
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23440412' d='M1.4.6 6 5.2 10.6.6 12 2 6 8 0 2z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 12px 8px;
}
select.form-control:disabled {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%237a6a6d' d='M1.4.6 6 5.2 10.6.6 12 2 6 8 0 2z'/%3E%3C/svg%3E");
}

.campo-solo-lectura:disabled,
.form-control:disabled {
  background-color: var(--surface-soft);
  color: var(--ink-soft);
  border-style: dashed;
  cursor: not-allowed;
}

.btn {
  margin-top: 16px;
  padding: 11px 22px;
  border-radius: 10px;
  border: none;
  font-weight: 700;
  font-size: .92rem;
  cursor: pointer;
  transition: transform .12s ease, box-shadow .15s ease, filter .15s ease;
}
.btn-primary {
  background: linear-gradient(135deg, var(--brand), var(--brand-dark));
  color: #fff;
  box-shadow: 0 8px 18px rgba(171, 10, 61, .25);
}
.btn-primary:hover:not(:disabled) {
  transform: translateY(-1px);
  filter: brightness(1.05);
  box-shadow: 0 12px 24px rgba(171, 10, 61, .32);
}
.btn-primary:active:not(:disabled) { transform: translateY(0); }
.btn-primary:disabled {
  opacity: .55;
  cursor: not-allowed;
  box-shadow: none;
}

form.caratula-form > .btn-primary {
  width: 100%;
  padding: 14px;
  font-size: 1rem;
  margin-top: 8px;
  letter-spacing: .01em;
}

.mb-4 { margin-bottom: 1.5rem; }
.mt-1 { margin-top: .25rem; }
.small { font-size: .82rem; }

.row {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
}
.row.g-3 { gap: 16px; }
.col-12 { flex: 0 0 100%; max-width: 100%; }

.col-md-4, .col-md-6 { flex: 1 1 100%; }

@media (min-width: 768px) {
  .col-md-4 { flex: 1 1 calc(33.333% - 16px); min-width: 220px; }
  .col-md-6 { flex: 1 1 calc(50% - 16px); min-width: 220px; }
}

@media (max-width: 640px) {
  .caratula-background { padding: 32px 14px 16px; }
  .caratula-form { padding: 22px; }
  h1 { font-size: 1.6rem; }
}
</style>
