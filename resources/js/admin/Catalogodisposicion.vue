<template>
  <div style="height: 100vh; display: flex; flex-direction: column;">
    <iframe
      :src="vistaPreviaUrl"
      style="flex-grow: 1; width: 100%; border: none;"
      frameborder="0"
    ></iframe>

    <div style="padding: 10px; text-align: center;">
    <button
      @click="descargarPDF"
      class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-800"
    >
      Descargar Catálogo de Disposición Documental
    </button>
  </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      vistaPreviaUrl: '/catalogodisposicion/vista-previa'
    };
  },
  methods: {
    descargarPDF() {
      const form = document.createElement('form');
      form.method = 'GET';
      form.action = '/catalogodisposicion/pdf';
      form.target = '_blank';

      document.body.appendChild(form);
      form.submit();
      form.remove();
    }
  }
};
</script>
