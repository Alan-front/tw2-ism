<template>
  <div class="app-container">
    <HeaderInitial
      :logoSrc="logoSrc"
      :headerTitle="headerTitle"
      @click="toggleMedia"
    />

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
      <div v-if="showMedia" class="timeline-marker" ref="marker"></div>
      <div
        v-for="n in 61"
        :key="n"
        class="timeline-tick"
        :class="{ major: n % 5 === 1 }"
        :style="{ left: `${(n - 1) * (100 / 60)}%` }"
      >
        <span v-if="n % 5 === 1">{{ (n - 1) * 1 }}</span>
      </div>
    </div>

    <!-- contenedor de welcome -->
    <div v-if="showWelcome" class="welcome-container">
      <div class="welcome-content">
        <h2>Welcome to the Twoism audio guide.</h2>
        <br />
        <span>
          Immerse yourself in a 60-minute sound experience, crafted from sound
          fragments, noise, and music taken from the Twoism archives, selected
          by Damian and Alanise.
          <span class="start-text" @click="toggleScroll"
            >Click here to start . . .</span
          >
        </span>
      </div>
    </div>

    <!-- contenedor de slides -->
    <div v-if="showMedia" class="images-container" ref="imagesContainer">
      <div
        v-for="(slide, index) in slides"
        :key="slide.id"
        class="image-box"
        :style="{
          transform: `translateY(${slide.offsetTop - scrollY + 90}vh)`,
          height: slide.height_vh + 'vh',
          backgroundImage: slide.backgroundUrl
            ? `url(${slide.backgroundUrl})`
            : 'none',
          backgroundSize: 'cover',
          backgroundPosition: 'center',
        }"
        :ref="(el) => setBoxRef(el, index)"
      >
        <div
          v-for="el in slide.elementos"
          :key="el.id"
          class="elemento"
          :style="{
            left: el.pos_x + '%',
            top: el.pos_y + '%',
            width: el.width + '%',
            transform: `rotate(${el.rotation}deg)`,
            zIndex: el.z_index,
          }"
        >
          <a
            v-if="el.url"
            :href="el.url"
            target="_blank"
            style="display: contents"
          >
            <img
              v-if="el.type === 'image' || el.type === 'gif'"
              :src="el.fileUrl"
              @mouseenter="onMediaEnter(el.title)"
              @mouseleave="onMediaLeave"
            />
            <video
              v-else-if="el.type === 'video'"
              :src="el.fileUrl"
              autoplay
              loop
              :muted="!el.sound_enabled"
              @mouseenter="onMediaEnter(el.title)"
              @mouseleave="onMediaLeave"
            />
          </a>
          <template v-else>
            <img
              v-if="el.type === 'image' || el.type === 'gif'"
              :src="el.fileUrl"
              @mouseenter="onMediaEnter(el.title)"
              @mouseleave="onMediaLeave"
            />
            <video
              v-else-if="el.type === 'video'"
              :src="el.fileUrl"
              autoplay
              loop
              :muted="!el.sound_enabled"
              @mouseenter="onMediaEnter(el.title)"
              @mouseleave="onMediaLeave"
            />
          </template>
          <div v-if="el.description" class="media-description">
            {{ el.description }}
          </div>
        </div>
      </div>
    </div>

    <!-- overlay de numeros de orden (debug), fuera del contexto transformado -->
    <div v-if="showOrden" class="orden-overlay">
      <div
        v-for="(slide, index) in slides"
        :key="'orden-' + slide.id"
        class="dev-orden"
        :class="{ active: ordenActivo === index }"
        :style="{ opacity: ordenOpacity[index] || 0 }"
      >
        {{ slide.orden }}
      </div>
    </div>

    <!-- timer -->
    <div v-if="showMedia" class="timer" ref="timer">00:00:00</div>

    <!-- panel de debug de scroll/sync -->
    <div v-if="showDebug && showMedia" class="debug-panel">
      <div>slide activa (index): {{ debugInfo.slideActiva }}</div>
      <div>orden (db): {{ debugInfo.ordenActiva }}</div>
      <div>scrollY actual: {{ debugInfo.scrollY }} vh</div>
      <div>offsetTop esperado: {{ debugInfo.offsetEsperado }} vh</div>
      <div>audio time: {{ debugInfo.audioTime }} s</div>
      <div>altura total: {{ totalAlturaVh }} vh</div>
      <div>slides cargadas: {{ slides.length }}</div>
      <hr style="border-color: #0f0; margin: 4px 0;" />
      <div style="font-weight: bold;">orden | esperada | real | diff</div>
      <div
        v-for="log in slideLog"
        :key="log.index + '-' + log.real"
        :style="{ color: log.ok ? '#0f0' : '#f55' }"
      >
        {{ log.orden }} | {{ log.esperada }}s | {{ log.real }}s | {{ log.diff }}s
      </div>
    </div>

    <!-- audio oculto -->
    <audio ref="audio" hidden></audio>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from "vue";
