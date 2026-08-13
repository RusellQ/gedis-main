<template>
  <div class="consultar-page">
    <header class="page-head">
      <div>
        <h1 class="page-title">Consultar carátulas</h1>
        <p class="page-sub">Consulte, descargue y gestione las carátulas de expediente ya generadas.</p>
      </div>
      <div class="head-actions">
        <div class="search-box">
          <span class="search-ico" aria-hidden="true">⌕</span>
          <input
            v-model="palabraClave"
            type="search"
            placeholder="Buscar carátula..."
            class="search-input"
            aria-label="Buscar carátula"
            @input="paginaActual = 1"
          >
        </div>
        <button type="button" class="btn-blank" @click="descargarCaratulaEnBlanco">
          Descargar carátula en blanco
        </button>
      </div>
    </header>

    <p class="results-count">
      {{ documentosFiltrados.length }} carátula{{ documentosFiltrados.length === 1 ? '' : 's' }}
      encontrada{{ documentosFiltrados.length === 1 ? '' : 's' }}
    </p>

    <div v-if="documentosPaginados.length" class="cards-grid">
      <article
        v-for="documento in documentosPaginados"
        :key="documento.id || documento._id"
        class="caratula-card"
      >
        <div class="card-accent" aria-hidden="true"></div>

        <div class="card-top">
          <span class="soporte-badge" :class="soporteClass(documento.soporte_documental)">
            {{ documento.soporte_documental || 'Sin soporte' }}
          </span>
          <span class="card-consecutivo">No. {{ documento.no_consecutivo || '—' }}</span>
        </div>

        <h2 class="card-title">{{ documento.titulo_expediente || 'Sin título' }}</h2>
        <p class="card-code">{{ documento.codigo_clasificacion_archivistica || 'Sin código' }}</p>
        <p class="card-desc">{{ documento.descripcion_asunto_expediente || 'Sin descripción del asunto' }}</p>

        <dl class="card-meta">
          <div>
            <dt>Sujeto obligado</dt>
            <dd>{{ documento.nombre_sujeto_obligado || '—' }}</dd>
          </div>
          <div>
            <dt>Área generadora</dt>
            <dd>{{ documento.area_generadora || '—' }}</dd>
          </div>
          <div>
            <dt>Fechas extremas</dt>
            <dd>{{ documento.anio_apertura || '—' }} – {{ documento.anio_cierre || '—' }}</dd>
          </div>
          <div>
            <dt>Legajo / Fojas</dt>
            <dd>{{ documento.no_legajo ?? '—' }} / {{ documento.no_fojas ?? '—' }}</dd>
          </div>
          <div>
            <dt>Capturador</dt>
            <dd>{{ documento.capturador || '—' }}</dd>
          </div>
          <div>
            <dt>Valor documental</dt>
            <dd>{{ documento.valor_documental || formatearValorDocumentalTabla(documento) || '—' }}</dd>
          </div>
        </dl>

        <details class="card-more">
          <summary>Ver más datos</summary>
          <dl class="card-extra">
            <div><dt>Área administrativa</dt><dd>{{ documento.area_administrativa || '—' }}</dd></div>
            <div><dt>Ubicación topográfica</dt><dd>{{ documento.ubicacion_topografica || '—' }}</dd></div>
            <div><dt>Vigencia documental</dt><dd>{{ documento.vigencia_documental || '—' }}</dd></div>
            <div><dt>Años trámite</dt><dd>{{ documento.anios_archivo_tramite ?? '—' }}</dd></div>
            <div><dt>Años concentración</dt><dd>{{ documento.anios_archivo_concentracion ?? '—' }}</dd></div>
            <div><dt>Carácter</dt><dd>{{ documento.caracter_informacion || '—' }}</dd></div>
            <div><dt>Fecha clasificación</dt><dd>{{ documento.fecha_clasificacion || '—' }}</dd></div>
            <div><dt>Fundamento legal</dt><dd>{{ documento.fundamento_legal || '—' }}</dd></div>
            <div><dt>Periodo reserva</dt><dd>{{ documento.periodo_reserva || '—' }}</dd></div>
            <div><dt>Aplicación reserva</dt><dd>{{ documento.aplicacion_periodo_reserva || '—' }}</dd></div>
            <div><dt>Rúbrica titular</dt><dd>{{ documento.rubrica_titular || '—' }}</dd></div>
            <div><dt>Fecha desclasificación</dt><dd>{{ documento.fecha_desclasificacion || '—' }}</dd></div>
            <div class="extra-full"><dt>Observaciones</dt><dd>{{ documento.observaciones || '—' }}</dd></div>
          </dl>
        </details>

        <div class="card-actions">
          <button type="button" class="btn-action btn-pdf" title="Descargar carátula con datos" @click="generarPdf(documento)">
            PDF
          </button>
          <button
            v-if="!currentUserRoles.includes('lector')"
            type="button"
            class="btn-action btn-edit"
            title="Editar carátula"
            @click="editarDocumento(documento)"
          >
            Editar
          </button>
          <button
            v-if="!currentUserRoles.includes('lector')"
            type="button"
            class="btn-action btn-delete"
            title="Eliminar carátula"
            @click="eliminarDocumento(documento)"
          >
            Eliminar
          </button>
        </div>
      </article>
    </div>

    <div v-else class="empty">
      <p class="empty-title">No se encontraron carátulas</p>
      <p class="empty-sub">Ajuste el término de búsqueda o genere una carátula desde el módulo correspondiente.</p>
    </div>

    <div v-if="totalPaginas > 1" class="pager">
      <button type="button" class="pager-btn" :disabled="paginaActual <= 1" @click="cambiarPagina(paginaActual - 1)">
        ‹ Anterior
      </button>
      <span class="pager-info">Página {{ paginaActual }} de {{ totalPaginas }}</span>
      <button type="button" class="pager-btn" :disabled="paginaActual >= totalPaginas" @click="cambiarPagina(paginaActual + 1)">
        Siguiente ›
      </button>
    </div>

    <!-- Modal de Edición -->
    <div class="modal fade cons-modal" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel">Editar carátula de expediente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <form class="edit-form" @submit.prevent="updateDocumento">
              <div class="edit-grid">
                <div class="field field-span-2">
                  <label for="nombre_sujeto_obligado">Nombre del Sujeto Obligado</label>
                  <input id="nombre_sujeto_obligado" v-model="editDocumento.nombre_sujeto_obligado" type="text" class="form-control" required disabled>
                </div>
                <div class="field">
                  <label for="area_administrativa">Área Administrativa</label>
                  <input id="area_administrativa" v-model="editDocumento.area_administrativa" type="text" class="form-control" required disabled>
                </div>
                <div class="field">
                  <label for="area_generadora">Área Generadora</label>
                  <input id="area_generadora" v-model="editDocumento.area_generadora" type="text" class="form-control" required disabled>
                </div>
                <div class="field">
                  <label for="no_consecutivo">No. Consecutivo</label>
                  <input id="no_consecutivo" v-model="editDocumento.no_consecutivo" type="text" class="form-control" required>
                </div>
                <div class="field">
                  <label for="codigo_clasificacion_archivistica">Código de Clasificación Archivística</label>
                  <input id="codigo_clasificacion_archivistica" v-model="editDocumento.codigo_clasificacion_archivistica" type="text" class="form-control" required>
                </div>
                <div class="field">
                  <label for="anio_apertura">Año Apertura</label>
                  <input id="anio_apertura" v-model="editDocumento.anio_apertura" type="number" class="form-control" required>
                </div>
                <div class="field">
                  <label for="anio_cierre">Año Cierre</label>
                  <input id="anio_cierre" v-model="editDocumento.anio_cierre" type="number" class="form-control" required>
                </div>
                <div class="field">
                  <label for="soporte_documental">Soporte Documental</label>
                  <input id="soporte_documental" v-model="editDocumento.soporte_documental" type="text" class="form-control" required>
                </div>
                <div class="field field-span-2">
                  <label for="titulo_expediente">Título del Expediente</label>
                  <input id="titulo_expediente" v-model="editDocumento.titulo_expediente" type="text" class="form-control" required>
                </div>
                <div class="field field-span-2">
                  <label for="descripcion_asunto_expediente">Descripción del Asunto</label>
                  <input id="descripcion_asunto_expediente" v-model="editDocumento.descripcion_asunto_expediente" type="text" class="form-control" required>
                </div>
                <div class="field field-span-2">
                  <label for="ubicacion_topografica">Ubicación Topográfica</label>
                  <input id="ubicacion_topografica" v-model="editDocumento.ubicacion_topografica" type="text" class="form-control" required disabled>
                </div>
                <div class="field">
                  <label for="no_legajo">No. de Legajo</label>
                  <input id="no_legajo" v-model="editDocumento.no_legajo" type="text" class="form-control" required>
                </div>
                <div class="field">
                  <label for="no_fojas">No. de Fojas</label>
                  <input id="no_fojas" v-model="editDocumento.no_fojas" type="number" class="form-control" required>
                </div>

                <div class="field field-span-2">
                  <span class="field-label">Valor Documental</span>
                  <div class="checks">
                    <label class="check">
                      <input v-model="editDocumento.administrativo" type="checkbox" id="administrativo">
                      Administrativo
                    </label>
                    <label class="check">
                      <input v-model="editDocumento.legal" type="checkbox" id="legal">
                      Legal
                    </label>
                    <label class="check">
                      <input v-model="editDocumento.contable" type="checkbox" id="contable">
                      Contable
                    </label>
                  </div>
                </div>

                <div class="field">
                  <label for="vigencia_documental">Vigencia Documental</label>
                  <input id="vigencia_documental" v-model="editDocumento.vigencia_documental" type="text" class="form-control" required>
                </div>
                <div class="field">
                  <label for="caracter_informacion">Carácter de la Información</label>
                  <input id="caracter_informacion" v-model="editDocumento.caracter_informacion" type="text" class="form-control" required>
                </div>
                <div class="field">
                  <label for="anios_archivo_tramite">Años en Archivo de Trámite</label>
                  <input id="anios_archivo_tramite" v-model="editDocumento.anios_archivo_tramite" type="number" class="form-control" required>
                </div>
                <div class="field">
                  <label for="anios_archivo_concentracion">Años en Archivo de Concentración</label>
                  <input id="anios_archivo_concentracion" v-model="editDocumento.anios_archivo_concentracion" type="number" class="form-control" required>
                </div>
                <div class="field">
                  <label for="fecha_clasificacion">Fecha de Clasificación</label>
                  <input id="fecha_clasificacion" v-model="editDocumento.fecha_clasificacion" type="date" class="form-control" required>
                </div>
                <div class="field">
                  <label for="fecha_desclasificacion">Fecha de Desclasificación</label>
                  <input id="fecha_desclasificacion" v-model="editDocumento.fecha_desclasificacion" type="date" class="form-control" required>
                </div>
                <div class="field field-span-2">
                  <label for="fundamento_legal">Fundamento Legal</label>
                  <input id="fundamento_legal" v-model="editDocumento.fundamento_legal" type="text" class="form-control" required>
                </div>
                <div class="field">
                  <label for="periodo_reserva">Periodo de Reserva</label>
                  <input id="periodo_reserva" v-model="editDocumento.periodo_reserva" type="text" class="form-control" required>
                </div>
                <div class="field">
                  <label for="aplicacion_periodo_reserva">Aplicación del Periodo de Reserva</label>
                  <input id="aplicacion_periodo_reserva" v-model="editDocumento.aplicacion_periodo_reserva" type="text" class="form-control" required>
                </div>
                <div class="field field-span-2">
                  <label for="rubrica_titular">Rubrica del Titular</label>
                  <input id="rubrica_titular" v-model="editDocumento.rubrica_titular" type="text" class="form-control" required>
                </div>
                <div class="field field-span-2">
                  <label for="observaciones">Observaciones</label>
                  <textarea id="observaciones" v-model="editDocumento.observaciones" class="form-control" rows="3" required></textarea>
                </div>
              </div>

              <div class="edit-footer">
                <button type="submit" class="btn-save">Guardar cambios</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';
