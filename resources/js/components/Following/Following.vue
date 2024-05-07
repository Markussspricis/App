<template>
    <div class="following">
        <div class="content">
            <FollowingContent></FollowingContent>
        </div>
        <div class="search">
            <Users></Users>
        </div>
    </div>
</template>

<script>
    import FollowingContent from './Following-content.vue';
    import Users from '../Home/Users.vue';

    export default {
        name: 'Following',
        
        components: {
            FollowingContent,
            Users,
        },
    }
</script>

<style lang="scss" scoped>
    .following{
        margin: 0 auto;
        display:flex;
        flex-direction: row;
    }
</style>