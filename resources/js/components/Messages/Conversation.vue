<template>
  <!-- <div class="conversation-overlay"> -->
    <div class="conversation" v-if="conversationExists">
      <div class="top-bar">
        <button class="close" @click="closeConversation">
          <ion-icon name="arrow-back-outline"></ion-icon>
        </button>
        <div class="person-img">
          <img :src="'/storage/' + user.ProfilePicture">
        </div>
        <div class="top-user">
          <p class="name">{{ user.Name }}</p>
          <p class="tag">{{ user.UserTag }}</p>
        </div>
      </div>

      <div class="messages">
        <div v-for="(message, index) in conversationMessages" :key="index" class="message">
          <div v-if="message.type === 'sent'" class="sent">
            <div class="content">{{ message.Content }}</div>
            <div class="image">
              <img v-if="message.Image" :src="'/storage/' + message.Image" alt="Sent Image">
            </div>
            <span class="time">{{ message.time_ago }}</span>
          </div>
          <div v-else class="received">
            <div class="content">{{ message.Content }}</div>
            <div class="image">
              <img v-if="message.Image" :src="'/storage/' + message.Image" alt="Received Image">
            </div>
            <div class="time">{{ message.time_ago }}</div>
          </div>
        </div>
      </div>

      <div class="bottom">
        <div class="message-input-container">
          <textarea class="message-input" rows="1" @input="handleInput" ref="tweetInputnav" placeholder="Message..." maxlength="255"></textarea>
        </div>
        <div class="message-image-preview">
          <img :src="previewImagenav" v-if="previewImagenav">
          <div class="preview-cover" v-if="previewImagenav">
            <div class="preview-close" @click="removeImage">
              <ion-icon class="preview-close-icon" name="close"></ion-icon>
            </div>
          </div>
        </div>
        <div class="buttons">
          <button class="message-btn"><input type="file" accept="image/png, image/gif, image/jpeg, video/mp4,video/x-m4v,video/*" id="message-img-input" @change="onImageChangenav" hidden><label for="message-img-input" class="message-img-label"><ion-icon name="images-outline" class="create-message-icon"></ion-icon></label></button>
          <button class="popup-button" @click="sendMessageToStore" :disabled="isSendDisabled">Send</button>
        </div>
      </div>
    </div>
    <div v-else>
      <p>No conversation found.</p>
    </div>
  <!-- </div> -->
</template>
  
