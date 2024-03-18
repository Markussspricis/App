import { createStore } from 'vuex';

const store = createStore({
  state() {
    return {
      auth: {
        isAuthenticated: false,
      },
    };
  },
  mutations: {
    login(state) {
      state.auth.isAuthenticated = true;
    },
    logout(state) {
      state.auth.isAuthenticated = false;
    },
  },
  actions: {
    initializeApp(context) {
        // You can perform any initialization logic here
        console.log('App initialization logic goes here');
    },
  },
  getters: {
    isAuthenticated(state) {
      return state.auth.isAuthenticated;
    },
  },
});

export default store;
