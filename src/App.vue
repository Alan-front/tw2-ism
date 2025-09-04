<template>
 <div class="app-container">

  <HeaderInitial :logoSrc="logoSrc" @click="toggleMedia"></HeaderInitial>

  <div v-if="showMedia" class="menu-oculto">
    <ul class="menu">
      <li><a href="#">STORE</a></li>
      <li><a href="#">TW2ISM</a></li>
      <li><a href="#">AUDIOGUIDE</a></li>
      <li><a href="#">MANIFIESTO</a></li>
    </ul>
  </div>

   <!-- timeline -->
   <div class="timeline" ref="timeline">
     <div class="timeline-marker" ref="marker"></div>
     <div
       v-for="n in 61"
       :key="n"
       class="timeline-tick"
       :class="{ 'major': n % 5 === 1 }"
       :style="{ left: `${(n - 1) * (100 / 60)}%` }"
     >
       <span v-if="n % 5 === 1">{{ (n - 1) * 1 }}</span>
     </div>
   </div>

   <!-- contenedor de welcome -->
   <div v-if="showWelcome" class="welcome-container">
  <div class="welcome-content">
    <h2>Welcome to the Twoism audio guide.</h2>
    <br>
    <span>
      Immerse yourself in a 60-minute sound 
      experience, crafted from sound fragments, 
      noise, and music taken from the Twoism 
      archives, selected by Damian and Alanise.
      <span class="start-text" @click="toggleScroll">Click here to start . .  .</span>
    </span>
  </div>
</div>


   <!-- contenedor de media (videos e imagenes) -->
   <div v-if="showMedia" class="images-container" ref="imagesContainer">
     <!-- videos -->
     <div 
       v-for="(video, index) in videos" 
       :key="`video-${video.name}`"
       class="image-box"
       :style="{ transform: `translateY(${(index * 100) - (scrollY * 0.1) + 90}vh)` }"
     >
       <video autoplay muted loop>
         <source :src="video.url" type="video/mp4">
       </video>
     </div>

     <!-- imagenes -->
     <div 
       v-for="(imagen, index) in imagenes" 
       :key="`imagen-${imagen.name}`"
       class="image-box"
       :style="{ transform: `translateY(${((index + videos.length) * 100) - (scrollY * 0.1) + 90}vh)` }"
     >
       <img :src="imagen.url" :alt="imagen.name">
     </div>
   </div>

   <!-- timer -->
   <div v-if="showMedia" class="timer" ref="timer">00:00</div>

   <!-- audio oculto -->
   <audio ref="audio" hidden>
  <!-- <source src="@/assets/media_audios/kashmir.mp3" type="audio/mpeg" /> -->
  <source src="@/assets/media_audios/oboe_quartet.mp3" type="audio/mpeg" />
</audio>



 </div>
</template>


<script setup>
import { ref, onMounted } from 'vue';
import HeaderInitial from './components/HeaderInitial.vue';
import logoImage from '/src/assets/media_site/logo-web.png';
import logoNoFondo from '/src/assets/media_site/logo-no-fondo.png';




const logoSrc = ref(logoImage);
const showMenu = ref(false);
const showWelcome = ref(false);

const toggleMenu = () => {
  showMenu.value = !showMenu.value;
};

const toggleMedia = () => {
  if (showMedia.value) return; 

  logoSrc.value = logoNoFondo;
  showWelcome.value = true;
};


const showMedia = ref(false);
const videos = ref([]);
const imagenes = ref([]);
const scrollY = ref(0);

const audio = ref(null);
const marker = ref(null); 
const timer = ref(null);
const timeline = ref(null); // 🔹 referencia a la barra

// funcion para agregar toggleScroll 
const toggleScroll = () => {
  console.log('Toggle scroll clicked');
  showWelcome.value = false;
  showMedia.value = true;
  cargarMedia();

  if (audio.value) {
    audio.value.play().catch(err => console.log("Autoplay bloqueado:", err));
  }
};

