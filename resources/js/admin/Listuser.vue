<template>
  <div class="user-list">
    <div v-for="user in users" :key="user._id" class="user-card">
      <div class="user-info">
        <h4>{{ user.nombre }} {{ user.apellido_paterno }} {{ user.apellido_materno }}</h4>
        <p><strong>Email:</strong> {{ user.email }}</p>
        <p><strong>Roles:</strong></p>
        <ul class="role-list">
          <li v-for="role in user.roles" :key="role" class="role">{{ role }}</li>
        </ul>
        <div class="button-group">
          <button type="button" @click="openEditModal(user)" class="btn-edit">
            <i class="fas fa-edit"></i>
          </button>
          <button @click="confirmDelete(user)" class="btn-delete">
            <i class="fas fa-minus-circle"></i> <!-- Icono de eliminar -->
          </button>
        </div>
      </div>
    </div>

    <!-- Modal para editar usuario -->
    <div ref="editModal" class="modal fade" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel">Editar Usuario</h5>
            <button type="button" class="close" @click="cerrarModal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="confirmUpdate">
              <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input v-model="editedUser.nombre" type="text" id="nombre" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="apellido_paterno">Apellido Paterno:</label>
                <input v-model="editedUser.apellido_paterno" type="text" id="apellido_paterno" class="form-control"
                  required>
              </div>
              <div class="form-group">
                <label for="apellido_materno">Apellido Materno:</label>
                <input v-model="editedUser.apellido_materno" type="text" id="apellido_materno" class="form-control"
                  required>
              </div>
              <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input v-model="editedUser.email" type="email" id="email" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="id_dependencia">Dependencia:</label>
                <select v-model="editedUser.id_dependencia" id="id_dependencia" class="form-control"
                  :disabled="!esRoot" required @change="onDependenciaChange">
                  <option disabled value="">Selecciona una dependencia</option>
                  <option v-for="dep in dependencias" :key="dep.id" :value="dep.id">
                    {{ dep.nombre }}
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label for="id_departamento">Departamento:</label>
                <select v-model="editedUser.id_departamento" id="id_departamento" class="form-control"
                  :disabled="!editedUser.id_dependencia" required>
                  <option disabled value="">Selecciona un departamento</option>
                  <option v-for="dep in departamentos" :key="dep.id" :value="dep.id">
                    {{ dep.nombre }}
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label for="password">Contraseña temporal:</label>
                <input v-model="editedUser.password" type="password" id="password" class="form-control"
                  placeholder="Dejar en blanco para no cambiarla" minlength="8">
                <small class="text-muted">Si escribes una, deberá cambiarla en su siguiente inicio de sesión.</small>
              </div>
              <div class="form-group">
                <label>Roles:</label>
                <div class="form-check" v-for="role in availableRoles" :key="role">
                  <input class="form-check-input" type="checkbox" :value="role" v-model="editedUser.roles"
                    :id="'edit-' + role">
                  <label class="form-check-label" :for="'edit-' + role">{{ role }}</label>
                </div>
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from 'sweetalert2';
import axios from 'axios';
import { Modal } from 'bootstrap';

