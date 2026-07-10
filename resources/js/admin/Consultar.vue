<template>
  <div>
    <h1>Consultar Documentos</h1>

    <!-- Formulario de Búsqueda -->
    <form class="d-flex mb-4" @submit.prevent="buscar">
      <input v-model="palabraClave" class="form-control me-2" type="search" placeholder="Buscar" aria-label="Buscar">
    </form>

    <div class="mb-3 d-flex justify-content-end">
      <button @click="descargarCaratulaEnBlanco" class="btn btn-success">
        <i class="fas fa-file-invoice me-1"></i>
        Descargar carátula en blanco
      </button>
    </div>

    <!-- Tabla de Resultados -->
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Nombre del Sujeto Obligado </th>
            <th>Área Administrativa</th>
            <th>No. Consecutivo</th>
            <th>Área Generadora</th>
            <th>Código de Clasificación Archivística</th>
            <th>Año Apertura</th>
            <th>Año Cierre</th>
            <th>Soporte Documental</th>
            <th>Título del Expediente</th>
            <th>Descripción del Asunto</th>
            <th>Ubicación Topográfica</th>
            <th>No. de Legajo</th>
            <th>No. de Fojas</th>
            <th>Valor Documental</th>
            <th>Vigencia Documental</th>
            <th>Años en Archivo de Trámite</th>
            <th>Años en Archivo de Concentración</th>
            <th>Carácter de la Información</th>
            <th>Fecha de Clasificación</th>
            <th>Fundamento Legal</th>
            <th>Periodo de Reserva</th>
            <th>Aplicación del Periodo de Reserva</th>
            <th>Rubrica del Titular</th>
            <th>Fecha de Desclasificación</th>
            <th>Observaciones</th>
            <th>Capturador</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="documentos.length === 0">
            <td colspan="26">
              <div class="no-data-message">
                Sin datos
              </div>
            </td>
          </tr>
          <tr v-for="(documento, index) in documentos" :key="documento._id">
            <td>{{ documento.nombre_sujeto_obligado }}</td>
            <td>{{ documento.area_administrativa }}</td>
            <td>{{ documento.no_consecutivo }}</td>
            <td>{{ documento.area_generadora }}</td>
            <td>{{ documento.codigo_clasificacion_archivistica }}</td>
            <td>{{ documento.anio_apertura }}</td>
            <td>{{ documento.anio_cierre }}</td>
            <td>{{ documento.soporte_documental }}</td>
            <td>{{ documento.titulo_expediente }}</td>
            <td>{{ documento.descripcion_asunto_expediente }}</td>
            <td>{{ documento.ubicacion_topografica }}</td>
            <td>{{ documento.no_legajo }}</td>
            <td>{{ documento.no_fojas }}</td>
            <td>{{ documento.valor_documental || formatearValorDocumentalTabla(documento) }}</td>

            <td>{{ documento.vigencia_documental }}</td>
            <td>{{ documento.anios_archivo_tramite }}</td>
            <td>{{ documento.anios_archivo_concentracion }}</td>
            <td>{{ documento.caracter_informacion }}</td>
            <td>{{ documento.fecha_clasificacion }}</td>
            <td>{{ documento.fundamento_legal }}</td>
            <td>{{ documento.periodo_reserva }}</td>
            <td>{{ documento.aplicacion_periodo_reserva }}</td>
            <td>{{ documento.rubrica_titular }}</td>
            <td>{{ documento.fecha_desclasificacion }}</td>
            <td>{{ documento.observaciones }}</td>
            <td>{{ documento.capturador }}</td>
            <td>
              <button @click="generarPdf(documento)" class="btn btn-sm"
                style="background-color: #b61917; color: white; margin-right: 5px;"
                title="Descargar carátula con datos">
                <i class="fas fa-file-pdf"></i>
              </button>

              <button v-if="!currentUserRoles.includes('lector')" @click="editarDocumento(documento)" class="btn btn-sm"
                style="background-color: #2076ce; color: white; margin-right: 5px;">
                <i class="fas fa-edit" style="color: #ffffff;"></i>
              </button>

              <button v-if="!currentUserRoles.includes('lector')" @click="eliminarDocumento(documento)"
                class="btn btn-sm" style="background-color: #ff345c; color: white;">
                <i class="fas fa-trash-alt" style="color: #ffffff;"></i>
              </button>

            </td>

          </tr>
        </tbody>
      </table>

      <!-- Paginación -->
      <div v-if="totalPaginas > 1" class="pagination-container">
        <button v-for="page in totalPaginas" :key="page" @click="cambiarPagina(page)"
          :class="['page-button', { active: page === paginaActual }]">
          {{ page }}
        </button>
      </div>
    </div>

    <!-- Modal de Edición -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel">Editar Caratula de Expediente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="updateDocumento">
              <div class="mb-3">
                <label for="nombre_sujeto_obligado" class="form-label">Nombre del Sujeto Obligado</label>
                <input v-model="editDocumento.nombre_sujeto_obligado" type="text" class="form-control"
                  id="nombre_sujeto_obligado" required disabled>
              </div>

              <div class="mb-3">
                <label for="area_administrativa" class="form-label">Área Administrativa</label>
                <input v-model="editDocumento.area_administrativa" type="text" class="form-control"
                  id="area_administrativa" required disabled>
              </div>

              <div class="mb-3">
                <label for="no_consecutivo" class="form-label">No. Consecutivo</label>
                <input v-model="editDocumento.no_consecutivo" type="text" class="form-control" id="no_consecutivo"
                  required>
              </div>

              <div class="mb-3">
                <label for="area_generadora" class="form-label">Área Generadora</label>
                <input v-model="editDocumento.area_generadora" type="text" class="form-control" id="area_generadora"
                  required disabled>
              </div>

              <div class="mb-3">
                <label for="codigo_clasificacion_archivistica" class="form-label">Código de Clasificación
                  Archivística</label>
                <input v-model="editDocumento.codigo_clasificacion_archivistica" type="text" class="form-control"
                  id="codigo_clasificacion_archivistica" required>
              </div>

              <div class="mb-3">
                <label for="anio_apertura" class="form-label">Año Apertura</label>
                <input v-model="editDocumento.anio_apertura" type="number" class="form-control" id="anio_apertura"
                  required>
              </div>

              <div class="mb-3">
                <label for="anio_cierre" class="form-label">Año Cierre</label>
                <input v-model="editDocumento.anio_cierre" type="number" class="form-control" id="anio_cierre" required>
              </div>

              <div class="mb-3">
                <label for="soporte_documental" class="form-label">Soporte Documental</label>
                <input v-model="editDocumento.soporte_documental" type="text" class="form-control"
                  id="soporte_documental" required>
              </div>

              <div class="mb-3">
                <label for="titulo_expediente" class="form-label">Título del Expediente</label>
                <input v-model="editDocumento.titulo_expediente" type="text" class="form-control" id="titulo_expediente"
                  required>
              </div>

              <div class="mb-3">
                <label for="descripcion_asunto_expediente" class="form-label">Descripción del Asunto</label>
                <input v-model="editDocumento.descripcion_asunto_expediente" type="text" class="form-control"
                  id="descripcion_asunto_expediente" required>
              </div>

              <div class="mb-3">
                <label for="ubicacion_topografica" class="form-label">Ubicación Topográfica</label>
                <input v-model="editDocumento.ubicacion_topografica" type="text" class="form-control"
                  id="ubicacion_topografica" required disabled>
              </div>

              <div class="mb-3">
                <label for="no_legajo" class="form-label">No. de Legajo</label>
                <input v-model="editDocumento.no_legajo" type="text" class="form-control" id="no_legajo" required>
              </div>

              <div class="mb-3">
                <label for="no_fojas" class="form-label">No. de Fojas</label>
                <input v-model="editDocumento.no_fojas" type="number" class="form-control" id="no_fojas" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Valor Documental</label>
                <div class="d-flex gap-3">
                  <div class="form-check">
                    <input v-model="editDocumento.administrativo" class="form-check-input" type="checkbox"
                      id="administrativo" />
                    <label class="form-check-label" for="administrativo">Administrativo</label>
                  </div>
                  <div class="form-check">
                    <input v-model="editDocumento.legal" class="form-check-input" type="checkbox" id="legal" />
                    <label class="form-check-label" for="legal">Legal</label>
                  </div>
                  <div class="form-check">
                    <input v-model="editDocumento.contable" class="form-check-input" type="checkbox" id="contable" />
                    <label class="form-check-label" for="contable">Contable</label>
                  </div>
                </div>
              </div>



              <div class="mb-3">
                <label for="vigencia_documental" class="form-label">Vigencia Documental</label>
                <input v-model="editDocumento.vigencia_documental" type="text" class="form-control"
                  id="vigencia_documental" required>
              </div>

              <div class="mb-3">
                <label for="anios_archivo_tramite" class="form-label">Años en Archivo de Trámite</label>
                <input v-model="editDocumento.anios_archivo_tramite" type="number" class="form-control"
                  id="anios_archivo_tramite" required>
              </div>

              <div class="mb-3">
                <label for="anios_archivo_concentracion" class="form-label">Años en Archivo de Concentración</label>
                <input v-model="editDocumento.anios_archivo_concentracion" type="number" class="form-control"
                  id="anios_archivo_concentracion" required>
              </div>

              <div class="mb-3">
                <label for="caracter_informacion" class="form-label">Carácter de la Información</label>
                <input v-model="editDocumento.caracter_informacion" type="text" class="form-control"
                  id="caracter_informacion" required>
              </div>

              <div class="mb-3">
                <label for="fecha_clasificacion" class="form-label">Fecha de Clasificación</label>
                <input v-model="editDocumento.fecha_clasificacion" type="date" class="form-control"
                  id="fecha_clasificacion" required>
              </div>

              <div class="mb-3">
                <label for="fundamento_legal" class="form-label">Fundamento Legal</label>
                <input v-model="editDocumento.fundamento_legal" type="text" class="form-control" id="fundamento_legal"
                  required>
              </div>

              <div class="mb-3">
                <label for="periodo_reserva" class="form-label">Periodo de Reserva</label>
                <input v-model="editDocumento.periodo_reserva" type="text" class="form-control" id="periodo_reserva"
                  required>
              </div>

              <div class="mb-3">
                <label for="aplicacion_periodo_reserva" class="form-label">Aplicación del Periodo de Reserva</label>
                <input v-model="editDocumento.aplicacion_periodo_reserva" type="text" class="form-control"
                  id="aplicacion_periodo_reserva" required>
              </div>

              <div class="mb-3">
                <label for="rubrica_titular" class="form-label">Rubrica del Titular</label>
                <input v-model="editDocumento.rubrica_titular" type="text" class="form-control" id="rubrica_titular"
                  required>
              </div>

              <div class="mb-3">
                <label for="fecha_desclasificacion" class="form-label">Fecha de Desclasificación</label>
                <input v-model="editDocumento.fecha_desclasificacion" type="date" class="form-control"
                  id="fecha_desclasificacion" required>
              </div>

              <div class="mb-3">
                <label for="observaciones" class="form-label">Observaciones</label>
                <textarea v-model="editDocumento.observaciones" class="form-control" id="observaciones" rows="3"
                  required></textarea>
              </div>

              <button type="submit" class="btn btn-primary">Guardar Cambios</button>
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
      currentUserRoles: [], // inicial vacío, luego carga real
      documentos: [],
      paginaActual: 1,
      documentosPorPagina: 12,
      editDocumento: {
        administrativo: false,
        legal: false,
        contable: false,
        valor_documental: {}, // Inicializa como un objeto vacío
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
      return Math.ceil(this.documentosFiltrados.length / this.documentosPorPagina);
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
    Fancybox.bind("[data-fancybox]", {
      // Opciones adicionales de FancyBox si es necesario
    });
  },
  methods: {
    saveForm() {
      const valorDocumentalStr = this.valorDocumental.join(", ");
    },
    buscar() {
      this.paginaActual = 1;
      this.obtenerDocumentos();
    },
    cambiarPagina(pagina) {
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
    // Suponiendo que la respuesta tiene un array de roles en response.data.roles
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


      // Asegúrate de que los valores booleanos estén correctamente configurados
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

      // Crear el objeto valor_documental a partir de los valores booleanos
      const valorDocumental = {
        administrativo: this.editDocumento.administrativo || false,
        legal: this.editDocumento.legal || false,
        contable: this.editDocumento.contable || false,
      };

      // Asignar el objeto a editDocumento
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
          this.getAllDocuments(); // Refrescar
          $('#editModal').modal('hide'); // Cerrar modal
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
        format: "letter", // Tamaño carta
      });

      // Usar documentoId directamente, similar a la segunda función
      const idDocumento = documento.id;

      axios.get(`/documentos/${idDocumento}`)
        .then(response => {
          const documento = response.data;
          const imgData = "assets/logoletras.png"; // Inserta la imagen en base64
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

          // Posición inicial y tamaño de celda ajustado
          let startX = 10;
          let startY = 10;
          const pageWidth = 190; // Ancho total para tamaño carta
          const cellWidth = pageWidth / 2;
          const rowHeight = 8;
          const logoCellHeight = 16; // Nueva altura para la celda del LOGOTIPO

          // Configuración de la tabla
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

          // Función para dibujar una celda con color de fondo para los labels
          function drawCell(x, y, width, height, text, align = "left", bold = false, img = null, isLabel = false) {
            // Si la celda es un label, aplicar fondo gris
            if (isLabel) {
              doc.setFillColor(200, 200, 200); // Gris claro
              doc.rect(x, y, width, height, "F"); // Dibuja el fondo gris
            }

            doc.rect(x, y, width, height); // Dibuja el borde de la celda

            // Si la celda tiene imagen, se inserta
            if (img) {
              // Ajusta la imagen para que se ajuste dentro de la celda
              const imgWidth = Math.min(width - 4, 30); // Ajusta el ancho de la imagen
              const imgHeight = logoCellHeight - 4; // Ajustamos la altura de la imagen al tamaño de la celda menos los márgenes

              // Inserta la imagen ajustada a las dimensiones de la celda
              doc.addImage(img, "PNG", x + (width - imgWidth) / 2, y + (height - imgHeight) / 2, imgWidth, imgHeight);
            } else {
              // Dibuja el texto (si lo hay)
              doc.setFont("helvetica", bold ? "bold" : "normal");
              doc.setFontSize(8);
              doc.text(String(text), x + 2, y + height / 2 + 2, { align: align });
            }
          }

          // Generar tabla
          tableData.forEach((row) => {
            let currentX = startX;
            row.forEach((cell) => {
              const width = cell.colspan ? cellWidth * 2 : (cell.width || cellWidth); // Verifica si tiene un 'width' específico
              const height = (cell.label === "LOGOTIPO") ? logoCellHeight : (cell.height || rowHeight); // Ajusta la altura si es la celda del LOGOTIPO

              // Verifica si es la celda donde va la imagen
              if (cell.label === "LOGOTIPO") {
                drawCell(currentX, startY, width, logoCellHeight, "", "center", false, imgData); // Inserta la imagen aquí
              } else {
                // Para los labels, pasamos isLabel: true para que tengan fondo gris
                drawCell(currentX, startY, width, rowHeight, cell.label, "left", true, null, true); // Label con fondo gris
                drawCell(currentX, startY + rowHeight, width, rowHeight, cell.value || "", "left");
              }

              currentX += width;
            });
            startY += rowHeight * 2; // Altura para label y valor

            // Salto de página si excede el tamaño carta
            if (startY + rowHeight * 2 > 270) {
              doc.addPage();
              startY = 10;
            }
          });

          // Guardar el PDF
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
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          axios.delete(`/documentos/${idDocumento}`)  // Usa el documentoId correcto
            .then(() => {
              Swal.fire(
                'Eliminado',
                'El documento ha sido eliminado',
                'success'
              );
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
.modal-title {
  text-align: center;
  width: 100%;
  /* Ocupa todo el ancho */
}


.table {
  table-layout: auto;
  word-wrap: break-word;
}

.table th,
.table td {
  padding: 12px;
  vertical-align: middle;
  white-space: nowrap;
}

.table-responsive {
  margin: 30px 0;
  -webkit-overflow-scrolling: touch;
  overflow-x: auto;
}

.table-hover tbody tr:hover {
  background-color: #f5f5f5;
}

.table-sm th,
.table-sm td {
  padding: 8px;
}

.pagination {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

.no-data-message {
  text-align: center;
  padding: 60px 0;
  font-size: 30px;
  color: #666;
}

.button-spacing {
  margin-right: 10px;
}

@media (max-width: 768px) {
  .table thead {
    display: none;
  }

  .table tbody,
  .table tr,
  .table td {
    display: block;
    width: 100%;
  }

  .table tr {
    margin-bottom: 15px;
  }

  .table td {
    text-align: right;
    padding-left: 50%;
    position: relative;
  }

  .table td::before {
    content: attr(data-label);
    position: absolute;
    left: 0;
    width: 50%;
    padding-left: 15px;
    font-weight: bold;
    text-align: left;
  }
}
</style>