import HeaderInitial from "./components/HeaderInitial.vue";
import logoImage from "/src/assets/media_site/logo-web.png";
import logoNoFondo from "/src/assets/media_site/logo-no-fondo.png";

const API_BASE = import.meta.env.VITE_API_BASE;
const UPLOADS_BASE = import.meta.env.VITE_UPLOADS_BASE;

const logoSrc = ref(logoImage);
const showMenu = ref(false);
const showWelcome = ref(false);

const showOrden = import.meta.env.VITE_SHOW_ORDEN === 'true'

const toggleMenu = () => {
  showMenu.value = !showMenu.value;
};

const toggleMedia = () => {
  if (showMedia.value) return;
  logoSrc.value = logoNoFondo;
  showWelcome.value = true;
};

const headerTitle = ref("");
const hoverTitle = ref(false);

const showMedia = ref(false);
const slides = ref([]);
const scrollY = ref(0);
const totalAlturaVh = ref(0);

// --- debug de scroll/sincronizacion ---
const showDebug = import.meta.env.VITE_SHOW_DEBUG === 'true';
const debugInfo = ref({
  slideActiva: null,
  ordenActiva: null,
  scrollY: 0,
  offsetEsperado: 0,
  audioTime: 0,
});

// --- tracking de duracion real vs esperada por slide ---
const slideLog = ref([]); // historial de las ultimas transiciones
let slideActualIdx = null;
let tiempoEntradaReal = null; // Date.now() cuando entro a la slide actual
let tiempoEntradaAudio = null; // audio.currentTime cuando entro

// --- dev-orden: refs a cada image-box + opacidad calculada ---
const boxRefs = ref([]);
const ordenOpacity = ref([]);
const ordenActivo = ref(null);

const setBoxRef = (el, index) => {
  if (el) boxRefs.value[index] = el;
};

const updateOrdenVisibility = () => {
  const vh = window.innerHeight;
  let bestIndex = null;
  let bestRatio = 0;
  const nuevaOpacidad = [];

  boxRefs.value.forEach((el, i) => {
    if (!el) return;
    const rect = el.getBoundingClientRect();
    const visible = Math.min(rect.bottom, vh) - Math.max(rect.top, 0);
    const ratio = Math.max(0, visible) / vh;
    nuevaOpacidad[i] = ratio > 0.05 ? Math.min(1, ratio * 1.3) : 0;
    if (ratio > bestRatio) {
      bestRatio = ratio;
      bestIndex = i;
    }
  });

  ordenOpacity.value = nuevaOpacidad;
  ordenActivo.value = bestIndex;
};

const audio = ref(null);
const marker = ref(null);
const timer = ref(null);
const timeline = ref(null);

const onMediaEnter = (title) => {
  hoverTitle.value = true;
  headerTitle.value = title || "";
};

const onMediaLeave = () => {
  hoverTitle.value = false;
  headerTitle.value = "";
};
const audioSrc = ref("");

// import audioSrc from '@/assets/media_audios/00100101.mp3';
// const audioElio = audioSrc;
// console.log("y aqui lo vemos Elio", audioElio);

const cargarAudio = async () => {
  const res = await fetch(`${API_BASE}/get_audio.php`);
  const data = await res.json();
  console.log("Audio data:", data);
  if (data.success) {
    audio.value.src = data.audio_url;
    console.log("Audio URL:", audioSrc.value);
  }

  // audio.value.src = audioElio;
  // console.log("esto funciona");
};

