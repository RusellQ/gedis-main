<template>
  <div class="expedientes-page">
    <header class="page-head">
      <div>
        <h1 class="page-title">Bajas</h1>
        <p class="page-sub">Expedientes dados de baja del acervo archivístico.</p>
      </div>
      <div class="search-box">
        <span class="search-ico">⌕</span>
        <input
          v-model="busqueda"
          type="text"
          placeholder="Buscar por nombre, clave o motivo..."
          class="search-input"
        >
      </div>
    </header>

    <p class="results-count">
      {{ filtrados.length }} expediente{{ filtrados.length === 1 ? '' : 's' }} de baja
    </p>

    <div v-if="filtrados.length" class="folders-grid">
      <article
        v-for="expediente in filtrados"
        :key="expediente.id"
        class="folder estado-baja"
      >
        <div class="folder-cover folder-cover--static">
          <span class="folder-tab" aria-hidden="true"></span>
          <div class="folder-cover-inner">
            <div class="folder-cover-top">
              <span class="exp-badge badge-baja">
                <span class="exp-badge-dot"></span>Baja
              </span>
              <span class="baja-fecha">{{ formatFecha(expediente.fecha_baja) }}</span>
            </div>

            <div class="folder-cover-main">
              <span class="folder-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="26" height="26" fill="none"
                     stroke="currentColor" stroke-width="1.7"
                     stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" />
                  <path d="M9 12h6M12 9v6" />
                </svg>
              </span>
              <div class="folder-heading">
                <h3 class="exp-title">{{ expediente.nombre || 'Sin nombre' }}</h3>
                <p class="exp-path">Clave: {{ expediente.clave || '—' }}</p>
              </div>
            </div>

            <div class="folder-cover-foot baja-foot">
              <div class="baja-motivo">
                <span class="baja-motivo-label">Motivo de baja</span>
                <p class="baja-motivo-text">{{ expediente.motivo_baja || 'Sin motivo registrado' }}</p>
              </div>
            </div>
          </div>
        </div>
      </article>
    </div>

    <div v-else class="empty">
      <div class="empty-ico">▤</div>
      <p class="empty-title">No hay expedientes dados de baja</p>
      <p class="empty-sub">Cuando se den de baja expedientes aparecerán en esta lista.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const expedientes = ref([])
const busqueda = ref('')

const filtrados = computed(() => {
  const texto = busqueda.value.trim().toLowerCase()
  if (!texto) return expedientes.value
  return expedientes.value.filter((e) =>
    [e.nombre, e.clave, e.motivo_baja]
      .filter(Boolean)
      .some((campo) => String(campo).toLowerCase().includes(texto))
  )
})

onMounted(async () => {
  try {
    const response = await axios.get('/indexbaja')
    expedientes.value = response.data
  } catch (error) {
    console.error('Error al obtener expedientes dados de baja:', error)
  }
})

function formatFecha(fecha) {
  if (!fecha) return 'Sin fecha'
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Date(fecha).toLocaleDateString('es-MX', options)
}
</script>

<style scoped>
.expedientes-page {
  --bg: #f7f5f2;
  --surface: #ffffff;
  --ink: #3D3935;
  --ink-soft: #78736a;
  --line: #e4e0d8;
  --brand: #AB0A3D;
  --brand-dark: #440412;
  --brand-soft: #f7e5eb;
  --gold: #B68400;
  --gold-soft: #faf1dd;
  --stone: #B0ABA1;

  background: transparent;
  min-height: 100vh;
  max-width: 1240px;
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
  margin-bottom: .5rem;
}
.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 0 .25rem;
  letter-spacing: -.01em;
  color: var(--brand-dark);
}
.page-sub { margin: 0; color: var(--ink-soft); font-size: .9rem; }

.search-box {
  position: relative;
  min-width: 260px;
}
.search-ico {
  position: absolute;
  left: .85rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--ink-soft);
}
.search-input {
  width: 100%;
  border: 1px solid var(--line);
  border-radius: .6rem;
  background: var(--surface);
  padding: .55rem .75rem .55rem 2.1rem;
  font-size: .88rem;
  color: var(--ink);
}
.search-input:focus {
  outline: none;
  border-color: var(--brand);
}

