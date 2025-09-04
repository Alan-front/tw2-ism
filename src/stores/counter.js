<template>
  <Header></Header>
  <WelcomeContainer v-if="!showMedia" @show-media="showMedia = true"></WelcomeContainer>
  <TimelineMedia v-if="showMedia"></TimelineMedia>
</template>

<script>
import Header from './views/Header.vue';
import TimelineMedia from './components/TimelineMedia.vue';
import WelcomeContainer from './components/WelcomeContainer.vue';

export default {
  components: { 
    Header,
    TimelineMedia,
    WelcomeContainer
  },
  data() {
    return {
      showMedia: false
    }
  }
}
</script>

<style>
/* Global styles can remain here if needed */
</style>