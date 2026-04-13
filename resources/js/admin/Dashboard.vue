<template>
    <div class="container">
      <h1 class="text-4xl font-bold mb-4">Bienvenid@, {{ userName }}</h1>
      <p class="text-lg text-gray-600 mt-2">Puede empezar a realizar sus actividades con el panel de control que se encuentra a su izquierda.</p>
    </div>
  </template>
  
  <script>
  
  export default {
    props: ['userName']
  }
  </script>
  
  <style scoped>
  .container {
    height: 80vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background-color: #f8f9fc;
  }
  </style>
  