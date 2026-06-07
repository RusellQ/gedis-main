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

      <!-- Formulario igual que v1 -->
      <form @submit.prevent="guardarCaratula" class="caratula-form">
        <div class="form-row">
          <div class="form-group">
            <label for="nombreSujetoObligado">Sujeto Obligado</label>
            <input type="text" id="nombreSujetoObligado" v-model="form.nombre_sujeto_obligado" class="form-control" disabled>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="areaAdministrativa">Área Administrativa</label>
            <input type="text" id="areaAdministrativa" v-model="form.area_administrativa" class="form-control" disabled>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="noConsecutivo">No. Consecutivo</label>
            <input type="number" id="noConsecutivo" v-model="form.no_consecutivo" class="form-control" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="areaGeneradora">Área Generadora</label>
            <input type="text" id="areaGeneradora" v-model="form.area_generadora" class="form-control" disabled>
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
            <input type="text" id="ubicacionTopografica" v-model="form.ubicacion_topografica" class="form-control" disabled>
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
            <label for="valorDocumental">Valor Documental</label>
            <div class="d-flex justify-content-start">
              <div class="form-check mr-3">
                <input class="form-check-input" type="checkbox" v-model="form.valor_documental.administrativo" id="administrativo">
                <label class="form-check-label" for="administrativo">Administrativo</label>
              </div>
              <div class="form-check mr-3">
                <input class="form-check-input" type="checkbox" v-model="form.valor_documental.legal" id="legal">
                <label class="form-check-label" for="legal">Legal</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" v-model="form.valor_documental.contable" id="contable">
                <label class="form-check-label" for="contable">Contable</label>
              </div>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="vigenciaDocumental">Vigencia Documental</label>
            <input type="text" id="vigenciaDocumental" v-model="form.vigencia_documental" class="form-control" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="añosArchivoTramite">Años en Archivo de Trámite</label>
            <input type="number" id="añosArchivoTramite" v-model="form.anios_archivo_tramite" class="form-control" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="añosArchivoConcentracion">Años en Archivo de Concentración</label>
            <input type="number" id="añosArchivoConcentracion" v-model="form.anios_archivo_concentracion" class="form-control" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="subtitulo">Información Reservada y/o Confidencial</label>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="caracterInformacion">Carácter de la Información</label>
            <select id="caracterInformacion" v-model="form.caracter_informacion" class="form-control" required>
              <option value="Reservada">Reservada</option>
              <option value="Confidencial">Confidencial</option>
            </select>
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
            <label for="fundamentoLegal">Fundamento Legal</label>
            <input type="text" id="fundamentoLegal" v-model="form.fundamento_legal" class="form-control" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="periodoReserva">Periodo de Reserva</label>
            <input type="text" id="periodoReserva" v-model="form.periodo_reserva" class="form-control" required>
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
          <input type="text" id="capturador" v-model="form.capturador" class="form-control" disabled>
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
  valor_documental: {
    administrativo: false,
    legal: false,
    contable: false,
  },
  vigencia_documental: '',
  anios_archivo_tramite: '',
  anios_archivo_concentracion: '',
  caracter_informacion: 'Reservada',
  fecha_clasificacion: '',
  fundamento_legal: '',
  periodo_reserva: '',
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

const mapearValorDocumentalDesdeExpediente = (caracter) => {
  const vd = { administrativo: false, legal: false, contable: false }
  if (!caracter) return vd
  const c = caracter.toLowerCase()
  if (c === 'administrativo') vd.administrativo = true
  if (c === 'legal') vd.legal = true
  if (c === 'contable') vd.contable = true
  return vd
}

const mapearValorDocumentalDesdeSerie = (serie) => {
  const vd = { administrativo: false, legal: false, contable: false }
  if (!serie) return vd
  if (serie.vigencia_ad) vd.administrativo = true
  if (serie.vigencia_le) vd.legal = true
  if (serie.vigencia_confi) vd.contable = true
  return vd
}

const combinarValorDocumental = (a, b) => ({
  administrativo: a.administrativo || b.administrativo,
  legal: a.legal || b.legal,
  contable: a.contable || b.contable,
})

const mapearCaracterInformacion = (clasificacion) => {
  if (clasificacion === 'Reservada' || clasificacion === 'Confidencial') {
    return clasificacion
  }
  return null
}

const obtenerDatosSerie = (serie, expediente) => {
  if (!serie) return {}

  const datos = {}

  if (serie.plazo_at != null && serie.plazo_at !== '') {
    datos.anios_archivo_tramite = Number(serie.plazo_at)
  }
  if (serie.plazo_ac != null && serie.plazo_ac !== '') {
    datos.anios_archivo_concentracion = Number(serie.plazo_ac)
  }
  if (serie.plazo_ta != null && serie.plazo_ta !== '') {
    datos.vigencia_documental = String(serie.plazo_ta)
  } else if (expediente?.vigencia) {
    datos.vigencia_documental = String(expediente.vigencia)
  }

  const clasificacion = expediente?.clasificacion
  if (clasificacion === 'Reservada' && serie.clasificacion_r) {
    datos.periodo_reserva = serie.clasificacion_r
  } else if (clasificacion === 'Confidencial' && serie.clasificacion_c) {
    datos.periodo_reserva = serie.clasificacion_c
  }

  if (serie.observaciones) {
    datos.observacionesSerie = serie.observaciones
  }

  return datos
}