import { Fancybox } from "@fancyapps/ui";
import '@fancyapps/ui/dist/fancybox/fancybox.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import jsPDF from 'jspdf';
import 'jspdf-autotable';
import html2canvas from 'html2canvas';

export default {
  data() {
    return {
      currentUserRoles: [],
      documentos: [],
      paginaActual: 1,
      documentosPorPagina: 12,
      editDocumento: {
        administrativo: false,
        legal: false,
        contable: false,
        valor_documental: {},
      },
      files: [],
      palabraClave: '',
    };
  },
  computed: {
    documentosPaginados() {
      const documentos = this.documentosFiltrados;
      const inicio = (this.paginaActual - 1) * this.documentosPorPagina;
      return documentos.slice(inicio, inicio + this.documentosPorPagina);
    },
    totalPaginas() {
      return Math.ceil(this.documentosFiltrados.length / this.documentosPorPagina) || 0;
    },
    documentosFiltrados() {
      if (!this.palabraClave) return this.documentos;
      return this.documentos.filter(doc =>
        Object.values(doc).some(value =>
          String(value).toLowerCase().includes(this.palabraClave.toLowerCase())
        )
      );
    }
  },
  mounted() {
    this.getAllDocuments();
    this.getCurrentUser();
    Fancybox.bind("[data-fancybox]", {});
  },
  methods: {
    soporteClass(soporte) {
      const valor = String(soporte || '').toLowerCase();
      if (valor.includes('electr')) return 'soporte-electronico';
      if (valor.includes('fís') || valor.includes('fis')) return 'soporte-fisico';
      return 'soporte-otro';
    },
    cambiarPagina(pagina) {
      if (pagina < 1 || pagina > this.totalPaginas) return;
      this.paginaActual = pagina;
    },
    getAllDocuments() {
      axios.get('/documentos')
        .then(response => {
          this.documentos = response.data;
        })
        .catch(error => {
          console.error('Error al obtener documentos:', error);
        });
    },
    formatearValorDocumentalTabla(documento) {
      const partes = [];
      if (documento.valor_documental_administrativo == 1) partes.push('Administrativo');
      if (documento.valor_documental_legal == 1) partes.push('Legal');
      if (documento.valor_documental_contable == 1) partes.push('Contable');
      return partes.join(', ');
    },
    async getCurrentUser() {
      try {
        const response = await axios.get('/currentuser');
        this.currentUser = response.data;
        this.currentUserRoles = response.data.roles || [];
      } catch (error) {
        console.error('Error al obtener usuario actual:', error);
      }
    },
    editarDocumento(documento) {
      if (!documento) {
        console.error('El documento no está definido:', documento);
        return;
      }

      this.editDocumento = { ...documento };
      this.editDocumento.administrativo = documento.valor_documental_administrativo == 1;
      this.editDocumento.legal = documento.valor_documental_legal == 1;
      this.editDocumento.contable = documento.valor_documental_contable == 1;
      $('#editModal').modal('show');
    },
    updateDocumento() {
      if (!this.editDocumento) {
        console.error('editDocumento no está definido');
        return;
      }

      const valorDocumental = {
        administrativo: this.editDocumento.administrativo || false,
        legal: this.editDocumento.legal || false,
        contable: this.editDocumento.contable || false,
      };

      this.editDocumento.valor_documental = valorDocumental;
      const idDocumento = this.editDocumento.id;

      axios.post(`/documentos/update/${idDocumento}`, this.editDocumento)
        .then(() => {
          Swal.fire({
            icon: 'success',
            title: 'Documento actualizado',
            showConfirmButton: false,
            timer: 1500
          });
          this.getAllDocuments();
          $('#editModal').modal('hide');
        })
        .catch(error => {
          console.error('Error al actualizar documento:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un problema al actualizar el documento'
          });
        });
    },
    descargarCaratulaEnBlanco() {
      if (!this.documentos.length) {
        Swal.fire({
          icon: 'warning',
          title: 'Sin documentos',
          text: 'No hay documentos disponibles para descargar una carátula en blanco.'
        });
        return;
      }

      const documentoSeleccionado = this.documentos[0];
      this.generarPdf(documentoSeleccionado, true);
    },
    generarPdf(documento, enBlanco = false) {
      const doc = new jsPDF({
        orientation: "portrait",
        unit: "mm",
        format: "letter",
      });

      const idDocumento = documento.id;

      axios.get(`/documentos/${idDocumento}`)
        .then(response => {
          const documento = response.data;
          const imgData = "assets/logoletras.png";
          const emptyValue = (value) => (enBlanco ? "" : value);

          const valorDocumentalDisplay = (() => {
            if (enBlanco) return "";

            if (documento.valor_documental) {
              const partes = documento.valor_documental.split(',').map((p) => p.trim()).filter(Boolean);
              if (partes.length > 1) {
                const lastItem = partes.pop();
                return `${partes.join(", ")} y ${lastItem}`;
              }
              return documento.valor_documental;
            }

            let display = [];
            if (documento.valor_documental_legal) display.push("Legal");
            if (documento.valor_documental_contable) display.push("Contable");
            if (documento.valor_documental_administrativo) display.push("Administrativo");

            if (display.length > 1) {
              const lastItem = display.pop();
              return `${display.join(", ")} y ${lastItem}`;
            }

            return display[0] || "";
          })();

          const caracterInformacionDisplay = enBlanco ? '' : (valorDocumentalDisplay || '');

          let startX = 10;
          let startY = 10;
          const pageWidth = 190;
          const cellWidth = pageWidth / 2;
          const rowHeight = 8;
          const logoCellHeight = 16;

          const tableData = [
            [
              { label: "LOGOTIPO" },
              { label: "NOMBRE DEL SUJETO OBLIGADO:", value: emptyValue(documento.nombre_sujeto_obligado) },
            ],
            [
              { label: "Área administrativa", value: emptyValue(documento.area_administrativa) },
              { label: "No. Consecutivo", value: emptyValue(documento.no_consecutivo) },
            ],
            [
              { label: "Área generadora", value: emptyValue(documento.area_generadora) },
              { label: "Código de clasificación", value: emptyValue(documento.codigo_clasificacion_archivistica) },
            ],
            [
              { label: "Año de apertura", value: emptyValue(documento.anio_apertura), width: 25 },
              { label: "Año de cierre", value: emptyValue(documento.anio_cierre), width: 25 },
              { label: "Soporte documental", value: emptyValue(documento.soporte_documental), width: 140 },
            ],
            [
              { label: "Título del expediente", colspan: 2, value: emptyValue(documento.titulo_expediente) },
            ],
            [
              { label: "Descripción del asunto que trata el expediente", colspan: 2, value: emptyValue(documento.descripcion_asunto_expediente) },
            ],
            [
              { label: "Ubicación topográfica", value: emptyValue(documento.ubicacion_topografica), width: 110 },
              { label: "No. De legajo", value: emptyValue(documento.no_legajo), width: 40 },
              { label: "No. De fojas", value: emptyValue(documento.no_fojas), width: 40 },
            ],
            [
              { label: "Valor documental", value: valorDocumentalDisplay },
              { label: "Vigencia documental", value: emptyValue(documento.vigencia_documental) },
            ],
            [
              { label: "Años en Archivo de Trámite", value: emptyValue(documento.anios_archivo_tramite) },
              { label: "Años en Archivo de Concentración", value: emptyValue(documento.anios_archivo_concentracion) },
            ],
            [
              { label: "INFORMACIÓN RESERVADA Y/O CONFIDENCIAL", colspan: 2, value: "" },
            ],
            [
              { label: "Carácter de la Información", value: caracterInformacionDisplay },
              { label: "Fecha de clasificación", value: emptyValue(documento.fecha_clasificacion) },
            ],
            [
              { label: "Fundamento legal", colspan: 2, value: emptyValue(documento.fundamento_legal) },
            ],
            [
              { label: "Periodo de reserva", value: emptyValue(documento.periodo_reserva) },
              { label: "Ampliación del periodo de reserva", value: emptyValue(documento.aplicacion_periodo_reserva) },
            ],
            [
              { label: "Rúbrica del titular de la unidad administrativa", value: emptyValue(documento.rubrica_titular) },
              { label: "Fecha de desclasificación", value: emptyValue(documento.fecha_desclasificacion) },
            ],
            [
              { label: "Observaciones", colspan: 2, value: emptyValue(documento.observaciones) },
            ],
          ];

          function drawCell(x, y, width, height, text, align = "left", bold = false, img = null, isLabel = false) {
            if (isLabel) {
              doc.setFillColor(200, 200, 200);
              doc.rect(x, y, width, height, "F");
            }

            doc.rect(x, y, width, height);

            if (img) {
              const imgWidth = Math.min(width - 4, 30);
              const imgHeight = logoCellHeight - 4;
              doc.addImage(img, "PNG", x + (width - imgWidth) / 2, y + (height - imgHeight) / 2, imgWidth, imgHeight);
            } else {
              doc.setFont("helvetica", bold ? "bold" : "normal");
              doc.setFontSize(8);
              doc.text(String(text), x + 2, y + height / 2 + 2, { align: align });
            }
          }

          tableData.forEach((row) => {
            let currentX = startX;
            row.forEach((cell) => {
              const width = cell.colspan ? cellWidth * 2 : (cell.width || cellWidth);
              const height = (cell.label === "LOGOTIPO") ? logoCellHeight : (cell.height || rowHeight);

              if (cell.label === "LOGOTIPO") {
                drawCell(currentX, startY, width, logoCellHeight, "", "center", false, imgData);
              } else {
                drawCell(currentX, startY, width, rowHeight, cell.label, "left", true, null, true);
                drawCell(currentX, startY + rowHeight, width, rowHeight, cell.value || "", "left");
              }

              currentX += width;
            });
            startY += rowHeight * 2;

            if (startY + rowHeight * 2 > 270) {
              doc.addPage();
              startY = 10;
            }
          });

          const fileName = enBlanco ? "caratula_expediente_en_blanco.pdf" : "caratula_expediente.pdf";
          doc.save(fileName);
        })
        .catch(error => {
          console.error("Error al obtener los datos:", error);
        });
    },
    eliminarDocumento(documento) {
      const idDocumento = documento.id;

      Swal.fire({
        title: '¿Estás seguro?',
        text: "No podrás revertir esto",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#AB0A3D',
        cancelButtonColor: '#78736a',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          axios.delete(`/documentos/${idDocumento}`)
            .then(() => {
              Swal.fire('Eliminado', 'El documento ha sido eliminado', 'success');
              this.getAllDocuments();
            })
            .catch(error => {
              console.error('Error al eliminar documento:', error);
              Swal.fire({
                icon: 'error',
                title: 'Error al eliminar',
                text: 'No se pudo eliminar el documento. Por favor, intenta de nuevo.'
              });
            });
        }
      });
    },
  }
};
</script>

