import { createStore } from 'vuex';
import axios from 'axios';

const TOKEN_KEY = 'user_token';

const store = createStore({
  state: {
    user: null,
    isLoggedIn: false,
  },
  mutations: {
    setUser(state, user) {
      state.user = user;
      state.isLoggedIn = !!user;
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
  },
});

export default store;