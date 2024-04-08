import { createStore } from 'vuex';
import axios from 'axios';

const TOKEN_KEY = 'user_token';

const store = createStore({
  state: {
    user: null,
    isLoggedIn: false,
    conversations: JSON.parse(localStorage.getItem('conversations')) || [],
  },
  mutations: {
    setUser(state, user) {
      state.user = user;
      state.isLoggedIn = !!user;
    },
    setConversations(state, conversations) {
      state.conversations = conversations;
      localStorage.setItem('conversations', JSON.stringify(conversations));
    },
    addMessageToConversation(state, { conversationId, messages }) {
        const conversation = state.conversations.find(conv => conv.ConversationID === conversationId);
        if (conversation) {
            // Initialize messages array if it's undefined
            if (!Array.isArray(conversation.messages)) {
                conversation.messages = [];
            }
            // Push each new message into the messages array
            messages.forEach(message => {
                conversation.messages.push(message);
            });
        }
    },
    setMessagesForConversation(state, { conversationId, messages }) {
      console.log('Messages for conversation:', messages);
      const conversation = state.conversations.find(conv => conv.UserID === conversationId);
      if (conversation) {
          conversation.messages = messages;
      }
    },  
    removeToken(state) {
      localStorage.removeItem(TOKEN_KEY);
      delete axios.defaults.headers.common['Authorization'];
    },
  },
  getters: {
    user: (state) => {
      return state.user;
    },
    conversations: (state) => { // Add getter for conversations
      return state.conversations;
    },
    getConversationByUserId: (state) => (userId) => {
      return state.conversations.find(conv => conv.UserID === userId);
    },
  },
  actions: {
    initializeApp({ commit }) {
      const token = localStorage.getItem(TOKEN_KEY);
      if (token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

        axios
          .get('/api/user')
          .then((response) => {
            commit('setUser', response.data);
          })
          .catch(() => {
            localStorage.removeItem(TOKEN_KEY);
          });
      }
    },
    async register({ commit }, { Name, UserTag, Email, Password, DOB }) {
      console.log('Register action called');
      try {
        const response = await axios.post('/api/register', {
          Name,
          UserTag,
          Email,
          Password,
          DOB,
        });
        if (response.data.success) {
          commit('setUser', response.data.user);
          localStorage.setItem(TOKEN_KEY, response.data.user.token);
          return response.data;
        } else {
          return response.data;
        }
      } catch(error) {
        console.error(error);
        throw error;
      }
    },
    async login({ commit }, { Email, Password }) {
      console.log('Login action called');
      try {
        const response = await axios.post('/api/login', {
          Email,
          Password,
        });
  
        if (response.data.success) {
          commit('setUser', response.data.user);
          localStorage.setItem(TOKEN_KEY, response.data.user.token);
          return response.data;
        } else {
          throw new Error('Invalid password...');
        }
      } catch (error) {
        console.error(error);
        throw error;
      }
    },

    async logout({ commit }) {
      const response = await axios.post('/api/logout');

      if (response.data.success) {
        commit('setUser', null);
        commit('removeToken');
      }
    },
    async sendMessage({ commit }, messageData) {
      try {
          const formData = new FormData();
          formData.append('ReceiverID', messageData.ReceiverID);
          formData.append('Content', messageData.Content);
          if (messageData.Image) {
              formData.append('image', messageData.Image);
          }
  
          const response = await axios.post('/api/send-message', formData, {
              headers: {
                  'Content-Type': 'multipart/form-data',
              },
          });
  
          console.log('Response from server:', response.data);
  
          // Commit a mutation to add the new message to the conversation
          // commit('addMessageToConversation', { conversationId: response.data.conversationId, message: response.data.message });
          commit('addMessageToConversation', { conversationId: response.data.conversationId, messages: [response.data.message] });
  
          // Emit messageSent event to trigger conversation list update
          this.$emit('messageSent');
  
          return response.data;
      } catch (error) {
          console.error('Error sending message:', error);
          throw error;
      }
    },  
    async fetchConversations({ commit }) {
      try {
        // Fetch conversations from the server
        const response = await axios.get('/api/conversations');
        const conversations = response.data.conversations;

        // Update the state with conversations
        commit('setConversations', conversations);
      } catch (error) {
        console.error('Error fetching conversations:', error);
      }
    },
    async fetchMessages({ commit }, userId) {
      try {
          const response = await axios.get(`/api/user-messages/${userId}`);
          const { messages } = response.data;
  
          // Commit mutation to set the fetched messages
          commit('setMessagesForConversation', { conversationId: userId, messages });
      } catch (error) {
          console.error('Error fetching messages:', error);
          throw error; // Optionally rethrow the error for error handling in the component
      }
    },  
  },
});

export default store;