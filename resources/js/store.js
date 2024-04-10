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
    setConversation(state, conversation) {
      // Find if the conversation already exists in state
      const existingConversationIndex = state.conversations.findIndex(conv => conv.ConversationID === conversation.ConversationID);
      if (existingConversationIndex !== -1) {
        // If the conversation already exists, update it
        state.conversations[existingConversationIndex] = conversation;
      } else {
        // If the conversation doesn't exist, add it to the array
        state.conversations.push(conversation);
      }
      // localStorage.setItem('conversations', JSON.stringify(state.conversations)); // Optionally update localStorage
    },
    // Add message to the conversation in state
    addMessageToConversation(state, { conversationId, message }) {
      const conversation = state.conversations.find(conv => conv.ConversationID === conversationId);
      if (conversation) {
        // Ensure conversation.messages is an array
        conversation.messages = conversation.messages || [];
        conversation.messages.push(message);
      }
    },    
    setMessagesForConversation(state, { conversationId, messages }) {
      const conversation = state.conversations.find(conv => conv.ConversationID === conversationId);
      if (conversation) {
        conversation.messages = messages;
      }
    },
    setConversationId(state, conversationId) {
      state.selectedConversationId = conversationId; // Assuming you have a state property named selectedConversationId to store the conversation ID
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
        console.log('Conversation Data:', response.data);
    
        // Check if ConversationID is present in the response
        if (response.data && response.data.conversation && response.data.conversation.ConversationID) {
          commit('setConversation', response.data.conversation);
          commit('setConversationId', response.data.conversation.ConversationID); // Set the ConversationID in the store
          return response.data.conversation;
        } else {
          throw new Error('ConversationID not found in the response');
        }
      } catch (error) {
        console.error('Error fetching conversation:', error);
        throw error;
      }
    },    
    // Add action to create a conversation with a user
    async createConversation(_, userId) { // Removed `commit` from function parameters
      try {
        const response = await axios.post('/api/conversations', { userId });
        // You may want to commit the created conversation to the state if needed
        return response.data.conversation;
      } catch (error) {
        console.error('Error creating conversation:', error);
        throw error;
      }
    },
    async fetchMessages({ commit, state }) {
      try {
        const conversationId = state.selectedConversationId; // Get the ConversationID from the store
        const response = await axios.get(`/api/conversation-messages/${conversationId}`);
        const { messages } = response.data;
        commit('setMessagesForConversation', { conversationId, messages });
      } catch (error) {
        console.error('Error fetching messages:', error);
        throw error;
      }
    },    
  },
});

export default store;