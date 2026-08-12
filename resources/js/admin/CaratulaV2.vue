<template>
  <div class="form-container">
    <div class="caratula-background">
      <h1>Generar Carátula de Expediente</h1>

      <!-- Selector de expediente (solo v2) -->
      <div class="selector-expediente caratula-form mb-4">
        <h5 class="selector-titulo">Seleccionar expediente</h5>
        <p class="selector-ayuda text-muted">
          Elija el expediente en el catálogo archivístico y pulse «Cargar datos» para llenar automáticamente
          los campos disponibles. El resto deberá capturarse manualmente.
        </p>

        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label>Fondo</label>
            <select v-model="selectedFondo" @change="onFondoChange" class="form-control">
              <option disabled value="">Selecciona un fondo</option>
              <option v-for="f in fondos" :key="f.id" :value="f.id">{{ f.nombre }}</option>
            </select>
          </div>

          <div class="col-md-4" v-if="selectedFondo">
            <label>Subfondo (opcional)</label>
            <select v-model="selectedSubfondo" @change="fetchSecciones" class="form-control">
              <option value="">Sin subfondo</option>
              <option v-for="s in subfondos" :key="s.id" :value="s.id">{{ s.nombre }}</option>
            </select>
          </div>

          <div class="col-md-4" v-if="selectedFondo">
            <label>Sección</label>
            <select v-model="selectedSeccion" @change="fetchSeries" class="form-control">
              <option disabled value="">Selecciona una sección</option>
              <option v-for="sec in secciones" :key="sec.id" :value="sec.id">{{ sec.nombre }}</option>
            </select>
          </div>

          <div class="col-md-4" v-if="selectedSeccion">
            <label>Serie</label>
            <select v-model="selectedSerie" @change="handleSerieChange" class="form-control">
              <option disabled value="">Selecciona una serie</option>
              <option v-for="ser in series" :key="ser.id" :value="ser.id">{{ ser.nombre }}</option>
            </select>
          </div>

          <div class="col-md-4" v-if="selectedSerie">
            <label>Subserie (opcional)</label>
            <select v-model="selectedSubserie" @change="fetchExpedientes" class="form-control">
              <option value="">Sin subserie</option>
              <option v-for="ss in subseries" :key="ss.id" :value="ss.id">{{ ss.nombre }}</option>
            </select>
          </div>
        </div>

        <div v-if="expedientes.length">
          <label class="fw-semibold">Expedientes disponibles</label>
          <div class="list-group mb-3 mt-2">
            <label v-for="exp in expedientes" :key="exp.id" class="list-group-item">
              <input type="checkbox" v-model="selectedExpedientes" :value="exp.id" />
              <strong class="ms-2">{{ exp.nombre }}</strong>
              <small class="text-muted"> — {{ exp.codigo }}</small>
            </label>
          </div>
          <button type="button" class="btn btn-primary" @click="cargarDatosSeleccionados">Cargar datos</button>
        </div>
      </div>

      <form @submit.prevent="guardarCaratula" class="caratula-form">
        <ReglasDocumentalesPanel
          :reglas="reglasDocumentales"
          :loading="cargandoReglas"
          empty-message="Seleccione una serie en el catálogo o cargue un expediente para ver las reglas documentales."
        />

        <FormSectionHeader
          title="Datos de su sesión"
          badge="Automático"
          badge-class="bg-info text-dark"
          variant="session"
          description="Información obtenida del usuario y su dependencia."
        />

        <div class="form-row">
          <div class="form-group">
            <label for="nombreSujetoObligado">Sujeto Obligado</label>
            <input type="text" id="nombreSujetoObligado" v-model="form.nombre_sujeto_obligado" class="form-control campo-solo-lectura" disabled>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="areaAdministrativa">Área Administrativa</label>
            <input type="text" id="areaAdministrativa" v-model="form.area_administrativa" class="form-control campo-solo-lectura" disabled>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="areaGeneradora">Área Generadora</label>
            <input type="text" id="areaGeneradora" v-model="form.area_generadora" class="form-control campo-solo-lectura" disabled>
          </div>
        </div>

        <FormSectionHeader
          title="Datos de la carátula a capturar"
          badge="Captura manual"
          description="Complete estos campos con la información específica del expediente."
        />

        <div class="form-row">
          <div class="form-group">
            <label for="noConsecutivo">No. Consecutivo</label>
            <input type="number" id="noConsecutivo" v-model="form.no_consecutivo" class="form-control" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="codigoClasificacionArchivistica">Código de Clasificación Archivística</label>
            <input type="text" id="codigoClasificacionArchivistica" v-model="form.codigo_clasificacion_archivistica" class="form-control" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-12">
            <label for="fechasExtremas">Fechas Extremas</label>
          </div>
          <div class="form-group col-md-6">
            <label for="anoApertura">Año de apertura</label>
            <input type="number" id="anoApertura" v-model="form.anio_apertura" class="form-control" required>
          </div>
          <div class="form-group col-md-6">
            <label for="anoCierre">Año de cierre</label>
            <input type="number" id="anoCierre" v-model="form.anio_cierre" class="form-control" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="soporteDocumental">Soporte Documental</label>
            <select id="soporteDocumental" v-model="form.soporte_documental" class="form-control" required>
              <option value="Físico">Físico</option>
              <option value="Electrónico">Electrónico</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="tituloExpediente">Título del Expediente</label>
            <input type="text" id="tituloExpediente" v-model="form.titulo_expediente" class="form-control" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="descripcionAsuntoExpediente">Descripción del Asunto que Trata el Expediente</label>
            <input type="text" id="descripcionAsuntoExpediente" v-model="form.descripcion_asunto_expediente" class="form-control" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="ubicacionTopografica">Ubicación Topográfica</label>
            <input type="text" id="ubicacionTopografica" v-model="form.ubicacion_topografica" class="form-control campo-solo-lectura" disabled>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="noLegajo">No. de Legajo</label>
            <input type="number" id="noLegajo" v-model="form.no_legajo" class="form-control" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="noFojas">No. de Fojas</label>
            <input type="number" id="noFojas" v-model="form.no_fojas" class="form-control" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="subtitulo">Información Reservada y/o Confidencial</label>
            <p class="text-muted small mb-2">La clasificación, fundamento legal y periodo de reserva se muestran arriba en las reglas del catálogo. Aquí capture los datos operativos del expediente.</p>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="informacionReservada">Información reservada y/o confidencial</label>
            <input type="text" id="informacionReservada" v-model="form.informacion_reservada_y_o_confidencial" class="form-control">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="fechaClasificacion">Fecha de Clasificación</label>
            <input type="date" id="fechaClasificacion" v-model="form.fecha_clasificacion" class="form-control" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="aplicacionPeriodoReserva">Aplicación del Periodo de Reserva</label>
            <input type="text" id="aplicacionPeriodoReserva" v-model="form.aplicacion_periodo_reserva" class="form-control" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="rubricaTitularUnidadAdministrativa">Rubrica del Titular de la Unidad Administrativa</label>
            <input type="text" id="rubricaTitularUnidadAdministrativa" v-model="form.rubrica_titular" class="form-control" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="fechaDesclasificacion">Fecha de Desclasificación</label>
            <input type="date" id="fechaDesclasificacion" v-model="form.fecha_desclasificacion" class="form-control">
          </div>
        </div>

        <div class="form-group">
          <label for="observaciones">Observaciones</label>
          <textarea id="observaciones" v-model="form.observaciones" class="form-control" rows="3"></textarea>
        </div>

        <div class="form-group">
          <label for="capturador">Capturador</label>
          <input type="text" id="capturador" v-model="form.capturador" class="form-control campo-solo-lectura" disabled>
        </div>

        <button type="submit" class="btn btn-primary">
          <span>Generar Carátula</span>
        </button>
      </form>
    </div>
    <br>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import FormSectionHeader from '../components/FormSectionHeader.vue'
