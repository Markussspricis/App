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
                        <div class="person" v-for="Person in foundUsers" :key="Person.UserID" @click="Person.personClicked = true; clickedPerson = Person.UserID" :class="{ 'highlighted': Person.UserID === clickedPerson }">
                            <div class="user-info">
                                <img :src="'/storage/' + Person.ProfilePicture" class="person-img">
                                <div class="person-info">
                                    <p class="username">{{ Person.Name }}</p>
                                    <p class="usertag">{{ Person.UserTag }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="next-btn" :disabled="!clickedPerson" @click="openConversation(selectedUser)">Start conversation</button>
            </div>
            <div class="messages-right">
                <div class="right-text">Your conversations</div>
                <div class="conversations">
                    <div v-for="conversation in conversations" :key="conversation.UserID" class="person-conversation" @click="openConversation(conversation)">
                        <div>{{ conversation.Name }}</div>
                    </div>
                </div>
            </div>
        </div>
        <Conversation v-if="showConversation" :user="selectedUser" @closeConversation="handleCloseConversation" @messageSent="fetchConversations" />
    </div>
</template>

<!-- <Popup v-if="popupTriggers.EditTrigger" :TogglePopup="() => ToggleFirstPopup('EditTrigger')">
            <div class="edit-popup">
                <p class="title-popup">New message</p>
                <button class="next-btn" :disabled="!clickedPerson" @click="nextButtonClick">Next</button>
                <div class="search-people">
                    <div class="input-wrap">
                        <input v-model="searchInput" @input="handleSearchInput" class="Edit-Input" :class="{ 'focused': isInputFocused }" @focus="inputFocus" @blur="inputBlur" placeholder="Search usernames..." />
                        <ion-icon name="search-outline" class="search-icon"></ion-icon>
                    </div>
                    <div class="people-container">
                        <div class="person" v-for="Person in foundUsers" :key="Person.UserID" @click="Person.personClicked = true; clickedPerson = Person.UserID" :class="{ 'highlighted': Person.UserID === clickedPerson }">
                            <div class="user-info">
                                <img :src="'/storage/' + Person.ProfilePicture" class="person-img">
                                <div class="person-info">
                                    <p class="username">{{ Person.Name }}</p>
                                    <p class="usertag">{{ Person.UserTag }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
             </div>
        </Popup> -->
        <!-- <Popup v-if="popupTriggers.MessageTrigger" :TogglePopup="() => ToggleSecondPopup('MessageTrigger')">
            <div class="message-popup" v-if="selectedUser">
                <div class="title-messages">Write your message to:</div>
                <div class="top">
                    <div class="left-side-popup">
                        <img @click.stop="openProfile(selectedUser.UserTag)" :src="'/storage/' + selectedUser.ProfilePicture">
                    </div>
                    <div class="right-side-popup">
                        <div class="userinfo-popup">
                            <p class="username">{{ selectedUser ? selectedUser.Name : 'No User Selected' }}</p>
                            <p class="usertag">{{ selectedUser ? selectedUser.UserTag : '' }}</p>
                        </div>
                        <div class="message-input-container">
                            <textarea class="message-input" rows="1" @input="handleInput" ref="tweetInputnav" placeholder="Message..." maxlength="255"></textarea>
                        </div>
                        <div class="message-image-preview">
                            <img :src="previewImagenav" v-if="previewImagenav">
                        </div>
                    </div>
                </div>
                <div class="bottom">
                    <div class="buttons">
                        <button class="message-btn"><input type="file" accept="image/png, image/gif, image/jpeg, video/mp4,video/x-m4v,video/*" id="message-img-input" @change="onImageChangenav" hidden><label for="message-img-input" class="message-img-label"><ion-icon name="images-outline" class="create-message-icon"></ion-icon></label></button>
                    </div>
                    <button class="popup-button" @click="sendMessage" :disabled="isSendDisabled">Send</button>
                </div>
            </div>
        </Popup>
        <Popup v-if="popupTriggers.MessageTrigger2" :TogglePopup="() => ToggleThirdPopup('MessageTrigger2')">
            <div class="received-messages-popup" v-if="user">
                <div class="title-messages">Received Messages<button @click="ToggleFourthPopup('MessageTrigger3')" class="sent-messages-btn">Sent messages</button></div>
                <div class="message-container">
                    <div v-for= "message in receivedMessages" :key="message.MessageID" class="received-message">
                        <div class="message-p">
                            <div class="message-p2">From</div>
                            <div class="message-p3" v-if="message.senderTag">{{ message.senderTag }}</div>
                            <div class="message-p4">{{ message.received_ago }}</div>
                            <div>
                                <button class="delete-btn" @click.stop="message.MessageID && TogglePopup(message.MessageID)">
                                    <ion-icon name="trash-bin-outline" class="delete-icon"></ion-icon>
                                </button>
                            </div>
                        </div>
                        <p class="message-content">{{ message.content }}</p>
                        <img class="message-img" v-if="message.image" :src="'/storage/'+ message.image" alt="Message Image">
                    </div>
                </div>
            </div>
        </Popup>
        <Popup v-if="popupTriggers.MessageTrigger3" :TogglePopup="() => ToggleFourthPopup('MessageTrigger3')">
            <div class="sent-messages-popup" v-if="user">
                <div class="title-messages">Sent Messages</div>
                <div class="message-container">
                    <div v-for="message in sentMessages" :key="message.id" class="sent-message">
                        <div class="message-p">
                            <div class="message-p2">To</div>
                            <div class="message-p3" v-if="message.receiverTag">{{ message.receiverTag }}</div>
                            <div class="message-p4">{{ message.sent_ago }}</div>
                            <div>
                                <button class="delete-btn" @click.stop="DeleteMessageID = message.id; TogglePopup(message.MessageID); console.log(message.id)">
                                    <ion-icon name="trash-bin-outline" class="delete-icon"></ion-icon>
                                </button>
                            </div>
                        </div>
                        <p class="message-content">{{ message.content }}</p>
                        <img class="message-img" v-if="message.image" :src="'/storage/'+ message.image" alt="Message Image">
                    </div>
                </div>
            </div>
        </Popup>
        <Popup v-if="popupTriggers.DeleteMessageTrigger" :TogglePopup="() => TogglePopup('DeleteMessageTrigger')">
            <div class="delete-popup">
                <h1 class="delete-title">Delete Message</h1>
                <p class="tweet-p">Are you sure you want to delete this message?</p>
                <div class="tweet-buttons">
                    <button class="cancel-button" @click="TogglePopup('DeleteMessageTrigger')">Cancel</button>
                    <button class="delete-button" @click.stop="deleteMessage(DeleteMessageID)">Delete</button>
                </div>
            </div>
        </Popup> -->

<script>
import { ref, computed } from 'vue';
import Conversation from './Conversation.vue';
// import Popup from '../Popup.vue';
import { mapState } from 'vuex';
import axios from 'axios';
export default{
    name: 'MessagesContent',
    components: {
        // Popup,
        Conversation,
    },
    data() {
        return {
            isInputFocused: false,
            personClicked: false,
            messageText: '',
            // deleteMessageID: null,
            showConversation: false,
            selectedUser: null,
            conversations: [],
        };
    },
    computed: {
        ...mapState(['user']),
        // isSendDisabled() {
        //     return this.messageText.trim().length === 0;
        // },
    },
    setup () {
        const DeleteMessageID = ref(null);
        const previewImagenav = ref(null);
        const messageImagenav = ref(null);
        const searchInput = ref('');
        const users = ref([]);
        const clickedPerson = ref(null);
        const selectedUser = ref(null);
        const tweetInputnav = ref(null);
        const receivedMessages = ref([]);
        const sentMessages = ref([]);
        const popupTriggers = ref({
            EditTrigger: false,
            MessageTrigger: false,
            MessageTrigger2: false,
            MessageTrigger3: false,
            DeleteMessageTrigger: false,
        });
        const deleteMessage = (messageID) => {
            console.log('Message ID to delete:', messageID);
            performDeleteMessage(messageID);
        };
        const performDeleteMessage = async (messageID) => {
            try {
                await axios.delete(`/api/messages/${messageID}`);
                const receivedIndex = receivedMessages.value.findIndex((m) => m.MessageID === messageID);
                if (receivedIndex !== -1) {
                    receivedMessages.value.splice(receivedIndex, 1);
                }
                const sentIndex = sentMessages.value.findIndex((m) => m.MessageID === messageID);
                if (sentIndex !== -1) {
                    sentMessages.value.splice(sentIndex, 1);
                }
                popupTriggers.value['DeleteMessageTrigger'] = false;
                console.log(`Message with ID ${messageID} deleted successfully.`);
            } catch (error) {
                console.error('Error deleting message:', error);
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
        const resetFirstPopup = () => {
            foundUsers.value = [];
            searchInput.value = '';
            clickedPerson.value = null;
        };
        const TogglePopup = (id) => {
            popupTriggers.value['DeleteMessageTrigger'] = !popupTriggers.value['DeleteMessageTrigger'];

            if (!popupTriggers.value['DeleteMessageTrigger']) {
                
            }
        };
        const ToggleFirstPopup = () => {
            popupTriggers.value['EditTrigger'] = !popupTriggers.value['EditTrigger'];
            if (!popupTriggers.value['EditTrigger']) {
                resetFirstPopup();
            }
        };
        const ToggleSecondPopup = () => {
            popupTriggers.value['MessageTrigger'] = !popupTriggers.value['MessageTrigger'];
            if (!popupTriggers.value['MessageTrigger']) {
            }
            previewImagenav.value = null;
            messageImagenav.value = null;
        };
        let fetchReceivedMessages = null;

        const setReceivedMessages = (messages) => {
            receivedMessages.value = messages;
        };
        const ToggleThirdPopup = async () => {
            popupTriggers.value['MessageTrigger2'] = !popupTriggers.value['MessageTrigger2'];
            if (popupTriggers.value['MessageTrigger2'] && fetchReceivedMessages) {
                await fetchReceivedMessages();
                popupTriggers.value['MessageTrigger2'] = true;
            }
        };
        fetchReceivedMessages = async () => {
            try {
                const response = await axios.get(`/api/user-messages`);
                const { received_messages } = response.data;

                const senderIds = received_messages.map(message => message.SenderID);
                const senderTagsMap = await fetchSenderTags(senderIds);

                setReceivedMessages(received_messages.map(message => ({
                    content: message.Content,
                    image: message.Image,
                    senderId: message.SenderID,
                    senderTag: senderTagsMap[message.SenderID] || 'Unknown Sender',
                    received_ago: message.received_ago,
                })));
            } catch (error) {
                console.error('Error fetching received messages:', error);
            }
        };
        let fetchSentMessages = null;

        const setSentMessages = (messages) => {
            sentMessages.value = messages;
        };
        const ToggleFourthPopup = async () => {
            popupTriggers.value['MessageTrigger3'] = !popupTriggers.value['MessageTrigger3'];
            if (popupTriggers.value['MessageTrigger3'] && fetchSentMessages) {
                await fetchSentMessages();
                popupTriggers.value['MessageTrigger3'] = true;
            }
        };
        fetchSentMessages = async () => {
            try {
                const response = await axios.get(`/api/user-messages`);
                const { sent_messages } = response.data;

                const receiverIds = sent_messages.map(message => message.ReceiverID);
                const receiverTagsMap = await fetchReceiverTags(receiverIds);

                setSentMessages(sent_messages.map(message => ({
                    id: message.MessageID,
                    content: message.Content,
                    image: message.Image,
                    receiverId: message.ReceiverID,
                    receiverTag: receiverTagsMap[message.ReceiverID] || 'Unknown Sender',
                    sent_ago: message.sent_ago,
                })));
            } catch (error) {
                console.error('Error fetching sent messages:', error);
            }
        };
        const fetchSenderTags = async (senderIds) => {
            try {
                const senderTagsMap = {};

                for (const id of senderIds) {
                    try {
                        const response = await axios.get(`/api/get-user/${id}`);
                        
                        if (response && response.data && response.data.user && response.data.user.UserTag) {
                            const userTag = response.data.user.UserTag;
                            senderTagsMap[id] = userTag;
                        } else {
                            console.warn(`Invalid or missing data for ID ${id}`);
                            senderTagsMap[id] = 'Unknown';
                        }

                        console.log('Response for ID', id, ':', response.data);

                    } catch (error) {
                        console.error(`Error fetching user data for ID ${id}:`, error);
                    }
                }

                console.log('Sender Tags Map:', senderTagsMap);
                return senderTagsMap;

            } catch (error) {
                console.error('Error fetching sender tags:', error);
                return {};
            }
        };
        const fetchReceiverTags = async (receiverIds) => {
            try {
                const receiverTagsMap = {};

                for (const id of receiverIds) {
                    try {
                        const response = await axios.get(`/api/get-user/${id}`);

                        if (response && response.data && response.data.user && response.data.user.UserTag) {
                            const userTag = response.data.user.UserTag;
                            receiverTagsMap[id] = userTag;
                        } else {
                            console.warn(`Invalid or missing data for ID ${id}`);
                            receiverTagsMap[id] = 'Unknown';
                        }

                        console.log('Response for ID', id, ':', response.data);

                    } catch (error) {
                        console.error(`Error fetching user data for ID ${id}:`, error);
                    }
                }

                console.log('Receiver Tags Map:', receiverTagsMap);
                return receiverTagsMap;

            } catch (error) {
                console.error('Error fetching receiver tags:', error);
                return {};
            }
        };
        const nextButtonClick = () => {
            const selectedUserID = clickedPerson.value;
            if (selectedUserID) {
                const foundUser = foundUsers.value.find((user) => user.UserID === selectedUserID);
                if (foundUser) {
                    selectedUser.value = foundUser;
                    ToggleSecondPopup();
                }
            }
        };
        const handlePersonClick = (Person) => {
            this.clickedPerson = Person.UserID;
        };
        const handleSearchInput = () => {
            if (searchInput.value.length === 0) {
                clickedPerson.value = null;
            }
        };
        return {
            DeleteMessageID,
            popupTriggers,
            ToggleFirstPopup,
            ToggleSecondPopup,
            fetchReceivedMessages,
            ToggleThirdPopup,
            ToggleFourthPopup,
            TogglePopup,
            fetchSentMessages,
            nextButtonClick,
            resetFirstPopup,
            previewImagenav,
            messageImagenav,
            handlePersonClick,
            searchInput,
            foundUsers,
            clickedPerson,
            selectedUser,
            handleSearchInput,
            users,
            tweetInputnav,
            receivedMessages,
            sentMessages,
            deleteMessage,
            performDeleteMessage,
        }
    },
    methods: {
        openConversation() {
            if (!this.selectedUser && this.clickedPerson) {
                const foundUser = this.foundUsers.find((user) => user.UserID === this.clickedPerson);
                if (foundUser) {
                    this.selectedUser = foundUser;
                    this.showConversation = true;
                }
            } else if (this.selectedUser) {
                this.showConversation = true;
            }
        },
        handleCloseConversation() {
            this.selectedUser = null;
            this.showConversation = false;
        },
        updateConversationList() {
    this.fetchConversations()
        .then(() => {
            console.log('Conversations:', this.conversations);
        })
        .catch(error => {
            console.error('Error updating conversations:', error);
        });
},
async fetchConversations() {
    try {
        const response = await this.$axios.get('/api/user-messages');
        const { sent_messages, received_messages } = response.data;

        // Assuming currentUser is set elsewhere in your component
        const currentUserID = this.currentUser ? this.currentUser.UserID : null;

        const allMessages = [...sent_messages, ...received_messages];
        const conversations = allMessages.reduce((acc, message) => {
    const otherUserID = message.SenderID === currentUserID ? message.ReceiverID : message.SenderID;
    const otherUser = message.SenderID === currentUserID ? message.Receiver : message.Sender;

    if (otherUser && !acc.find(user => user.UserID === otherUserID)) {
        acc.push({ UserID: otherUserID, Name: otherUser.Name || 'Unknown' }); // Use a default value if Name is undefined
    }
    return acc;
}, []);

        this.conversations = conversations;
    } catch (error) {
        console.error('Error fetching conversations:', error);
    }
},
        onImageChangenav(event) {
            this.messageImagenav = event.target.files[0];
            if (this.messageImagenav) {
                this.previewImagenav = URL.createObjectURL(this.messageImagenav);
            } else {
                this.previewImagenav = null;
            }
        },
        openProfile(tag){
            const NoSymbolTag = tag.replace(/^@/, '');
            this.$router.push({ name: 'profile', params: { UserTag : NoSymbolTag } });
            console.log(tag);
        },
        goBack() {
            this.$router.go(-1);
        },
        inputFocus() {
            this.isInputFocused = true;
        },
        inputBlur() {
            this.isInputFocused = false;
        },
        async sendMessage() {
            if (!this.selectedUser || !this.tweetInputnav) return;
            const formData = new FormData();
            formData.append('ReceiverID', this.selectedUser.UserID);
            const content = this.tweetInputnav ? this.tweetInputnav.value.trim() : '';
            formData.append('Content', content);
            if (this.messageImagenav) {
                formData.append('image', this.messageImagenav);
            }
            try {
                const response = await this.$axios.post('/api/send-message', formData, {
                    headers: {
                    'Content-Type': 'multipart/form-data',
                    },
                });
                const sentMessage = response.data;

                if (!this.selectedUser.messages) {
                    this.selectedUser.messages = [];
                }
                this.selectedUser.messages.push(sentMessage);
                this.ToggleSecondPopup();
            } catch (error) {
            console.error('Error sending message:', error);
            }
        },
        // deleteMessage(messageID) {
        //     console.log('Message ID to delete:', messageID);
        //     this.performDeleteMessage(messageID);
        // },
        // async performDeleteMessage(messageID) {
        //     console.log('Performing deletion for message ID:', messageID);
        //     if (!messageID) {
        //         console.error('Message ID not provided');
        //         return;
        //     }
        //     try {
        //         await this.$axios.delete(`/api/messages/${messageID}`);
        //         const receivedIndex = this.receivedMessages.findIndex((m) => m.MessageID === messageID);
        //         if (receivedIndex !== -1) {
        //             this.receivedMessages.splice(receivedIndex, 1);
        //         }
        //         const sentIndex = this.sentMessages.findIndex((m) => m.MessageID === messageID);
        //         if (sentIndex !== -1) {
        //             this.sentMessages.splice(sentIndex, 1);
        //         }
        //         this.popupTriggers.DeleteMessageTrigger = false;
        //         console.log(`Message with ID ${messageID} deleted successfully.`);
        //     } catch (error) {
        //         console.error('Error deleting message:', error);
        //     }
        // },
    },
    async mounted() {
        await this.$store.dispatch('initializeApp');
        this.$axios.get('/api/allusers')
        .then(response => {
            this.users = response.data;
        })
        .catch(error => {
            console.error(error);
        });
    },
}
</script>

<style lang="scss" scoped>
.messages-container{
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
                    z-index:99;
                    padding:0 20px;
                    box-sizing: border-box;
                    .Edit-Input{
                        width: 100%;
                        height: 80%;
                        border-radius: 50px;
                        padding-left:60px;
                        border:  1px solid transparent;
                        background-color: #e8dddd;
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
                .person-conversation{
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
                }
            }
        }
    }
    .message-popup{
        width:500px;
        min-height: 270px;
        display:flex;
        flex-direction:column;
        box-sizing: border-box;
        justify-content: space-between;
        padding:0px 0px 0px 0px;
        .title-messages{
            color: black;
            margin-left:20px;
            padding-top:50px;
            font-weight: bold;
            font-size: 22px;
        }
        .top{
            width:100%;
            display:flex;
            flex-direction: row;
            box-sizing: border-box;
            gap:15px;
            padding:10px 35px 10px 20px;
            .left-side-popup{
                width:50px;
                height:100%;
                display:flex;
                flex-direction:column;
                box-sizing: border-box;
                img{
                    width:50px;
                    height:50px;
                    border-radius: 50%;
                    background-color: white;
                    cursor: pointer;
                }
            }
            .right-side-popup{
                width:90%;
                height:100%;
                display:flex;
                flex-direction:column;
                box-sizing: border-box;
                .userinfo-popup{
                    width:100%;
                    height:20px;
                    display:flex;
                    flex-direction: row;
                    gap:5px;
                    .username{
                        margin:0;
                        font-weight: bold;
                        font-size: 17px;
                        color:black;
                        max-width:35%;
                        overflow: hidden;
                        white-space: nowrap;
                        text-overflow: ellipsis;
                    }
                    .usertag{
                        margin:0;
                        font-size: 17px;
                        color:#6A6F74;
                        max-width:40%;
                        overflow: hidden;
                        white-space: nowrap;
                    }
                }
                .message-input-container{
                    width:100%;
                    height:100%;
                    display:flex;
                    align-items: center;
                    justify-content: center;
                    padding-top: 10px;
                    padding-right:10px;
                    .message-input{
                        width:100%;
                        height:100%;
                        background-color: white;
                        color:black;
                        resize: none;
                        transition: height 0.2s;
                        font-family: Arial, sans-serif;
                        font-size: 22px;
                        border:none;
                        display:flex;
                        align-items: center;
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
                        &:focus{
                            outline:none;
                        }
                    }
                }
                .message-image-preview{
                    padding-top: 10px;
                    img{
                        max-width:100%;
                        max-height:100%;
                        border-radius: 15px;
                    }
                }
            }
        }
        .bottom{
            width:100%;
            height:60px;
            display:flex;
            flex-direction: row;
            justify-content: space-between;
            border-top:1px solid #2F3336;
            padding: 10px;
            box-sizing: border-box;
            .buttons{
                display:flex;
                flex-direction: row;
                align-items: center;
                gap:5px;
                .message-btn{
                    height:40px;
                    width:40px;
                    background:none;
                    border-radius:50%;
                    border:none;
                    display:flex;
                    justify-content: center;
                    align-items: center;
                    padding:0;
                    cursor:pointer;
                    transition: all 0.3s;
                    .message-img-label{
                        width:100%;
                        height:100%;
                        display:flex;
                        justify-content: center;
                        align-items: center;
                        margin: 0;
                        padding: 0;
                    }
                    .create-message-icon{
                        font-size:20px;
                        color:#1da1f2;
                        --ionicon-stroke-width: 40px;
                        visibility: visible;
                    }
                }
                .message-btn:hover{
                    background-color: rgba($color: #1da1f2, $alpha: 0.1);
                }
            }
            .popup-button{
                width:auto;
                padding:20px;
                height:auto;
                display:flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                border-radius: 50px;
                border:none;
                background-color: #1da1f2;
                color:white;
                font-size: medium;
                font-weight: bold;
                transition: all 0.3s;
                cursor:pointer;
                &:hover{
                    background-color: #2394db;
                }
                &:disabled{
                    color: gray;
                    background-color: #0e537e;
                    cursor: default;
                }
            }
        }
    }
    .received-messages-popup{
        width:500px;
        max-height: 600px;
        min-height: 200px;
        display:flex;
        flex-direction:column;
        box-sizing: border-box;
        justify-content: space-between;
        padding:0px 0px 0px 0px;
        .title-messages{
            color: black;
            margin-left:20px;
            padding-top:50px;
            font-weight: bold;
            font-size: 22px;
        }
        .sent-messages-btn{
            position:absolute;
            top:10px;
            right:10px;
            width:auto;
            height:auto;
            display:flex;
            align-items: center;
            justify-content: center;
            color:white;
            border:none;
            background-color: #1da1f2;
            border-radius: 50px;
            font-size: 14px;
            font-weight: bold;
            padding:8px 16px;
            cursor: pointer;
            &:hover{
                background-color: #2394db;
            }
        }
        .message-container{
            overflow-y: auto;
            height: auto;
            &::-webkit-scrollbar{
            width:4px;
            }
            &::-webkit-scrollbar-thumb{
                background-color: #2F3336;
                border-radius: 5px;
                border:none;
            }
            &::-webkit-scrollbar-track{
                background:none;
                border:none;
            }
            &:focus{
                outline:none;
            }
            .received-message{
                color:black;
                margin-left: 20px;
                margin-right: 20px;
                .message-p{
                    margin-top: 20px;
                    font-size: 18px;
                    display: flex;
                    flex-direction: row;
                    .message-p2{
                        font-weight: bold;
                    }
                    .message-p3, .message-p4{
                        color: gray;
                        margin-left: 5px;
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
                        margin-left: 5px;
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
                }
                .message-content{
                    overflow-wrap: anywhere;
                    font-size: 16px;
                }
                .message-img{
                    width: 100%;
                    max-height: 300px;
                    border-radius: 15px;
                }
            }
        }
    }
    .sent-messages-popup{
        width:500px;
        max-height: 600px;
        min-height: 200px;
        display:flex;
        flex-direction:column;
        box-sizing: border-box;
        justify-content: space-between;
        padding:0px 0px 0px 0px;
        .title-messages{
            color: black;
            margin-left:20px;
            padding-top:50px;
            font-weight: bold;
            font-size: 22px;
        }
        .message-container{
            overflow-y: auto;
            height: auto;
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
            &:focus{
                outline:none;
            }
            .sent-message{
                color:black;
                margin-left: 20px;
                margin-right: 20px;
                .message-p{
                    margin-top: 20px;
                    font-size: 18px;
                    display: flex;
                    flex-direction: row;
                    .message-p2{
                        font-weight: bold;
                    }
                    .message-p3, .message-p4{
                        color: gray;
                        margin-left: 5px;
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
                        margin-left: 5px;
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
                }
                .message-content{
                    overflow-wrap: anywhere;
                    font-size: 16px;
                }
                .message-img{
                    width: 100%;
                    max-height: 300px;
                    border-radius: 15px;
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
        .message-popup, .received-messages-popup, .sent-messages-popup{
            width: 100%;
        }
    }
}
</style>