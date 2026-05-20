<template>
  <div class="catalogo-page">
    <div class="catalogo-controls no-print">
      <div class="catalogo-controls-inner">
        <div class="control-group">
          <label>Modo de Visualización</label>
          <select v-model="selectedFondo">
            <option value="all">DESCARGA GENERAL (Continuo por Secciones)</option>
            <option v-for="fondo in fondos" :key="fondo.id" :value="fondo.id">
              FONDO: {{ fondo.nombre }}
            </option>
          </select>
        </div>
        <div class="control-actions">
          <button @click="descargarPDF" class="primary-button">
            🖨️ Imprimir Reporte
          </button>
        </div>
      </div>
    </div>

    <div class="catalogo-print-container">
      <div class="catalogo-header">
        <div class="catalogo-logo">
          <img src="/assets/logo1.png" alt="Escudo" />
        </div>
        <div class="catalogo-title">
          <h1>Catálogo de Disposición Documental (CADIDO)</h1>
          <h2>SISTEMA ESTATAL DE ARCHIVOS</h2>
          <div class="catalogo-subtitle">
            <span v-if="selectedFondo !== 'all'">FONDO: {{ currentFondoName }}</span>
            <span v-else>REPORTE INTEGRAL MULTI-FONDO</span>
          </div>
        </div>
        <div class="catalogo-logo catalogo-logo-right">
          <img src="/assets/logo2.png" alt="Logo" />
        </div>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th rowspan="3">Código</th>
              <th rowspan="3">Niveles de clasificación</th>
              <th rowspan="3">Nombre Serie / Subserie</th>
              <th colspan="6">Vigencia Documental</th>
              <th colspan="3">Técnicas de Selección</th>
              <th rowspan="3">Observaciones</th>
              <th colspan="2">Clasificación</th>
            </tr>
            <tr>
              <th colspan="3">Valor Documental</th>
              <th colspan="3">Plazos Conservación</th>
              <th rowspan="2"><div class="header-vertical">Eliminación</div></th>
              <th rowspan="2"><div class="header-vertical">Conservación</div></th>
              <th rowspan="2"><div class="header-vertical">Muestreo</div></th>
              <th rowspan="2">R</th>
              <th rowspan="2">C</th>
            </tr>
            <tr>
              <th>AD</th>
              <th>LE</th>
              <th>COR</th>
              <th>AT</th>
              <th>AC</th>
              <th>TA</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="fondo in filteredFondos" :key="fondo.id">
              <tr v-if="selectedFondo === 'all'" class="fondo-row">
                <td colspan="15">FONDO: {{ fondo.nombre }}</td>
              </tr>

              <template v-for="seccion in fondo.secciones" :key="seccion.id">
                <tr class="seccion-row">
                  <td colspan="15">Sección: {{ seccion.codigo }} - {{ seccion.nombre }}</td>
                </tr>
                <template v-for="serie in seccion.series" :key="serie.id">
                  <tr class="serie-row">
                    <td class="cell-center">{{ serie.codigo }}</td>
                    <td class="cell-center">SERIE</td>
                    <td class="cell-label">{{ serie.nombre }}</td>
                    <td class="cell-center">{{ serie.vigencia_ad ? 'X' : '' }}</td>
                    <td class="cell-center">{{ serie.vigencia_le ? 'X' : '' }}</td>
                    <td class="cell-center">{{ serie.vigencia_confi ? 'X' : '' }}</td>
                    <td class="cell-center">{{ serie.plazo_at ?? '-' }}</td>
                    <td class="cell-center">{{ serie.plazo_ac ?? '-' }}</td>
                    <td class="cell-center">{{ serie.plazo_ta ?? '-' }}</td>
                    <td class="cell-center">{{ serie.tecnica_eliminacion ? 'X' : '' }}</td>
                    <td class="cell-center">{{ serie.tecnica_conservacion ? 'X' : '' }}</td>
                    <td class="cell-center">{{ serie.tecnica_muestreo ? 'X' : '' }}</td>
                    <td class="cell-text">{{ serie.observaciones || '-' }}</td>
                    <td class="cell-center">{{ serie.clasificacion_r ? 'X' : '' }}</td>
                    <td class="cell-center">{{ serie.clasificacion_c ? 'X' : '' }}</td>
                  </tr>
                  <tr v-for="subserie in serie.subseries" :key="subserie.id" class="subserie-row">
                    <td class="cell-center">{{ subserie.codigo }}</td>
                    <td class="cell-center">SUBSERIE</td>
                    <td class="cell-label">{{ subserie.nombre }}</td>
                    <td class="cell-center">-</td>
                    <td class="cell-center">-</td>
                    <td class="cell-center">-</td>
                    <td class="cell-center">-</td>
                    <td class="cell-center">-</td>
                    <td class="cell-center">-</td>
                    <td class="cell-center">-</td>
                    <td class="cell-center">-</td>
                    <td class="cell-center">-</td>
                    <td class="cell-text">-</td>
                    <td class="cell-center">-</td>
                    <td class="cell-center">-</td>
                  </tr>
                </template>
              </template>
            </template>
          </tbody>
        </table>
      </div>

      <div class="catalogo-footer">
        <div>Generado el: {{ currentDate }}</div>
        <div class="footer-right">Documento Oficial de Disposición Documental</div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      fondos: [],
      selectedFondo: 'all',
      loading: false,
      error: null,
    };
  },
  computed: {
    filteredFondos() {
      if (this.selectedFondo === 'all') {
        return this.fondos;
      }
      return this.fondos.filter((fondo) => fondo.id === Number(this.selectedFondo));
    },
    currentFondoName() {
      const found = this.fondos.find((fondo) => fondo.id === Number(this.selectedFondo));
      return found ? found.nombre : '';
    },
    currentDate() {
      return new Date().toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      });
    },
  },
  methods: {
    async fetchData() {
      this.loading = true;
      try {
        const response = await axios.get('/catalogosecciones/data');
        this.fondos = response.data;
      } catch (error) {
        this.error = 'No fue posible cargar la información. Intenta nuevamente.';
      } finally {
        this.loading = false;
      }
    },
    descargarPDF() {
      const form = document.createElement('form');
      form.method = 'GET';
      form.action = '/catalogosecciones/pdf';
      form.target = '_blank';
      document.body.appendChild(form);
      form.submit();
      form.remove();
    },
  },
  mounted() {
    this.fetchData();
  },
};
</script>