import ReglasDocumentalesPanel from '../components/ReglasDocumentalesPanel.vue'
import { reglasDesdeSerieYInstrumento } from '../utils/reglasDocumentales.js'

const fondos = ref([])
const subfondos = ref([])
const secciones = ref([])
const series = ref([])
const subseries = ref([])
const expedientes = ref([])

const selectedFondo = ref('')
const selectedSubfondo = ref('')
const selectedSeccion = ref('')
const selectedSerie = ref('')
const selectedSubserie = ref('')
const selectedExpedientes = ref([])

const expedientesData = ref([])
const reglasDocumentales = ref(null)
const cargandoReglas = ref(false)

const form = ref({
  id_dependencia: null,
  id_departamento: null,
  nombre_sujeto_obligado: '',
  area_administrativa: '',
  no_consecutivo: '',
  area_generadora: '',
  codigo_clasificacion_archivistica: '',
  anio_apertura: '',
  anio_cierre: '',
  soporte_documental: 'Físico',
  titulo_expediente: '',
  descripcion_asunto_expediente: '',
  ubicacion_topografica: '',
  no_legajo: '',
  no_fojas: '',
  informacion_reservada_y_o_confidencial: '',
  fecha_clasificacion: '',
  aplicacion_periodo_reserva: '',
  rubrica_titular: '',
  fecha_desclasificacion: '',
  observaciones: '',
  capturador: '',
})

