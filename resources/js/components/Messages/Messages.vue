<template>
    <div class="messages">
        <div class="content">
            <MessagesContent></MessagesContent>
        </div>
    </div>
</template>

<script>
    import MessagesContent from './Messages-content.vue';
    import Popup from '../Popup.vue';

    export default {
        name: 'Messages',
        
        components: {
            MessagesContent,
            Popup,
        },
    }
</script>

<style lang="scss" scoped>
    .messages{
        font-family: Arial, sans-serif;
        margin: 0 auto;
        display:flex;
        flex-direction: row;
        .content{
            width: 100%;
            box-sizing: border-box;
        }
    }
</style>