<script>
  import { mapState, mapActions } from 'vuex';
  export default {
    name: 'Conversation',
    data(){
      return{
        previewImagenav: null,
        messageImagenav: null,
        messageText: '',
        // receivedMessages: [],
        // sentMessages: [],
        messages: [],
        loggedInUserID: null,
        isSendDisabled: true
      }
    },
    props: {
      user: Object,
      conversationId: String,
    },
    computed: {
      ...mapState(['conversations']),
    conversation() {
      return this.conversations.find(conv => conv.ConversationID === this.conversationId);
    },
    conversationExists() {
      return !!this.conversation;
    },
    conversationMessages() {
      return this.conversation ? this.conversation.messages || [] : [];
    },
    },
    methods: {
      ...mapActions(['sendMessage', 'fetchConversation']),
      async sendMessageToStore() {
      try {
        console.log('Sending message...');
        await this.fetchConversation(this.user.UserID); // Fetch conversation
        
        const ReceiverID = this.user.UserID;
        if (!this.user || !this.messageText.trim()) {
          console.log('User or message text is undefined');
          return;
        }

        const messageData = {
          ReceiverID: ReceiverID,
          Content: this.messageText.trim(),
          Image: this.messageImagenav
        };

        await this.sendMessage(messageData);

        this.messageText = '';
        this.messageImagenav = null;
      } catch (error) {
        console.error('Error sending message:', error);
      }
    },
  //   async fetchInitialData() {
  //   try {
  //     console.log('Fetching initial data...');
  //     if (this.conversationId) { // Ensure conversationId is available and valid
  //       await this.fetchMessages(this.conversationId); // Dispatch fetchMessages action with conversationId
  //     }
  //   } catch (error) {
  //     console.error('Error fetching initial data:', error);
  //   }
  // },
    // async fetchMessages() {
    //   try {
    //     // Pass the conversationId to fetchMessages
    //     await this.fetchMessages(this.conversationId); 
    //   } catch (error) {
    //     console.error('Error fetching messages:', error);
    //   }
    // },
    // async fetchConversation() {
    //   try {
    //     await this.$store.dispatch('fetchConversation', this.conversationId);
    //   } catch (error) {
    //     console.error('Error fetching conversation:', error);
    //     throw error;
    //   }
    // },

      closeConversation() {
        this.$emit('closeConversation');
      },
      onImageChangenav(event) {
        this.messageImagenav = event.target.files[0];
        if (this.messageImagenav) {
          this.previewImagenav = URL.createObjectURL(this.messageImagenav);
        } else {
          this.previewImagenav = null;
        }
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
      updateMessageText(event) {
        this.messageText = event.target.value;
      },
      handleInput(event) {
        this.autoSize();
        this.updateMessageText(event);
        this.isSendDisabled = !this.messageText.trim();
      },
      removeImage(){
        // this.tweetImage = null;
        this.previewImagenav = null;
      },
    },
    mounted() {
    // Fetch initial data when component is mounted
    console.log('Conversations:', this.conversations);
  console.log('Conversation ID:', this.conversationId);
    // this.fetchMessages(this.conversationId); 
    // this.fetchConversation(this.conversationId); 
    // this.fetchInitialData();
//     if (this.conversationId) {
//         this.fetchConversation(this.user.UserID); // Fetch conversation and messages
//       }
this.fetchConversation(this.conversationId);
},
  };
</script>
  
<style lang="scss" scoped>
  // .conversation-overlay {
  //   position: fixed;
  //   top: 0;
  //   left: 0;
  //   width: 100%;
  //   height: 100%;
  //   background-color: rgba(0, 0, 0, 0.5);
  //   z-index: 999;
    .conversation {
      // position: relative;
      // background-color: white;
      // margin: auto;
      // top: 50%;
      // transform: translateY(-50%);
      // z-index: 99;
      // width: 600px;
      // height: 400px;
      // display: flex;
      // flex-direction: column;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      //background-color: pink;
      z-index: 999; /* Ensure it's above other content */
      display: flex;
      flex-direction: column;
      background-color:rgba($color: white, $alpha: 1);
      .top-bar{
        // height:15%;
        // width:100%;
        //background-color:rgba($color: white, $alpha: 1);
        // border-bottom:solid 1px #2F3336;
        // position:fixed;
        // top:0;
        // z-index:9;
        // display: flex;
        // flex-direction: row;
        // align-items: center;
        height: 60px;
        width: 100%;
        //background-color: rgba($color: white, $alpha: 0.8);
        border-bottom: solid 1px #2F3336;
        position: relative;
        display: flex;
        flex-direction: row;
        align-items: center;
        //z-index: 9;
        .close{
          display:flex;
          align-items: center;
          justify-content: center;
          width:40px;
          height:40px;
          border-radius: 50%;
          margin-left: 10px;
          border:none;
          font-size:22px;
          background:none;
          color:black;
          cursor:pointer;
          transition: all 0.3s;
          &:hover{
            background-color: rgba($color: #e8dddd, $alpha: 1);
          }
        }
        .person-img{
          width:50px;
          height:50px;
          top: 0;
          bottom: 0;
          margin-left: 10px;
          img{
            width:100%;
            height:100%;
            border-radius:50%;
            background-color: rgb(255, 255, 255);
          }
        }
        .top-user{
          height:100%;
          width:auto;
          display:flex;
          flex-direction: column;
          margin-left: 10px;
          .name{
            width:100%;
            height:50%;
            display:flex;
            align-items: center;
            justify-content: flex-start;
            font-weight: bold;
            font-size: 20px;
            margin:0;
            color: black;
            margin-top: 10px;
          }
          .tag{
            width:100%;
            height:50%;
            display:flex;
            align-items: center;
            font-size: 15px;
            color:#6A6F74;
            margin:0;
            max-width:100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 10px;
          }
        }
      }
      .messages{
        //background-color: lightblue;
        height: 83%;
        width: 100%;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        //z-index: 9;
        // position: fixed;
        //top: 15%;
        //background-color:rgba($color: white, $alpha: 1);
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
        .message{
          // background-color: red;
          //height: auto;
          //width: 100%;
          //display: flex;
          //flex-direction: row;
          padding: 5px 10px 5px 10px;
          .sent{
            // background-color: red;
            //width:600px;
            width: 100%;
            height: auto;
            display: flex;
            flex-direction: column;
            overflow-wrap: anywhere;
            // justify-content: center;
            // align-items: center;
            //padding-left: 5px;
            .content{
              color: red;
              // display: flex;
              // justify-content: flex-start;
            }
            .time{
              color: gray;
              // display: flex;
              // justify-content: flex-start;
            }
            .image{
              img{
                max-width: 100%;
                max-height: 100%;
                border-radius: 15px;
              }
            }
          }
          .received{
            // background-color: red;
            width: 100%;
            height: auto;
            display: flex;
            flex-direction: column;
            overflow-wrap: anywhere;
            justify-content: flex-end;
            // word-break: break-all;
            .content{
              color: green;
              // display: flex;
              // justify-content: flex-start;
            }
            .time{
              color: gray;
              // display: flex;
              // justify-content: flex-start;
            }
            .image{
              img{
                max-width: 100%;
                max-height: 100%;
                border-radius: 15px;
              }
            }
          }
        }
      }
      .bottom{
        // width:100%;
        // min-height: 15%;
        // display:flex;
        // flex-direction: row;
        // border-top:1px solid #2F3336;
        // bottom: 0;
        // position: fixed;
        width: 100%;
        min-height: 60px;
        display: flex;
        flex-direction: row;
        //border-top: 1px solid #2F3336;
        bottom: 0;
        position: absolute;
        background-color:rgba($color: white, $alpha: 1);
        //background-color: blue;
        //z-index: 99;
        .message-input-container{
          width:85%;
          //height:100%;
          display:flex;
          align-items: center;
          justify-content: center;
          padding-right:10px;
          border-top: 1px solid #2F3336;
          //background-color: yellow;
          .message-input{
            width:100%;
            //height:100%;
            margin-right: 0px;
            padding-left: 10px;
            display: flex;
            //align-items: center;
            color:black;
            resize: none;
            transition: height 0.2s;
            font-family: Arial, sans-serif;
            font-size: 22px;
            border:none;
            display:flex;
            //align-items: center;
            //background-color: green;
            // ::placeholder{
            //   display: flex;
            //   align-items: center;
            // }
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
          position: relative;
          border-top: 1px solid #2F3336;
          img{
            width:150px;
            height:100px;
            border-radius: 15px;
          }
          .preview-cover{
            display:none;
            top: 10px;
            left: 0;
            width: 100%;
            height: 90%;
            position:absolute;
            z-index:99;
            background-color: rgba($color: #000000, $alpha: 0.1);
            backdrop-filter: blur(2px);
            border-radius:15px;
            overflow: hidden;
            .preview-close{
              position: absolute;
              top:50%;
              left:50%;
              transform: translate(-50%, -50%);
              width:50px;
              height:50px;
              border-radius:50%;
              display:flex;
              align-items: center;
              justify-content: center;
              background-color: rgba($color: #000000, $alpha: 0.4);
              cursor:pointer;
              .preview-close-icon{
                color:white;
                font-size:30px;
              }
            }
          }
          &:hover{
            .preview-cover{
              display:block;
            }
          }
        }
        .buttons{
          display:flex;
          flex-direction: row;
          align-items: center;
          width: 15%;
          justify-content: space-around;
          border-top: 1px solid #2F3336;
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
            transition: all 0.3s;
            .message-img-label{
              width:100%;
              height:100%;
              display:flex;
              justify-content: center;
              align-items: center;
              margin: 0;
              padding: 0;
              cursor:pointer;
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
          .popup-button{
            width:60px;
            padding:20px;
            height:30px;
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
    }
  //}
  // @media(max-width: 768px){
  //   .conversation{
  //     max-width: 100%;
  //   }
  // }
  @media(max-width: 1250px){
    .conversation{
      // .top-bar{
      //   height: 8%;
      // }
      .messages{
        height: 84%;
      }
      // .bottom{
      //   min-height: 8%;
      // }
    }
  }
  @media(max-width: 1000px){
    .conversation{
      // .top-bar{
      //   height: 7%;
      // }
      .messages{
        height: 86%;
      }
      // .bottom{
      //   min-height: 7%;
      // }
    }
  }
  @media(max-width: 850px){
    .conversation{
      .bottom{
        .message-input-container{
          width: 80%;
        }
        .buttons{
          width: 20%;
        }
      }
    }
  }
  @media(max-width: 650px){
    .conversation{
      // .top-bar{
      //   height: 60px;
      // }
      .messages{
        height: 88%;
      }
      .bottom{
        // min-height: 6%;
        .message-input-container{
          width: 75%;
        }
        .buttons{
          width: 25%;
        }
      }
    }
  }
  @media(max-width: 500px){
    .conversation{
      height: 95%;
    }
  }
</style>