<template>
  <header class="header">
    <div class="logo-div" @click="emit('toggle-menu')">
      <img :src="logoSrc" alt="Logo" />
    </div>
    <div class="imagotipe"></div>
  </header>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'


const props = defineProps({
  logoSrc: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['toggle-menu'])
</script>

<style>
:root{
  --altura-header: 80px;
  --altura-timeline: 60px;
}

.header {
  background-color: #ee0303;
  color: white;
  text-align: center;
  padding-top: 0px;
  height: var(--altura-header);
  position: fixed;
  
  min-width: 100vw;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
  z-index: 4;
  cursor: pointer; 
}

.logo-div {
  padding: 0;
  width: 300px;
  height: 42px;
  cursor: pointer;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 5px; 
}

.logo-div img {
  width: 100%;
  height: 90%;
  object-fit: contain;
  opacity: 9;
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
  z-index: 3; 
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
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}
</style>
