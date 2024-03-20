<template>
    <div class="tweet">
        <div class="content">
            <PostsContent></PostsContent>
        </div>
        <div class="search">
            <Users></Users>
        </div>
    </div>
</template>

<script>
    import PostsContent from './Posts-content.vue';
    import Users from '../Home/Users.vue';
    import Popup from '../Popup.vue';
        export default {
        name: 'Posts',
        components: {
            PostsContent,
            Users,
            Popup,
        },
    }
</script>

<style lang="scss" scoped>
    .tweet{
        font-family: Arial, sans-serif;
        margin: 0 auto;
        display:flex;
        flex-direction: row;
        box-sizing: border-box;
    }
</style>