<style scoped>
.consultar-page {
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

.page-sub {
  margin: 0;
  color: var(--ink-soft);
  font-size: .9rem;
}

.head-actions {
  display: flex;
  flex-wrap: wrap;
  gap: .75rem;
  align-items: center;
}

.search-box {
  position: relative;
  min-width: 240px;
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
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand) 16%, transparent);
}

.btn-blank {
  border: 1px solid var(--gold);
  background: var(--gold-soft);
  color: var(--brand-dark);
  font-size: .82rem;
  font-weight: 700;
  padding: .55rem .9rem;
  border-radius: .6rem;
  cursor: pointer;
  transition: background .15s, transform .12s;
}

.btn-blank:hover {
  background: #f5e6c0;
  transform: translateY(-1px);
}

.results-count {
  font-size: .8rem;
  color: var(--ink-soft);
  margin: 0 0 1.25rem;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.25rem;
  align-items: stretch;
}

.caratula-card {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: .7rem;
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: .95rem;
  padding: 1.1rem 1.15rem 1rem;
  box-shadow: 0 8px 20px rgba(68, 4, 18, .06);
  min-height: 100%;
  overflow: hidden;
}

.card-accent {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--brand), var(--gold));
}

.card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .5rem;
  margin-top: .15rem;
}

.soporte-badge {
  display: inline-flex;
  align-items: center;
  font-size: .72rem;
  font-weight: 700;
  border-radius: 1rem;
  padding: .22rem .65rem;
}