const fetchFondos = async () => {
  const res = await axios.get('/mostrarfondos')
  fondos.value = res.data
}

const fetchCurrentUser = async () => {
  try {
    const res = await axios.get('/currentuser')
    const currentUser = res.data

    form.value.capturador = `${currentUser.nombre || ''} ${currentUser.apellido_paterno || ''} ${currentUser.apellido_materno || ''}`.trim()
    form.value.area_generadora = currentUser.departamento?.nombre || ''
    form.value.area_administrativa = currentUser.departamento?.nombre || ''
    form.value.ubicacion_topografica = currentUser.departamento?.dependencia?.nombre || ''
    form.value.nombre_sujeto_obligado = currentUser.departamento?.dependencia?.nombre || ''
    form.value.id_dependencia = currentUser.departamento?.id_dependencia || currentUser.id_dependencia || null
    form.value.id_departamento = currentUser.id_departamento || null
  } catch (error) {
    console.error('Error al obtener el usuario actual:', error)
  }
}

const onFondoChange = async () => {
  selectedSubfondo.value = ''
  await fetchSubfondos()
  await fetchSecciones()
}

const fetchSubfondos = async () => {
  selectedSubfondo.value = ''
  subfondos.value = []
  if (!selectedFondo.value) return
  const res = await axios.get(`/fondos/${selectedFondo.value}/subfondos`)
  subfondos.value = res.data
}

const fetchSecciones = async () => {
  if (!selectedFondo.value) return
  const url = selectedSubfondo.value
    ? `/subfondos/${selectedSubfondo.value}/secciones`
    : `/fondos/${selectedFondo.value}/secciones`
  const res = await axios.get(url)
  secciones.value = res.data
}

const fetchSeries = async () => {
  if (!selectedSeccion.value) return
  const res = await axios.get(`/secciones/${selectedSeccion.value}/series`)
  series.value = res.data
}

const fetchSubseries = async () => {
  selectedSubserie.value = ''
  subseries.value = []
  if (!selectedSerie.value) return
  const res = await axios.get(`/series/${selectedSerie.value}/subseries`)
  subseries.value = res.data.data || res.data
}

const handleSerieChange = async () => {
  await fetchSubseries()
  await fetchExpedientes()
  await cargarReglasPorSerie(selectedSerie.value)
}

const fetchExpedientes = async () => {
  expedientes.value = []
  if (!selectedSerie.value) return
  const url = selectedSubserie.value
    ? `/subseries/${selectedSubserie.value}/expedientes`
    : `/series/${selectedSerie.value}/expedientes`
  const res = await axios.get(url)
  expedientes.value = res.data
}