<style scoped>
.catalogo-page {
  background: #f1f5f9;
  min-height: 100vh;
  padding: 24px;
}
.catalogo-controls {
  max-width: 1200px;
  margin: 0 auto 24px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 24px;
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
}
.catalogo-controls-inner {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  justify-content: space-between;
  align-items: flex-end;
  padding: 24px;
}
.control-group {
  flex: 1;
  min-width: 280px;
}
.control-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 11px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #64748b;
}
.control-group select {
  width: 100%;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  padding: 12px 14px;
  background: #f8fafc;
  font-size: 14px;
  color: #0f172a;
}
.control-actions {
  display: flex;
  align-items: center;
}
.primary-button {
  border: none;
  color: white;
  background: #0f172a;
  padding: 12px 22px;
  border-radius: 14px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 16px 32px rgba(15, 23, 42, 0.16);
}
.primary-button:hover {
  background: #000;
}
.catalogo-print-container {
  max-width: 1200px;
  margin: 0 auto;
  background: white;
  border-radius: 24px;
  padding: 32px;
  box-shadow: 0 30px 60px rgba(15, 23, 42, 0.12);
}
.catalogo-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 24px;
  margin-bottom: 32px;
}
.catalogo-logo,
.catalogo-logo-right {
  width: 100px;
  min-width: 100px;
}
.catalogo-logo img,
.catalogo-logo-right img {
  width: 100%;
  height: auto;
  object-fit: contain;
}
.catalogo-title {
  flex: 1;
  text-align: center;
}
.catalogo-title h1 {
  font-size: 20px;
  margin: 0 0 8px;
  letter-spacing: -0.03em;
}
.catalogo-title h2 {
  margin: 0;
  font-size: 13px;
  color: #475569;
}
.catalogo-subtitle {
  display: inline-flex;
  margin-top: 16px;
  padding: 10px 16px;
  border: 1px solid #cbd5e1;
  background: #f8fafc;
  border-radius: 9999px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: #0f172a;
}
.table-wrap {
  overflow-x: auto;
}
table {
  width: 100%;
  border-collapse: collapse;
  font-size: 10px;
  color: #0f172a;
}
th,
td {
  border: 1px solid #475569;
  padding: 8px 6px;
  vertical-align: middle;
}
th {
  background: #f8fafc;
  font-weight: 700;
}
.header-vertical {
  writing-mode: vertical-lr;
  transform: rotate(180deg);
  text-align: center;
  font-size: 8px;
  padding: 4px 2px;
}
.fondo-row td,
.seccion-row td {
  background: #e2e8f0;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.series-row td,
.subserie-row td {
  background: white;
}
.cell-center {
  text-align: center;
}
.cell-label {
  padding-left: 12px;
  font-weight: 600;
  text-transform: uppercase;
}
.cell-text {
  font-size: 9px;
  color: #475569;
}
.catalogo-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 28px;
  padding-top: 18px;
  border-top: 1px solid #cbd5e1;
  font-size: 10px;
  color: #64748b;
  text-transform: uppercase;
}
.footer-right {
  text-align: right;
}
@media print {
  body, .catalogo-page {
    background: white !important;
    padding: 0 !important;
  }
  .no-print {
    display: none !important;
  }
  .catalogo-print-container {
    box-shadow: none !important;
    padding: 10px !important;
  }
  table {
    font-size: 8px !important;
  }
  th {
    background: #f8fafc !important;
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
  }
}
</style>