const toggleScroll = async () => {
  showWelcome.value = false;
  showMedia.value = true;

  await cargarAudio();
  await cargarMedia();

  audio.value.load();
  audio.value.play().catch((err) => console.log("Autoplay bloqueado:", err));
};

const cargarMedia = async () => {
  const res = await fetch(`${API_BASE}/media.php`);
  const data = await res.json();

  let acumulado = 0;
  const slidesOrdenadas = [...data.slides].sort((a, b) => a.orden - b.orden);
  slides.value = slidesOrdenadas.map((slide) => {
    const offsetTop = acumulado;
    acumulado += Number(slide.height_vh) || 0;
    return {
      ...slide,
      offsetTop,
      backgroundUrl: slide.background
        ? `${UPLOADS_BASE}/${slide.background}`
        : null,
      elementos: slide.elementos.map((el) => ({
        ...el,
        fileUrl: `${UPLOADS_BASE}/${el.filename}`,
      })),
    };
  });

  totalAlturaVh.value = acumulado;
  console.log("Slides cargados:", slides.value.length, "| altura total vh:", acumulado);
};

const handleScroll = (event) => {
  event.preventDefault();
  if (!audio.value) return;

  const delta = event.deltaY > 0 ? 5 : -5;
  const newTime = Math.max(
    0,
    Math.min(audio.value.duration, audio.value.currentTime + delta),
  );

  audio.value.currentTime = newTime;
  actualizarUI(newTime);
};

const actualizarUI = (current) => {
  if (!audio.value || !marker.value || !timer.value) return;

  const percent = (current / audio.value.duration) * 100;
  marker.value.style.left = `${percent}%`;

  const hours = Math.floor(current / 3600);
  const mins = Math.floor((current % 3600) / 60)
    .toString()
    .padStart(2, "0");
  const secs = Math.floor(current % 60)
    .toString()
    .padStart(2, "0");
  timer.value.textContent = `${hours.toString().padStart(2, "0")}:${mins}:${secs}`;

  scrollY.value = (current / audio.value.duration) * totalAlturaVh.value;

  if (showDebug) {
    const idx = slides.value.findIndex(
      (s) => scrollY.value >= s.offsetTop && scrollY.value < s.offsetTop + Number(s.height_vh)
    );

    // deteccion de cambio de slide -> cerrar medicion de la anterior
    if (idx !== slideActualIdx && idx >= 0) {
      if (slideActualIdx !== null && tiempoEntradaReal !== null) {
        const s = slides.value[slideActualIdx];
        const duracionEsperada =
          (Number(s.height_vh) / totalAlturaVh.value) * audio.value.duration;
        const duracionReal = (Date.now() - tiempoEntradaReal) / 1000;
        const diff = duracionReal - duracionEsperada;

        slideLog.value.unshift({
          orden: s.orden,
          index: slideActualIdx,
          esperada: duracionEsperada.toFixed(2),
          real: duracionReal.toFixed(2),
          diff: diff.toFixed(2),
          ok: Math.abs(diff) < 0.5,
        });
        if (slideLog.value.length > 8) slideLog.value.pop();

        console.log(
          `Slide orden=${s.orden}: esperada=${duracionEsperada.toFixed(2)}s | real=${duracionReal.toFixed(2)}s | diff=${diff.toFixed(2)}s`
        );
      }
      slideActualIdx = idx;
      tiempoEntradaReal = Date.now();
      tiempoEntradaAudio = current;
    }

    debugInfo.value = {
      slideActiva: idx >= 0 ? idx : "fuera de rango",
      ordenActiva: idx >= 0 ? slides.value[idx].orden : "-",
      scrollY: scrollY.value.toFixed(1),
      offsetEsperado: idx >= 0 ? slides.value[idx].offsetTop.toFixed(1) : "-",
      audioTime: current.toFixed(1),
    };
  }
};

watch(scrollY, () => {
  nextTick(updateOrdenVisibility);
});

