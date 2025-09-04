<template>
  <div class="gallery">
    <!-- Videos -->
    <div v-for="video in videos" :key="video.name">
      <video controls width="300">
        <source :src="video.url" type="video/mp4">
      </video>
    </div>

    <!-- Imágenes -->
    <div v-for="imagen in imagenes" :key="imagen.name">
      <img :src="imagen.url" width="300">
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      videos: [],
      imagenes: []
    }
  },
  mounted() {
    // Importa todos los archivos de media_scroll
    const archivos = import.meta.glob('/src/assets/media_scroll/*')
    
    // Procesa cada archivo
    for (const ruta in archivos) {
      const nombre = ruta.split('/').pop() // obtiene el nombre del archivo
      
      if (nombre.endsWith('.mp4')) {
        this.videos.push({
          name: nombre,
          url: ruta
        })
      } else if (nombre.endsWith('.jpg')) {
        this.imagenes.push({
          name: nombre,
          url: ruta
        })
      }
    }
  }
}
</script>

<style>
.gallery {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  padding: 20px;
}
</style>