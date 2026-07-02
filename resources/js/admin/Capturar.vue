<template>
    <div class="form-container">
        <div class="caratula-background">
            <h1>Generar Carátula de Expediente</h1>

            <form @submit.prevent="submitForm" class="caratula-form">
                <ReglasDocumentalesPanel
                    :reglas="reglasDocumentales"
                    :loading="cargandoReglas"
                    empty-message="Ingrese el código de clasificación archivística para consultar las reglas del catálogo."
                />

                <FormSectionHeader
                    title="Datos de su sesión"
                    badge="Automático"
                    badge-class="bg-info text-dark"
                    variant="session"
                    description="Información obtenida del usuario y su dependencia."
                />

                <div class="form-row">
                    <div class="form-group">
                        <label for="nombreSujetoObligado">Sujeto Obligado</label>
                        <input type="text" id="nombreSujetoObligado" v-model="form.nombre_sujeto_obligado"
                            class="form-control campo-solo-lectura" disabled>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="areaAdministrativa">Área Administrativa</label>
                        <input type="text" id="areaAdministrativa" v-model="form.area_administrativa"
                            class="form-control campo-solo-lectura" disabled>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="areaGeneradora">Área Generadora</label>
                        <input type="text" id="areaGeneradora" v-model="form.area_generadora" class="form-control campo-solo-lectura"
                            disabled>
                    </div>
                </div>

                <FormSectionHeader
                    title="Datos de la carátula a capturar"
                    badge="Captura manual"
                    description="Complete estos campos con la información específica del expediente."
                />

                <div class="form-row">
                    <div class="form-group">
                        <label for="noConsecutivo">No. Consecutivo</label>
                        <input type="number" id="noConsecutivo" v-model="form.no_consecutivo" class="form-control"
                            required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="codigoClasificacionArchivistica">Código de Clasificación Archivística</label>
                        <input type="text" id="codigoClasificacionArchivistica"
                            v-model="form.codigo_clasificacion_archivistica"
                            class="form-control"
                            @blur="cargarReglasPorClave"
                            required>
                        <small class="text-muted">Al salir del campo se consultan las reglas documentales del catálogo.</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-12">
                        <label for="fechasExtremas">Fechas Extremas</label>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="anoApertura">Año de apertura</label>
                        <input type="number" id="anoApertura" v-model="form.anio_apertura" class="form-control"
                            required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="anoCierre">Año de cierre</label>
                        <input type="number" id="anoCierre" v-model="form.anio_cierre" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="soporteDocumental">Soporte Documental</label>
                        <select id="soporteDocumental" v-model="form.soporte_documental" class="form-control" required>
                            <option value="Físico">Físico</option>
                            <option value="Electrónico">Electrónico</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tituloExpediente">Título del Expediente</label>
                        <input type="text" id="tituloExpediente" v-model="form.titulo_expediente" class="form-control"
                            required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="descripcionAsuntoExpediente">Descripción del Asunto que Trata el Expediente</label>
                        <input type="text" id="descripcionAsuntoExpediente" v-model="form.descripcion_asunto_expediente"
                            class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ubicacionTopografica">Ubicación Topográfica</label>
                        <input type="text" id="ubicacionTopografica" v-model="form.ubicacion_topografica"
                            class="form-control campo-solo-lectura" disabled>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="noLegajo">No. de Legajo</label>
                        <input type="number" id="noLegajo" v-model="form.no_legajo" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="noFojas">No. de Fojas</label>
                        <input type="number" id="noFojas" v-model="form.no_fojas" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="subtitulo">Información Reservada y/o Confidencial</label>
                        <p class="text-muted small mb-2">La clasificación, fundamento legal y periodo de reserva se muestran arriba en las reglas del catálogo.</p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="fechaClasificacion">Fecha de Clasificación</label>
                        <input type="date" id="fechaClasificacion" v-model="form.fecha_clasificacion"
                            class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="aplicacionPeriodoReserva">Aplicación del Periodo de Reserva</label>
                        <input type="text" id="aplicacionPeriodoReserva" v-model="form.aplicacion_periodo_reserva"
                            class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="rubricaTitularUnidadAdministrativa">Rubrica del Titular de la Unidad
                            Administrativa</label>
                        <input type="text" id="rubricaTitularUnidadAdministrativa" v-model="form.rubrica_titular"
                            class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="fechaDesclasificacion">Fecha de Desclasificación</label>
                        <input type="date" id="fechaDesclasificacion" v-model="form.fecha_desclasificacion"
                            class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" v-model="form.observaciones" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="capturador">Capturador</label>
                    <input type="text" id="capturador" v-model="form.capturador" class="form-control campo-solo-lectura" disabled>
                </div>

                <button type="submit" class="btn btn-primary">
                    <span>Generar Carátula</span>
                </button>
            </form>
        </div>
        <br>
    </div>
</template>

<script>
import axios from 'axios'
import FormSectionHeader from '../components/FormSectionHeader.vue'
import ReglasDocumentalesPanel from '../components/ReglasDocumentalesPanel.vue'

