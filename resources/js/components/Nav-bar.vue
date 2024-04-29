<template>
    <div class="navbar-container">
        <div class="buttons">
            <div class="logo">
                <ion-icon name="logo-yahoo"></ion-icon>
            </div>

            <button class="Home" :class="{ 'active': activeRoute === '/home' }" @click="$router.push('/home')">
                <div class="button-content">
                    <ion-icon class="button-icon" :name="isHomeFilled ? 'home' : 'home-outline'"></ion-icon>
                    <span class="button-text">Home</span>
                </div>
            </button>

            <button class="Notifications" :class="{ 'active': activeRoute === '/notifications' }" @click="$router.push('/notifications')">
                <div class="button-content">
                    <div class="icon-container">
                        <ion-icon class="button-icon" :name="isNotificationsFilled ? 'notifications' : 'notifications-outline'"></ion-icon>
                        <div class="notification-count" v-if="unreadNotificationsCount">{{ unreadNotificationsCount }}</div>
                    </div>
                    <span class="button-text">Notifications</span>
                </div>
            </button>

            <button class="Messages" :class="{ 'active': activeRoute === '/messages' }" @click="$router.push('/messages')">
                <div class="button-content">

                    <div class="icon-container">
                        <ion-icon class="button-icon" :name="isMessagesFilled ? 'mail' : 'mail-outline'"></ion-icon>
                        <div class="messages-count" v-if="unreadMessagesCount">{{ unreadMessagesCount }}</div>
                    </div>

                    <span class="button-text">Messages</span>
                </div>
            </button>

            <button class="Bookmarks" :class="{ 'active': activeRoute === '/bookmarks' }" @click="$router.push('/bookmarks')">
                <div class="button-content">
                    <ion-icon class="button-icon" :name="isBookmarksFilled ? 'bookmark' : 'bookmark-outline'"></ion-icon>
                    <span class="button-text">Bookmarks</span>
                </div>
            </button>

            <button class="Profile" :class="{ 'active': activeRoute.includes('/profile') }" @click="openProfile(user.UserTag)">
                <div class="button-content">
                    <ion-icon class="button-icon" :name="isProfileFilled ? 'person' : 'person-outline'"></ion-icon>
                    <span class="button-text">Profile</span>
                </div>
            </button>

            <button class="Tweet" @click="() => TogglePopup('TweetTrigger')">
                <div class="button-content">
                    <ion-icon class="button-icon" name="logo-yahoo"></ion-icon>
                    <span class="button-text">Post</span>
                </div>
            </button>

        </div>
        <div class="profile" @click.stop="toggleProfilePopup">
            <div class="user-img" v-if="user">
                <img :src="'/storage/' + user.ProfilePicture">
            </div>
            <div class="user-info" v-if="user">
                <p class="username">{{ user.Name }}</p>
                <p class="usertag">{{ user.UserTag }}</p>
            </div>
            <div class="more-icon">
                <ion-icon name="ellipsis-horizontal"></ion-icon>
            </div>
            <div class="profile-popup" v-if="isPopupVisible">
                <div class="popup-content">
                    <button class="logout-btn" @click="logoutUser">Logout</button>
                    <button class="profile-btn" @click="openProfile(user.UserTag)">Profile</button>
                </div>
            </div>
        </div>
    </div>
    <Popup v-if="popupTriggers.TweetTrigger" :TogglePopup="() => TogglePopup('TweetTrigger')">
        <div class="create-tweet-or-comment-popup" v-if="user">
            <div class="top">
                <div class="left-side-popup">
                    <img :src="'/storage/' + user.ProfilePicture">
                </div>
                <div class="right-side-popup">
                    <div class="userinfo-popup">
                        <p class="username">{{ user.Name }}</p>
                        <p class="usertag">{{ user.UserTag }}</p>
                    </div>
                    <div class="tweet-input-container">
                        <textarea v-model="tweet_text_inputnav" id="tweet-input-nav" class="tweet-input" rows="1" placeholder="What's happening?!" @input="autoSize" ref="tweetInputnav" maxlength="255"></textarea>
                    </div>
                    <div class="tweet-image-preview">
                        <img :src="previewImagenav" v-if="previewImagenav">
                        <div class="preview-cover" v-if="previewImagenav">
                            <div class="preview-close" @click="removeImage">
                                <ion-icon class="preview-close-icon" name="close"></ion-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bottom">
                <div class="buttons">
                    <button class="tweet-btn"><input type="file" accept="image/png, image/gif, image/jpeg, video/mp4,video/x-m4v,video/*" id="tweet-img-input-tweetnav" @change="onImageChangenav" hidden><label for="tweet-img-input-tweetnav" class="tweet-img-label"><ion-icon name="images-outline" class="create-tweet-icon"></ion-icon></label></button>
                    <!-- <button class="tweet-btn"><ion-icon name="happy-outline" class="create-tweet-icon"></ion-icon></button> -->
                    <!-- <button class="tweet-btn" @click.stop="TogglePopup('MentionTrigger', 'comment')"><ion-icon name="at-sharp" class="create-tweet-icon"></ion-icon></button> -->
                </div>
                <button class="popup-button"  @click="createTweetnav" :disabled="buttonDisabled || !tweet_text_inputnav && !previewImagenav">Post</button>
            </div>
        </div>
    </Popup>
    <!-- <Popup v-if="popupTriggers.MentionTrigger" :TogglePopup="() => TogglePopup('MentionTrigger')">
        <div class="mention-popup">
            <p class="title">Mention</p>
            <div class="search-input-container">
                <input 
                    type="text"
                    id="mention-input"
                    class="search-input" 
                    maxlength="30" 
                    placeholder="Search"
                    :class="{ 'focused': isInputFocused }"
                    @input="handleMentionInput"
                    @focus="inputFocus"
                    @blur="inputBlur"
                    v-model="mentionSearch"
                >
                <ion-icon name="search-outline" class="search-icon"></ion-icon>
                <button class="close-icon-btn" :class="{ 'focused': isInputFocused }">
                    <ion-icon name="close-circle-sharp" class="close-icon"></ion-icon>
                </button>
            </div>
            <div class="user-suggestions">
                <div class="user" v-for="user in filteredUsers" :key="user.UserID" @click="insertMention(user)">
                    <div class="user-img">
                        <img @click="openProfile(user.UserTag)" :src="'/storage/' + user.ProfilePicture">
                    </div>
                    <div class="user-info">
                        <p class="username">{{ user.Name }}</p>
                        <p class="usertag">{{ user.UserTag }}</p>
                    </div>
                </div>
                <div class="no-users" v-if="filteredUsers.length === 0">
                    <p>No users found 🌵</p>   
                </div>
            </div>
        </div>
    </Popup> -->
