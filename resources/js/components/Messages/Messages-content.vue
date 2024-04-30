<template>
    <div class="messages-container">
        <div class="top-top">
            <button class="back-icon" @click="goBack">
                <ion-icon name="arrow-back-outline"></ion-icon>
            </button>
            <div class="top-top-top" v-if="user">
                <p class="title">Messages</p>
                <p class="user-tag">{{ user.UserTag }}</p>
            </div>
        </div>
        
        <div class="messages-sub">
            <div class="messages-main">
                <div class="main-text">Start a new conversation</div>
                <div class="search-people">
                    <div class="input-wrap">
                        <input v-model="searchInput" @input="handleSearchInput" class="Edit-Input" :class="{ 'focused': isInputFocused }" @focus="inputFocus" @blur="inputBlur" placeholder="Search usernames..." />
                        <ion-icon name="search-outline" class="search-icon"></ion-icon>
                    </div>
                    <div class="people-container">
                        <div class="person" v-for="person in foundUsers" :key="person.UserID" @click="handlePersonClick(person)" :class="{ 'highlighted': person.UserID === clickedPerson }">
                            <div class="user-info">
                                <img :src="'/storage/' + person.ProfilePicture" class="person-img">
                                <div class="person-info">
                                    <p class="username">{{ person.Name }}</p>
                                    <p class="usertag">{{ person.UserTag }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="next-btn" :disabled="!clickedPerson" @click="startConversation">Start conversation</button>
            </div>
            
            <div class="messages-right">
                <div class="right-text">Your conversations</div>
                <div class="conversations">
                    <div class="person" v-for="conversation in userConversations" :key="conversation.ConversationID" @click="openConversation(conversation)">
                        <div class="user-info">
                            <img :src="'/storage/' + getUserProfilePicture(conversation)" class="person-img">
                            <div class="person-info">
                                <p class="username">{{ getUserName(conversation) }}</p>
                                <p class="usertag">{{ getUserTag(conversation) }}</p>
                            </div>
                        </div>
                        <button class="delete-btn" @click.stop="setConversationToDelete(conversation)">
                            <ion-icon name="trash-bin-outline" class="delete-icon"></ion-icon>
                        </button>
                        <div v-if="conversation.unreadCount > 0">
                            <div class="message-count" @click.stop="markConversationAsRead(conversation)">{{ conversation.unreadCount }}</div>
                        </div>
                    </div>
                </div>
                <Conversation v-if="showConversation" :user="selectedUser" @closeConversation="handleCloseConversation"/>
            </div>
        </div>
    </div>
    <Popup v-if="popupTriggers.DeleteTrigger" :TogglePopup="() => TogglePopup('DeleteTrigger')">
        <div class="delete-popup">
            <h1 class="delete-title">Delete Conversation</h1>
            <p class="tweet-p">Are you sure you want to delete this conversation?</p>
            <div class="tweet-buttons">
                <button class="cancel-button" @click="TogglePopup('DeleteTrigger')">Cancel</button>
                <button class="delete-button" @click="deleteConversation">Delete</button>
            </div>
        </div>
    </Popup>
</template>

<script>
    import { ref, computed, onMounted } from 'vue';
    import Conversation from './Conversation.vue';
    import Popup from '../Popup.vue';
    import { useStore } from 'vuex';
    import axios from 'axios';

    export default {
        name: 'MessagesContent',

        components: {
            Conversation,
            Popup,
        },

        setup() {
            const $axios = axios.create({ baseURL: '/api/' });
            const searchInput = ref('');
            const userConversations = ref([]);
            const users = ref([]);
            const clickedPerson = ref(null);
            const selectedUser = ref(null);
            const showConversation = ref(false);
            const isInputFocused = ref(false);
            const store = useStore();
            const currentUser = computed(() => store.state.user);
            const popupTriggers = ref({});
            const conversationToDelete = ref(null);

            const TogglePopup = (trigger) => {
                popupTriggers.value[trigger] = !popupTriggers.value[trigger];
            };

            const setConversationToDelete = (conversation) => {
                conversationToDelete.value = conversation;
                if (conversation) {
                    TogglePopup('DeleteTrigger');
                } else {
                    console.error('Conversation is null or undefined');
                }
            };

            const deleteConversation = async () => {
                try {
                    const conversationId = conversationToDelete.value.ConversationID;
                    await $axios.delete(`/conversation/${conversationId}`);
                    fetchUserConversations(currentUser);
                    conversationToDelete.value = null;
                    TogglePopup('DeleteTrigger');
                } catch (error) {
                    console.error('Error deleting conversation:', error);
                }
            };

            const foundUsers = computed(() => {
                if (searchInput.value.length > 0) {
                    const searchInputLower = searchInput.value.toLowerCase();
                    return users.value.filter(user => {
                        const userTagLower = user.UserTag.toLowerCase();
                        return userTagLower.includes(searchInputLower);
                    });
                } else {
                    return [];
                }
            });

            const handlePersonClick = (person) => {
                clickedPerson.value = person.UserID;
                selectedUser.value = person;
            };

            const handleSearchInput = () => {
                if (searchInput.value.length === 0) {
                    clickedPerson.value = null;
                }
            };

            const fetchUserConversations = async (currentUser) => {
                try {
                    const response = await $axios.get('/user-conversations');
                    const conversations = response.data.conversations;
                    const filteredConversations = conversations.filter(conversation =>
                        conversation.user1.UserID === currentUser.value.UserID || conversation.user2.UserID === currentUser.value.UserID
                    );

                    await Promise.all(filteredConversations.map(async conversation => {
                        const response = await $axios.get(`/conversation/${conversation.ConversationID}/unread-count`);
                        conversation.unreadCount = response.data.unreadCount;
                    }));

                    userConversations.value = filteredConversations;
                    console.log("User conversations:", userConversations.value);
                } catch (error) {
                    console.error("Error fetching user's conversations:", error);
                }
            };

            const startConversation = () => {
                openConversation();
            };

            const openConversation = async (conversation) => {
                try {
                    if (conversation) {
                        if (conversation.user1 && conversation.user2) {
                            if (conversation.user1.UserID === currentUser.value.UserID) {
                                selectedUser.value = conversation.user2;
                            } else {
                                selectedUser.value = conversation.user1;
                            }
                        } else {
                            console.error("Invalid conversation object:", conversation);
                            return;
                        }
                    }

                    if (!conversation && selectedUser.value) {
                        const response = await $axios.get(`/check-conversation/${selectedUser.value.UserID}`);
                        if (response.data.conversationExists) {
                            await fetchConversation(selectedUser.value.UserID);
                            await markConversationAsRead(selectedUser.value.ConversationID);
                        } else {
                            await createConversation(selectedUser.value.UserID);
                        }
                    } else if (conversation) {
                        await markConversationAsRead(conversation.ConversationID);
                        conversation.unreadCount = 0;

                        const conversationIndex = userConversations.value.findIndex(conv => conv.ConversationID === conversation.ConversationID);
                        if (conversationIndex !== -1) {
                            userConversations.value[conversationIndex].unreadCount = 0;
                        }
                    }

                    showConversation.value = true;

                } catch (error) {
                    console.error(error);
                }
            };

            const markConversationAsRead = async (conversationId) => {
                try {
                    const response = await $axios.post(`/conversation/${conversationId}/mark-as-read`);
                    const conversationUnreadCount = response.data.conversationUnreadCount;

                } catch (error) {
                    console.error('Error marking conversation as read:', error);
                }
            };

            const fetchConversation = async (userId) => {
                try {
                    const response = await $axios.get(`/conversation/${userId}`);
                    if (response.data.conversation) {
                        console.log("Conversation fetched successfully");
                    } else {
                        console.error("Failed to fetch conversation");
                    }
                } catch (error) {
                    console.error("Error fetching conversation:", error);
                }
            };

            const createConversation = async (userId) => {
                try {
                    const response = await $axios.post('/create-conversation', { userId });
                    if (response.data.conversation) {
                        console.log("Conversation created successfully");
                        await fetchUserConversations(currentUser);
                    } else {
                        console.error("Failed to create conversation");
                    }
                } catch (error) {
                    console.error("Error creating conversation:", error);
                }
            };

            const handleCloseConversation = () => {
                clickedPerson.value = null;
                selectedUser.value = null;
                showConversation.value = false;
                searchInput.value = '';
            };

            const goBack = () => {
                window.history.length > 1 ? window.history.go(-1) : this.$router.push('/');
            };

            const inputFocus = () => {
                isInputFocused.value = true;
            };

            const inputBlur = () => {
                isInputFocused.value = false;
            };

            const getUserProfilePicture = (conversation) => {
                if (conversation.user1 && conversation.user2) {
                    const currentUserID = currentUser.value.UserID;
                    return conversation.user1.UserID !== currentUserID ? conversation.user1.ProfilePicture : conversation.user2.ProfilePicture;
                }
                return '';
            };

            const getUserName = (conversation) => {
                if (conversation.user1 && conversation.user2) {
                    const currentUserID = currentUser.value.UserID;
                    return conversation.user1.UserID !== currentUserID ? conversation.user1.Name : conversation.user2.Name;
                }
                return '';
            };

            const getUserTag = (conversation) => {
                if (conversation.user1 && conversation.user2) {
                    const currentUserID = currentUser.value.UserID;
                    return conversation.user1.UserID !== currentUserID ? conversation.user1.UserTag : conversation.user2.UserTag;
                }
                return '';
            };

            onMounted(async () => {
                try {
                    await fetchUserConversations(currentUser);
                    const response = await $axios.get('/allusers');
                    users.value = response.data;
                } catch (error) {
                    console.error("Error fetching users:", error);
                }
            });

            return {
                searchInput,
                foundUsers,
                clickedPerson,
                selectedUser,
                handlePersonClick,
                handleSearchInput,
                handleCloseConversation,
                goBack,
                inputFocus,
                inputBlur,
                showConversation,
                isInputFocused,
                $axios,
                openConversation,
                startConversation,
                user: computed(() => store.state.user),
                userConversations,
                getUserProfilePicture,
                getUserName,
                getUserTag,
                popupTriggers,
                TogglePopup,
                setConversationToDelete,
                deleteConversation,
            };
        },
    };
</script>

<style lang="scss" scoped>
    .messages-container{
        position: relative;
        height: 100vh;
        display: flex;
        flex-direction: column;
        border-right: 1px solid #2F3336;
        .messages-sub{
            display: flex;
            flex-direction: row;
            height: 100vh;
            .messages-main{
                width: 40%;
                display:flex;
                flex-direction:column;
                box-sizing: border-box;
                border-right: 1px solid #2F3336;
                .main-text{
                    color: black;
                    font-size: 28px;
                    font-weight: bold;
                    display: flex;
                    margin-top: 30px;
                    justify-content: center;
                }
                .search-people{
                    padding-top:20px;
                    .input-wrap{
                        height:60px;
                        width:100%;
                        display:flex;
                        align-items: center;
                        background-color:rgba($color: white, $alpha: 0.8);
                        backdrop-filter: blur(5px);
                        position:sticky;
                        top:0;
                        z-index:9;
                        padding:0 20px;
                        box-sizing: border-box;
                        .Edit-Input{
                            width: 100%;
                            height: 80%;
                            border-radius: 50px;
                            padding-left:60px;
                            border:  1px solid transparent;
                            background-color: #f2f2f2;
                            position: relative;
                            color:black;
                            font-size: medium;
                            &.focused {
                                outline:none;
                                background-color: white;
                                border-color: #1da1f2;
                                box-shadow: 0 0 5px #1da1f2;
                            }
                        }
                        .Edit-Input:focus + .search-icon{
                            color: #1da1f2;
                        }
                        .Edit-Input::-webkit-input-placeholder {
                            color: #71767B;
                        }
                        .search-icon {
                            position: absolute;
                            left: 40px;
                            top: 50%;
                            transform: translate(0, -50%);
                            color: #71767B;
                            font-size: 24px;
                        }
                    }
                    .people-container{
                        width:100%;
                        max-height: 400px;
                        overflow-y: auto;
                        display:flex;
                        flex-direction:column;
                        box-sizing: border-box;
                        padding-top: 0px;
                        padding-bottom: 0px;
                        &::-webkit-scrollbar{
                            width:4px;
                        }
                        &::-webkit-scrollbar-thumb{
                            background-color: #2F3336;
                            border-radius: 5px;;
                            border:none;
                        }
                        &::-webkit-scrollbar-track{
                            background:none;
                            border:none;
                        }
                        &:disabled{
                            color:#808080;
                        }
                        .person{
                            width:100%;
                            height:70px;
                            display:flex;
                            flex-direction:row;
                            align-items: center;
                            justify-content: space-between;
                            box-sizing: border-box;
                            padding: 40px 20px;
                            cursor:pointer;
                            transition: all 0.3s;
                            .user-info{
                                display:flex;
                                flex-direction:row;
                                align-items: center;
                                justify-content: flex-start;
                                box-sizing: border-box;
                                gap:10px;
                                img{
                                    width:50px;
                                    height:50px;
                                    border-radius:50%;
                                    background-color: rgb(255, 255, 255);
                                }
                                .person-info{
                                    display:flex;
                                    flex-direction:column;
                                    align-items: flex-start;
                                    justify-content: flex-start;
                                    gap:5px;
                                    .username{
                                        color: black;
                                        font-weight: bold;
                                        font-size: 16px;
                                        margin:0;
                                    }
                                    .usertag{
                                        color: #6e767d;
                                        font-size: 14px;
                                        margin: 0;
                                    }
                                }
                            }
                        }
                        .highlighted {
                            border: 2px solid #1da1f2;
                            border-radius: 50px;
                        }
                    }
                }
                .next-btn{
                    display: block;
                    width: 170px;
                    background:#1da1f2;
                    justify-content: center;
                    color: white;
                    text-align: center;
                    margin: 35px auto 0; 
                    border-radius: 50px;
                    font-size: 16px;
                    font-weight: bold;
                    border: none;
                    height: 50px;
                    cursor: pointer;
                    &:hover{
                        background-color:#2394db;
                    }
                }
                .next-btn:disabled {
                    color: gray;
                    background-color: #0e537e;
                    cursor: default;
                }
            }
            .messages-right{
                width: 60%;
                flex-direction:column;
                box-sizing: border-box;
                justify-content: center;
                .right-text{
                    color: black;
                    font-size: 28px;
                    font-weight: bold;
                    display: flex;
                    margin-top: 30px;
                    justify-content: center;
                }
                .conversations{
                    width:100%;
                    max-height: 580px;
                    overflow-y: auto;
                    display:flex;
                    flex-direction:column;
                    box-sizing: border-box;
                    padding-top: 0px;
                    padding-bottom: 0px;
                    &::-webkit-scrollbar{
                        width:4px;
                    }
                    &::-webkit-scrollbar-thumb{
                        background-color: #2F3336;
                        border-radius: 5px;;
                        border:none;
                    }
                    &::-webkit-scrollbar-track{
                        background:none;
                        border:none;
                    }
                    &:disabled{
                        color:#808080;
                    }
                    .person{
                        width:100%;
                        height:70px;
                        display:flex;
                        flex-direction:row;
                        align-items: center;
                        justify-content: space-between;
                        box-sizing: border-box;
                        padding: 40px 20px;
                        cursor:pointer;
                        transition: all 0.3s;
                        .user-info{
                            display:flex;
                            flex-direction:row;
                            align-items: center;
                            justify-content: flex-start;
                            box-sizing: border-box;
                            gap:10px;
                            img{
                                width:50px;
                                height:50px;
                                border-radius:50%;
                                background-color: rgb(255, 255, 255);
                            }
                            .person-info{
                                display:flex;
                                flex-direction:column;
                                align-items: flex-start;
                                justify-content: flex-start;
                                gap:5px;
                                .username{
                                    color: black;
                                    font-weight: bold;
                                    font-size: 16px;
                                    margin:0;
                                }
                                .usertag{
                                    color: #6e767d;
                                    font-size: 14px;
                                    margin: 0;
                                }
                            }
                        }
                        .delete-btn{
                            height:25px;
                            width:25px;
                            background:none;
                            border-radius:50%;
                            border:none;
                            display:flex;
                            justify-content: center;
                            align-items: center;
                            padding:0;
                            cursor:pointer;
                            .delete-icon{
                                font-size:16px;
                                color:#f11515;
                                --ionicon-stroke-width: 30px;
                                visibility: visible;
                            }
                            &:hover{
                                background-color: rgba($color: #f11515, $alpha: 0.1);
                            }
                        }
                        .message-count{
                            background-color: #1da1f2;
                            font-size:18px;
                            border-radius:50px;
                            right:5px;
                            min-width:30px;
                            max-width:60px;
                            height:30px;
                            color:white;
                            display:flex;
                            align-items: center;
                            justify-content: center;
                        }
                    }
                }
            }
        }
    }
    @media (max-width: 1000px){
        .messages-container{
            .messages-sub{
                display: flex;
                flex-direction: column;
                .messages-main{
                    height: 50%;
                    width: 100%;
                    border-bottom:solid 1px #2F3336;
                    border-right: none;
                    .main-text{
                        margin-top: 5%;
                    }
                    .search-people{
                        .people-container{
                            max-height: 200px;
                        }
                    }
                }
                .messages-right{
                    height: 50%;
                    width: 100%;
                    .right-text{
                        margin-top: 5%;
                    }
                    .conversations{
                        max-height: 380px;
                    }
                }
            }
        }
    }
    @media (max-width: 700px){
        .messages-container{
            .messages-sub{
                display: flex;
                flex-direction: column;
                .messages-main{
                    .main-text{
                        font-size: 26px;
                    }
                    .search-people{
                        .people-container{
                            max-height: 220px;
                        }
                    }
                }
                .messages-right{
                    .right-text{
                        font-size: 26px;
                    }
                }
            }
        }
    }
</style>