// cargar videos e imagenes
const cargarMedia = async () => {
  const archivos = import.meta.glob('/src/assets/media_scroll/*');

  videos.value = [];
  imagenes.value = [];

  for (const ruta in archivos) {
    const nombre = ruta.split('/').pop();

    if (nombre.endsWith('.mp4')) {
      videos.value.push({ 
        name: nombre, 
        url: ruta 
      });
    } else if (nombre.endsWith('.jpg') || nombre.endsWith('.png')) {
      imagenes.value.push({ 
        name: nombre, 
        url: ruta 
      });
    }
  }

  console.log('Videos cargados:', videos.value.length);
  console.log('Imágenes cargadas:', imagenes.value.length);
};

// manejar el scroll con la rueda del mouse
const handleScroll = (event) => {
  event.preventDefault();
  if (!audio.value) return;

  const delta = event.deltaY > 0 ? 5 : -5; // mover 5 segundos
  const newTime = Math.max(0, Math.min(audio.value.duration, audio.value.currentTime + delta));

  audio.value.currentTime = newTime;
  actualizarUI(newTime);
};

// actualizar marker, timer y scrollY en base al tiempo
const actualizarUI = (current) => {
  if (!audio.value || !marker.value || !timer.value) return;

  const percent = (current / audio.value.duration) * 100;
  marker.value.style.left = `${percent}%`;

  const mins = Math.floor(current / 60).toString().padStart(2, "0");
  const secs = Math.floor(current % 60).toString().padStart(2, "0");
  timer.value.textContent = `${mins}:${secs}`;

  // actualizar scrollY para mover imágenes y videos
  const totalMedia = videos.value.length + imagenes.value.length;
  const maxScroll = (totalMedia * 1000) + 150;
  scrollY.value = (current / audio.value.duration) * maxScroll;
};

onMounted(() => {
  const audioEl = audio.value;
  if (!audioEl) return;

  audioEl.addEventListener("timeupdate", () => {
    actualizarUI(audioEl.currentTime);
  });

  document.addEventListener('wheel', handleScroll);
});

onMounted(() => {
  const audioEl = audio.value;

  // actualizar marker + timer
  audioEl.addEventListener("timeupdate", () => {
    const current = audioEl.currentTime; 
    const percent = (current / audioEl.duration) * 100;
    marker.value.style.left = `${percent}%`;

    const minutes = Math.floor(current / 60);
    const seconds = Math.floor(current % 60).toString().padStart(2, '0');
    timer.value.textContent = `${minutes}:${seconds}`;
  });

  // 🔁 cuando termine, reinicia todo
  audioEl.addEventListener("ended", () => {
    audioEl.currentTime = 0;
    audioEl.play(); // vuelve a empezar el audio

    marker.value.style.left = "0%";
    timer.value.textContent = "00:00";
    scrollY.value = 0; // reinicia scroll
  });

  // 🎯 click en timeline para saltar
  if (timeline.value) {
    timeline.value.addEventListener("click", (e) => {
      const rect = timeline.value.getBoundingClientRect();
      const clickX = e.clientX - rect.left;
      const percentage = clickX / rect.width;

      if (isNaN(audioEl.duration) || audioEl.duration === 0) return;

      const newTime = percentage * audioEl.duration;
      audioEl.currentTime = newTime;

      marker.value.style.left = `${percentage * 100}%`;

      // mover scroll en proporción
      const totalMedia = videos.value.length + imagenes.value.length;
      const maxScroll = (totalMedia * 1000) + 150;
      scrollY.value = percentage * maxScroll;
    });
  }
});
</script>



<style>
/* reset gglobal - debe ir sin scoped */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  overflow: hidden;
}
</style>

<style scoped>
/* container general */
.app-container {
  background-color: rgb(0, 0, 0);
  width: 100vw;
  height: 100vh;
  overflow: hidden;
  position: relative;
  
}

