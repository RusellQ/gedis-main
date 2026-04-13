<template>
  <div>
    <h1>Consultar Documentos</h1>


    <div class="table-responsive">
      <!-- Formulario de Búsqueda -->
      <form class="d-flex mb-4" @submit.prevent="buscar">
        <input v-model="palabraClave" class="form-control me-2" type="search" placeholder="Buscar" aria-label="Buscar">
      </form>

      <table class="table table-minimal">
        <thead >
          <tr>
            <th style="min-width: 100px;">Inventario</th>
            <th style="min-width: 100px;">Formato</th>
            <th style="min-width: 100px;">Foto Única</th>
            <th style="min-width: 100px;">Medidas</th>
            <th style="min-width: 100px;">Cantidad</th>
            <th style="min-width: 100px;">Color</th>
            <th style="min-width: 150px;">Descripción Imagen</th>
            <th style="min-width: 200px;">Clasificación Contenido</th>
            <th style="min-width: 100px;">Fondo</th>
            <th style="min-width: 100px;">Serie</th>
            <th style="min-width: 100px;">Sub Serie</th>
            <th style="min-width: 100px;">Sección</th>
            <th style="min-width: 200px;">Descripción</th>
            <th style="min-width: 100px;">Capturador</th>
            <th style="min-width: 150px;">Daño</th>
            <th style="min-width: 100px;">Archivos</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="documentosPaginados.length === 0">
            <td colspan="18">
              <div class="no-data-message">
                Sin datos
              </div>
            </td>
          </tr>
          <tr v-for="(documento, index) in documentosPaginados" :key="index">
            <td>{{ documento.inventario }}</td>
            <td>{{ documento.formato }}</td>
            <td>{{ documento.fotoUnica }}</td>
            <td>{{ documento.medidas }}</td>
            <td>{{ documento.cantidad }}</td>
            <td>{{ documento.color }}</td>
            <td>{{ documento.descripcionImagen }}</td>
            <td>{{ documento.clasificacionContenido }}</td>
            <td>{{ documento.fondo }}</td>
            <td>{{ documento.serie }}</td>
            <td>{{ documento.subSerie }}</td>
            <td>{{ documento.seccion }}</td>
            <td>{{ documento.descripcion }}</td>
            <td>{{ documento.capturador }}</td>
            <td>{{ documento.dano.join(', ') }}</td>
            <td>
              <a v-if="documento.archivos" @click.prevent="openFancybox(documento.archivos)" href="#">
                <i class="fas fa-external-link-alt"></i>
              </a>
            </td>
            
          </tr>
        </tbody>
      </table>

       <!-- Paginador -->
      <nav aria-label="Page navigation">
        <ul class="pagination">
          <li class="page-item" :class="{ disabled: paginaActual === 1 }">
            <a class="page-link" href="#" @click.prevent="cambiarPagina(paginaActual - 1)">Anterior</a>
          </li>
          <li class="page-item" v-for="pagina in totalPaginas" :key="pagina" :class="{ active: pagina === paginaActual }">
            <a class="page-link" href="#" @click.prevent="cambiarPagina(pagina)">{{ pagina }}</a>
          </li>
          <li class="page-item" :class="{ disabled: paginaActual === totalPaginas }">
            <a class="page-link" href="#" @click.prevent="cambiarPagina(paginaActual + 1)">Siguiente</a>
          </li>
        </ul>
      </nav>
    </div>


    <!-- Modal de Edición -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel">Editar Documento</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="updateDocumento">
              <div class="form-group">
                <label for="editInventario">No. Inventario</label>
                <input type="text" id="editInventario" v-model="editDocumento.inventario" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="editFotoUnica">Foto Única</label>
                <select id="editFotoUnica" v-model="editDocumento.fotoUnica" class="form-control" required>
                  <option value="Si">Si</option>
                  <option value="No">No</option>
                </select>
              </div>

              <div class="form-group">
                <label for="editMedidas">Medidas</label>
                <input type="text" id="editMedidas" v-model="editDocumento.medidas" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="editCantidad">Cantidad</label>
                <input type="number" id="editCantidad" v-model="editDocumento.cantidad" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="editColor">Color</label>
                <select id="editColor" v-model="editDocumento.color" class="form-control" required>
                  <option value="BN">Blanco y Negro</option>
                  <option value="C">Color</option>
                </select>
              </div>

              <div class="form-group">
                <label for="editDescripcionImagen">Descripción de la Imagen</label>
                <textarea id="editDescripcionImagen" v-model="editDocumento.descripcionImagen" class="form-control"
                  rows="3" required></textarea>
              </div>

              <div class="form-group">
                <label for="editFormato">Formato</label>
                <select id="editFormato" v-model="editDocumento.formato" class="form-control" required>
                  <option value="Si">Gran formato</option>
                  <option value="No">Estandar</option>
                </select>
              </div>

              <div class="form-group">
                <label for="editClasificacionContenido">Tema / Clasificación del Contenido</label>
                <input type="text" id="editClasificacionContenido" v-model="editDocumento.clasificacionContenido"
                  class="form-control" required>
              </div>

              <div class="form-group">
                <label for="editFondo">Fondo</label>
                <input type="text" id="editFondo" v-model="editDocumento.fondo" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="editSerie">Serie</label>
                <input type="text" id="editSerie" v-model="editDocumento.serie" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="editSubSerie">Sub Serie</label>
                <input type="text" id="editSubSerie" v-model="editDocumento.subSerie" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="editSeccion">Sección</label>
                <input type="text" id="editSeccion" v-model="editDocumento.seccion" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="editDescripcion">Descripción</label>
                <textarea id="editDescripcion" v-model="editDocumento.descripcion" class="form-control" rows="3"
                  required></textarea>
              </div>

              <div class="form-group">
                <label>Daño</label>
                <div class="d-flex flex-wrap">
                  <div v-for="(dano, index) in danos" :key="index" class="custom-control custom-checkbox mr-3 mb-2">
                    <input type="checkbox" class="custom-control-input" :id="'editDano_' + index"
                      v-model="editDocumento.dano" :value="dano">
                    <label class="custom-control-label" :for="'editDano_' + index">{{ dano }}</label>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="editArchivos">Recurso digital</label><br>
                <input type="file" id="editArchivos" ref="editArchivos" multiple @change="handleFileUpload">
              </div>

              <div class="form-group">
                <label for="editCapturador">Capturador</label>
                <input type="text" id="editCapturador" v-model="editDocumento.capturador" class="form-control" disabled>
              </div>

              <button type="submit" class="btn btn-primary">Guardar cambios</button>
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