const aplicarDatosExpediente = (e) => {
  const serie = e.serie
  const datosSerie = obtenerDatosSerie(serie, e)

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

  form.value.valor_documental = combinarValorDocumental(
    mapearValorDocumentalDesdeExpediente(e.caracter),
    mapearValorDocumentalDesdeSerie(serie),
  )

  const caracterInfo = mapearCaracterInformacion(e.clasificacion)
  if (caracterInfo) {
    form.value.caracter_informacion = caracterInfo
  }

  if (datosSerie.vigencia_documental) {
    form.value.vigencia_documental = datosSerie.vigencia_documental
  }
  if (datosSerie.anios_archivo_tramite !== undefined) {
    form.value.anios_archivo_tramite = datosSerie.anios_archivo_tramite
  }
  if (datosSerie.anios_archivo_concentracion !== undefined) {
    form.value.anios_archivo_concentracion = datosSerie.anios_archivo_concentracion
  }
  if (datosSerie.periodo_reserva) {
    form.value.periodo_reserva = datosSerie.periodo_reserva
  }

  const observaciones = [e.observacion, datosSerie.observacionesSerie].filter(Boolean)
  if (observaciones.length) {
    form.value.observaciones = observaciones.join(' | ')
  }
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
  let valorDocumental = { administrativo: false, legal: false, contable: false }

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

    valorDocumental = combinarValorDocumental(
      valorDocumental,
      combinarValorDocumental(
        mapearValorDocumentalDesdeExpediente(e.caracter),
        mapearValorDocumentalDesdeSerie(e.serie),
      ),
    )
  })

  form.value.no_legajo = sumLeg
  form.value.no_fojas = sumFojas
  form.value.ubicacion_topografica = ubicaciones.size === 1 ? [...ubicaciones][0] : form.value.ubicacion_topografica
  form.value.titulo_expediente = titulos.size === 1 ? [...titulos][0] : ''
  form.value.codigo_clasificacion_archivistica = claves.size === 1 ? [...claves][0] : ''
  form.value.descripcion_asunto_expediente = descripciones.join(' | ')
  form.value.valor_documental = valorDocumental

  if (clasificaciones.size === 1) {
    const caracterInfo = mapearCaracterInformacion([...clasificaciones][0])
    if (caracterInfo) form.value.caracter_informacion = caracterInfo
  }

  const aniosApertura = data.map((e) => extraerAnio(e.fecha_apertura)).filter(Boolean)
  const aniosCierre = data.map((e) => extraerAnio(e.fecha_cierre)).filter(Boolean)
  const aperturaUnica = new Set(aniosApertura)
  const cierreUnico = new Set(aniosCierre)
  if (aperturaUnica.size === 1) form.value.anio_apertura = [...aperturaUnica][0]
  if (cierreUnico.size === 1) form.value.anio_cierre = [...cierreUnico][0]

  const datosSerie = obtenerDatosSerie(data[0]?.serie, data[0])
  if (datosSerie.vigencia_documental) form.value.vigencia_documental = datosSerie.vigencia_documental
  if (datosSerie.anios_archivo_tramite !== undefined) form.value.anios_archivo_tramite = datosSerie.anios_archivo_tramite
  if (datosSerie.anios_archivo_concentracion !== undefined) {
    form.value.anios_archivo_concentracion = datosSerie.anios_archivo_concentracion
  }
  if (datosSerie.periodo_reserva) form.value.periodo_reserva = datosSerie.periodo_reserva

  const obsSerie = datosSerie.observacionesSerie
  const todasObs = [...observaciones, obsSerie].filter(Boolean)
  if (todasObs.length) form.value.observaciones = todasObs.join(' | ')
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
    valor_documental: {
      administrativo: false,
      legal: false,
      contable: false,
    },
    vigencia_documental: '',
    anios_archivo_tramite: '',
    anios_archivo_concentracion: '',
    caracter_informacion: 'Reservada',
    fecha_clasificacion: '',
    fundamento_legal: '',
    periodo_reserva: '',
    aplicacion_periodo_reserva: '',
    rubrica_titular: '',
    fecha_desclasificacion: '',
    observaciones: '',
  }

  expedientesData.value = []
  selectedExpedientes.value = []
}

const guardarCaratula = async () => {
  try {
    const payload = { ...form.value }

    if (payload.valor_documental) {
      payload.valor_documental = {
        administrativo: !!payload.valor_documental.administrativo,
        legal: !!payload.valor_documental.legal,
        contable: !!payload.valor_documental.contable,
      }
      if (!payload.valor_documental.administrativo) delete payload.valor_documental.administrativo
      if (!payload.valor_documental.legal) delete payload.valor_documental.legal
      if (!payload.valor_documental.contable) delete payload.valor_documental.contable
    }

    await axios.post('/documentos', payload)
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

.subtitulo {
  text-align: center;
  font-size: 1.5em;
  font-weight: bold;
  margin-bottom: 20px;
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

.form-row {
  display: block;
  margin-bottom: 20px;
}

.form-group {
  width: 100%;
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

.d-flex label {
  margin-right: 10px;
}

.list-group-item input[type="checkbox"] {
  transform: scale(1.05);
}
</style>
