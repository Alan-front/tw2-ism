<template>
    <div class="welcome-container">
      <div class="welcome-content">
        <h1>Welcome to the Twoism audio guide.</h1>
        <p>Immerse yourself in a 60-minute sound 
          experience, crafted from sound fragments, 
          noise, and music taken from the Twoism 
          archives, selected by Damian and Alanise.</p>
        <button class="start-button" @click="$emit('show-media')">Click anywhere to start.</button>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    emits: ['show-media']
  }
  </script>
  
  <style>
  .welcome-container {
    height: calc(100vh - 9px);
    /* display: flex; */
    align-items: center;
    justify-content: left;
    background-color: rgb(54, 53, 53);
    color: white;
    padding: 40px;
  }
  
  .welcome-content {
  
    text-align: left;
    max-width: 800px;
    padding: 20px;
    /* border: 1px solid salmon; */
  }
  
  .start-button {
    margin-top: 20px;
    padding: 10px 20px;
    font-size: 1.2em;
    background-color: #4a4a4a;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  
  .start-button:hover {
    background-color: #666666;
  }

  p, button{
    font-size: 1.2em;
  }

  h1, p, button{
    font-family: Arial, Helvetica, sans-serif;
  }
  </style>