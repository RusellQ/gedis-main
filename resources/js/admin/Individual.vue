<template>
    <div v-if="expediente" class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow rounded-4 ">
                <div class="card-header" :class="claseEstado(expediente.estado)">
                    <h4 class="mb-0">Expediente: {{ expediente.nombre }}</h4>
                </div>
                <div class="card-body">
                    <!-- Info del expediente -->
                    <div class="row mb-2">
                        <div class="col-md-6"><strong>Código:</strong> {{ expediente.codigo }}</div>
                        <div class="col-md-6"><strong>Estado:</strong> {{ estadoLegible(expediente.estado) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6"><strong>Clasificación:</strong> {{ expediente.clasificacion }}</div>
                        <div class="col-md-6"><strong>Carácter:</strong> {{ expediente.caracter }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6"><strong>Ubicación Topográfica:</strong> {{
                            expediente.ubicacion_topografica }}</div>
                        <div class="col-md-6"><strong>No. Caja:</strong> {{ expediente.no_caja }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6"><strong>No. Legajos:</strong> {{ expediente.no_legajos }}</div>
                        <div class="col-md-6"><strong>No. Hojas:</strong> {{ expediente.no_hojas }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6"><strong>Tiempo de Conservación:</strong> {{ expediente.tiempo_conservacion
                            }} años</div>
                        <div class="col-md-6"><strong>Preservación:</strong> {{ expediente.preservacion }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6"><strong>Clave:</strong> {{ expediente.clave }}</div>
                        <div class="col-md-6"><strong>Observaciones:</strong> {{ expediente.observacion || 'Ninguna' }}
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4"><strong>Fecha de Creación:</strong> {{
                            formatDate(expediente.fecha_creacion) }}</div>
                        <div class="col-md-4"><strong>Fecha de Apertura:</strong> {{
                            formatDate(expediente.fecha_apertura) }}</div>
                        <div class="col-md-4"><strong>Fecha de Cierre:</strong> {{ formatDate(expediente.fecha_cierre)
                            }}</div>
                    </div>

                    <!-- Botones según estado -->
                    <div class="row mt-4">
                        <div class="col text-center">
                            <button v-if="expediente.estado === 'en_tramite'" class="btn btn-warning"
                                @click="cambiarEstado('en_concentracion')">
                                Mandar a concentración
                            </button>

                            <button v-else-if="expediente.estado === 'en_concentracion'" class="btn btn-success"
                                @click="cambiarEstado('en_historico')">
                                Mandar a histórico
                            </button>

                            <button v-else-if="expediente.estado === 'en_historico'" class="btn btn-danger"
                                @click="darDeBaja">
                                Dar de baja
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-else class="text-center mt-5 text-muted">
        <div class="spinner-border text-primary mb-2" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p>Cargando expediente...</p>
    </div>
</template>

<script>
import Swal from 'sweetalert2'

export default {
    props: ['expedienteId'],
    data() {
        return {
            expediente: null,
            cargandoCambio: false,
        };
    },
    mounted() {
        this.cargarExpediente();
    },
    methods: {
        /* ---------- helpers ---------- */
        cargarExpediente() {
            axios.get(`/expedienteid/${this.expedienteId}`)
                .then(res => this.expediente = res.data)
                .catch(() => Swal.fire('Error', 'No se pudo cargar el expediente', 'error'));
        },
        formatDate(date) {
            if (!date) return 'No especificada';
            return new Date(date).toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' });
        },
        claseEstado(estado) {
            switch (estado) {
                case 'en_tramite': return 'bg-primary text-white';
                case 'en_concentracion': return 'bg-warning text-dark';
                case 'en_historico': return 'bg-success text-white';
                default: return 'bg-secondary text-white';
            }
        },
        estadoLegible(estado) {
            return {
                en_tramite: 'En trámite',
                en_concentracion: 'En concentración',
                en_historico: 'Histórico'
            }[estado] ?? estado;
        },

        /* ---------- acciones ---------- */
        async cambiarEstado(nuevoEstado) {
            if (this.cargandoCambio) return;

            /* Confirmación */
            const { isConfirmed } = await Swal.fire({
                title: `¿Mandar a ${this.estadoLegible(nuevoEstado)}?`,
                text: 'Esta acción actualizará el estado del expediente.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
            });
            if (!isConfirmed) return;

            this.cargandoCambio = true;

            try {
                await axios.put(`/cambiarEstado/${this.expediente.id}`, { estado: nuevoEstado });
                this.expediente.estado = nuevoEstado;

                /* Alerta de éxito breve; al cerrarse, redirige */
                await Swal.fire({
                    title: 'Estado actualizado',
                    text: `Ahora está en ${this.estadoLegible(nuevoEstado)}.`,
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false,
                });

                window.location.href = '/dashboard';   // redirección

            } catch (error) {
                Swal.fire('Error', 'No se pudo cambiar el estado.', 'error');
            } finally {
                this.cargandoCambio = false;
            }
        },

        async darDeBaja() {
    const { value: motivo_baja, isConfirmed } = await Swal.fire({
        title: '¿Dar de baja este expediente?',
        text: 'Esta acción no se puede deshacer. Por favor, indica el motivo de baja:',
        input: 'textarea',
        inputPlaceholder: 'Escribe aquí el motivo de baja...',
        inputAttributes: {
            'aria-label': 'Motivo de baja'
        },
        showCancelButton: true,
        confirmButtonText: 'Sí, dar de baja',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        inputValidator: (value) => {
            if (!value) {
                return 'El motivo de baja es obligatorio';
            }
            if (value.length > 500) {
                return 'El motivo no puede tener más de 500 caracteres';
            }
            return null;
        }
    });

    if (!isConfirmed) return;

    try {
        await axios.delete(`/bajaexpediente/${this.expediente.id}`, {
            data: { motivo_baja }  // Enviamos el motivo en el body
        });

        // Mostrar alerta con timer y sin botón de confirmar
        await Swal.fire({
            title: 'Borrado',
            text: 'El expediente fue dado de baja.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false,
        });

        this.expediente = null;

        // Redirigir después de 2 segundos (mismo tiempo que el timer)
        setTimeout(() => {
            window.location.href = '/dashboard';
        }, 2000);

    } catch {
        Swal.fire('Error', 'No se pudo dar de baja.', 'error');
    }
}
    }
}
</script>