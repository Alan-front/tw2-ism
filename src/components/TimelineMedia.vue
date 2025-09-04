<template>
  
  
  
</template>

<script>
export default {
  data() {
    return {
      
    }
  },
  
  methods: {
    handleScroll() {
      // Solo procesar si no es un scroll automático
      if (this.isUserScrolling) return;
      
      // Indicar que el usuario está scrolleando
      this.isUserScrolling = true;
      
      // Actualizar la posición del marcador y timer basado en el scroll actual
      const container = this.$refs.container;
      const marker = this.$refs.marker;
      
      if (!container || !marker) return;
      
      // Calcular posición actual
      const progress = container.scrollTop / (container.scrollHeight - container.clientHeight);
      
      // Actualizar marcador
      marker.style.left = `${progress * 100}%`;
      
      // Actualizar timer
      this.updateTimerFromProgress(progress);
      
      // Reiniciar tiempo de auto-scroll después de 1 segundo sin interacción
      clearTimeout(this.userScrollTimeout);
      this.userScrollTimeout = setTimeout(() => {
        this.isUserScrolling = false;
      }, 1000);
    },
    
    updateTimerFromProgress(progress) {
      const elapsed = this.ANIMATION_DURATION * progress;
      this.$refs.timer.textContent = this.formatTime(elapsed);
      
      // Actualizar startTime
      this.startTime = Date.now() - elapsed;
    },
    
    formatTime(milliseconds) {
      const totalSeconds = Math.floor(milliseconds / 1000);
      const minutes = Math.floor(totalSeconds / 60);
      const seconds = totalSeconds % 60;
      return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    },
    
    initializeAnimation() {
      this.startTime = Date.now();
      this.lastTimestamp = performance.now();
      
      const container = this.$refs.container;
      const timeline = this.$refs.timeline;
      const marker = this.$refs.marker;
      
      // Animación de scroll fluido con control de tiempo
      const animate = (timestamp) => {
        // Calcular delta time para movimiento fluido independiente de framerate
        const deltaTime = timestamp - this.lastTimestamp;
        this.lastTimestamp = timestamp;
        
        // Calcular tiempo transcurrido y progreso
        const currentTime = Date.now();
        const elapsed = currentTime - this.startTime;
        const progress = (elapsed % this.ANIMATION_DURATION) / this.ANIMATION_DURATION;
        
        // Actualizar posición del marcador
        marker.style.left = `${progress * 100}%`;
        
        // Actualizar timer
        this.$refs.timer.textContent = this.formatTime(elapsed % this.ANIMATION_DURATION);
        
        // Scroll automático con velocidad constante si el usuario no está scrolleando
        if (!this.isUserScrolling) {
          const maxScroll = container.scrollHeight - container.clientHeight;
          const targetScrollTop = maxScroll * progress;
          
          // Calcular la velocidad de scroll basada en el delta time
          const pixelsPerFrame = this.scrollSpeed * deltaTime;
          
          // Actualizar directamente la posición de scroll
          container.scrollTop = targetScrollTop;
        }
        
        // Continuar la animación
        this.animation = requestAnimationFrame(animate);
      }
      
      // Iniciar animación
      this.animation = requestAnimationFrame(animate);
      
      // Clicks en la timeline
      timeline.addEventListener('click', (e) => {
        const rect = timeline.getBoundingClientRect();
        const clickPercentage = ((e.clientX - rect.left) / rect.width);
        
        // Actualizar tiempo de inicio
        this.startTime = Date.now() - (this.ANIMATION_DURATION * clickPercentage);
        
        // Actualizar scroll con comportamiento suave
        const maxScroll = container.scrollHeight - container.clientHeight;
        container.scrollTo({
          top: maxScroll * clickPercentage,
          behavior: 'smooth'
        });
        
        // Marcar como interacción de usuario
        this.isUserScrolling = true;
        clearTimeout(this.userScrollTimeout);
        this.userScrollTimeout = setTimeout(() => {
          this.isUserScrolling = false;
        }, 1000);
      });
      
      // Clicks en contenedores
      container.addEventListener('click', (e) => {
        if (e.target.closest('.content-box')) {
          const clickedBox = e.target.closest('.content-box');
          const totalHeight = container.scrollHeight - container.clientHeight;
          const boxTop = clickedBox.offsetTop;
          const percentage = boxTop / totalHeight;
          
          // Actualizar tiempo de inicio
          this.startTime = Date.now() - (this.ANIMATION_DURATION * percentage);
          
          // Scroll con comportamiento suave
          container.scrollTo({
            top: boxTop,
            behavior: 'smooth'
          });
          
          // Marcar como interacción de usuario
          this.isUserScrolling = true;
          clearTimeout(this.userScrollTimeout);
          this.userScrollTimeout = setTimeout(() => {
            this.isUserScrolling = false;
          }, 1000);
        }
      });
    }
  },
  beforeUnmount() {
    // Limpiar animación
    if (this.animation) {
      cancelAnimationFrame(this.animation);
    }
    
    // Limpiar timeouts
    if (this.userScrollTimeout) {
      clearTimeout(this.userScrollTimeout);
    }
  }
}
</script>

<style>


</style>