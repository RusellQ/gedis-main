<template>
  <div class="expedientes-page">
    <!-- Encabezado -->
    <header class="page-head">
      <div>
        <h1 class="page-title">Expedientes</h1>
        <p class="page-sub">Consulte, filtre y gestione los expedientes del archivo.</p>
      </div>
      <div class="search-box">
        <span class="search-ico">⌕</span>
        <input
          v-model="busqueda"
          type="text"
          placeholder="Buscar expediente..."
          class="search-input"
        >
      </div>
    </header>

    <p class="results-count">
      {{ expedientesFiltrados.length }} expediente{{ expedientesFiltrados.length === 1 ? '' : 's' }} encontrado{{ expedientesFiltrados.length === 1 ? '' : 's' }}
    </p>

    <!-- Expedientes como folders -->
    <div v-if="paginatedExpedientes.length" class="folders-grid">
      <details
        v-for="expediente in paginatedExpedientes"
        :key="expediente.id"
        class="folder"
        :class="'estado-' + expediente.estado"
      >
        <!-- Portada del folder (clic para abrir) -->
        <summary class="folder-cover">
          <span class="folder-tab" aria-hidden="true"></span>

          <div class="folder-cover-inner">
            <div class="folder-cover-top">
              <span class="exp-badge" :class="'badge-' + expediente.estado">
                <span class="exp-badge-dot"></span>{{ estadoLabel(expediente.estado) }}
              </span>
              <span class="folder-chevron" aria-hidden="true"></span>
            </div>

            <div class="folder-cover-main">
              <span class="folder-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="26" height="26" fill="none"
                     stroke="currentColor" stroke-width="1.7"
                     stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" />
                </svg>
              </span>
              <div class="folder-heading">
                <h3 class="exp-title">{{ expediente.nombre }}</h3>
                <p class="exp-path">
                  {{ expediente.fondo }}
                  <template v-if="expediente.subfondo"> › {{ expediente.subfondo }}</template>
                  › {{ expediente.seccion }}
                  › {{ expediente.serie }}
                  <template v-if="expediente.subserie"> › {{ expediente.subserie }}</template>
                </p>
              </div>
            </div>

            <div class="folder-cover-foot">
              <span class="folder-chip">Clave: {{ expediente.clave || '—' }}</span>
              <span class="folder-chip">Caja: {{ expediente.no_caja ?? '—' }}</span>
              <span class="folder-open-label">
                <span class="lbl-closed">Abrir expediente</span>
                <span class="lbl-open">Cerrar expediente</span>
              </span>
            </div>
          </div>
        </summary>

        <!-- Contenido del expediente (se revela al abrir) -->
        <div class="folder-content">
          <div class="folder-content-grid">
            <!-- Información visual de la serie -->
            <div class="exp-visual">
              <div class="exp-visual-title">Información visual de la serie</div>
              <div class="exp-visual-row">
                <span>Valor documental</span>
                <strong>{{ formatearValorDocumental(expediente.serie_detalle) }}</strong>
              </div>
              <div class="exp-visual-row">
                <span>Vigencia documental</span>
                <strong>{{ formatearVigenciaDocumental(expediente.serie_detalle) }}</strong>
              </div>
              <div class="exp-visual-row">
                <span>Años en Archivo de Trámite</span>
                <strong>{{ formatearValorSimple(expediente.serie_detalle?.plazo_at) }}</strong>
              </div>
              <div class="exp-visual-row">
                <span>Años en Archivo de Concentración</span>
                <strong>{{ formatearValorSimple(expediente.serie_detalle?.plazo_ac) }}</strong>
              </div>
            </div>

            <div class="folder-detail-block">
              <!-- Datos generales -->
              <div class="exp-section-label">Datos generales</div>
              <dl class="exp-grid">
                <div class="exp-field"><dt>Clave</dt><dd>{{ expediente.clave || '—' }}</dd></div>
                <div class="exp-field"><dt>Fecha apertura</dt><dd>{{ expediente.fecha_apertura || '—' }}</dd></div>
                <div class="exp-field"><dt>Fecha cierre</dt><dd>{{ expediente.fecha_cierre || '—' }}</dd></div>
                <div class="exp-field"><dt>Fecha creación</dt><dd>{{ expediente.fecha_creacion || '—' }}</dd></div>
                <div class="exp-field"><dt>Tiempo conservación</dt><dd>{{ expediente.tiempo_conservacion || '—' }}</dd></div>
              </dl>
            </div>

            <div class="folder-detail-block">
              <!-- Ubicación física -->
              <div class="exp-section-label">Ubicación física</div>
              <dl class="exp-grid">
                <div class="exp-field"><dt>No. legajos</dt><dd>{{ expediente.no_legajos ?? '—' }}</dd></div>
                <div class="exp-field"><dt>No. hojas</dt><dd>{{ expediente.no_hojas ?? '—' }}</dd></div>
                <div class="exp-field"><dt>No. caja</dt><dd>{{ expediente.no_caja ?? '—' }}</dd></div>
                <div class="exp-field exp-field-wide">
                  <dt>Ubicación topográfica</dt><dd>{{ expediente.ubicacion_topografica || '—' }}</dd>
                </div>
              </dl>
            </div>

            <div class="folder-detail-block">
              <!-- Clasificación -->
              <div class="exp-section-label">Clasificación</div>
              <dl class="exp-grid">
                <div class="exp-field"><dt>Clasificación</dt><dd>{{ expediente.clasificacion || '—' }}</dd></div>
                <div class="exp-field"><dt>Carácter</dt><dd>{{ expediente.caracter || '—' }}</dd></div>
                <div class="exp-field"><dt>Preservación</dt><dd>{{ expediente.preservacion || '—' }}</dd></div>
              </dl>
            </div>
          </div>

          <p v-if="expediente.observacion" class="exp-obs">
            <strong>Observaciones:</strong> {{ expediente.observacion }}
          </p>

          <div class="exp-actions">
            <button class="btn-apply" @click="mostrarModalUnidades(expediente)">
              Ver unidades documentales
            </button>
            <button
              v-if="!currentUserRoles.includes('lector')"
              class="btn-ghost"
              @click="abrirModalEditar(expediente)"
            >
              Editar expediente
            </button>
          </div>
        </div>
      </details>
    </div>

    <!-- Estado vacío -->
    <div v-else class="empty">
      <div class="empty-ico">▤</div>
      <p class="empty-title">No se encontraron expedientes</p>
      <p class="empty-sub">Ajuste el término de búsqueda e intente de nuevo.</p>
    </div>

    <!-- Modal Visual de Unidades Documentales -->
    <div
      ref="modalVerUnidades"
      class="modal fade exp-modal"
      tabindex="-1"
      aria-labelledby="modalVerUnidadesLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 id="modalVerUnidadesLabel" class="modal-title">Unidades documentales</h5>
            <button type="button" class="btn-close" aria-label="Cerrar" @click="cerrarModal('ver')"></button>
          </div>
          <div class="modal-body">
            <ListaUnidadesDocumentales
              :unidades="expedienteActual?.unidades_documentales || []"
              @seleccionada="cerrarModal('ver')"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para editar expediente -->
    <div
      v-if="expedienteActual"
      ref="modalEditarExpediente"
      class="modal fade exp-modal"
      tabindex="-1"
      aria-labelledby="modalEditarLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 id="modalEditarLabel" class="modal-title">Editar expediente</h5>
            <button type="button" class="btn-close" aria-label="Cerrar" @click="cerrarModal('editar')"></button>
          </div>
          <div class="modal-body">
            <ReglasDocumentalesPanel
              :reglas="reglasDocumentalesEditar"
              empty-message="No hay reglas documentales vinculadas a este expediente."
            />

            <FormSectionHeader
              title="Datos del expediente a editar"
              badge="Captura manual"
              description="Modifique solo la información operativa del expediente."
            />

            <form @submit.prevent="guardarCambios">
              <div class="field-grid">
                <div class="field">
                  <label class="field-label">Nombre</label>
                  <input v-model="expedienteActual.nombre" type="text" class="form-control" required>
                </div>

                <div class="field">
                  <label class="field-label">Código</label>
                  <input v-model="expedienteActual.codigo" type="text" class="form-control" required>
                </div>

                <div class="field">
                  <label class="field-label">Número de legajos</label>
                  <input v-model.number="expedienteActual.no_legajos" type="number" class="form-control" min="0">
                </div>

                <div class="field">
                  <label class="field-label">Número de hojas</label>
                  <input v-model.number="expedienteActual.no_hojas" type="number" class="form-control" min="0">
                </div>

                <div class="field field-wide">
                  <label class="field-label">Ubicación topográfica</label>
                  <input
                    v-model="expedienteActual.ubicacion_topografica"
                    type="text"
                    class="form-control"
                    placeholder="Ej. Dirección General, Oficina Central, etc."
                    required
                  >
                </div>

                <div class="field">
                  <label class="field-label">Número de caja</label>
                  <input v-model.number="expedienteActual.no_caja" type="number" class="form-control" min="0">
                </div>

                <div class="field field-wide">
                  <label class="field-label">Observación</label>
                  <input v-model="expedienteActual.observacion" type="text" class="form-control">
                </div>

                <div class="field">
                  <label class="field-label">Estado</label>
                  <select v-model="expedienteActual.estado" class="form-select">
                    <option value="en_tramite">En trámite</option>
                    <option value="en_concentracion">En concentración</option>
                    <option value="en_historico">En histórico</option>
                  </select>
                </div>

                <div class="field">
                  <label class="field-label">Preservación</label>
                  <select v-model="expedienteActual.preservacion" class="form-select">
                    <option value="Si">Sí</option>
                    <option value="No">No</option>
                  </select>
                </div>

                <div class="field">
                  <label class="field-label">Fecha de creación</label>
                  <input v-model="expedienteActual.fecha_creacion" type="date" class="form-control">
                </div>

                <div class="field">
                  <label class="field-label">Fecha de captura</label>
                  <input v-model="expedienteActual.fecha_apertura" type="date" class="form-control">
                </div>

                <div class="field">
                  <label class="field-label">Fecha de cierre</label>
                  <input v-model="expedienteActual.fecha_cierre" type="date" class="form-control">
                </div>

                <div class="field">
                  <label class="field-label">Clave</label>
                  <input v-model="expedienteActual.clave" type="text" class="form-control">
                </div>

                <div class="field field-wide">
                  <label class="field-label">Información reservada y/o confidencial</label>
                  <input v-model="expedienteActual.informacion_reservada_y_o_confidencial" type="text" class="form-control">
                </div>

                <div class="field">
                  <label class="field-label">Carácter de la información</label>
                  <input v-model="expedienteActual.caracter_de_la_informacion" type="text" class="form-control">
                </div>

                <div class="field">
                  <label class="field-label">Fecha de clasificación</label>
                  <input v-model="expedienteActual.fecha_clasificacion" type="date" class="form-control">
                </div>

                <div class="field">
                  <label class="field-label">Rúbrica titular de la unidad</label>
                  <input v-model="expedienteActual.rubrica_titular_de_la_unidad" type="text" class="form-control">
                </div>

                <div class="field">
                  <label class="field-label">Fecha de desclasificación</label>
                  <input v-model="expedienteActual.fecha_desclasificacion" type="date" class="form-control">
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn-ghost" @click="cerrarModal('editar')">Cancelar</button>
                <button type="submit" class="btn-apply">Guardar cambios</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Paginación -->
    <nav v-if="totalPaginas > 1" aria-label="Paginación" class="pager">
      <button class="btn-ghost" :disabled="paginaActual === 1" @click="paginaActual--">
        ‹ Anterior
      </button>
      <span class="pager-info">Página {{ paginaActual }} de {{ totalPaginas }}</span>
      <button class="btn-ghost" :disabled="paginaActual === totalPaginas" @click="paginaActual++">
        Siguiente ›
      </button>
    </nav>
  </div>