onMounted(() => {
  const audioEl = audio.value;
  if (!audioEl) return;

  audioEl.addEventListener("timeupdate", () => {
    actualizarUI(audioEl.currentTime);
  });

  audioEl.addEventListener("ended", () => {
    audioEl.currentTime = 0;
    audioEl.play();
    marker.value.style.left = "0%";
    timer.value.textContent = "00:00:00";
    scrollY.value = 0;
  });

  document.addEventListener("wheel", handleScroll, { passive: false });

  if (timeline.value) {
    timeline.value.addEventListener("click", (e) => {
      const rect = timeline.value.getBoundingClientRect();
      const clickX = e.clientX - rect.left;
      const percentage = clickX / rect.width;

      if (isNaN(audioEl.duration) || audioEl.duration === 0) return;

      audioEl.currentTime = percentage * audioEl.duration;
      marker.value.style.left = `${percentage * 100}%`;

      scrollY.value = percentage * totalAlturaVh.value;
    });
  }
});
</script>

<style>
/* reset global */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html,
body {
  margin: 0;
  padding: 0;
  height: 100%;
  overflow: hidden;
}
</style>

<style scoped>
.app-container {
  background-color: rgb(0, 0, 0);
  width: 100vw;
  height: 100vh;
  overflow: hidden;
  position: relative;
}

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
  transition: opacity 0.6s ease;
}

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
  transition:
    opacity 0.8s ease,
    transform 0.8s ease;
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

h2,
span {
  font-family: Arial, Helvetica, sans-serif;
}

.menu-oculto {
  background-color: rgb(12, 12, 12);
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
  z-index:;
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
}

.menu a {
  text-decoration: none;
  color: rgb(238, 231, 231);
  font-weight: bold;
  font-size: 1.1em;
  letter-spacing: 3px;
  font-family:
    system-ui,
    -apple-system,
    BlinkMacSystemFont,
    "Segoe UI",
    Roboto,
    Oxygen,
    Ubuntu,
    Cantarell,
    "Open Sans",
    "Helvetica Neue",
    sans-serif;
}

.timer {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0);
  color: white;
  padding: 10px 20px;
  border-radius: 5px;
  font-size: 2.3em;
  font-family: Arial, Helvetica, sans-serif;
  font-weight: 800;
  z-index: 1000;
}

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
  top: 0;
  left: 0;
  width: 100%;
  display: block;
  /* display: flex;
  align-items: center;
  justify-content: center; */
  transition: transform 0.6s ease-out;
  /* border-bottom: 2px solid green;
  border-top: 2px solid red; */
  pointer-events: none;
  overflow: hidden;
  
}

/* elementos posicionados dentro del slide */
.elemento {
  position: absolute;
  pointer-events: all;
}

.elemento img,
.elemento video {
  width: 100%;
  height: auto;
  display: block;
  object-fit: contain;
  /* opacity: 0.5;
  transition: opacity 0.6s ease, transform 0.6s ease; */
}

.elemento img:hover,
.elemento video:hover {
  /* opacity: 1; */
}

.media-description {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 0 0 12px 12px;
  color: white;
  padding: 14px 20px;
  font-size: 0.9em;
  text-align: center;
  opacity: 0;
  transition: opacity 0.4s ease;
  pointer-events: none;
}

.elemento:hover .media-description {
  opacity: 1;
}

.orden-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 9999;
}

.dev-orden {
  position: fixed;
  top: 130px;
  left: 50px;
  background: rgb(248, 5, 5);
  color: rgb(223, 216, 216);
  font-size: 2rem;
  font-weight: 900;
  padding: 4px 12px;
  border-radius: 4px;
  pointer-events: none;
  font-family: monospace;
  transition: opacity 0.2s linear;
}

.dev-orden.active {
  background: #f52206;
  box-shadow: 0 0 16px #e9e391;
}

.debug-panel {
  position: fixed;
  bottom: 90px;
  left: 10px;
  background: rgba(0, 0, 0, 0.85);
  color: #0f0;
  font-family: monospace;
  font-size: 0.8rem;
  padding: 10px 14px;
  border-radius: 6px;
  z-index: 10000;
  line-height: 1.5;
  pointer-events: none;
}

</style>