const extraerAnio = (fecha) => {
  if (!fecha) return ''
  const match = String(fecha).match(/^(\d{4})/)
  return match ? match[1] : ''
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

const cargarReglasPorClave = async (clave) => {
  if (!clave?.trim()) {
    reglasDocumentales.value = null
    return
  }
  cargandoReglas.value = true
  try {
    const res = await axios.get('/reglas-documentales/por-clave', { params: { clave: clave.trim() } })
    reglasDocumentales.value = res.data.reglas
  } catch (error) {
    console.error('Error al cargar reglas por clave:', error)
    reglasDocumentales.value = null
  } finally {
    cargandoReglas.value = false
  }
}

const obtenerValorUnico = (valores) => {
  const valoresValidos = valores.filter((valor) => valor !== null && valor !== undefined && valor !== '')
  if (!valoresValidos.length) return ''
  const unico = new Set(valoresValidos)
  return unico.size === 1 ? [...unico][0] : ''
}

const aplicarReglasDesdeExpediente = (e) => {
  const instrumento = e.instrumento_control || e.instrumentoControl || null
  if (e.serie) {
    reglasDocumentales.value = reglasDesdeSerieYInstrumento(e.serie, instrumento)
    return
  }
  if (e.id_serie) {
    cargarReglasPorSerie(e.id_serie)
  } else if (e.clave) {
    cargarReglasPorClave(e.clave)
  }
}

const aplicarDatosExpediente = (e) => {
  aplicarReglasDesdeExpediente(e)

  form.value.titulo_expediente = e.nombre || ''
  form.value.codigo_clasificacion_archivistica = e.clave || ''
  form.value.anio_apertura = extraerAnio(e.fecha_apertura)
  form.value.anio_cierre = extraerAnio(e.fecha_cierre)

  if (e.soporte_documental) {
    form.value.soporte_documental = e.soporte_documental === 'Electrónico' ? 'Electrónico' : 'Físico'
  }

  form.value.descripcion_asunto_expediente = e.descripcion_expediente || e.observacion || ''
  form.value.ubicacion_topografica = e.ubicacion_topografica || form.value.ubicacion_topografica
  form.value.no_legajo = e.no_legajos || 0
  form.value.no_fojas = e.no_hojas || 0
  form.value.informacion_reservada_y_o_confidencial = e.informacion_reservada_y_o_confidencial || ''
  form.value.fecha_clasificacion = e.fecha_clasificacion || ''
  form.value.aplicacion_periodo_reserva = e.ampliacion_periodo_reserva != null ? String(e.ampliacion_periodo_reserva) : ''
  form.value.rubrica_titular = e.rubrica_titular_de_la_unidad || ''
  form.value.fecha_desclasificacion = e.fecha_desclasificacion || ''

  form.value.observaciones = e.observacion || ''
}

const cargarDatosSeleccionados = async () => {
  if (!selectedExpedientes.value.length) return alert('Selecciona al menos un expediente')
  const res = await axios.post('/expedientes/batch', { ids: selectedExpedientes.value })
  expedientesData.value = res.data

  const data = res.data
  if (data.length === 1) {
    aplicarDatosExpediente(data[0])
    return
  }

  let sumLeg = 0
  let sumFojas = 0
  const ubicaciones = new Set()
  const titulos = new Set()
  const claves = new Set()
  const clasificaciones = new Set()
  const descripciones = []
  const observaciones = []
  const informacionReservada = []
  const fechasClasificacion = []
  const aplicacionesReserva = []
  const rubricasTitulares = []
  const fechasDesclasificacion = []

  data.forEach((e) => {
    sumLeg += Number(e.no_legajos || 0)
    sumFojas += Number(e.no_hojas || 0)
    if (e.ubicacion_topografica) ubicaciones.add(e.ubicacion_topografica)
    if (e.nombre) titulos.add(e.nombre)
    if (e.clave) claves.add(e.clave)
    if (e.clasificacion) clasificaciones.add(e.clasificacion)
    if (e.descripcion_expediente) descripciones.push(e.descripcion_expediente)
    if (e.observacion && !e.descripcion_expediente) descripciones.push(e.observacion)
    if (e.observacion) observaciones.push(e.observacion)
    if (e.informacion_reservada_y_o_confidencial) informacionReservada.push(e.informacion_reservada_y_o_confidencial)
    if (e.fecha_clasificacion) fechasClasificacion.push(e.fecha_clasificacion)
    if (e.ampliacion_periodo_reserva != null && e.ampliacion_periodo_reserva !== '') aplicacionesReserva.push(String(e.ampliacion_periodo_reserva))
    if (e.rubrica_titular_de_la_unidad) rubricasTitulares.push(e.rubrica_titular_de_la_unidad)
    if (e.fecha_desclasificacion) fechasDesclasificacion.push(e.fecha_desclasificacion)
  })

  form.value.no_legajo = sumLeg
  form.value.no_fojas = sumFojas
  form.value.ubicacion_topografica = ubicaciones.size === 1 ? [...ubicaciones][0] : form.value.ubicacion_topografica
  form.value.titulo_expediente = titulos.size === 1 ? [...titulos][0] : ''
  form.value.codigo_clasificacion_archivistica = claves.size === 1 ? [...claves][0] : ''
  form.value.descripcion_asunto_expediente = descripciones.join(' | ')
  form.value.informacion_reservada_y_o_confidencial = obtenerValorUnico(informacionReservada)
  form.value.fecha_clasificacion = obtenerValorUnico(fechasClasificacion)
  form.value.aplicacion_periodo_reserva = obtenerValorUnico(aplicacionesReserva)
  form.value.rubrica_titular = obtenerValorUnico(rubricasTitulares)
  form.value.fecha_desclasificacion = obtenerValorUnico(fechasDesclasificacion)

  aplicarReglasDesdeExpediente(data[0])

  const aniosApertura = data.map((e) => extraerAnio(e.fecha_apertura)).filter(Boolean)
  const aniosCierre = data.map((e) => extraerAnio(e.fecha_cierre)).filter(Boolean)
  const aperturaUnica = new Set(aniosApertura)
  const cierreUnico = new Set(aniosCierre)
  if (aperturaUnica.size === 1) form.value.anio_apertura = [...aperturaUnica][0]
  if (cierreUnico.size === 1) form.value.anio_cierre = [...cierreUnico][0]

  form.value.observaciones = observaciones.join(' | ')
}

const resetForm = () => {
  const usuario = {
    id_dependencia: form.value.id_dependencia,
    id_departamento: form.value.id_departamento,
    nombre_sujeto_obligado: form.value.nombre_sujeto_obligado,
    area_administrativa: form.value.area_administrativa,
    area_generadora: form.value.area_generadora,
    ubicacion_topografica: form.value.ubicacion_topografica,
    capturador: form.value.capturador,
  }

  form.value = {
    ...usuario,
    no_consecutivo: '',
    codigo_clasificacion_archivistica: '',
    anio_apertura: '',
    anio_cierre: '',
    soporte_documental: 'Físico',
    titulo_expediente: '',
    descripcion_asunto_expediente: '',
    no_legajo: '',
    no_fojas: '',
    informacion_reservada_y_o_confidencial: '',
    fecha_clasificacion: '',
    aplicacion_periodo_reserva: '',
    rubrica_titular: '',
    fecha_desclasificacion: '',
    observaciones: '',
  }

  expedientesData.value = []
  selectedExpedientes.value = []
  reglasDocumentales.value = null
}

const guardarCaratula = async () => {
  try {
    await axios.post('/documentos', { ...form.value })
    alert('Carátula de expediente generada con éxito')
    resetForm()
  } catch (e) {
    console.error(e)
    alert('Hubo un error al generar la carátula. Intenta nuevamente.')
  }
}

onMounted(() => {
  fetchFondos()
  fetchCurrentUser()
})
</script>

<style scoped>
/* ===== Paleta compartida ===== */
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
  background:
    radial-gradient(1200px 480px at 100% -10%, rgba(171, 10, 61, .06), transparent 60%),
    radial-gradient(900px 420px at -10% 110%, rgba(182, 132, 0, .07), transparent 55%),
    var(--bg);
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

/* ===== Título ===== */
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

/* ===== Tarjetas / formularios ===== */
.caratula-form {
  background: var(--surface);
  padding: 32px;
  border: 1px solid var(--line);
  border-radius: 16px;
  max-width: 100%;
  margin: 0 auto 24px;
  box-shadow: 0 12px 30px rgba(68, 4, 18, .07);
}

/* Selector de expediente destacado */
.selector-expediente {
  position: relative;
  border: 1px solid var(--line);
  border-top: 4px solid var(--brand);
  background: var(--surface-soft);
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

/* Etiquetas */
label {
  display: block;
  font-size: .82rem;
  font-weight: 600;
  color: var(--brand-dark);
  margin-bottom: 2px;
  letter-spacing: .01em;
}
.fw-semibold { font-weight: 700; }

.form-row {
  display: block;
  margin-bottom: 18px;
}

.form-group {
  width: 100%;
}

/* Campos */
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
textarea.form-control { resize: vertical; min-height: 84px; }

.campo-solo-lectura:disabled,
.form-control:disabled {
  background-color: var(--surface-soft);
  color: var(--ink-soft);
  border-style: dashed;
  cursor: not-allowed;
}

/* Lista de expedientes disponibles */
.list-group {
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid var(--line);
}
.list-group-item {
  display: flex;
  align-items: center;
  gap: .25rem;
  padding: 12px 14px;
  background: var(--surface);
  border: none;
  border-bottom: 1px solid var(--line);
  cursor: pointer;
  transition: background .12s ease;
}
.list-group-item:last-child { border-bottom: none; }
.list-group-item:hover { background: var(--surface-soft); }
.list-group-item input[type="checkbox"] {
  width: 16px;
  height: 16px;
  accent-color: var(--brand);
  cursor: pointer;
}
.list-group-item strong { color: var(--ink); font-size: .92rem; }
.list-group-item small { color: var(--ink-soft); }

/* ===== Botones ===== */
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
.btn-primary:hover {
  transform: translateY(-1px);
  filter: brightness(1.05);
  box-shadow: 0 12px 24px rgba(171, 10, 61, .32);
}
.btn-primary:active { transform: translateY(0); }

/* El botón final Generar Carátula, más protagónico */
form.caratula-form > .btn-primary {
  width: 100%;
  padding: 14px;
  font-size: 1rem;
  margin-top: 24px;
}

.d-flex label { margin-right: 10px; }

/* Utilidades de espaciado usadas en la plantilla */
.mb-4 { margin-bottom: 1.5rem; }
.mb-3 { margin-bottom: 1rem; }
.mb-2 { margin-bottom: .5rem; }
.mt-2 { margin-top: .5rem; }
.ms-2 { margin-left: .5rem; }
.small { font-size: .82rem; }

/* ===== Grilla responsive (respeta clases Bootstrap del template) ===== */
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