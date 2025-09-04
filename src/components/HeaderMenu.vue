<template>
  <header class="header">
    <div class="logo-div">
      <img :src="imago" alt="Logo" />
    </div>
    <div class="imagotipe"></div>
  </header>
</template>

<script>
export default {
data() {
  return {
    imago: new URL("@/assets/media_site/logo-no-fondo.png", import.meta.url).href
  };
},
};
</script>
  
<style scoped>
@import "@/assets/style-headers.css";




</style>