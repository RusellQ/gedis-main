import './bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import Chart from 'chart.js/auto';

import { createApp } from 'vue';



const app = createApp({});

import Dashboard from './admin/Dashboard.vue';
app.component('dashboard-vue', Dashboard);

import Capturar from './admin/Capturar.vue';
app.component('capturar-vue', Capturar);

import Createuser from './admin/Createuser.vue';
app.component('createuser-vue', Createuser);

import Listuser from './admin/Listuser.vue';
app.component('listuser-vue', Listuser);

import Consultar from './admin/Consultar.vue';
app.component('consultar-vue', Consultar);


import Estadisticas from './admin/Estadisticas.vue';
app.component('estadisticas-vue', Estadisticas);

import Buscar from './admin/Buscar.vue';
app.component('buscar-vue', Buscar);

import Expedientes from './admin/Expedientes.vue';
app.component('expedientes-vue', Expedientes);

import Inventario from './admin/Inventario.vue';
app.component('inventario-vue', Inventario);

import Fondos from './admin/Fondos.vue';
app.component('fondos-vue', Fondos);

import Historico from './admin/Historico.vue';
app.component('historico-vue', Historico);

import Tramite from './admin/Tramite.vue';
app.component('tramite-vue', Tramite);

import Concentracion from './admin/Concentracion.vue';
app.component('concentracion-vue', Concentracion);

import Reportes from './admin/Reportes.vue';
app.component('reportes-vue', Reportes);


import Individual from './admin/Individual.vue';
app.component('individual-vue', Individual);

import Bajas from './admin/Bajas.vue';
app.component('bajas-vue', Bajas);

import Cuadro from './admin/Cuadro.vue';
app.component('cuadro-vue', Cuadro);

import Catalogodisposicion from './admin/Catalogodisposicion.vue';
app.component('catalogodisposicion-vue', Catalogodisposicion);

import Departamentos from './admin/Departamentos.vue';
app.component('departamentos-vue', Departamentos);

import Dependencias from './admin/Dependencias.vue';
app.component('dependencias-vue', Dependencias);


import Indexfondos from './admin/Indexfondos.vue';
app.component('indexfondos-vue', Indexfondos);




app.mount('#app');
app.config.globalProperties.$Chart = Chart;

