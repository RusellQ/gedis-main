<template>
  <div>

    <div v-if="expedientes.length === 0" class="alert alert-warning">
      No hay expedientes dados de baja.
    </div>

    <div class="row">
      <div
        class="col-md-4 mb-3"
        v-for="expediente in expedientes"
        :key="expediente.id"
      >
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title">{{ expediente.nombre }}</h5>
            <h6 class="card-subtitle mb-2 text-muted">
              Clave: {{ expediente.clave }}
            </h6>
            <p class="card-text">
              <strong>Motivo de baja:</strong><br />
              {{ expediente.motivo_baja }}
            </p>
            <p class="card-text">
              <strong>Fecha de baja:</strong><br />
              {{ formatFecha(expediente.fecha_baja) }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const expedientes = ref([])

onMounted(async () => {
  try {
    const response = await axios.get('/indexbaja')
    expedientes.value = response.data
  } catch (error) {
    console.error('Error al obtener expedientes dados de baja:', error)
  }
})

function formatFecha(fecha) {
  if (!fecha) return 'N/A'
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Date(fecha).toLocaleDateString('es-MX', options)
}
</script>
