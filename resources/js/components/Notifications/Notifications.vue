<template>
    <div class="notifications">
        <div class="content">
            <NotificationsContent></NotificationsContent>
        </div>
        <div class="search">
            <Users></Users>
        </div>
    </div>
</template>

<script>
    import NotificationsContent from './Notifications-content.vue';
    import Popup from '../Popup.vue';
    import Users from '../Home/Users.vue';
    export default {
        name: 'Notifications',
        components: {
            NotificationsContent,
            Popup,
            Users,
        },
    }
</script>

<style lang="scss" scoped>
    .notifications{
        font-family: Arial, sans-serif;
        margin: 0 auto;
        display:flex;
        flex-direction: row;
    }
</style>