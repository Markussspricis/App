<template>
  <div class="conversation-overlay">
    <div class="conversation">
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
        <div v-for="(message, index) in messages" :key="index" class="message">
          <div v-if="message.SenderID === loggedInUserID" class="sent">
            <div class="content">{{ message.Content }}</div>
            <div class="time">{{ message.sent_ago }}</div>
            <div class="image">
              <img v-if="message.Image" :src="'/storage/' + message.Image" alt="Sent Image">
            </div>
          </div>
          <div v-else class="received">
            <p>{{ message.Content }}</p>
            <div class="time">{{ message.received_ago }}</div>
            <img v-if="message.Image" :src="'/storage/' + message.Image" alt="Received Image">
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
          <button class="popup-button" @click="sendMessage" :disabled="isSendDisabled">Send</button>
        </div>
      </div>
    </div>
  </div>
</template>
  
<script>
  import axios from 'axios';
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
        loggedInUserID: null
      }
    },
    props: {
      user: Object,
    },
    methods: {
      async sendMessage() {
  if (!this.user || !this.messageText.trim()) return;

  const formData = new FormData();
  formData.append('ReceiverID', this.user.UserID);
  formData.append('Content', this.messageText.trim());
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
    this.messages.push(sentMessage); // Add sent message to messages array immediately

    // Clear message input and image preview
    this.messageText = '';
    this.messageImagenav = null;

    // Fetch messages again to update the conversation
    this.fetchMessages();
  } catch (error) {
    console.error('Error sending message:', error);
  }
},
async fetchMessages() {
  try {
    const response = await this.$axios.get(`/api/user-messages/${this.user.UserID}`);
    const { sent_messages, received_messages } = response.data;

    this.messages = [...sent_messages, ...received_messages];

    this.messages.forEach(message => {
      if (message.SenderID === this.loggedInUserID) {
        message.sent_ago = message.sent_ago;
      } 
      if (!message.received_ago) {
        message.received_ago = message.sent_ago;
      }
    });
  } catch (error) {
    console.error('Error fetching messages:', error);
  }
},

    closeConversation() {
      this.$emit('closeConversation');
    },
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
      },
      removeImage(){
        // this.tweetImage = null;
        this.previewImagenav = null;
      },
    },
    mounted() {
    // Fetch initial data when component is mounted
    this.fetchMessages();
  },
  };
</script>
  
<style lang="scss" scoped>
  .conversation-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
    .conversation {
      position: relative;
      background-color: white;
      margin: auto;
      top: 50%;
      transform: translateY(-50%);
      z-index: 99;
      width: 600px;
      height: 400px;
      display: flex;
      flex-direction: column;
      .top-bar{
        height:15%;
        width:100%;
        background-color:rgba($color: white, $alpha: 0.8);
        border-bottom:solid 1px #2F3336;
        position:sticky;
        top:0;
        z-index:9;
        display: flex;
        flex-direction: row;
        align-items: center;
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
        // background-color: lightblue;
        height: 73%;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
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
          height: auto;
          display: flex;
          flex-direction: column;
          .sent{
            // background-color: red;
            width: 100%;
            display: flex;
            flex-direction: row;
            .content{
              color: red !important;
            }
            .time{
              color: red;
            }
            .image{
              img{
                max-width: 50px;
                max-height: 40px;
              }
            }
          }
        }
      }
      .bottom{
        width:100%;
        min-height: 12%;
        display:flex;
        flex-direction: row;
        border-top:1px solid #2F3336;
        bottom: 0;
        position: absolute;
        .message-input-container{
          width:80%;
          height:100%;
          display:flex;
          align-items: center;
          justify-content: center;
          padding-right:10px;
          .message-input{
            width:100%;
            height:70%;
            padding-left: 10px;
            display: flex;
            align-items: center;
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
          .preview-cover{
            display:none;
            width:100%;
            height:100% ;
            position:absolute;
            top:-2.5px;
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
          width: 20%;
          justify-content: space-around;
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
  }
  @media(max-width: 768px){
    .conversation{
      max-width: 90%;
    }
  }
</style>