</template>

<script>
import axios from 'axios'
import { Modal } from 'bootstrap'
import FormSectionHeader from '../components/FormSectionHeader.vue'
import ReglasDocumentalesPanel from '../components/ReglasDocumentalesPanel.vue'
import ListaUnidadesDocumentales from '../components/ListaUnidadesDocumentales.vue'
import { reglasDesdeSerieYInstrumento } from '../utils/reglasDocumentales.js'

// Misma paleta semántica que el dashboard de estadísticas, para mantener
// consistencia visual entre módulos.
const ESTADO_LABELS = {
  en_tramite: 'En trámite',
  en_concentracion: 'En concentración',
  en_historico: 'Histórico',
}

export default {
  name: 'ExpedientesPage',
  components: {
    FormSectionHeader,
    ReglasDocumentalesPanel,
    ListaUnidadesDocumentales,
  },
  data() {
    return {
      currentUserRoles: [],
      expedientes: [],
      busqueda: '',
      paginaActual: 1,
      porPagina: 3,
      modalAbierto: false,
      modalVerUnidades: null,
      modalEditarExpediente: null,
      expedienteActual: null,
    }
  },
  created() {
    this.cargarUsuarioActual()
  },
  mounted() {
    this.modalVerUnidades = new Modal(this.$refs.modalVerUnidades)
    this.cargarExpedientes()
  },
  computed: {
    expedientesFiltrados() {
      const texto = this.busqueda.toLowerCase()
      return this.expedientes.filter(e =>
        [e.nombre, e.codigo, e.fondo, e.seccion, e.serie]
          .filter(Boolean)
          .some(campo => campo.toLowerCase().includes(texto))
      )
    },
    totalPaginas() {
      return Math.ceil(this.expedientesFiltrados.length / this.porPagina)
    },
    paginatedExpedientes() {
      const start = (this.paginaActual - 1) * this.porPagina
      return this.expedientesFiltrados.slice(start, start + this.porPagina)
    },
    reglasDocumentalesEditar() {
      if (!this.expedienteActual) return null

      const serie = this.expedienteActual.serie_detalle
      if (serie) {
        return reglasDesdeSerieYInstrumento(serie, {
          fundamento_legal: this.expedienteActual.fundamento_legal,
          periodo_reserva: this.expedienteActual.periodo_reserva,
          nombre_elemento: this.expedienteActual.serie,
        })
      }

      return {
        serie_nombre: this.expedienteActual.serie || '',
        vigencia_documental: this.expedienteActual.tiempo_conservacion != null ? String(this.expedienteActual.tiempo_conservacion) : '',
        anios_archivo_tramite: this.expedienteActual.vigencia ?? '',
        clasificacion: this.expedienteActual.clasificacion || '',
        caracter: this.expedienteActual.caracter || '',
        fundamento_legal: this.expedienteActual.fundamento_legal || '',
        periodo_reserva: this.expedienteActual.periodo_reserva != null ? String(this.expedienteActual.periodo_reserva) : '',
        valor_documental: { administrativo: false, legal: false, contable: false },
      }
    }
  },
  watch: {
    busqueda() {
      this.paginaActual = 1
    }
  },
  methods: {
    async cargarUsuarioActual() {
      try {
        const response = await axios.get('/currentuser')
        if (typeof response.data.roles === 'string') {
          this.currentUserRoles = JSON.parse(response.data.roles)
        } else {
          this.currentUserRoles = response.data.roles || []
        }
        console.log('Roles del usuario:', this.currentUserRoles)
      } catch (error) {
        console.error('Error cargando usuario:', error)
      }
    },
    async cargarExpedientes() {
      try {
        const response = await axios.get('/expedientes')
        this.expedientes = response.data
      } catch (error) {
        console.error('Error al cargar expedientes:', error)
      }
    },
    mostrarModalUnidades(expediente) {
      this.expedienteActual = expediente
      this.modalVerUnidades.show()
    },
    abrirModalEditar(expediente) {
      this.expedienteActual = expediente;
      this.$nextTick(() => {
        if (!this.modalEditar) {
          this.modalEditar = new Modal(this.$refs.modalEditarExpediente);
        }
        this.modalEditar.show();
      });
    },
    cerrarModal(tipo) {
      if (tipo === 'ver') {
        this.modalVerUnidades.hide()
      } else if (tipo === 'editar') {
        this.modalEditar.hide()
      }
    },
    estadoLabel(estado) {
      return ESTADO_LABELS[estado] || estado
    },
    formatearValorDocumental(serieDetalle) {
      if (!serieDetalle) return 'Sin información'
      const valores = []
      if (serieDetalle.vigencia_ad) valores.push('Administrativo')
      if (serieDetalle.vigencia_le) valores.push('Legal')
      if (serieDetalle.vigencia_confi) valores.push('Confidencial')
      return valores.length ? valores.join(', ') : 'Sin información'
    },
    formatearVigenciaDocumental(serieDetalle) {
      if (!serieDetalle) return 'Sin información'
      const valores = []
      if (serieDetalle.vigencia_ad) valores.push('Administrativo')
      if (serieDetalle.vigencia_le) valores.push('Legal')
      if (serieDetalle.vigencia_confi) valores.push('Confidencial')
      return valores.length ? valores.join(', ') : 'Sin información'
    },
    formatearValorSimple(valor) {
      return valor === null || valor === undefined || valor === '' ? 'Sin información' : valor
    },
    guardarCambios() {
      const {
        tiempo_conservacion,
        vigencia,
        clasificacion,
        caracter,
        fundamento_legal,
        periodo_reserva,
        ampliacion_periodo_reserva,
        serie_detalle,
        subserie_detalle,
        ...datosOperativos
      } = this.expedienteActual

      axios
        .put(`/editarexpedientes/${this.expedienteActual.id}`, datosOperativos)
        .then((response) => {
          alert('Expediente actualizado correctamente');
          this.modalEditar.hide();
          this.cargarExpedientes();
        })
        .catch((error) => {
          console.error(error);
          alert('Error al actualizar el expediente');
        });
    },
  }
}
</script>