export default {
  data() {
    return {
      users: [],
      editedUser: {
        id: '',
        nombre: '',
        apellido_paterno: '',
        apellido_materno: '',
        email: '',
        password: '',
        roles: [],
        id_dependencia: '',
        id_departamento: '',
      },
      availableRoles: ['administrador', 'editor', 'lector'],
      dependencias: [],
      departamentos: [],
      usuarioActual: null,
    };
  },
  computed: {
    esRoot() {
      return Array.isArray(this.usuarioActual?.roles) && this.usuarioActual.roles.includes('root');
    },
  },
  mounted() {
    this.fetchUsers();
    this.cargarUsuarioActual();
    this.cargarDependencias();
  },
  beforeUnmount() {
    const el = this.$refs.editModal;
    if (el) {
      Modal.getInstance(el)?.dispose();
    }
  },
  methods: {
    fetchUsers() {
      axios.get('/list-users')
        .then(response => {
          this.users = response.data;
        })
        .catch(error => {
          console.error('Error al obtener la lista de usuarios:', error);
        });
    },
    async cargarUsuarioActual() {
      try {
        const response = await axios.get('/currentuser');
        const roles = typeof response.data.roles === 'string'
          ? JSON.parse(response.data.roles)
          : response.data.roles || [];
        this.usuarioActual = {
          id_dependencia: response.data.id_dependencia || null,
          roles,
        };
      } catch (error) {
        console.error('Error cargando usuario:', error);
      }
    },
    async cargarDependencias() {
      try {
        const response = await axios.get('/indexdependencias');
        this.dependencias = response.data;
      } catch (error) {
        console.error('Error al cargar dependencias:', error);
      }
    },
    async cargarDepartamentos(dependenciaId) {
      if (!dependenciaId) {
        this.departamentos = [];
        return;
      }
      try {
        const response = await axios.get(`/indexdepartamentos?dependencia_id=${dependenciaId}`);
        this.departamentos = response.data;
      } catch (error) {
        console.error('Error al cargar departamentos:', error);
        this.departamentos = [];
      }
    },
    onDependenciaChange() {
      this.editedUser.id_departamento = '';
      this.cargarDepartamentos(this.editedUser.id_dependencia);
    },
    obtenerModal() {
      return Modal.getOrCreateInstance(this.$refs.editModal);
    },
    cerrarModal() {
      this.obtenerModal().hide();
    },
    async openEditModal(user) {
      if (!this.usuarioActual) {
        await this.cargarUsuarioActual();
      }
      if (!this.dependencias.length) {
        await this.cargarDependencias();
      }

      const { password, ...resto } = user;
      this.editedUser = {
        ...resto,
        password: '',
        roles: Array.isArray(resto.roles) ? [...resto.roles] : [],
        id_dependencia: resto.id_dependencia != null ? Number(resto.id_dependencia) : '',
        id_departamento: resto.id_departamento != null ? Number(resto.id_departamento) : '',
      };

      await this.cargarDepartamentos(this.editedUser.id_dependencia);
      this.obtenerModal().show();
    },
    confirmUpdate() {
      if (!this.editedUser.id_dependencia || !this.editedUser.id_departamento) {
        Swal.fire({
          icon: 'error',
          title: 'Faltan datos',
          text: 'Selecciona la dependencia y el departamento.',
        });
        return;
      }

      Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Deseas guardar los cambios?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          this.submitEditForm();
        }
      });
    },
    submitEditForm() {
      if (!this.editedUser) {
        console.error('Error: editedUser no está definido');
        return;
      }

      const updatedUser = {
        id: this.editedUser.id,
        nombre: this.editedUser.nombre,
        apellido_paterno: this.editedUser.apellido_paterno,
        apellido_materno: this.editedUser.apellido_materno,
        email: this.editedUser.email,
        id_dependencia: this.editedUser.id_dependencia,
        id_departamento: this.editedUser.id_departamento,
        roles: this.editedUser.roles,
      };

      if (this.editedUser.password) {
        updatedUser.password = this.editedUser.password;
      }

      axios.put(`/users/${updatedUser.id}`, updatedUser)
        .then(() => {
          this.cerrarModal();
          this.fetchUsers();
          const texto = this.editedUser.password
            ? 'El usuario ha sido actualizado. La contraseña es temporal y deberá cambiarla en su siguiente inicio de sesión.'
            : 'El usuario ha sido actualizado exitosamente.';
          Swal.fire(
            'Actualizado',
            texto,
            'success'
          );
        })
        .catch(error => {
          console.error('Error al actualizar el usuario:', error);
          const errores = error.response?.data?.errors || {};
          const mensaje = errores.password?.[0]
            || errores.id_departamento?.[0]
            || errores.id_dependencia?.[0]
            || 'Hubo un problema al actualizar el usuario.';
          Swal.fire(
            'Error',
            mensaje,
            'error'
          );
        });
    },
    confirmDelete(user) {
      Swal.fire({
        title: '¿Estás seguro?',
        text: 'Una vez eliminado, no podrás recuperar este usuario.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          axios.delete(`/users/${user._id}`)
            .then(response => {
              Swal.fire(
                'Eliminado',
                'El usuario ha sido eliminado.',
                'success'
              );
              this.fetchUsers();
            })
            .catch(error => {
              console.error('Error al eliminar el usuario:', error);
              Swal.fire(
                'Error',
                'Hubo un problema al eliminar el usuario.',
                'error'
              );
            });
        }
      });
    }
  }
};
</script>

<style scoped>
/* Estilos personalizados, si es necesario */
.user-list {
  display: flex;
  flex-wrap: wrap;
}

.user-card {
  position: relative;
  width: calc(33.33% - 20px);
  margin: 10px;
  background-color: #ffffff;
  border: 1px solid #ddd;
  border-radius: 5px;
  padding: 20px;
  transition: all 0.3s ease;
}

.user-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.user-info {
  text-align: center;
}

.user-info h4 {
  margin-bottom: 10px;
}

.role-list {
  padding-left: 0;
  list-style-type: none;
}

.role {
  display: inline-block;
  margin-right: 5px;
  background-color: #791818;
  color: #fff;
  padding: 5px 10px;
  border-radius: 15px;
}

.button-group {
  position: absolute;
  top: 1px;
  right: 1px;
  background-color: transparent;
}

.btn-edit,
.btn-delete {
  padding: 4px 8px;
  margin-right: 5px;
  border: none;
  background: none;
  cursor: pointer;
}

.btn-edit i,
.btn-delete i {
  font-size: 16px;
}
</style>
