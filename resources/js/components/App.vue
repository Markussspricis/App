<template>
  <div class="app">
    <div class="navbar" v-if="this.$route.name != 'Index' && this.$route.name != 'Register' && this.$route.name != 'Login' && this.$route.name != 'UpdatePassword'">
      <NavBar />
    </div>
    <div class="content">
      <router-view />
    </div>
  </div>
</template>

<script>
  import NavBar from './Nav-bar.vue';

  export default {
    name: 'App',

    components: {
      NavBar,
    },
    
    methods: {
      logout(e){
        e.preventDefault()
        this.$axios.get('/sanctum/csrf-cookie').then(response => {
          this.$axios.post('/api/logout')
          .then(response => {
            if (response.data.success){
              window.location.href = '/'
            } else {
              console.log(response);
            }
          })
          .catch(function (error){
            console.log(error);
          })
        })
      }
    },
  };
</script>

<style lang="scss" scoped>
  .app {
    margin: 0 auto;
    display: flex;
    flex-direction: row;
    box-sizing: border-box;
  }
</style>