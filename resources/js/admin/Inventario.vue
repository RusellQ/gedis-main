<template>
  <div class="container mt-4">
    <h1>Inventario</h1>

    <!-- Formulario para filtrar por fechas -->
    <form @submit.prevent="fetchInventario" class="mb-4">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="start_date" class="form-label">Fecha Inicio:</label>
          <input type="date" v-model="startDate" id="start_date" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
          <label for="end_date" class="form-label">Fecha Fin:</label>
          <input type="date" v-model="endDate" id="end_date" class="form-control">
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Filtrar</button>
      <button @click="descargarExcel" class="btn btn-success ms-2">Descargar Excel</button>
      <button @click="generarPDF" class="btn btn-danger ms-2">Exportar PDF</button>
    </form>

    <!-- Tabla -->
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>Inventario</th>
            <th>Formato</th>
            <th>Foto Única</th>
            <th>Medidas</th>
            <th>Cantidad</th>
            <th>Color</th>
            <th>Descripción Imagen</th>
            <th>Clasificación Contenido</th>
            <th>Fondo</th>
            <th>Serie</th>
            <th>Sub Serie</th>
            <th>Sección</th>
            <th>Descripción</th>
            <th>Capturador</th>
            <th>Daño</th>
            <th>Creado</th>
            <th>Actualizado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in paginatedInventario" :key="index">
            <td>{{ item.inventario }}</td>
            <td>{{ item.formato }}</td>
            <td>{{ item.fotoUnica }}</td>
            <td>{{ item.medidas }}</td>
            <td>{{ item.cantidad }}</td>
            <td>{{ item.color }}</td>
            <td>{{ item.descripcionImagen }}</td>
            <td>{{ item.clasificacionContenido }}</td>
            <td>{{ item.fondo }}</td>
            <td>{{ item.serie }}</td>
            <td>{{ item.subSerie }}</td>
            <td>{{ item.seccion }}</td>
            <td>{{ item.descripcion }}</td>
            <td>{{ item.capturador }}</td>
            <td>{{ item.dano?.length > 0 ? 'Sí' : 'No' }}</td>
            <td>{{ item.created_at }}</td>
            <td>{{ item.updated_at }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginador -->
    <nav aria-label="Page navigation" class="mt-3">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click="changePage(currentPage - 1)">Anterior</button>
        </li>
        <li
          class="page-item"
          v-for="page in totalPages"
          :key="page"
          :class="{ active: currentPage === page }"
        >
          <button class="page-link" @click="changePage(page)">{{ page }}</button>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <button class="page-link" @click="changePage(currentPage + 1)">Siguiente</button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
import axios from 'axios';
import jsPDF from 'jspdf';
import 'jspdf-autotable';

export default {
  data() {
    return {
      inventario: [],
      startDate: '',
      endDate: '',
      currentPage: 1, // Página actual
      itemsPerPage: 10 // Elementos por página
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.inventario.length / this.itemsPerPage);
    },
    paginatedInventario() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.inventario.slice(start, end);
    }
  },
  methods: {
    async fetchInventario() {
      try {
        const params = {};
        if (this.startDate) params.start_date = this.startDate;
        if (this.endDate) params.end_date = this.endDate;

        const response = await axios.get('/obtenerinventario', { params });
        this.inventario = response.data;
        this.currentPage = 1; // Resetear a la primera página al filtrar
      } catch (error) {
        console.error('Error al cargar el inventario:', error);
      }
    },
    descargarExcel() {
      const params = new URLSearchParams();
      if (this.startDate) params.append('start_date', this.startDate);
      if (this.endDate) params.append('end_date', this.endDate);

      window.open(`/exportar-inventario?${params.toString()}`, '_blank');
    },
    generarPDF() {
      const pdf = new jsPDF('l', 'pt', 'a4');

      // Agregar logos y encabezados
      pdf.addImage('/assets/logo1.png', 'PNG', 20, 20, 100, 70);
      pdf.addImage('/assets/logo2.png', 'PNG', 750, 20, 50, 70);

      pdf.setFontSize(11);
      pdf.text('Gobierno del Estado de Quintana Roo', 250, 40);
      pdf.text('SECRETARIA DE GOBIERNO', 270, 55);
      pdf.text('DIRECCIÓN DE ARCHIVO GENERAL DEL ESTADO', 230, 70);
      pdf.text('DEPARTAMENTO DE ARCHIVO HISTORICO', 260, 85);

      pdf.setFontSize(12);

      const body = this.inventario.map(item => [
        item.inventario || 'N/A',
        item.formato || 'N/A',
        item.fotoUnica || 'N/A',
        item.medidas || 'N/A',
        item.cantidad || 'N/A',
        item.color || 'N/A',
        item.descripcionImagen || 'N/A',
        item.clasificacionContenido || 'N/A',
        item.fondo || 'N/A',
        item.serie || 'N/A',
        item.subSerie || 'N/A',
        item.seccion || 'N/A',
        item.descripcion || 'N/A',
        item.capturador || 'N/A',
        item.dano?.length > 0 ? 'Sí' : 'No',
        `${item.created_at?.split('T')[0] || 'N/A'} / ${item.updated_at?.split('T')[0] || 'N/A'}`,
      ]);

      pdf.autoTable({
        startY: 150,
        head: [
          [
            'Inventario',
            'Formato',
            'Foto Única',
            'Medidas',
            'Cantidad',
            'Color',
            'Descripción Imagen',
            'Clasificación Contenido',
            'Fondo',
            'Serie',
            'Sub Serie',
            'Sección',
            'Descripción',
            'Capturador',
            'Daño',
          ],
        ],
        body,
        theme: 'grid',
        styles: {
          fontSize: 7,
          cellPadding: 2,
          overflow: 'linebreak',
        },
        columnStyles: {
          0: { cellWidth: 60 },
          1: { cellWidth: 50 },
          2: { cellWidth: 50 },
          3: { cellWidth: 50 },
          4: { cellWidth: 40 },
          5: { cellWidth: 40 },
          6: { cellWidth: 70 },
          7: { cellWidth: 70 },
          8: { cellWidth: 50 },
          9: { cellWidth: 50 },
          10: { cellWidth: 50 },
          11: { cellWidth: 50 },
          12: { cellWidth: 70 },
          13: { cellWidth: 50 },
          14: { cellWidth: 40 },
        },
        didDrawPage: (data) => {
          const pageCount = pdf.internal.getNumberOfPages();
          pdf.setFontSize(10);
          pdf.text(
            `Página ${data.pageNumber} de ${pageCount}`,
            pdf.internal.pageSize.width / 2,
            pdf.internal.pageSize.height - 10,
            { align: 'center' }
          );
        },
      });

      pdf.save('inventario.pdf');
    },
    changePage(page) {
      if (page > 0 && page <= this.totalPages) {
        this.currentPage = page;
      }
    }
  },
  mounted() {
    this.fetchInventario();
  }
};
</script>

<style>
.table {
  font-size: 0.9rem;
}

.table-responsive {
  overflow-x: auto;
}

.pagination .page-item.active .page-link {
  background-color: #007bff;
  border-color: #007bff;
}

.pagination .page-item.disabled .page-link {
  cursor: not-allowed;
  pointer-events: none;
}
</style>
