<template>
  <!-- Selector de dependencia -->
  <div class="mb-4">
    <label for="dependenciaSelect" class="form-label">Selecciona una dependencia:</label>
    <select id="dependenciaSelect" v-model="dependenciaSeleccionada" class="form-select">
      <option value="">-- Todas las dependencias --</option>
      <option v-for="dep in dependencias" :key="dep.id" :value="dep.id">
        {{ dep.nombre }}
      </option>
    </select>
  </div>

  <div v-if="fondosFiltrados.length === 0" class="alert alert-info">No hay fondos disponibles.</div>

  <div class="row g-4">
    <div class="accordion" id="fondosAccordion">
      <div class="accordion-item col-md-4" v-for="(fondo, i) in fondosFiltrados" :key="fondo.id">
        <h2 class="accordion-header">
          <button class="accordion-button" :class="{ collapsed: openFondo !== i }" @click="toggleFondo(i)">
            {{ fondo.nombre }} ({{ fondo.codigo }})
          </button>
        </h2>
        <div v-show="openFondo === i" class="accordion-collapse collapse show">
          <div class="accordion-body">

            <!-- SUBFONDOS -->
            <div v-if="fondo.subfondos && fondo.subfondos.length">
              <div class="accordion" :id="'subfondosAccordion' + i">
                <div class="accordion-item" v-for="(subfondo, j) in fondo.subfondos" :key="subfondo.id">
                  <strong>Subfondo:</strong>
                  <h2 class="accordion-header">
                    <button class="accordion-button" :class="{ collapsed: openSubfondo !== i + '-' + j }"
                      @click="toggleSubfondo(i + '-' + j)">
                      {{ subfondo.nombre }} ({{ subfondo.codigo }})
                    </button>
                  </h2>
                  <div v-show="openSubfondo === i + '-' + j" class="accordion-collapse collapse show">
                    <div class="accordion-body">

                      <!-- SECCIONES EN SUBFONDO -->
                      <div v-if="subfondo.secciones?.length">
                        <div class="accordion" :id="'seccionesAccordion' + i + j">
                          <div class="accordion-item" v-for="(seccion, k) in subfondo.secciones" :key="seccion.id">
                            <strong>Sección:</strong>
                            <h2 class="accordion-header">
                              <button class="accordion-button"
                                :class="{ collapsed: openSeccion !== i + '-' + j + '-' + k }"
                                @click="toggleSeccion(i + '-' + j + '-' + k)">
                                {{ seccion.nombre }} ({{ seccion.codigo }})
                              </button>
                            </h2>
                            <div v-show="openSeccion === i + '-' + j + '-' + k"
                              class="accordion-collapse collapse show">
                              <div class="accordion-body">

                                <!-- SERIES -->
                                <div v-if="seccion.series?.length">
                                  <div class="accordion" :id="'seriesAccordion' + i + j + k">
                                    <div class="accordion-item" v-for="(serie, l) in seccion.series" :key="serie.id">
                                      <strong>Serie:</strong>
                                      <h2 class="accordion-header">
                                        <button class="accordion-button"
                                          :class="{ collapsed: openSerie !== i + '-' + j + '-' + k + '-' + l }"
                                          @click="toggleSerie(i + '-' + j + '-' + k + '-' + l)">
                                          {{ serie.nombre }} ({{ serie.codigo }})
                                        </button>
                                      </h2>
                                      <div v-show="openSerie === i + '-' + j + '-' + k + '-' + l"
                                        class="accordion-collapse collapse show">
                                        <div class="accordion-body">

                                          <!-- SUBSERIES -->
                                          <div v-if="serie.subseries?.length">
                                            <strong>Subserie:</strong>
                                            <div class="accordion" :id="'subseriesAccordion' + i + j + k + l">
                                              <div class="accordion-item" v-for="(subserie, m) in serie.subseries"
                                                :key="subserie.id">
                                                <h2 class="accordion-header">
                                                  <button class="accordion-button"
                                                    :class="{ collapsed: openSubserie !== i + '-' + j + '-' + k + '-' + l + '-' + m }"
                                                    @click="toggleSubserie(i + '-' + j + '-' + k + '-' + l + '-' + m)">
                                                    {{ subserie.nombre }} ({{ subserie.codigo }})
                                                  </button>
                                                </h2>
                                                <div v-show="openSubserie === i + '-' + j + '-' + k + '-' + l + '-' + m"
                                                  class="accordion-collapse collapse show">
                                                  <div class="accordion-body">
                                                    <div v-if="subserie.expedientes?.length">
                                                      <strong>Expedientes:</strong>
                                                      <ul class="list-group mt-2">
                                                        <li class="list-group-item" v-for="exp in subserie.expedientes"
                                                          :key="exp.id + '_subserie'">
                                                          {{ exp.nombre }} ({{ exp.codigo }})
                                                        </li>
                                                      </ul>
                                                    </div>
                                                    <div v-else>No hay expedientes en esta subserie.</div>
                                                  </div>
                                                </div>
                                              </div>
                                            </div>
                                          </div>

                                          <!-- EXPEDIENTES DIRECTOS EN SERIE -->
                                          <div v-if="filteredSerieExpedientes(serie).length" class="mt-3">
                                            <strong>Expedientes en esta serie:</strong>
                                            <ul class="list-group mt-2">
                                              <li class="list-group-item" v-for="exp in filteredSerieExpedientes(serie)"
                                                :key="exp.id + '_serie'">
                                                {{ exp.nombre }} ({{ exp.codigo }})
                                              </li>
                                            </ul>
                                          </div>
                                          <div
                                            v-if="(!serie.subseries || serie.subseries.length === 0) &&
                                              (!serie.expedientes || filteredSerieExpedientes(serie).length === 0)"
                                            class="mt-3">
                                            No hay expedientes ni subseries.
                                          </div>

                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div v-else>No hay series en esta sección.</div>

                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div v-else>No hay secciones en este subfondo.</div>

                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- SECCIONES DIRECTAS EN FONDO (solo si NO hay subfondos) -->
            <div v-if="(!fondo.subfondos || fondo.subfondos.length === 0) && fondo.secciones && fondo.secciones.length">
              <strong>Secciones:</strong>
              <div class="accordion" :id="'seccionesAccordion' + i + 'f'">
                <div class="accordion-item" v-for="(seccion, k) in fondo.secciones" :key="seccion.id">
                  <h2 class="accordion-header">
                    <button class="accordion-button" :class="{ collapsed: openSeccion !== i + '-f-' + k }"
                      @click="toggleSeccion(i + '-f-' + k)">
                      {{ seccion.nombre }} ({{ seccion.codigo }})
                    </button>
                  </h2>
                  <div v-show="openSeccion === i + '-f-' + k" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                      <div v-if="seccion.series?.length">
                        <div class="accordion" :id="'seriesAccordion' + i + 'f' + k">
                          <div class="accordion-item" v-for="(serie, l) in seccion.series" :key="serie.id">
                            <strong>Serie:</strong>
                            <h2 class="accordion-header">
                              <button class="accordion-button"
                                :class="{ collapsed: openSerie !== i + '-f-' + k + '-' + l }"
                                @click="toggleSerie(i + '-f-' + k + '-' + l)">
                                {{ serie.nombre }} ({{ serie.codigo }})
                              </button>
                            </h2>
                            <div v-show="openSerie === i + '-f-' + k + '-' + l"
                              class="accordion-collapse collapse show">
                              <div class="accordion-body">

                                <!-- SUBSERIES -->
                                <div v-if="serie.subseries?.length">
                                  <strong>Subserie:</strong>
                                  <div class="accordion" :id="'subseriesAccordion' + i + 'f' + k + l">
                                    <div class="accordion-item" v-for="(subserie, m) in serie.subseries"
                                      :key="subserie.id">
                                      <h2 class="accordion-header">
                                        <button class="accordion-button"
                                          :class="{ collapsed: openSubserie !== i + '-f-' + k + '-' + l + '-' + m }"
                                          @click="toggleSubserie(i + '-f-' + k + '-' + l + '-' + m)">
                                          {{ subserie.nombre }} ({{ subserie.codigo }})
                                        </button>
                                      </h2>
                                      <div v-show="openSubserie === i + '-f-' + k + '-' + l + '-' + m"
                                        class="accordion-collapse collapse show">
                                        <div class="accordion-body">
                                          <div v-if="subserie.expedientes?.length">
                                            <strong>Expedientes:</strong>
                                            <ul class="list-group mt-2">
                                              <li class="list-group-item" v-for="exp in subserie.expedientes"
                                                :key="exp.id + '_subserie'">
                                                {{ exp.nombre }} ({{ exp.codigo }})
                                              </li>
                                            </ul>
                                          </div>
                                          <div v-else>No hay expedientes en esta subserie.</div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>

                                <!-- EXPEDIENTES DIRECTOS EN SERIE -->
                                <div v-if="filteredSerieExpedientes(serie).length" class="mt-3">
                                  <strong>Expedientes en esta serie:</strong>
                                  <ul class="list-group mt-2">
                                    <li class="list-group-item" v-for="exp in filteredSerieExpedientes(serie)"
                                      :key="exp.id + '_serie'">
                                      {{ exp.nombre }} ({{ exp.codigo }})
                                    </li>
                                  </ul>
                                </div>
                                <div v-if="(!serie.subseries || serie.subseries.length === 0) &&
                                  (!serie.expedientes || filteredSerieExpedientes(serie).length === 0)"
                                  class="mt-3">
                                  No hay expedientes ni subseries.
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div v-else>No hay series en esta sección.</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- SI NO HAY NADA -->
            <div
              v-if="(!fondo.subfondos || fondo.subfondos.length === 0) && (!fondo.secciones || fondo.secciones.length === 0)">
              No hay subfondos ni secciones en este fondo.
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'

