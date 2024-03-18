import { createRouter, createWebHistory } from 'vue-router';
import store from './store';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      component: () => import('./components/Index.vue'),
      name: 'Index',
    },
    {
      path: '/login',
      component: () => import('./components/Login.vue'),
      name: 'Login',
    },
    {
      path: '/register',
      component: () => import('./components/Register.vue'),
      name: 'Register',
    },
    {
      path: '/password-update',
      component: () => import('./components/UpdatePassword.vue'),
      name: 'UpdatePassword',
    },
    {
      path: '/home',
      component: () => import('./components/Home/Home.vue'),
      name: 'Home',
    },
  ],
});

router.beforeEach((to, from, next) => {
  console.log('Navigating to:', to.name);
  
  // Check if the user is authenticated using Vuex store
  const isAuthenticated = store.getters.isAuthenticated;

  if (to.name === 'Home') {
    // If user is not authenticated, redirect to login page
    if (!isAuthenticated) {
      console.log('User not authenticated. Redirecting to Login.');
      next({ name: 'Login' });
    } else {
      // If user is authenticated, proceed to the Home route
      console.log('User authenticated. Proceeding to Home.');
      next();
    }
  } else {
    // For other routes, proceed as usual
    next();
  }
});

export default router;
