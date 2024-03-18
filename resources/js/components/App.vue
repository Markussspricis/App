<template>
  <div class="app">
    <div class="navbar" v-if="showNavBar">
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
  computed: {
    // Compute whether to show the navigation bar based on the current route
    showNavBar() {
      // List of route names where the navigation bar should be hidden
      const hiddenRoutes = ['Index', 'Login', 'Register', 'UpdatePassword'];
      // Check if the current route name is in the hiddenRoutes list
      return !hiddenRoutes.includes(this.$route.name);
    },
  },
};
</script>
<style lang="scss" scoped>
  .app {
    font-family: Arial, sans-serif;
    margin: 0 auto;
    display: flex;
    flex-direction: row;
    box-sizing: border-box;
  }
</style>