</template>

<script>
    import { ref } from 'vue';
    import Popup from './Popup.vue';
    import { useStore } from 'vuex';
    import { useRouter } from 'vue-router';
    import axios from 'axios';
    import { mapState } from 'vuex';
    export default{
        name: 'NavBar',
        components: {
            Popup,
        },
        computed:{
            ...mapState(['user']),
        },
        data(){
            return {
                users: [],
                tweets: [],
                isPopupVisible: false,
                buttonDisabled: false,
                isInputFocused: false,
                activeRoute: '',
                isHomeFilled: false,
                isNotificationsFilled: false,
                isMessagesFilled: false,
                isBookmarksFilled: false,
                isProfileFilled: false,
                unreadNotificationsCount: '',
                unreadMessagesCount: '',
            }
        },
        setup(){
            const tweet_text_input = ref('');
            const tweet_text_inputnav = ref('');
            const tweetImagenav = ref(null);
            const previewImagenav = ref(null);
            const router = useRouter();
            const store = useStore();
            // const mentionSearch = ref('');
            // const filteredUsers = ref([]);
            const tweetInputnav = ref(null);


            const popupTriggers = ref({
                TweetTrigger: false,
                // MentionTrigger: false,
            });
            const TogglePopup = (trigger) => {
                popupTriggers.value[trigger] = !popupTriggers.value[trigger]
                tweetImagenav.value=null;
                previewImagenav.value=null;
                // if (trigger === 'MentionTrigger') {
                //     mentionSearch.value = '';
                //     filteredUsers.value = [];
                // }
                if (trigger === 'TweetTrigger') {
                    tweet_text_inputnav.value='';
                }
            }
            const logoutUser = async () => {
                try {
                    await store.dispatch('logout');
                    router.push('/');
                } catch (error) {
                    console.error(error);
                }
            };
            return {
                popupTriggers,
                TogglePopup,
                logoutUser,
                tweet_text_inputnav,
                tweet_text_input,
                tweetImagenav,
                previewImagenav,
                // mentionSearch,
                // filteredUsers,
                tweetInputnav,
            }
        },
        methods: {
            inputFocus() {
                this.isInputFocused = true;
            },
            inputBlur() {
                this.isInputFocused = false;
            },
            // insertMention(user) {
            //     const cursorPosition = this.tweetInputnav.value.selectionStart;

            //     const textarea = this.tweetInputnav;

            //     if (!textarea) {
            //         console.error("Textarea not found");
            //         return;
            //     }

            //     const mentionTag = `${user.UserTag}`;
            //     const cursorPos = textarea.selectionStart;
            //     const textBeforeCursor = textarea.value.substring(0, cursorPos);
            //     const textAfterCursor = textarea.value.substring(cursorPos);

            //     this.tweet_text_inputnav = textBeforeCursor + mentionTag + textAfterCursor;

            //     this.TogglePopup('MentionTrigger');

            //     textarea.setSelectionRange(cursorPosition + mentionTag.length, cursorPosition + mentionTag.length);
            // },
            // handleMentionInput() {
            //     if (this.mentionSearch.length > 0) {
            //         this.filteredUsers = this.users.filter(user => {
            //             const searchInputLower = this.mentionSearch.toLowerCase();
            //             const userTagLower = user.UserTag.toLowerCase();
            //             return userTagLower.includes(searchInputLower);
            //         });
            //     } else {
            //         this.filteredUsers = [];
            //     }
            // },
            toggleProfilePopup() {
                this.isPopupVisible = !this.isPopupVisible;
                setTimeout(() => { this.isPopupVisible = false; }, 10000);
            },
            autoSize() {
                const maxRows = 5;
                const textarea = this.$refs.tweetInputnav;
                textarea.style.height = 'auto';
                const customLineHeight = 1;
                const maxHeight = maxRows * customLineHeight * parseFloat(getComputedStyle(textarea).fontSize);

                if (textarea.scrollHeight <= maxHeight) {
                    textarea.style.height = textarea.scrollHeight + 'px';
                } else {
                    textarea.style.height = maxHeight + 'px';
                }
            },
            openProfile(tag){
                const NoSymbolTag = tag.replace(/^@/, '');
                this.$router.push({ name: 'Profile', params: { UserTag : NoSymbolTag } });
                console.log(tag);
                this.isPopupVisible = false;
            },
            onImageChangenav(event) {
                this.tweetImagenav = event.target.files[0];
                if (this.tweetImagenav) {
                    this.previewImagenav = URL.createObjectURL(this.tweetImagenav);
                } else {
                    this.previewImagenav = null;
                }
            },
            removeImage(){
                this.tweetImagenav = null;
                this.previewImagenav = null;
            },
            async createTweetnav() {
                if (this.buttonDisabled) {
                    return;
                }
                const formData = new FormData();
                formData.append('tweetText', this.tweet_text_inputnav);
                if (this.tweetImagenav) {
                    formData.append('tweetImage', this.tweetImagenav);
                }
                try {
                    this.buttonDisabled = true;

                    const response = await this.$axios.post('/api/tweets', formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                        },
                    });
                    this.tweet_text_inputnav = '';
                    this.tweetImagenav = null;
                    this.previewImagenav = null;
                    this.popupTriggers.TweetTrigger = false;
                    const textarea = this.tweetInputnav;
                    textarea.style.height = 'auto';
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 2000);
                } catch (error) {
                    console.error(error);
                    this.buttonDisabled = false;
                }
            },
            // getAllUsersMention() {
            // axios
            //     .get('/api/all-users-mention')
            //     .then(response => {
            //         this.users = response.data;
            //     })
            //     .catch(error => {
            //         console.error(error);
            //     });
            // },
            getUnreadNotificationCount(){
            axios
                .get('/api/get-unread-notification-count')
                .then(response => {
                    this.unreadNotificationsCount = response.data.unreadCount;
                })
                .catch(error => {
                    console.error(error);
                })
            },

            getUnreadMessagesCount() {
            axios.get('/api/get-unread-messages-count')
                .then(response => {
                    this.unreadMessagesCount = response.data.unreadCount;
                })
                .catch(error => {
                    console.error(error);
                });
            },
        },
            
        async mounted() {
            await this.$store.dispatch('initializeApp');
            this.activeRoute = this.$route.path;
            this.isHomeFilled = this.activeRoute.includes('/home');
            this.isNotificationsFilled = this.activeRoute.includes('/notifications');
            this.isMessagesFilled = this.activeRoute.includes('/messages');
            this.isBookmarksFilled = this.activeRoute.includes('/bookmarks');
            this.isProfileFilled = this.activeRoute.includes('/profile');

            this.$router.afterEach((to) => {
                this.activeRoute = to.path;
                this.isHomeFilled = this.activeRoute.includes('/home');
                this.isNotificationsFilled = this.activeRoute.includes('/notifications');
                this.isMessagesFilled = this.activeRoute.includes('/messages');
                this.isBookmarksFilled = this.activeRoute.includes('/bookmarks');
                this.isProfileFilled = this.activeRoute.includes('/profile');
            });

            if (this.$route.name != 'Index' && this.$route.name != 'Register' && this.$route.name != 'Login' && this.$route.name != 'UpdatePassword') {
                // this.getAllUsersMention();
                this.unreadNotificationsIntervalId = setInterval(
                    this.getUnreadNotificationCount,
                    10000
                );
            }
            if (this.$route.name != 'Index' && this.$route.name != 'Register' && this.$route.name != 'Login' && this.$route.name != 'UpdatePassword') {
                // this.getAllUsersMention();
                this.unreadMessagesIntervalId = setInterval(
                    this.getUnreadMessagesCount,
                    10000
                )
            }
        },
        beforeDestroy() {
            clearInterval(this.unreadNotificationsIntervalId && this.unreadMessagesIntervalId);
        },
        beforeUnmount() {
            clearInterval(this.unreadNotificationsIntervalId && this.unreadMessagesIntervalId);
        },
    }
