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
                    <!-- <div v-for="conversationId in conversations" :key="conversationId" class="person-conversation" @click="openConversation(conversationId)">
                        <div>{{ getConversationByUserId(conversationId).Name }}</div>
                    </div> -->
                    <div class="person-conversation">
                    </div>
                </div>
                <Conversation v-if="showConversation" :user="selectedUser" @closeConversation="handleCloseConversation"/>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed } from 'vue';
import Conversation from './Conversation.vue';
import { mapState, mapActions} from 'vuex';
export default{
    name: 'MessagesContent',
    components: {
        Conversation,
    },
    data() {
        return {
            isInputFocused: false,
            personClicked: false,
            showConversation: false,
            selectedUser: null,
            conversations: [],
        };
    },
    computed: {
        ...mapState(['user']),
    },
    setup () {
        const searchInput = ref('');
        const users = ref([]);
        const clickedPerson = ref(null);
        const selectedUser = ref(null);

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
        const handlePersonClick = (Person) => {
            this.clickedPerson = Person.UserID;
        };
        const handleSearchInput = () => {
            if (searchInput.value.length === 0) {
                clickedPerson.value = null;
            }
        };
        return {
            handlePersonClick,
            searchInput,
            foundUsers,
            clickedPerson,
            selectedUser,
            handleSearchInput,
            users,
        }
    },
    methods: {
        openConversation(conversationId) {
      if (!this.selectedUser && this.clickedPerson) {
        const foundUser = this.foundUsers.find((user) => user.UserID === this.clickedPerson);
        if (foundUser) {
          this.selectedUser = foundUser;
          this.showConversation = true;
          if (!this.conversations.includes(foundUser.UserID)) {
            this.conversations.push(foundUser.UserID);
          }
        }
      } else if (this.selectedUser) {
        this.showConversation = true;
      }
    },
        handleCloseConversation() {
            this.selectedUser = null;
            this.showConversation = false;
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
    },
    async created() {
        await this.$store.dispatch('fetchConversations'); // Fetch conversations when the component is created
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