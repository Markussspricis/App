<template>
    <div class="profile">
        <div class="content">
            <Profile></Profile>
        </div>
        <div class="search">
            <Users></Users>
        </div>
    </div>
</template>

<script>
    import Profile from './User-content.vue';
    import Users from '../Home/Users.vue';
    import Popup from '../Popup.vue';
    export default {
        name: 'User',
        components: {
            Profile,
            Users,
            Popup,
        },
    }
</script>

<style lang="scss" scoped>
    .profile{
        font-family: Arial, sans-serif;
        margin: 0 auto;
        display:flex;
        flex-direction: row;
        box-sizing: border-box;
    }
</style>