</script>

<style lang="scss" scoped>
    .navbar-container{
        width: 3/12*100%;
        height: 100vh;
        display:flex;
        flex-direction: column;
        justify-content: space-between;
        position:fixed;
        box-sizing: border-box;
        padding-left:100px;
        z-index:10;
        border-right:solid 1px #2F3336;
        background:white;
        color:black;
    }
    .buttons{
        width:90%;
        display: flex;
        flex-direction: column;
        gap:10px;
        box-sizing: border-box;
        .logo{
            width: 100%;
            height:50px;
            display:flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            background-color:white;
            cursor: pointer;
        }
        button{
            width: max-content;
            height:50px;
            padding:0 30px 0 15px;
            display:flex;
            align-items: center;
            color:black;
            background-color:white;
            border:none;
            border-radius: 50px;
            transition: color 0.3s;
            cursor: pointer;
            &:hover{
                background-color: #e8dddd;
            }
            .button-content {
                display: flex;
                gap:15px;
                align-items: center;
                justify-content: space-between;
                .button-icon{
                    font-size:24px;
                    --ionicon-stroke-width: 40px;
                }
                .button-text{
                    font-size:18px;
                }
            }
        }
        .Notifications{
            .icon-container{
                position:relative;
                width:auto;
                height:auto;
                .notification-count{
                    position:absolute;
                    bottom:0px;
                    right:-4px;
                    background-color: #1da1f2;
                    border-radius: 50%;
                    font-size:12px;
                    width:16px;
                    height:16px;
                    color:white;
                    display:flex;
                    align-items: center;
                    justify-content: center;
                }
            }
        }
        .Messages{
            .icon-container{
                position:relative;
                width:auto;
                height:auto;
                .messages-count{
                    position:absolute;
                    bottom:0px;
                    right:-4px;
                    background-color: #1da1f2;
                    border-radius: 50%;
                    font-size:12px;
                    width:16px;
                    height:16px;
                    color:white;
                    display:flex;
                    align-items: center;
                    justify-content: center;
                }
            }
        }
        .Tweet{
            width: 100%;
            background-color:#1da1f2;
            color: white;
            justify-content: center;
            font-weight: bold;
            &:hover{
                background-color: #2394db;
            }
        }
    }
    .profile{
        width:100%;
        display: flex;
        flex-direction: row;
        align-items: center;
        cursor: pointer;
        box-sizing: border-box;
        padding: 10px;
        margin-bottom:10px;
        font-size: 16px;
        border-radius: 50px;
        background:none;
        position:relative;
        transition: all 0.3s;
        .user-img{
            width: auto;
            height:100%;
            display:flex;
            align-items: center;
            justify-content: center;
            img{
                width:50px;
                height:50px;
                border-radius:50%;
                background-color: rgb(255, 255, 255);
            }
        }
        .user-info{
            width: 60%;
            height:100%;
            display:flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            gap:10px;
            padding-left:10px;
            .username{
                max-width:100%;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
                font-weight: bold;
                font-size: 14px;
                margin:0;
            }
            .usertag{
                max-width:100%;
                overflow: hidden;
                white-space: nowrap;
                color: #6A6F74;
                font-size: 14px;
                margin:0;
            }
        }
        .more-icon{
            width: 20%;
            height:100%;
            display:flex;
            align-items: center;
            justify-content: center;
        }
        &:hover{
            background-color: #e8dddd;
        }
    }
    .profile-popup{
        width:100%;
        height:100px;
        position:absolute;
        top:-100px;
        left:0px;
        z-index:99;

        .popup-content{
            height:100%;
            width:100%;
            border-radius:25px;
            border:1px solid #2F3336;
            background-color: white;
            box-shadow: 0 0 5px #1da1f2;
            transition: all 0.3s;
            display:flex;
            flex-direction: column;
            button{
                width:100%;
                height:50%;
                border:none;
                background: none;
                color:black;
                cursor:pointer;
                font-size:16px;
                transition: all 0.3s;
                &:hover{
                    background-color: #e8dddd;
                }
            }
            .logout-btn{
                border-top-left-radius: 25px;
                border-top-right-radius: 25px;
            }
            .profile-btn{
                border-bottom-left-radius: 25px;
                border-bottom-right-radius: 25px;
            }
        }
    }

    .active {
        .button-content{
            color: #1da1f2;
            .icon-container{
                .notification-count{
                    background-color: white;
                    color:#1da1f2;
                }
                .messages-count{
                    background-color: white;
                    color:#1da1f2;
                }
            }
        }
    }
    @media (max-width: 1250px) {
        .navbar-container {
            position: fixed;
            bottom: 0;
            width: 100px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            // background-color: black;
            z-index: 99;
        }
        .buttons {
            width:auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex-wrap: nowrap;
        }
        .buttons button {
            width: 50px;
            height: 50px;
            border-radius:50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        .buttons span {
            display: none;
        }
        .profile{
            width: 50px;
            height: 50px;
            border-radius:50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 35px;
            .user-info{
                display:none;
            }
            .more-icon{
                display:none;
            }
        }
        .profile-popup{
            width:250px;
            height:100px;
            position:absolute;
            top:-100px;
            left:10px;

            .popup-content{
                button{
                    width:100%;
                    height:50%;
                    font-size:16px;
                }
            }
        }
    }
    @media (max-width: 500px) {
        .navbar-container {
            position: fixed;
            bottom: 0;
            width: 100%;
            height:50px;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            box-sizing: border-box;
            padding: 5px !important;
            z-index: 10;
            border-top: 1px solid #2F3336;
            border-right:none;
        }
        .buttons {
            width:100%;
            display: flex;
            flex-direction: row;
            justify-content: space-evenly;
        }
        .Home, .Notifications, .Messages, .Bookmarks, .Profile{
            display:flex;
            align-items: center;
            justify-content: center;
            height:40px !important;
            width:40px !important;
            border-radius: 50% !important;
        }
        .logo, .profile{
            display:none !important;
        }
        .Tweet{
            width:50px !important;
            height:50px !important;
            position:fixed;
            right:12px;
            bottom:55px;
            border-radius:50% !important;
        }
    }
</style>