export default {
    components: {
        FormSectionHeader,
        ReglasDocumentalesPanel,
    },
    data() {
        return {
            reglasDocumentales: null,
            cargandoReglas: false,
            form: {
                nombre_sujeto_obligado: '',
                area_administrativa: '',
                no_consecutivo: null,
                area_generadora: '',
                codigo_clasificacion_archivistica: '',
                anio_apertura: null,
                anio_cierre: null,
                soporte_documental: 'Físico',
                titulo_expediente: '',
                descripcion_asunto_expediente: '',
                ubicacion_topografica: '',
                no_legajo: null,
                no_fojas: null,
                fecha_clasificacion: '',
                aplicacion_periodo_reserva: '',
                rubrica_titular: '',
                fecha_desclasificacion: '',
                observaciones: '',
                capturador: ''
            }
        };
    },
    methods: {
        submitForm() {
            axios.post('/documentos', { ...this.form })
                .then(() => {
                    alert('Carátula de expediente generada con éxito');
                    this.resetForm();
                })
                .catch(error => {
                    console.error('Error al generar la carátula:', error);
                    alert('Hubo un error al generar la carátula. Intenta nuevamente.');
                });
        },
        async cargarReglasPorClave() {
            const clave = this.form.codigo_clasificacion_archivistica?.trim()
            if (!clave) {
                this.reglasDocumentales = null
                return
            }
            this.cargandoReglas = true
            try {
                const response = await axios.get('/reglas-documentales/por-clave', { params: { clave } })
                this.reglasDocumentales = response.data.reglas
            } catch (error) {
                console.error('Error al cargar reglas documentales:', error)
                this.reglasDocumentales = null
            } finally {
                this.cargandoReglas = false
            }
        },
        resetForm() {
            const capturador = this.form.capturador
            const areaGeneradora = this.form.area_generadora
            const areaAdministrativa = this.form.area_administrativa
            const ubicacion = this.form.ubicacion_topografica
            const sujeto = this.form.nombre_sujeto_obligado
            const idDependencia = this.form.id_dependencia
            const idDepartamento = this.form.id_departamento

            this.form = {
                nombre_sujeto_obligado: sujeto,
                area_administrativa: areaAdministrativa,
                no_consecutivo: null,
                area_generadora: areaGeneradora,
                codigo_clasificacion_archivistica: '',
                anio_apertura: null,
                anio_cierre: null,
                soporte_documental: 'Físico',
                titulo_expediente: '',
                descripcion_asunto_expediente: '',
                ubicacion_topografica: ubicacion,
                no_legajo: null,
                no_fojas: null,
                fecha_clasificacion: '',
                aplicacion_periodo_reserva: '',
                rubrica_titular: '',
                fecha_desclasificacion: '',
                observaciones: '',
                capturador,
                id_dependencia: idDependencia,
                id_departamento: idDepartamento,
            }
            this.reglasDocumentales = null
        },
        fetchCurrentUser() {
            axios.get('/currentuser')
                .then(response => {
                    const currentUser = response.data;

                    this.form.capturador = `${currentUser.nombre} ${currentUser.apellido_paterno} ${currentUser.apellido_materno}`;
                    this.form.area_generadora = currentUser.departamento?.nombre || 'Sin departamento';
                    this.form.area_administrativa = currentUser.departamento?.nombre || 'Sin departamento';
                    this.form.ubicacion_topografica = currentUser.departamento?.dependencia?.nombre || 'Sin dependencia';
                    this.form.nombre_sujeto_obligado = currentUser.departamento?.dependencia?.nombre || 'Sin dependencia';
                    this.form.id_dependencia = currentUser.departamento?.id_dependencia || null;
                    this.form.id_departamento = currentUser.id_departamento;
                })
                .catch(error => {
                    console.error('Error al obtener el usuario actual:', error);
                });
        }
    },
    mounted() {
        this.fetchCurrentUser();
    },
};
</script>

<style scoped>
.form-container {
    position: relative;
    width: 100%;
    min-height: 100vh;
}

.caratula-background {
    background-size: cover;
    background-position: center;
    padding: 40px;
    position: relative;
    color: #000;
}

h1 {
    color: #000;
    text-align: center;
    font-size: 2em;
    margin-bottom: 30px;
}

.subtitulo {
    text-align: center;
    font-size: 1.5em;
    font-weight: bold;
    margin-bottom: 20px;
}

.caratula-form {
    background: rgba(255, 255, 255, 0.7);
    padding: 40px;
    border-radius: 8px;
    max-width: 8000px;
    margin: 0 auto;
    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
}

.form-row {
    display: block;
    margin-bottom: 20px;
}

.form-group {
    width: 100%;
}

.form-control {
    width: 100%;
    padding: 8px;
    margin-top: 8px;
    border-radius: 4px;
}

.campo-solo-lectura:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
}

.btn {
    margin-top: 20px;
}
</style>
