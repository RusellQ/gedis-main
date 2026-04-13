<template>
  <li class="mb-1">
    <div
      class="flex items-center space-x-2 cursor-pointer group"
      @click="toggle"
    >
      <span class="text-gray-500" v-if="hasChildren">
        {{ expanded ? '📂' : '📁' }}
      </span>
      <span class="text-gray-300" v-else>📄</span>

      <span
        @click.stop="seleccionar"
        class="hover:underline text-blue-600"
      >
        {{ node.nombre }}
      </span>

      <button
        @click.stop="mostrarModal = true"
        class="ml-auto text-xs bg-gray-200 px-2 py-0.5 rounded hover:bg-gray-300 hidden group-hover:block"
      >
        ＋
      </button>
    </div>

    <!-- Hijos -->
    <ul v-if="expanded" class="ml-4">
      <TreeNode
        v-for="hijo in node.hijos"
        :key="hijo.id"
        :node="hijo"
        @seleccionar="$emit('seleccionar', $event)"
        @nuevo="(payload) => $emit('nuevo', payload)"
      />
    </ul>

    <!-- Modal para nuevo hijo -->
    <div v-if="mostrarModal" class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">
      <div class="bg-white p-6 rounded shadow w-96">
        <h3 class="text-lg font-semibold mb-4">Agregar {{ siguienteNivel(node.tipo) }}</h3>
        <input v-model="nuevoNombre" type="text" placeholder="Nombre" class="w-full border p-2 mb-4 rounded" />
        <div class="flex justify-end space-x-2">
          <button @click="mostrarModal = false" class="px-4 py-2 bg-gray-300 rounded">Cancelar</button>
          <button @click="crearHijo" class="px-4 py-2 bg-blue-600 text-white rounded">Guardar</button>
        </div>
      </div>
    </div>
  </li>
</template>

<script setup>
import { ref, computed } from 'vue'
import TreeNode from './TreeNode.vue'

const props = defineProps({
  node: Object,
})

const emit = defineEmits(['seleccionar', 'nuevo'])

const expanded = ref(false)
const mostrarModal = ref(false)
const nuevoNombre = ref('')

const hasChildren = computed(() => props.node.hijos && props.node.hijos.length > 0)

function toggle() {
  expanded.value = !expanded.value
}

function seleccionar() {
  emit('seleccionar', props.node)
}

function siguienteNivel(tipo) {
  const niveles = ['Fondo', 'Sección', 'Serie', 'Expediente', 'Unidad Documental']
  const index = niveles.indexOf(tipo)
  return niveles[index + 1] || 'Nivel'
}

function crearHijo() {
  if (!nuevoNombre.value.trim()) return
  emit('nuevo', {
    parentId: props.node.id,
    nombre: nuevoNombre.value.trim(),
    tipo: siguienteNivel(props.node.tipo),
  })
  mostrarModal.value = false
  nuevoNombre.value = ''
}
</script>
