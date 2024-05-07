<template>
    <div class="followers">
        <div class="content">
            <FollowersContent></FollowersContent>
        </div>
        <div class="search">
            <Users></Users>
        </div>
    </div>
</template>

<script>
    import FollowersContent from './Followers-content.vue';
    import Users from '../Home/Users.vue';

    export default {
        name: 'Followers',
        
        components: {
            FollowersContent,
            Users,
        },
    }
</script>

<style lang="scss" scoped>
    .followers{
        margin: 0 auto;
        display:flex;
        flex-direction: row;
    }
</style>