<style scoped>
.expedientes-page {
  /* Misma paleta corporativa del dashboard de estadísticas */
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

  background: var(--bg);
  min-height: 100vh;
  max-width: 1240px;
  margin: 0 auto;
  padding: 1.75rem 1rem 3rem;
  color: var(--ink);
}

.modal-open {
  overflow: hidden;
}

/* Encabezado */
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

/* ===== Grid de folders ===== */
.folders-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem 1.25rem;
  align-items: start;
}

/* Cada expediente es un folder físico */
.folder {
  --folder-accent: var(--brand);
  position: relative;
  margin-top: 14px; /* espacio para la pestaña */
}
.estado-en_tramite { --folder-accent: #AB0A3D; }
.estado-en_concentracion { --folder-accent: #B68400; }
.estado-en_historico { --folder-accent: #440412; }

/* Pestaña superior del folder */
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

/* Portada clicable */
.folder-cover {
  position: relative;
  z-index: 1;
  list-style: none;
  cursor: pointer;
  background: var(--surface);
  border: 1px solid var(--line);
  border-top: 3px solid var(--folder-accent);
  border-radius: .2rem .95rem .95rem .95rem;
  box-shadow: 0 6px 16px rgba(68, 4, 18, .06);
  transition: box-shadow .18s ease, transform .18s ease;
}
.folder-cover::-webkit-details-marker { display: none; }
.folder-cover:hover {
  box-shadow: 0 12px 26px rgba(68, 4, 18, .12);
  transform: translateY(-3px);
}
.folder[open] .folder-cover {
  border-radius: .2rem .95rem 0 0;
  box-shadow: 0 8px 18px rgba(68, 4, 18, .1);
}

.folder-cover-inner {
  padding: 1rem 1.15rem 1.1rem;
  display: flex;
  flex-direction: column;
  gap: .75rem;
}
.folder-cover-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.exp-badge { margin-bottom: 0; }

/* Chevron que rota al abrir */
.folder-chevron {
  width: 11px;
  height: 11px;
  border-right: 2px solid var(--ink-soft);
  border-bottom: 2px solid var(--ink-soft);
  transform: rotate(45deg);
  transition: transform .2s ease;
}
.folder[open] .folder-chevron {
  transform: rotate(-135deg);
  border-color: var(--folder-accent);
}

.folder-cover-main {
  display: flex;
  align-items: flex-start;
  gap: .7rem;
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
.folder-heading { min-width: 0; }

/* Chips y etiqueta de apertura */
.folder-cover-foot {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: .4rem;
  border-top: 1px dashed var(--line);
  padding-top: .7rem;
}
.folder-chip {
  font-size: .7rem;
  font-weight: 600;
  color: var(--ink-soft);
  background: var(--bg);
  border: 1px solid var(--line);
  border-radius: .5rem;
  padding: .2rem .5rem;
}
.folder-open-label {
  margin-left: auto;
  font-size: .74rem;
  font-weight: 700;
  color: var(--folder-accent);
}
.folder-open-label .lbl-open { display: none; }
.folder[open] .folder-open-label .lbl-closed { display: none; }
.folder[open] .folder-open-label .lbl-open { display: inline; }

/* Contenido revelado */
.folder-content {
  position: relative;
  z-index: 1;
  background: var(--surface);
  border: 1px solid var(--line);
  border-top: none;
  border-radius: 0 0 .95rem .95rem;
  padding: 1.1rem 1.15rem 1.25rem;
  box-shadow: 0 12px 26px rgba(68, 4, 18, .1);
  animation: folder-reveal .22s ease;
}
@keyframes folder-reveal {
  from { opacity: 0; transform: translateY(-6px); }
  to   { opacity: 1; transform: translateY(0); }
}
.folder-content-grid {
  display: grid;
  gap: .5rem 1.5rem;
}
@media (min-width: 720px) {
  .folder[open] { grid-column: 1 / -1; }
  .folder[open] .folder-content-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .folder[open] .exp-visual { grid-column: 1 / -1; }
}
.folder-detail-block { min-width: 0; }

/* Badge de estado */
.exp-badge {
  --badge-color: var(--brand);
  align-self: flex-start;
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  font-size: .72rem;
  font-weight: 700;
  color: var(--badge-color);
  background: color-mix(in srgb, var(--badge-color) 12%, #fff);
  border-radius: 1rem;
  padding: .25rem .65rem;
  margin-bottom: .6rem;
}
.exp-badge-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--badge-color);
}
.badge-en_tramite { --badge-color: #AB0A3D; }
.badge-en_concentracion { --badge-color: #B68400; }
.badge-en_historico { --badge-color: #440412; }

.exp-title {
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0 0 .25rem;
}
.exp-path {
  font-size: .78rem;
  color: var(--ink-soft);
  margin: 0 0 1rem;
  line-height: 1.4;
}

/* Panel de información visual de la serie */
.exp-visual {
  background: var(--brand-soft);
  border-radius: .7rem;
  padding: .75rem .9rem;
  margin-bottom: 1rem;
}
.exp-visual-title {
  font-size: .72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .03em;
  color: var(--brand-dark);
  margin-bottom: .45rem;
}
.exp-visual-row {
  display: flex;
  justify-content: space-between;
  gap: .75rem;
  font-size: .78rem;
  padding: .2rem 0;
  color: var(--ink);
}
.exp-visual-row span { color: var(--ink-soft); }
.exp-visual-row strong { text-align: right; font-weight: 600; }

/* Secciones de datos */
.exp-section-label {
  font-size: .72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .03em;
  color: var(--ink-soft);
  border-top: 1px solid var(--line);
  padding-top: .75rem;
  margin: .25rem 0 .5rem;
}
.exp-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .5rem .75rem;
  margin: 0;
}
.exp-field {
  display: flex;
  flex-direction: column;
  gap: .1rem;
  min-width: 0;
}
.exp-field-wide { grid-column: 1 / -1; }
.exp-field dt {
  font-size: .7rem;
  color: var(--ink-soft);
}
.exp-field dd {
  font-size: .85rem;
  font-weight: 600;
  margin: 0;
  overflow-wrap: break-word;
}

.exp-obs {
  font-size: .82rem;
  color: var(--ink);
  background: var(--gold-soft);
  border-radius: .6rem;
  padding: .6rem .75rem;
  margin: .9rem 0 0;
}

/* Acciones */
.exp-actions {
  display: flex;
  flex-wrap: wrap;
  gap: .5rem;
  margin-top: 1.1rem;
}
.btn-apply {
  border: none;
  background: var(--brand);
  color: #fff;
  font-size: .82rem;
  font-weight: 600;
  padding: .5rem 1rem;
  border-radius: .55rem;
  cursor: pointer;
  transition: background .15s;
}
.btn-apply:hover { background: var(--brand-dark); }
.btn-ghost {
  border: 1px solid var(--line);
  background: #fff;
  color: var(--ink);
  font-size: .82rem;
  font-weight: 600;
  padding: .5rem 1rem;
  border-radius: .55rem;
  cursor: pointer;
  transition: background .15s, border-color .15s;
}
.btn-ghost:hover:not(:disabled) { background: #f5f7fb; border-color: var(--brand); }
.btn-ghost:disabled { opacity: .45; cursor: not-allowed; }

/* Estado vacío */
.empty { text-align: center; padding: 3.5rem 1rem; }
.empty-ico { font-size: 2.5rem; color: var(--stone); margin-bottom: .5rem; }
.empty-title { font-weight: 600; margin: 0 0 .25rem; }
.empty-sub { color: var(--ink-soft); font-size: .85rem; margin: 0; }

/* Paginación */
.pager {
  margin-top: 2rem;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
}
.pager-info { font-size: .82rem; color: var(--ink-soft); }

/* Formulario del modal de edición */
.field-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .9rem 1rem;
}
.field-wide { grid-column: 1 / -1; }
.field-label {
  display: block;
  font-size: .78rem;
  font-weight: 600;
  color: var(--ink-soft);
  margin-bottom: .3rem;
}

/* Reskin de componentes Bootstrap para que hereden la paleta corporativa */
.exp-modal :deep(.modal-content) {
  border: 1px solid var(--line);
  border-radius: .95rem;
  box-shadow: 0 12px 32px rgba(20, 10, 15, .18);
}
.exp-modal :deep(.modal-header) {
  border-bottom: 1px solid var(--line);
  padding: 1rem 1.25rem;
}
.exp-modal :deep(.modal-title) {
  font-weight: 700;
  color: var(--ink);
}
.exp-modal :deep(.modal-body) {
  padding: 1.25rem;
}
.exp-modal :deep(.modal-footer) {
  border-top: 1px solid var(--line);
  padding: .9rem 1.25rem;
  margin-top: 1rem;
}
.exp-modal :deep(.form-control),
.exp-modal :deep(.form-select) {
  border: 1px solid var(--line);
  border-radius: .55rem;
  font-size: .88rem;
  padding: .5rem .7rem;
}
.exp-modal :deep(.form-control:focus),
.exp-modal :deep(.form-select:focus) {
  border-color: var(--brand);
  box-shadow: 0 0 0 .2rem color-mix(in srgb, var(--brand) 18%, transparent);
}
.exp-modal :deep(.btn-close:focus) {
  box-shadow: 0 0 0 .2rem color-mix(in srgb, var(--brand) 18%, transparent);
}

/* Responsive */
@media (max-width: 640px) {
  .page-head { align-items: stretch; }
  .search-box { min-width: 0; }
  .folders-grid { grid-template-columns: 1fr; }
  .field-grid { grid-template-columns: 1fr; }
  .field-wide { grid-column: auto; }
}
</style>
