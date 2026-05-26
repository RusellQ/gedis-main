<template>
  <div class="p-4">
    <h4>Carátula de Expediente — Versión 2</h4>

    <!-- Jerarquía: fondo -> subfondo (opcional) -> sección -> serie -> subserie -->
    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <label>Fondo</label>
        <select v-model="selectedFondo" @change="onFondoChange" class="form-select">
          <option disabled value="">Selecciona un fondo</option>
          <option v-for="f in fondos" :key="f.id" :value="f.id">{{ f.nombre }}</option>
        </select>
      </div>

      <div class="col-md-4" v-if="selectedFondo">
        <label>Subfondo (opcional)</label>
        <select v-model="selectedSubfondo" @change="fetchSecciones" class="form-select">
          <option value="">Sin subfondo</option>
          <option v-for="s in subfondos" :key="s.id" :value="s.id">{{ s.nombre }}</option>
        </select>
      </div>

      <div class="col-md-4" v-if="selectedFondo">
        <label>Sección</label>
        <select v-model="selectedSeccion" @change="fetchSeries" class="form-select">
          <option disabled value="">Selecciona una sección</option>
          <option v-for="sec in secciones" :key="sec.id" :value="sec.id">{{ sec.nombre }}</option>
        </select>
      </div>

      <div class="col-md-4" v-if="selectedSeccion">
        <label>Serie</label>
        <select v-model="selectedSerie" @change="handleSerieChange" class="form-select">
          <option disabled value="">Selecciona una serie</option>
          <option v-for="ser in series" :key="ser.id" :value="ser.id">{{ ser.nombre }}</option>
        </select>
      </div>

      <div class="col-md-4" v-if="selectedSerie">
        <label>Subserie (opcional)</label>
        <select v-model="selectedSubserie" @change="fetchExpedientes" class="form-select">
          <option value="">Sin subserie</option>
          <option v-for="ss in subseries" :key="ss.id" :value="ss.id">{{ ss.nombre }}</option>
        </select>
      </div>
    </div>

    <!-- Lista de expedientes con selección múltiple -->
    <div v-if="expedientes.length">
      <h5>Expedientes disponibles</h5>
      <div class="list-group mb-3">
        <label v-for="exp in expedientes" :key="exp.id" class="list-group-item">
          <input type="checkbox" v-model="selectedExpedientes" :value="exp.id" />
          <strong class="ms-2">{{ exp.nombre }}</strong>
          <small class="text-muted"> — {{ exp.codigo }}</small>
        </label>
      </div>

      <button class="btn btn-primary mb-3" @click="cargarDatosSeleccionados">Cargar datos</button>
    </div>

    <!-- Formulario de carátula (prefill con datos de expedientes) -->
    <div class="card p-3">
      <h5>Formulario de carátula</h5>
      <form @submit.prevent="guardarCaratula">
        <div class="row g-3">
          <div class="col-md-6">
            <label>Nombre sujeto obligado</label>
            <input v-model="form.nombre_sujeto_obligado" class="form-control" disabled />
          </div>
          <div class="col-md-6">
            <label>Área administrativa</label>
            <input v-model="form.area_administrativa" class="form-control" disabled />
          </div>

          <div class="col-md-2">
            <label>No. consecutivo</label>
            <input v-model="form.no_consecutivo" type="number" class="form-control" required />
          </div>

          <div class="col-md-6">
            <label>Título del expediente</label>
            <input v-model="form.titulo_expediente" class="form-control" required />
          </div>

          <div class="col-md-6">
            <label>Ubicación topográfica</label>
            <input v-model="form.ubicacion_topografica" class="form-control" disabled />
          </div>

          <div class="col-md-6">
            <label>Área generadora</label>
            <input v-model="form.area_generadora" class="form-control" disabled />
          </div>

          <div class="col-md-6">
            <label>Código clasificación archivística</label>
            <input v-model="form.codigo_clasificacion_archivistica" class="form-control" required />
          </div>

          <div class="col-md-3">
            <label>Año apertura</label>
            <input v-model="form.anio_apertura" type="number" class="form-control" required />
          </div>

          <div class="col-md-3">
            <label>Año cierre</label>
            <input v-model="form.anio_cierre" type="number" class="form-control" required />
          </div>

          <div class="col-md-6">
            <label>Soporte documental</label>
            <select v-model="form.soporte_documental" class="form-select" required>
              <option value="Físico">Físico</option>
              <option value="Electrónico">Electrónico</option>
            </select>
          </div>

          <div class="col-md-12">
            <label>Descripción asunto expediente</label>
            <input v-model="form.descripcion_asunto_expediente" class="form-control" required />
          </div>

          <div class="col-md-4">
            <label>No. de legajo</label>
            <input v-model.number="form.no_legajo" type="number" class="form-control" required />
          </div>

          <div class="col-md-8">
            <label>No. de fojas</label>
            <input v-model.number="form.no_fojas" type="number" class="form-control" required />
          </div>

          <div class="col-md-12">
            <label>Valor documental</label>
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" v-model="form.valor_documental.administrativo" id="valorAdministrativo" />
                <label class="form-check-label" for="valorAdministrativo">Administrativo</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" v-model="form.valor_documental.legal" id="valorLegal" />
                <label class="form-check-label" for="valorLegal">Legal</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" v-model="form.valor_documental.contable" id="valorContable" />
                <label class="form-check-label" for="valorContable">Contable</label>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <label>Vigencia documental</label>
            <input v-model="form.vigencia_documental" class="form-control" required />
          </div>

          <div class="col-md-6">
            <label>Años archivo trámite</label>
            <input v-model.number="form.anios_archivo_tramite" type="number" class="form-control" required />
          </div>

          <div class="col-md-6">
            <label>Años archivo concentración</label>
            <input v-model.number="form.anios_archivo_concentracion" type="number" class="form-control" required />
          </div>

          <div class="col-md-6">
            <label>Carácter de la información</label>
            <select v-model="form.caracter_informacion" class="form-select" required>
              <option value="Reservada">Reservada</option>
              <option value="Confidencial">Confidencial</option>
            </select>
          </div>

          <div class="col-md-6">
            <label>Fecha clasificación</label>
            <input v-model="form.fecha_clasificacion" type="date" class="form-control" required />
          </div>

          <div class="col-md-6">
            <label>Fundamento legal</label>
            <input v-model="form.fundamento_legal" class="form-control" required />
          </div>

          <div class="col-md-6">
            <label>Periodo de reserva</label>
            <input v-model="form.periodo_reserva" class="form-control" required />
          </div>

          <div class="col-md-12">
            <label>Aplicación periodo reserva</label>
            <input v-model="form.aplicacion_periodo_reserva" class="form-control" required />
          </div>

          <div class="col-md-6">
            <label>Rúbrica titular</label>
            <input v-model="form.rubrica_titular" class="form-control" required />
          </div>

          <div class="col-md-6">
            <label>Fecha desclasificación</label>
            <input v-model="form.fecha_desclasificacion" type="date" class="form-control" />
          </div>

          <div class="col-md-12">
            <label>Observaciones</label>
            <textarea v-model="form.observaciones" class="form-control"></textarea>
          </div>

          <div class="col-md-6">
            <label>Capturador</label>
            <input v-model="form.capturador" class="form-control" disabled />
          </div>

          <div class="col-12 d-flex justify-content-end">
            <button class="btn btn-success">Guardar carátula</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Vista previa de datos crudos cargados -->
    <div v-if="expedientesData.length" class="mt-4">
      <h5>Datos cargados (expedientes seleccionados)</h5>
      <pre style="max-height:300px;overflow:auto;background:#f8f9fa;padding:10px;border-radius:6px">{{ expedientesData }}</pre>
    </div>
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

