<template>
    <div class="bookmarks">
        <div class="content">
            <BookmarksContent></BookmarksContent>
        </div>
        <div class="search">
            <Users></Users>
        </div>
    </div>
</template>

<script>
    import BookmarksContent from './Bookmarks-content.vue';
    import Popup from '../Popup.vue';
    import Users from '../Home/Users.vue';

    export default {
        name: 'Bookmarks',
        
        components: {
            BookmarksContent,
            Popup,
            Users,
        },
    }
</script>

<style lang="scss" scoped>
    .bookmarks{
        margin: 0 auto;
        display:flex;
        flex-direction: row;
        box-sizing: border-box;
    }
</style>