.results-count {
  font-size: .8rem;
  color: var(--ink-soft);
  margin: 0 0 1.25rem;
}

.folders-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem 1.25rem;
  align-items: stretch;
}

.folder {
  --folder-accent: #6b4a52;
  position: relative;
  margin-top: 14px;
  display: flex;
  flex-direction: column;
  height: 100%;
}
.estado-baja { --folder-accent: #6b4a52; }

.folder-tab {
  position: absolute;
  top: -13px;
  left: 18px;
  width: 108px;
  height: 16px;
  background: var(--folder-accent);
  border-radius: .5rem .5rem 0 0;
  z-index: 0;
}
.folder-tab::after {
  content: "";
  position: absolute;
  right: -12px;
  bottom: 0;
  width: 14px;
  height: 100%;
  background: var(--folder-accent);
  transform: skewX(24deg);
  transform-origin: bottom left;
  border-radius: 0 .4rem 0 0;
}

.folder-cover {
  position: relative;
  z-index: 1;
  background: var(--surface);
  border: 1px solid var(--line);
  border-top: 3px solid var(--folder-accent);
  border-radius: .2rem .95rem .95rem .95rem;
  box-shadow: 0 6px 16px rgba(68, 4, 18, .06);
  display: flex;
  flex-direction: column;
  flex: 1 1 auto;
  min-height: 100%;
}
.folder-cover--static { cursor: default; }

.folder-cover-inner {
  padding: 1rem 1.15rem 1.1rem;
  display: flex;
  flex-direction: column;
  gap: .75rem;
  flex: 1;
  min-height: 11.5rem;
}
.folder-cover-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .5rem;
}
.baja-fecha {
  font-size: .72rem;
  font-weight: 600;
  color: var(--ink-soft);
  text-align: right;
}

.folder-cover-main {
  display: flex;
  align-items: flex-start;
  gap: .7rem;
  flex: 1;
}
.folder-icon {
  flex: none;
  display: grid;
  place-items: center;
  width: 42px;
  height: 42px;
  border-radius: .6rem;
  color: var(--folder-accent);
  background: color-mix(in srgb, var(--folder-accent) 12%, #fff);
}
.folder-heading { min-width: 0; flex: 1; }

.exp-badge {
  --badge-color: #6b4a52;
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  font-size: .72rem;
  font-weight: 700;
  color: var(--badge-color);
  background: color-mix(in srgb, var(--badge-color) 12%, #fff);
  border-radius: 1rem;
  padding: .25rem .65rem;
}
.exp-badge-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--badge-color);
}
.badge-baja { --badge-color: #6b4a52; }

.exp-title {
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0 0 .25rem;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  overflow: hidden;
  min-height: calc(1.05rem * 1.3 * 2);
  color: var(--brand-dark);
}
.exp-path {
  font-size: .78rem;
  color: var(--ink-soft);
  margin: 0;
  line-height: 1.4;
}

.folder-cover-foot {
  border-top: 1px dashed var(--line);
  padding-top: .7rem;
  margin-top: auto;
}
.baja-motivo-label {
  display: block;
  font-size: .68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .03em;
  color: var(--ink-soft);
  margin-bottom: .25rem;
}
.baja-motivo-text {
  margin: 0;
  font-size: .86rem;
  font-weight: 600;
  color: var(--ink);
  line-height: 1.4;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  overflow: hidden;
}

.empty { text-align: center; padding: 3.5rem 1rem; }
.empty-ico { font-size: 2.5rem; color: var(--stone); margin-bottom: .5rem; }
.empty-title { font-weight: 600; margin: 0 0 .25rem; color: var(--brand-dark); }
.empty-sub { color: var(--ink-soft); font-size: .85rem; margin: 0; }

@media (max-width: 640px) {
  .page-head { align-items: stretch; }
  .search-box { min-width: 0; }
  .folders-grid { grid-template-columns: 1fr; }
}
</style>