/* timeline */
.timeline {
  top: var(--altura-header); 
  width: 100%;
  height: var(--altura-timeline);
  background: #1f1e1e00;
  position: fixed;
  cursor: pointer;
  z-index: 2;
  left: 0;
}

.timeline-marker {
  position: absolute;
  width: 2px;
  height: 570px;
  background: red;
  left: 0%;
  top: 9px;
  transition: left 0.3s ease-out;
}

.timer {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 10px 20px;
  border-radius: 5px;
  font-size: 1.2em;
  font-family: monospace;
  z-index: 1000;
}

.timeline-tick {
  position: absolute;
  top: 10px;
  width: 3px;
  background: rgb(255, 255, 255);
  height: 14%;
  opacity: 0.5;
}

.timeline-tick.major {
  height: 0.01%;
  opacity: 1;
}

.timeline-tick span {
  position: absolute;
  top: 15px;
  left: -5px;
  font-size: 0.65em;
  color: rgb(255, 255, 255);
}

img {
  transition: opacity .6s ease;
}

/* contenedor  welcome */
.welcome-container {
  position: absolute;  
  top: 30vh;              
  left: 40px;             
  height: 30vh;
  max-width: 55vw;
  align-items: center;
  justify-content: left;
  background-color: rgba(32, 31, 31, 0.315);
  color: white;
  padding: 20px 0px;
  
  transition: opacity .8s ease, transform .8s ease;
}

.welcome-content {
  text-align: left;
  max-width: 800px;
  padding: 20px;
}

.start-text {
  cursor: pointer;
  color: #fff;
  text-decoration: none; 
}

.start-text:hover {
  color: #aaa;
}

span {
  font-size: 1em;
}

h2, span {
  font-family: Arial, Helvetica, sans-serif;
}

/* contenedor de imgenes y videos */
.images-container {
  position: absolute;
  width: 100%;
  height: 100vh;
  top: 0;
  left: 0;
}

.image-box {
  position: absolute;
  width: 100%;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.3s ease;
}

.image-box img,
.image-box video {
  max-width: 80%;
  max-height: 80%;
  object-fit: contain;
  opacity: 0.4;
  transition: opacity 0.3s ease;
}

.image-box img:hover,
.image-box video:hover {
  opacity: 1;
}

/* menu oculto */

.menu-oculto {
  background-color: rgb(12, 12, 12);
  /* border: 1px solid blue; */
  width: 100%;
  position: fixed;
  top: 80px;
  left: 0;
  height: 26px;
  display: flex;
  justify-content: center;
  align-items: center;
  transform: translateY(-100%);
  transition: transform 0.3s ease-in-out;
  z-index: ;
}

.header:hover ~ .menu-oculto,
.menu-oculto:hover {
  transform: translateY(0%);
}

.menu {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  gap: 20px;
  justify-content: space-between;
  width: 50%;
}

.menu li {
  display: inline-block;
  /* margin-bottom: 50px; */
}

.menu a {
  text-decoration: none;
  color: rgb(238, 231, 231);
  font-weight: bold;
  font-size: 1.1em;
  letter-spacing: 3px;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}


.timer {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 10px 20px;
  border-radius: 5px;
  font-size: 1.2em;
  font-family: monospace;
  z-index: 1000;
}



/* animacion suave para imagenes y vdeos */
.images-container {
  position: absolute;
  width: 100%;
  height: 100vh;
  top: 0;
  left: 0;
  overflow: hidden;
}

.image-box {
  position: absolute;
  width: 100%;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.6s ease-out; /* suaviza el movimiento */
}

.image-box img,
.image-box video {
  max-width: 80%;
  max-height: 80%;
  object-fit: contain;
  opacity: 0.5;
  transition: opacity 0.6s ease, transform 0.6s ease;
}

.image-box img:hover,
.image-box video:hover {
  opacity: 1;
  transform: scale(1.05); /* pequeño zoom al hover */
}



</style>