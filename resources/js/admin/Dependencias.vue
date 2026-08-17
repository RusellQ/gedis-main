<template>
  <div class="admin-page">
    <header class="page-head">
      <div>
        <h1 class="page-title">Dependencias</h1>
        <p class="page-sub">Registre las dependencias del sujeto obligado. Cada una agrupa departamentos y su catálogo archivístico.</p>
      </div>
      <span class="total-pill">{{ filtradas.length }} dependencia{{ filtradas.length === 1 ? '' : 's' }}</span>
    </header>

    <section class="panel">
      <h2 class="panel-title">Nueva dependencia</h2>
      <form class="field-grid" @submit.prevent="guardarDependencia">
        <div class="field">
          <label for="nombre">Nombre</label>
          <input id="nombre" v-model="form.nombre" type="text" class="control" required>
          <p v-if="errores.nombre" class="field-error">{{ errores.nombre[0] }}</p>
        </div>
        <div class="field">
          <label for="clave">Clave</label>
          <input id="clave" v-model="form.clave" type="text" class="control" required>
          <p v-if="errores.clave" class="field-error">{{ errores.clave[0] }}</p>
        </div>
        <div class="field field-span">
          <label for="descripcion">Descripción</label>
          <textarea id="descripcion" v-model="form.descripcion" class="control" rows="2"></textarea>
        </div>
        <div class="field-span actions">
          <button type="submit" class="btn-apply" :disabled="guardando">
            {{ guardando ? 'Guardando…' : 'Guardar dependencia' }}
          </button>
        </div>
      </form>
    </section>

    <div class="results-toolbar">
      <input
        v-model="busqueda"
        type="search"
        class="search-input"
        placeholder="Buscar por nombre, clave o descripción…"
        aria-label="Buscar dependencias"
      >
    </div>

    <div v-if="!filtradas.length" class="empty">
      <p class="empty-title">{{ dependencias.length ? 'Sin coincidencias' : 'No hay dependencias registradas' }}</p>
      <p class="empty-sub">{{ dependencias.length ? 'Ajuste el término de búsqueda.' : 'Capture la primera dependencia con el formulario de arriba.' }}</p>
    </div>

    <div v-else class="table-card">
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Clave</th>
              <th>Nombre</th>
              <th>Descripción</th>
              <th>Registro</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="dep in filtradas" :key="dep.id">
              <td class="cell-clave">{{ dep.clave || '—' }}</td>
              <td class="cell-nombre">{{ dep.nombre }}</td>
              <td>{{ dep.descripcion || '—' }}</td>
              <td class="cell-muted">{{ formatFecha(dep.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from 'sweetalert2'
import axios from 'axios'

export default {
  name: 'DependenciasPage',
  data() {
    return {
      form: {
        nombre: '',
        clave: '',
        descripcion: '',
      },
      errores: {},
      dependencias: [],
      busqueda: '',
      guardando: false,
    }
  },
  computed: {
    filtradas() {
      const texto = this.busqueda.trim().toLowerCase()
      if (!texto) return this.dependencias
      return this.dependencias.filter((dep) =>
        [dep.nombre, dep.clave, dep.descripcion]
          .filter(Boolean)
          .some((campo) => String(campo).toLowerCase().includes(texto))
      )
    },
  },
  mounted() {
    this.obtenerDependencias()
  },
  methods: {
    formatFecha(fecha) {
      if (!fecha) return '—'
      const date = new Date(fecha)
      if (Number.isNaN(date.getTime())) return fecha
      return date.toLocaleDateString('es-MX', { year: 'numeric', month: 'short', day: 'numeric' })
    },
    async guardarDependencia() {
      this.errores = {}
      this.guardando = true
      try {
        const response = await axios.post('/creardependencia', this.form)
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: response.data.message,
          confirmButtonColor: '#AB0A3D',
        })
        this.form = { nombre: '', clave: '', descripcion: '' }
        this.obtenerDependencias()
      } catch (error) {
        if (error.response && error.response.status === 422) {
          this.errores = error.response.data.errors
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error inesperado.',
            confirmButtonColor: '#AB0A3D',
          })
        }
      } finally {
        this.guardando = false
      }
    },
    async obtenerDependencias() {
      try {
        const response = await axios.get('/indexdependencias')
        this.dependencias = response.data
      } catch (error) {
        console.error('Error al cargar dependencias:', error)
      }
    },
  },
}
</script>