const cargarDatosSeleccionados = async () => {
  if (!selectedExpedientes.value.length) return alert('Selecciona al menos un expediente')
  const res = await axios.post('/expedientes/batch', { ids: selectedExpedientes.value })
  expedientesData.value = res.data

  const data = res.data
  if (data.length === 1) {
    const e = data[0]
    form.value.titulo_expediente = e.nombre || ''
    form.value.descripcion_asunto_expediente = e.descripcion_expediente || e.observacion || ''
    form.value.ubicacion_topografica = e.ubicacion_topografica || form.value.ubicacion_topografica
    form.value.no_legajo = e.no_legajos || 0
    form.value.no_fojas = e.no_hojas || 0
    form.value.caracter_informacion = e.caracter || form.value.caracter_informacion
    form.value.vigencia_documental = e.vigencia ? String(e.vigencia) : form.value.vigencia_documental
  } else {
    let sumLeg = 0
    let sumFojas = 0
    const ubicaciones = new Set()
    const titulos = new Set()
    const caracteres = new Set()
    const descripciones = []

    data.forEach(e => {
      sumLeg += Number(e.no_legajos || 0)
      sumFojas += Number(e.no_hojas || 0)
      if (e.ubicacion_topografica) ubicaciones.add(e.ubicacion_topografica)
      if (e.nombre) titulos.add(e.nombre)
      if (e.caracter) caracteres.add(e.caracter)
      if (e.descripcion_expediente) descripciones.push(e.descripcion_expediente)
      if (e.observacion && !e.descripcion_expediente) descripciones.push(e.observacion)
    })

    form.value.no_legajo = sumLeg
    form.value.no_fojas = sumFojas
    form.value.ubicacion_topografica = ubicaciones.size === 1 ? [...ubicaciones][0] : form.value.ubicacion_topografica
    form.value.titulo_expediente = titulos.size === 1 ? [...titulos][0] : ''
    form.value.descripcion_asunto_expediente = descripciones.join(' | ')
    form.value.caracter_informacion = caracteres.size === 1 ? [...caracteres][0] : form.value.caracter_informacion
  }
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
    alert('Carátula guardada')

    form.value = {
      id_dependencia: form.value.id_dependencia,
      id_departamento: form.value.id_departamento,
      nombre_sujeto_obligado: form.value.nombre_sujeto_obligado,
      area_administrativa: form.value.area_administrativa,
      no_consecutivo: '',
      area_generadora: form.value.area_generadora,
      codigo_clasificacion_archivistica: '',
      anio_apertura: '',
      anio_cierre: '',
      soporte_documental: 'Físico',
      titulo_expediente: '',
      descripcion_asunto_expediente: '',
      ubicacion_topografica: form.value.ubicacion_topografica,
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
      capturador: form.value.capturador,
    }
    expedientesData.value = []
    selectedExpedientes.value = []
  } catch (e) {
    console.error(e)
    alert('Error al guardar carátula')
  }
}

onMounted(() => {
  fetchFondos()
  fetchCurrentUser()
})
</script>

<style scoped>
.list-group-item input[type="checkbox"] {
  transform: scale(1.05);
}

.d-flex.gap-3 > .form-check {
  margin-right: 1rem;
}
</style>
