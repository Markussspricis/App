<template>
    <div class="pop-up">
        <div class="pop-up-inner">
            <ion-icon name="logo-yahoo" class="pop-up-icon"></ion-icon>
            <div class="pop-up-close-div">
                <button class="pop-up-close" @click="TogglePopup()"><ion-icon name="close" class="close"></ion-icon></button>
            </div>
            <slot></slot>
        </div>
    </div>
</template>
<script>
export default {
    name: 'Popup',
    props: ['TogglePopup'],
}
</script>
<style lang="scss" scoped>
.pop-up {
    font-family: Arial, sans-serif;
    position:fixed;
    top:0;
    bottom:0;
    left:0;
    right:0;
    z-index: 100;
    background:rgba(29, 161, 242, 0.1);
    display:flex;
    justify-content:center;
    align-items:center;
    .pop-up-inner {
        background:white;
        border-radius:20px;
        display:flex;
        flex-direction:column;
        gap:40px;
        justify-content: center;
        position: relative;
        .pop-up-close-div {
            position: absolute;
            top:10px;
            left:10px;
        }
        .pop-up-close {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: white;
            display:flex;
            justify-content: center;
            align-items: center;
            border: none;
            cursor: pointer;
        }
        .pop-up-close:hover{
            background-color: #e8dddd;
        }
        .pop-up-icon{
            color:black;
            position:absolute;
            font-size:25px;
            top:10px;
            left:50%;
            transform: translate(-50%);
        }
    }
}
.close{
    font-size: 25px;
    color:black;
}
@media (max-width: 500px) {
    .pop-up-inner {
        width:100%;
        margin:5px;
    }
}
@media (max-width: 450px) {
    .pop-up-inner {
        .pop-up-close-div{
            top:5px !important;
            left:5px !important;
        }
        .pop-up-close{
            width:30px!important;
            height:30px!important;
        }
        .pop-up-icon{
            font-size: 20px!important;
        }
    }
}
</style>