.soporte-fisico {
  color: var(--brand);
  background: var(--brand-soft);
}

.soporte-electronico {
  color: #7a5a00;
  background: var(--gold-soft);
}

.soporte-otro {
  color: var(--ink-soft);
  background: #f3f1ed;
}

.card-consecutivo {
  font-size: .75rem;
  font-weight: 700;
  color: var(--ink-soft);
}

.card-title {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.3;
  color: var(--brand-dark);
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  overflow: hidden;
  min-height: calc(1.05rem * 1.3 * 2);
}

.card-code {
  margin: 0;
  font-size: .78rem;
  font-weight: 600;
  color: var(--brand);
  word-break: break-word;
}

.card-desc {
  margin: 0;
  font-size: .82rem;
  color: var(--ink-soft);
  line-height: 1.4;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  overflow: hidden;
  min-height: calc(0.82rem * 1.4 * 2);
}

.card-meta {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .55rem .75rem;
  margin: .15rem 0 0;
}

.card-meta dt {
  font-size: .68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .03em;
  color: var(--ink-soft);
  margin: 0 0 .1rem;
}

.card-meta dd {
  margin: 0;
  font-size: .82rem;
  font-weight: 600;
  color: var(--ink);
  overflow-wrap: break-word;
}

.card-more {
  border-top: 1px dashed var(--line);
  padding-top: .55rem;
}