export default {
  data() {
    return {
      documentos: [],
      paginaActual: 1,
      documentosPorPagina: 12,
      editDocumento: {
        dano: []
      },
      files: [],
      palabraClave: '',
      danos: ['Rotura', 'Suciedad', 'Cintas Adhesivas', 'Hongo', 'Comején']
    };
  },
  computed: {
    documentosFiltrados() {
      if (this.palabraClave) {
        return this.documentos.filter((documento) => {
          return Object.values(documento).some((value) =>
            String(value).toLowerCase().includes(this.palabraClave.toLowerCase())
          );
        });
      }
      return this.documentos;
    },
    totalPaginas() {
      return Math.ceil(this.documentosFiltrados.length / this.documentosPorPagina);
    },
    documentosPaginados() {
      const inicio = (this.paginaActual - 1) * this.documentosPorPagina;
      const fin = inicio + this.documentosPorPagina;
      return this.documentosFiltrados.slice(inicio, fin);
    },
  },
  mounted() {
    this.getAllDocuments();
    this.getCurrentUser();
    Fancybox.bind("[data-fancybox]", {
      // Opciones adicionales de FancyBox si es necesario
    });
  },
  methods: {
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
    async getCurrentUser() {
      try {
        const response = await axios.get('/currentuser');
        this.currentUser = response.data;
      } catch (error) {
        console.error('Error al obtener usuario actual:', error);
      }
    },
   
    handleFileUpload(event) {
      this.files = Array.from(event.target.files);
    },
    openFancybox(archivos) {
      const baseURL = `${window.location.protocol}//${window.location.hostname}:8000`;

      const items = archivos.map((archivo) => ({
        src: `${baseURL}/storage/${encodeURIComponent(archivo)}`,
        opts: {
          caption: archivo
        }
      }));

      Fancybox.show(items);
    }
  }
};
</script>

<style scoped>
.modal-dialog {
  max-width: 60%;
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