const fondos = ref([])

const openFondo = ref(null)
const openSubfondo = ref(null)
const openSeccion = ref(null)
const openSerie = ref(null)
const openSubserie = ref(null)

const dependencias = ref([])
const dependenciaSeleccionada = ref('')

// Cargar dependencias
onMounted(async () => {
  try {
    const depsRes = await axios.get('/indexdependencias')
    dependencias.value = depsRes.data
  } catch (error) {
    console.error('Error al cargar dependencias:', error)
  }
})

// Cargar fondos según la dependencia seleccionada
watch(dependenciaSeleccionada, async (id) => {

  fondos.value = []
  if (!id) return

  try {
    const res = await axios.get('/fondosPorDependencia', {
      params: { id_dependencia: id }
    })
    fondos.value = res.data


  } catch (error) {
    console.error('Error al cargar fondos:', error)
  }
})

function toggleFondo(index) {
  openFondo.value = openFondo.value === index ? null : index
}

function toggleSubfondo(id) {
  openSubfondo.value = openSubfondo.value === id ? null : id
}

function toggleSeccion(id) {
  openSeccion.value = openSeccion.value === id ? null : id
}

function toggleSerie(id) {
  openSerie.value = openSerie.value === id ? null : id
}

function toggleSubserie(id) {
  openSubserie.value = openSubserie.value === id ? null : id
}

const fondosFiltrados = computed(() => fondos.value)

// Filtrar expedientes que no están en subseries
function filteredSerieExpedientes(serie) {
  if (!serie.expedientes) return []

  const idsSubseries = (serie.subseries || [])
    .flatMap(subserie => (subserie.expedientes || []).map(exp => exp.id))

  return serie.expedientes.filter(exp => !idsSubseries.includes(exp.id))
}
</script>
