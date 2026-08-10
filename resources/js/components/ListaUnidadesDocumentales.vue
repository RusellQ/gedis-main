<template>
  <div v-if="unidades?.length" class="lista-unidades">
    <p class="text-muted small mb-2">
      Selecciona una unidad para visualizarla.
    </p>
    <div class="list-group lista-unidades__group">
      <button
        v-for="unidad in unidades"
        :key="unidad.id"
        type="button"
        class="list-group-item list-group-item-action lista-unidades__item"
        :class="{
          'lista-unidades__item--clickable': esPrevisualizable(unidad),
          'lista-unidades__item--disabled': !unidad.archivo,
        }"
        :disabled="!unidad.archivo"
        @click="onSeleccionar(unidad)"
      >
        <div class="d-flex align-items-start gap-3">
          <span
            class="lista-unidades__icon flex-shrink-0"
            :class="iconClass(unidad)"
            aria-hidden="true"
          >
            <i :class="icono(unidad)"></i>
          </span>
          <div class="flex-grow-1 min-w-0 text-start">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
              <span class="badge bg-secondary">{{ unidad.tipo || 'Sin tipo' }}</span>
              <span
                v-if="esArchivoPdf(unidad.archivo)"
                class="badge bg-danger-subtle text-danger border border-danger-subtle"
              >
                PDF
              </span>
              <span
                v-else-if="unidad.archivo"
                class="badge bg-warning-subtle text-dark border"
              >
                Archivo
              </span>
              <span v-else class="badge bg-light text-muted border">Sin archivo</span>
            </div>
            <div class="fw-semibold text-dark text-truncate">{{ unidad.nombre || 'Sin nombre' }}</div>
            <div v-if="unidad.descripcion" class="small text-muted text-truncate">
              {{ unidad.descripcion }}
            </div>
          </div>
          <span
            v-if="esPrevisualizable(unidad)"
            class="lista-unidades__chevron text-primary flex-shrink-0 align-self-center"
            aria-hidden="true"
          >
            <i class="fas fa-chevron-right"></i>
          </span>
          <span
            v-else-if="unidad.archivo"
            class="lista-unidades__chevron text-muted flex-shrink-0 align-self-center"
            aria-hidden="true"
            title="Descargar"
          >
            <i class="fas fa-download"></i>
          </span>
        </div>
      </button>
    </div>
  </div>
  <div v-else class="text-center text-muted py-3">
    <i class="fas fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
    <p class="mb-0">Este expediente no tiene unidades documentales.</p>
  </div>
</template>

<script>
import {
  abrirVisorPdf,
  esArchivoPdf,
  urlStorage,
} from '../utils/visorUnidadesDocumentales'

export default {
  name: 'ListaUnidadesDocumentales',
  props: {
    unidades: {
      type: Array,
      default: () => [],
    },
  },
  methods: {
    esArchivoPdf,
    esPrevisualizable(unidad) {
      return Boolean(unidad?.archivo && esArchivoPdf(unidad.archivo))
    },
    icono(unidad) {
      if (this.esPrevisualizable(unidad)) return 'fas fa-file-pdf'
      if (unidad?.archivo) return 'fas fa-file'
      return 'fas fa-file-alt'
    },
    iconClass(unidad) {
      if (this.esPrevisualizable(unidad)) return 'lista-unidades__icon--pdf'
      if (unidad?.archivo) return 'lista-unidades__icon--file'
      return 'lista-unidades__icon--empty'
    },
    onSeleccionar(unidad) {
      if (!unidad?.archivo) return

      this.$emit('seleccionada', unidad)

      if (esArchivoPdf(unidad.archivo)) {
        // Cierra el modal Bootstrap antes de abrir Fancybox para no dejarlo encima.
        this.$nextTick(() => {
          abrirVisorPdf(this.unidades, unidad)
        })
        return
      }

      window.open(urlStorage(unidad.archivo), '_blank', 'noopener')
    },
  },
}
</script>

<style scoped>
.lista-unidades__group {
  border-radius: 0.5rem;
  overflow: hidden;
  border: 1px solid #dee2e6;
}

.lista-unidades__item {
  border-left: 0;
  border-right: 0;
  padding: 0.85rem 1rem;
}

.lista-unidades__item:first-child {
  border-top: 0;
}

.lista-unidades__item:last-child {
  border-bottom: 0;
}

.lista-unidades__item--clickable {
  cursor: pointer;
}

.lista-unidades__item--clickable:hover {
  background-color: #f0f6ff;
}

.lista-unidades__item--disabled {
  opacity: 0.7;
  cursor: default;
}

.lista-unidades__icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.5rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.lista-unidades__icon--pdf {
  background: rgba(220, 53, 69, 0.12);
  color: #dc3545;
}

.lista-unidades__icon--file {
  background: rgba(108, 117, 125, 0.12);
  color: #6c757d;
}

.lista-unidades__icon--empty {
  background: rgba(108, 117, 125, 0.08);
  color: #adb5bd;
}

.lista-unidades__chevron {
  font-size: 0.85rem;
}

.min-w-0 {
  min-width: 0;
}

/* Fallback si Bootstrap subtle badges no están disponibles */
.bg-danger-subtle {
  background-color: rgba(220, 53, 69, 0.12) !important;
}

.border-danger-subtle {
  border-color: rgba(220, 53, 69, 0.25) !important;
}

.bg-warning-subtle {
  background-color: rgba(255, 193, 7, 0.18) !important;
}
</style>
