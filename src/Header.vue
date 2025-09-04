<template>
    <header class="header">
      <div class="logo-div">
        <img :src="logo" alt="Logo" />
      </div>
      <div class="imagotipe"></div>
    </header>
  </template>

<script>



export default {
  data() {
    return {
      logo: new URL("@/assets/media_site/logo-web.png", import.meta.url).href,
    };
  },
};
</script>
    
  <style scoped>
  
  
  
  
  
  </style>