.card-more summary {
  cursor: pointer;
  font-size: .78rem;
  font-weight: 700;
  color: var(--brand);
  list-style: none;
}

.card-more summary::-webkit-details-marker { display: none; }

.card-extra {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .45rem .7rem;
  margin: .65rem 0 0;
}

.card-extra dt {
  font-size: .66rem;
  font-weight: 700;
  color: var(--ink-soft);
  text-transform: uppercase;
  letter-spacing: .02em;
  margin: 0;
}

.card-extra dd {
  margin: .1rem 0 0;
  font-size: .8rem;
  color: var(--ink);
  overflow-wrap: break-word;
}

.extra-full { grid-column: 1 / -1; }

.card-actions {
  display: flex;
  flex-wrap: wrap;
  gap: .45rem;
  margin-top: auto;
  padding-top: .55rem;
  border-top: 1px solid var(--line);
}

.btn-action {
  border: none;
  border-radius: .55rem;
  font-size: .78rem;
  font-weight: 700;
  padding: .45rem .75rem;
  cursor: pointer;
  transition: transform .12s, filter .15s;
}

.btn-action:hover {
  transform: translateY(-1px);
  filter: brightness(1.05);
}

.btn-pdf {
  background: linear-gradient(135deg, var(--brand), var(--brand-dark));
  color: #fff;
}

