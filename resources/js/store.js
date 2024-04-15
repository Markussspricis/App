import { createStore } from 'vuex';
import axios from 'axios';

const TOKEN_KEY = 'user_token';

const store = createStore({
  state: {
    user: null,
    isLoggedIn: false,
    // conversations: JSON.parse(localStorage.getItem('conversations')) || [],
    conversations: [],
  },
  mutations: {
    setUser(state, user) {
      state.user = user;
      state.isLoggedIn = !!user;
    },
    setConversation(state, { conversation, messages }) {
      // Update or add conversation to the conversations array
      const index = state.conversations.findIndex(conv => conv.conversation.ConversationID === conversation.ConversationID);
      if (index !== -1) {
          // If conversation exists, update its messages
          state.conversations[index].messages = messages;
      } else {
          // If conversation doesn't exist, add it with messages
          state.conversations.push({ conversation, messages });
      }
  },
    setMessagesForConversation(state, { conversationId, messages }) {
      const conversation = state.conversations.find(conv => conv.ConversationID === conversationId);
      if (conversation) {
        conversation.messages = messages;
      }
    },
    addMessageToConversation(state, { conversationId, message }) {
      const conversation = state.conversations.find(conv => conv.conversation.ConversationID === conversationId);
      if (conversation) {
          conversation.messages.push(message);
      }
  },
    setConversationId(state, conversationId) {
      state.selectedConversationId = conversationId; // Assuming you have a state property named selectedConversationId to store the conversation ID
      state.selectedConversationMessages = state.conversations.find(conv => conv.ConversationID === conversationId)?.messages || [];
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
    
        // Commit the message to the conversation
        commit('addMessageToConversation', { conversationId: messageData.ReceiverID, message: response.data.message });
    
        return response.data;
      } catch (error) {
        console.error('Error sending message:', error);
        throw error;
      }
    },    
    async fetchConversation({ commit }, userId) {
      try {
          const response = await axios.get(`/api/conversations/${userId}`);
          const { conversation, messages } = response.data;
  
          if (conversation) {
              // Update or add conversation to the conversations array
              commit('setConversation', { conversation, messages });
              return conversation;
          } else {
              console.warn('Conversation data is null in the response:', response.data);
              return null;
          }
      } catch (error) {
          console.error('Error fetching conversation:', error);
          throw error;
      }
  },
    
    // Add action to create a conversation with a user
    async createConversation({ commit }, userId) { // Modified to accept `commit`
      try {
        const response = await axios.post('/api/conversations', { userId });
        // Update conversation in the store
        commit('setConversation', response.data);
        return response.data.conversation;
      } catch (error) {
        console.error('Error creating conversation:', error);
        throw error;
      }
    },
  },
});

export default store;