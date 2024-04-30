<template>
    <div class="people">
        <div class="content">
            <PeopleContent></PeopleContent>
        </div>
    </div>
</template>

<script>
    import PeopleContent from './People-content.vue';
    import Popup from '../Popup.vue';

    export default {
        name: 'People',
        
        components: {
            PeopleContent,
            Popup,
        },
    }
</script>

<style lang="scss" scoped>
    .people{
        font-family: Arial, sans-serif;
        margin: 0 auto;
        display:flex;
        flex-direction: row;
        box-sizing: border-box;
        .content{
            width: 100%;
        }
    }
    @media (max-width: 1250px) {
        .content{
            flex-grow: 1;
        }
    }
    @media (max-width: 1000px) {
        .content{
            flex-grow: 1;
        }
    }
    @media (max-width: 500px) {
        .content{
            width:100%
        }
    }
</style>