.btn-edit {
  background: var(--gold-soft);
  color: var(--brand-dark);
  border: 1px solid color-mix(in srgb, var(--gold) 40%, transparent);
}

.btn-delete {
  background: #fff;
  color: var(--brand);
  border: 1px solid color-mix(in srgb, var(--brand) 35%, transparent);
}

.empty {
  text-align: center;
  padding: 3.5rem 1rem;
}

.empty-title {
  font-weight: 700;
  margin: 0 0 .3rem;
  color: var(--brand-dark);
}

.empty-sub {
  margin: 0;
  color: var(--ink-soft);
  font-size: .88rem;
}

.pager {
  margin-top: 2rem;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
}

.pager-info {
  font-size: .82rem;
  color: var(--ink-soft);
}

.pager-btn {
  border: 1px solid var(--line);
  background: var(--surface);
  color: var(--ink);
  font-size: .82rem;
  font-weight: 600;
  padding: .45rem .85rem;
  border-radius: .55rem;
  cursor: pointer;
}

.pager-btn:hover:not(:disabled) {
  border-color: var(--brand);
  color: var(--brand);
}

.pager-btn:disabled {
  opacity: .45;
  cursor: not-allowed;
}

/* Modal */
.cons-modal :deep(.modal-content) {
  border: 1px solid var(--line);
  border-radius: .95rem;
  box-shadow: 0 12px 32px rgba(20, 10, 15, .18);
}

