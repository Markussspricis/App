import { createApp } from 'vue';
import axios from 'axios';
import App from './components/App.vue';
import router from './router';
import store from './store'; 

axios.defaults.baseURL = 'http://127.0.0.1:8000';

const app = createApp(App);

app.use(router);
app.use(store);
app.config.globalProperties.$axios = axios;

app.config.warnHandler = (warning, vm, trace) => {
  if (warning.includes('ion-icons')) {
    return;
  }
};

app.mount('#app');

store.dispatch('initializeApp')