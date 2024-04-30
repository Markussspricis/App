<template>
  <div class="home">
    <div class="content">
      <Posts></Posts>
    </div>
    <div class="search">
      <Users></Users>
    </div>
  </div>
</template>

<script>
  import Posts from './Posts.vue';
  import Users from './Users.vue';
  import Popup from '../Popup.vue';

  export default {
    name: 'Home',
    
    components: {
      Posts,
      Users,
      Popup,
    },
  }
</script>

<style lang="scss" scoped>
  .home{
    font-family: Arial, sans-serif;
    margin: 0 auto;
    display:flex;
    flex-direction: row;
    box-sizing: border-box;
  }
</style>