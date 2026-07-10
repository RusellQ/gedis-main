<template>
  <div class="reglas-documentales-panel">
    <FormSectionHeader
      title="Reglas documentales del catálogo archivístico"
      badge="Obtenido automáticamente"
      badge-class="bg-secondary"
      variant="catalog"
      description="Estos datos provienen de la serie (CADIDO) y del instrumento de control. No se capturan aquí; para modificarlos actualice el catálogo archivístico."
    />

    <div v-if="loading" class="text-muted small py-2">Cargando reglas documentales...</div>

    <div v-else-if="reglas" class="reglas-grid">
      <div v-if="reglas.serie_nombre" class="regla-item regla-item--full">
        <span class="regla-label">Serie</span>
        <span class="regla-value">{{ reglas.serie_nombre }} <small v-if="reglas.serie_codigo" class="text-muted">({{ reglas.serie_codigo }})</small></span>
        <span class="regla-origen badge bg-light text-dark border">Serie / CADIDO</span>
      </div>

      <div v-if="reglas.instrumento_nombre" class="regla-item regla-item--full">
        <span class="regla-label">Instrumento de control</span>
        <span class="regla-value">{{ reglas.instrumento_nombre }}</span>
        <span class="regla-origen badge bg-light text-dark border">instrumentos_control</span>
      </div>

      <div class="regla-item">
        <span class="regla-label">Valor documental</span>
        <span class="regla-value">{{ valorDocumentalDesdeReglas(reglas) }}</span>
        <span class="regla-origen badge bg-light text-dark border">Serie</span>
      </div>

      <div class="regla-item">
        <span class="regla-label">Vigencia documental</span>
        <span class="regla-value">{{ formatearValorSimple(reglas.vigencia_documental ? `${reglas.vigencia_documental} años` : null) }}</span>
        <span class="regla-origen badge bg-light text-dark border">Serie</span>
      </div>

      <div class="regla-item">
        <span class="regla-label">Años en archivo de trámite</span>
        <span class="regla-value">{{ formatearValorSimple(reglas.anios_archivo_tramite) }}</span>
        <span class="regla-origen badge bg-light text-dark border">Serie</span>
      </div>

      <div class="regla-item">
        <span class="regla-label">Años en archivo de concentración</span>
        <span class="regla-value">{{ formatearValorSimple(reglas.anios_archivo_concentracion) }}</span>
        <span class="regla-origen badge bg-light text-dark border">Serie</span>
      </div>

      <div class="regla-item">
        <span class="regla-label">Clasificación</span>
        <span class="regla-value">{{ formatearValorSimple(reglas.clasificacion) }}</span>
        <span class="regla-origen badge bg-light text-dark border">Serie</span>
      </div>

      <div class="regla-item">
        <span class="regla-label">Carácter de la información</span>
        <span class="regla-value">{{ valorDocumentalDesdeReglas(reglas) }}</span>
        <span class="regla-origen badge bg-light text-dark border">Serie (mismo que valor documental)</span>
      </div>

      <div class="regla-item regla-item--full">
        <span class="regla-label">Fundamento legal</span>
        <span class="regla-value">{{ formatearValorSimple(reglas.fundamento_legal) }}</span>
        <span class="regla-origen badge bg-light text-dark border">instrumentos_control</span>
      </div>

      <div class="regla-item">
        <span class="regla-label">Periodo de reserva</span>
        <span class="regla-value">{{ formatearValorSimple(reglas.periodo_reserva) }}</span>
        <span class="regla-origen badge bg-light text-dark border">instrumentos_control</span>
      </div>
    </div>

    <div v-else class="alert alert-warning py-2 px-3 mb-0 small">
      {{ emptyMessage }}
    </div>
  </div>
</template>

<script setup>
import FormSectionHeader from './FormSectionHeader.vue'
import {
  formatearValorSimple,
  valorDocumentalDesdeReglas,
} from '../utils/reglasDocumentales.js'

defineProps({
  reglas: { type: Object, default: null },
  loading: { type: Boolean, default: false },
  emptyMessage: {
    type: String,
    default: 'Seleccione una serie o expediente para consultar las reglas documentales del catálogo.',
  },
})
</script>

<style scoped>
.reglas-documentales-panel {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 1rem;
  background: #f8f9fa;
  margin-bottom: 1.5rem;
}

.reglas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 0.75rem;
}

.regla-item {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  padding: 0.6rem 0.75rem;
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 6px;
}

.regla-item--full {
  grid-column: 1 / -1;
}

.regla-label {
  font-size: 0.78rem;
  font-weight: 600;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

.regla-value {
  font-size: 0.95rem;
  color: #212529;
}

.regla-origen {
  align-self: flex-start;
  font-size: 0.68rem;
  font-weight: 500;
  margin-top: 0.15rem;
}
</style>