<style scoped>
.admin-page {
  --surface: #ffffff;
  --ink: #3D3935;
  --ink-soft: #78736a;
  --line: #e4e0d8;
  --brand: #AB0A3D;
  --brand-dark: #440412;
  --brand-soft: #f7e5eb;
  --gold: #B68400;

  max-width: 1040px;
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
  margin-bottom: 1.25rem;
}
.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 0 .25rem;
  color: var(--brand-dark);
}
.page-sub {
  margin: 0;
  color: var(--ink-soft);
  font-size: .9rem;
  max-width: 40rem;
}
.total-pill {
  font-size: .78rem;
  font-weight: 700;
  color: var(--brand);
  background: var(--brand-soft);
  border-radius: 999px;
  padding: .4rem .8rem;
}

.panel {
  background: var(--surface);
  border: 1px solid var(--line);
  border-top: 4px solid var(--gold);
  border-radius: .95rem;
  padding: 1.1rem 1.15rem 1.2rem;
  margin-bottom: 1.15rem;
  box-shadow: 0 8px 20px rgba(68, 4, 18, .05);
}
.panel-title {
  margin: 0 0 1rem;
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--brand-dark);
}
.field-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .85rem .9rem;
}
.field-span { grid-column: 1 / -1; }
.field label {
  display: block;
  font-size: .72rem;
  font-weight: 700;
  color: var(--ink-soft);
  text-transform: uppercase;
  letter-spacing: .03em;
  margin-bottom: .3rem;
}
.control {
  width: 100%;
  border: 1px solid var(--line);
  border-radius: .55rem;
  background: #fff;
  padding: .5rem .7rem;
  font-size: .88rem;
  color: var(--ink);
}
.control:focus {
  outline: none;
  border-color: var(--brand);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand) 16%, transparent);
}
.field-error {
  margin: .25rem 0 0;
  color: var(--brand);
  font-size: .78rem;
}
.actions { margin-top: .35rem; }
.btn-apply {
  border: none;
  background: linear-gradient(135deg, var(--brand), var(--brand-dark));
  color: #fff;
  font-size: .82rem;
  font-weight: 700;
  padding: .55rem 1rem;
  border-radius: .6rem;
  cursor: pointer;
  box-shadow: 0 8px 16px rgba(171, 10, 61, .2);
}
.btn-apply:hover:not(:disabled) { filter: brightness(1.05); }
.btn-apply:disabled { opacity: .65; cursor: not-allowed; }

.results-toolbar { margin-bottom: .85rem; }
.search-input {
  width: 100%;
  max-width: 28rem;
  border: 1px solid var(--line);
  border-radius: .55rem;
  padding: .5rem .75rem;
  font-size: .88rem;
}
.search-input:focus {
  outline: none;
  border-color: var(--brand);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand) 16%, transparent);
}

.table-card {
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: .95rem;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(68, 4, 18, .05);
}
.table-wrap { overflow-x: auto; }
.admin-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 640px;
}
.admin-table th {
  text-align: left;
  font-size: .7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .04em;
  color: var(--ink-soft);
  background: #faf8f5;
  padding: .7rem .8rem;
  border-bottom: 1px solid var(--line);
}
.admin-table td {
  padding: .7rem .8rem;
  font-size: .84rem;
  border-bottom: 1px solid var(--line);
  vertical-align: top;
}
.admin-table tbody tr:last-child td { border-bottom: none; }
.admin-table tbody tr:hover { background: #fcfbf9; }
.cell-clave { font-weight: 700; color: var(--brand); white-space: nowrap; }
.cell-nombre { font-weight: 700; color: var(--brand-dark); }
.cell-muted { color: var(--ink-soft); white-space: nowrap; }

.empty {
  text-align: center;
  padding: 2.5rem 1rem;
  background: var(--surface);
  border: 1px dashed var(--line);
  border-radius: .95rem;
}
.empty-title { margin: 0 0 .3rem; font-weight: 700; color: var(--brand-dark); }
.empty-sub { margin: 0; color: var(--ink-soft); font-size: .88rem; }

@media (max-width: 700px) {
  .field-grid { grid-template-columns: 1fr; }
  .field-span { grid-column: auto; }
  .search-input { max-width: none; }
}
</style>
