  <template>
    <div class="container mt-4">
      <form @submit.prevent="loadStatisticsData" class="mb-4">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="startDate" class="form-label">Fecha de inicio:</label>
            <input type="date" v-model="startDate" id="startDate" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label for="endDate" class="form-label">Fecha de fin:</label>
            <input type="date" v-model="endDate" id="endDate" class="form-control" required>
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
      </form>

      <div class="chart-container" v-if="chartData && chartData.datasets && chartData.datasets.length > 0 && chartData.datasets[0].data.length > 0">
        <StatisticsChart :chartData="chartData" :chartOptions="chartOptions" />
        <div class="text-center mt-3">
          <button @click="generatePDF" class="btn btn-primary btn-lg">Generar PDF</button>
        </div>
      </div>
    </div>
  </template>

  <script>
  import { ref } from 'vue';
  import StatisticsChart from '@/components/StatisticsChart.vue';
  import axios from 'axios';
  import jsPDF from 'jspdf';
  import 'jspdf-autotable';
  import html2canvas from 'html2canvas';
  import Swal from 'sweetalert2';


  export default {
    name: 'Estadisticas',
    components: {
      StatisticsChart
    },
    setup() {
      const chartData = ref({
        labels: [],
        datasets: [
          {
            label: 'Cantidad de registros',
            backgroundColor: [],
            data: []
          }
        ]
      });
      const chartOptions = ref({
        responsive: true,
        maintainAspectRatio: false
      });
      const startDate = ref('');
      const endDate = ref('');

      const getRandomColor = () => {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
          color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
      };

      const loadStatisticsData = async () => {
        try {
          const response = await axios.get('/obtenerstats', {
            params: {
              start_date: startDate.value,
              end_date: endDate.value
            }
          });

          const actualData = JSON.parse(JSON.stringify(response.data));

          if (actualData.length === 0) {
            Swal.fire({
              title: 'No hay datos',
              text: 'No se encontraron datos para las fechas seleccionadas.',
              icon: 'info',
              confirmButtonText: 'Aceptar'
            });
            chartData.value = {}; // O puedes resetear los datos del gráfico si es necesario
            return; // Salir de la función si no hay datos
          }
          actualData.sort((a, b) => a.count - b.count);
          const backgroundColors = actualData.map(() => getRandomColor());

          chartData.value = {
            labels: actualData.map(item => item._id),
            datasets: [
              {
                label: 'Cantidad de registros',
                backgroundColor: backgroundColors,
                data: actualData.map(item => item.count)
              }
            ]
          };
        } catch (error) {
          console.error('Error al cargar los datos:', error);
        }
      };

      const formatDate = (dateStr) => {
        const months = [
          'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
          'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
        ];

        const [year, month, day] = dateStr.split('-').map(Number);
        return `${day} de ${months[month - 1]} de ${year}`;
      };

      const generatePDF = async () => {
        const pdf = new jsPDF('p', 'pt', 'a4');
        const canvas = await html2canvas(document.querySelector('.chart-container'));
        const imgData = canvas.toDataURL('image/png');

        // Añadir el logo en la esquina superior izquierda
        const logo = '/assets/logo2.png'; // Ruta del logo
        pdf.addImage(logo, 'PNG', 20, 20, 50, 70); // Ajusta las coordenadas y el tamaño según sea necesario

        // Añadir el encabezado
        pdf.setFontSize(11);
        pdf.text('Gobierno del Estado de Quintana Roo', 170, 40); // Subir el texto más cerca del logo

        pdf.setFontSize(11);
        pdf.text('SECRETARIA DE GOBIERNO', 180, 55); // Subir el texto más cerca del logo

        pdf.setFontSize(11);
        pdf.text('DIRECCIÓN DE ARCHIVO GENERAL DEL ESTADO', 130, 70); // Subir el texto más cerca del logo

        pdf.setFontSize(11);
        pdf.text('DEPARTAMENTO DE ARCHIVO HISTORICO', 150, 85); // Subir el texto más cerca del logo

        // Definir y formatear la fecha
        const date = new Date();
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        const formattedDate = date.toLocaleDateString('es-ES', options);
        pdf.setFontSize(12);
        pdf.text(`Chetumal Qroo a ${formattedDate}`, 170, 100); // Subir la fecha más cerca del logo

        // Añadir el logo en la esquina superior derecha
        const logo1 = '/assets/logo1.png'; // Ruta del logo
        pdf.addImage(logo1, 'PNG', 595 - 200, 20, 200, 100); // 575 - 200 es la posición x, 20 es la posición y, 200 es el ancho y 100 es la altura

        // Usar las fechas de inicio y fin directamente
        const formattedStartDate = formatDate(startDate.value);
        const formattedEndDate = formatDate(endDate.value);

        // Añadir el texto de introducción
        pdf.setFontSize(12);
        pdf.text(`A continuación se reporta la cantidad de capturas realizadas por los distintos usuarios del`, 30, 190);
        pdf.text(`departamento de archivo histórico, en las fechas del ${formattedStartDate} al ${formattedEndDate}.`, 30, 210);

        // Añadir la tabla
        const statistics = chartData.value.labels.map((label, index) => ({
          capturador: label,
          count: chartData.value.datasets[0].data[index]
        }));

        pdf.autoTable({
          startY: 250, // Ajustar según la posición y el tamaño de la tabla
          head: [['Capturador', 'Cantidad de registros']],
          body: statistics.map(stat => [stat.capturador, stat.count]),
          theme: 'grid',
          headStyles: {
            fillColor: [0, 0, 0], // Color de fondo de la cabecera (negro)
            textColor: [255, 255, 255], // Color del texto de la cabecera (blanco)
          },
          bodyStyles: {
            fillColor: [245, 245, 245], // Color de fondo de las filas (gris claro)
          },
        });

        // Añadir la gráfica
        pdf.addImage(imgData, 'PNG', 20, 400, 560, 280); // Cambiar la posición de la gráfica para que aparezca después de la tabla

        pdf.save('estadisticas.pdf');
      };


      return {
        chartData,
        chartOptions,
        startDate,
        endDate,
        loadStatisticsData,
        generatePDF
      };
    }
  };
  </script>

  <style scoped>
  .chart-container {
    position: relative;
    width: 80%;
    height: 400px;
    margin: auto;
  }


  .btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
  }
  </style>