.cons-modal :deep(.modal-header) {
  border-bottom: 1px solid var(--line);
  padding: 1rem 1.25rem;
}

.cons-modal :deep(.modal-title) {
  font-weight: 700;
  color: var(--brand-dark);
  width: auto;
  text-align: left;
}

.cons-modal :deep(.modal-body) {
  padding: 1.25rem;
}

.edit-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .9rem 1rem;
}

.field label,
.field-label {
  display: block;
  font-size: .78rem;
  font-weight: 600;
  color: var(--ink-soft);
  margin-bottom: .3rem;
}

.field-span-2 { grid-column: 1 / -1; }

.edit-form :deep(.form-control) {
  border: 1px solid var(--line);
  border-radius: .55rem;
  font-size: .88rem;
  padding: .5rem .7rem;
  color: var(--ink);
}

.edit-form :deep(.form-control:focus) {
  border-color: var(--brand);
  box-shadow: 0 0 0 .2rem color-mix(in srgb, var(--brand) 18%, transparent);
}

.edit-form :deep(.form-control:disabled) {
  background: #f7f5f2;
  color: var(--ink-soft);
}

.checks {
  display: flex;
  flex-wrap: wrap;
  gap: .85rem;
}

.check {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  font-size: .88rem;
  color: var(--ink);
  cursor: pointer;
}

.check input {
  accent-color: var(--brand);
}

.edit-footer {
  margin-top: 1.25rem;
  display: flex;
  justify-content: flex-end;
}

.btn-save {
  border: none;
  background: linear-gradient(135deg, var(--brand), var(--brand-dark));
  color: #fff;
  font-weight: 700;
  font-size: .9rem;
  padding: .7rem 1.2rem;
  border-radius: .6rem;
  cursor: pointer;
  box-shadow: 0 8px 18px rgba(171, 10, 61, .22);
}

.btn-save:hover { filter: brightness(1.05); }

@media (max-width: 720px) {
  .edit-grid,
  .card-meta,
  .card-extra {
    grid-template-columns: 1fr;
  }

  .field-span-2,
  .extra-full {
    grid-column: auto;
  }

  .head-actions {
    width: 100%;
  }

  .search-box {
    min-width: 0;
    flex: 1;
  }
}
</style>
