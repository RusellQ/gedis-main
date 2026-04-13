<template>
    <div class="form-container">
        <!-- Contenedor con la imagen de fondo -->
        <div class="caratula-background">
            <h1>Generar Carátula de Expediente</h1>

            <form @submit.prevent="submitForm" class="caratula-form">
                <!-- Campos en una sola columna -->
                  <div class="form-row">
                    <div class="form-group">
                        <label for="nombreSujetoObligado">Sujeto Obligado</label>
                        <input type="text" id="nombreSujetoObligado" v-model="form.nombre_sujeto_obligado"
                            class="form-control" disabled>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="areaAdministrativa">Área Administrativa</label>
                        <input type="text" id="areaAdministrativa" v-model="form.area_administrativa"
                            class="form-control" disabled>
                    </div>

                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="noConsecutivo">No. Consecutivo</label>
                        <input type="number" id="noConsecutivo" v-model="form.no_consecutivo" class="form-control"
                            required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="areaGeneradora">Área Generadora</label>
                        <input type="text" id="areaGeneradora" v-model="form.area_generadora" class="form-control"
                            disabled>
                    </div>

                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="codigoClasificacionArchivistica">Código de Clasificación Archivística</label>
                        <input type="text" id="codigoClasificacionArchivistica"
                            v-model="form.codigo_clasificacion_archivistica" class="form-control" required>
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
                            class="form-control" disabled>
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
                        <label for="valorDocumental">Valor Documental</label>
                        <div class="d-flex justify-content-start">
                            <div class="form-check mr-3">
                                <input class="form-check-input" type="checkbox"
                                    v-model="form.valor_documental.administrativo" id="administrativo">
                                <label class="form-check-label" for="administrativo">Administrativo</label>
                            </div>
                            <div class="form-check mr-3">
                                <input class="form-check-input" type="checkbox" v-model="form.valor_documental.legal"
                                    id="legal">
                                <label class="form-check-label" for="legal">Legal</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" v-model="form.valor_documental.contable"
                                    id="contable">
                                <label class="form-check-label" for="contable">Contable</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="vigenciaDocumental">Vigencia Documental</label>
                        <input type="text" id="vigenciaDocumental" v-model="form.vigencia_documental"
                            class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="añosArchivoTramite">Años en Archivo de Trámite</label>
                        <input type="number" id="añosArchivoTramite" v-model="form.anios_archivo_tramite"
                            class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="añosArchivoConcentracion">Años en Archivo de Concentración</label>
                        <input type="number" id="añosArchivoConcentracion" v-model="form.anios_archivo_concentracion"
                            class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="subtitulo">Información Reservada y/o Confidencial</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="caracterInformacion">Carácter de la Información</label>
                        <select id="caracterInformacion" v-model="form.caracter_informacion" class="form-control"
                            required>
                            <option value="Reservada">Reservada</option>
                            <option value="Confidencial">Confidencial</option>
                        </select>
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
                        <label for="fundamentoLegal">Fundamento Legal</label>
                        <input type="text" id="fundamentoLegal" v-model="form.fundamento_legal" class="form-control"
                            required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="periodoReserva">Periodo de Reserva</label>
                        <input type="text" id="periodoReserva" v-model="form.periodo_reserva" class="form-control"
                            required>
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
                    <input type="text" id="capturador" v-model="form.capturador" class="form-control" disabled>
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
export default {
    data() {
        return {
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
                valor_documental: {
                    administrativo: false,
                    legal: false,
                    contable: false
                },
                vigencia_documental: '',
                anios_archivo_tramite: null,
                anios_archivo_concentracion: null,
                caracter_informacion: 'Reservada',
                fecha_clasificacion: '',
                fundamento_legal: '',
                periodo_reserva: '',
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
            // Filtrar solo los campos con valores verdaderos en valor_documental
            const formData = { ...this.form };
            if (!formData.valor_documental.administrativo) delete formData.valor_documental.administrativo;
            if (!formData.valor_documental.legal) delete formData.valor_documental.legal;
            if (!formData.valor_documental.contable) delete formData.valor_documental.contable;

            axios.post('/documentos', formData)
                .then(response => {
                    alert('Carátula de expediente generada con éxito');
                    this.resetForm(); // Opcional: resetea el formulario después de enviarlo
                })
                .catch(error => {
                    console.error('Error al generar la carátula:', error);
                    alert('Hubo un error al generar la carátula. Intenta nuevamente.');
                });
        },
        resetForm() {
            this.form.nombre_sujeto_obligado = '';
            this.form.area_administrativa = '';
            this.form.no_consecutivo = null;
            this.form.area_generadora = '';
            this.form.codigo_clasificacion_archivistica = '';
            this.form.anio_apertura = null;
            this.form.anio_cierre = null;
            this.form.soporte_documental = 'Físico';
            this.form.titulo_expediente = '';
            this.form.descripcion_asunto_expediente = '';
            this.form.ubicacion_topografica = '';
            this.form.no_legajo = null;
            this.form.no_fojas = null;
            this.form.valor_documental = {
                administrativo: false,
                legal: false,
                contable: false
            };
            this.form.vigencia_documental = '';
            this.form.anios_archivo_tramite = null;
            this.form.anios_archivo_concentracion = null;
            this.form.caracter_informacion = 'Reservada';
            this.form.fecha_clasificacion = '';
            this.form.fundamento_legal = '';
            this.form.periodo_reserva = '';
            this.form.aplicacion_periodo_reserva = '';
            this.form.rubrica_titular = '';
            this.form.fecha_desclasificacion = '';
            this.form.observaciones = '';
            capturador: this.capturador;
        },

        fetchCurrentUser() {
            axios.get('/currentuser')
                .then(response => {
                    const currentUser = response.data;

                    this.form.capturador = `${currentUser.nombre} ${currentUser.apellido_paterno} ${currentUser.apellido_materno}`;

                    // Nombre del departamento (área generadora)
                    this.form.area_generadora = currentUser.departamento?.nombre || 'Sin departamento';
                    
                    // Nombre del departamento (área administrativa sin importar redundancia)
                    this.form.area_administrativa = currentUser.departamento?.nombre || 'Sin departamento';

                    // Nombre de la dependencia asociada al departamento
                    this.form.ubicacion_topografica = currentUser.departamento?.dependencia?.nombre || 'Sin dependencia';

                     // Nombre del sujeto obligado
                    this.form.nombre_sujeto_obligado = currentUser.departamento?.dependencia?.nombre || 'Sin dependencia';

                    // Guardar los IDs si necesitas enviarlos después
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
    height: 100vh;
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

.btn {
    margin-top: 20px;
}

.d-flex label {
    margin-right: 10px;
}
</style>
