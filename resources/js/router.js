import { createRouter, createWebHashHistory } from 'vue-router';

const checkAuth = () => {
  const token = localStorage.getItem('user_token');
  return Boolean(token);
};

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    {
      path: '/',
      component: () => import('./components/Index.vue'),
      name: 'Index',
      meta: { requiresAuth: false },
      beforeEnter: (to, from, next) => {
        if (checkAuth()) {
          next('/home');
        } else {
          next();
        }
      },
    },
    {
      path: '/login',
      component: () => import('./components/Login.vue'),
      meta: { requiresAuth: false },
      name: 'Login',
    },
    {
      path: '/register',
      component: () => import('./components/Register.vue'),
      meta: { requiresAuth: false },
      name: 'Register',
    },
    {
      path: '/password-update',
      component: () => import('./components/UpdatePassword.vue'),
      meta: { requiresAuth: false },
      name: 'UpdatePassword',
    },
    {
      path: '/home',
      component: () => import('./components/Home/Home.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/people',
      component: () => import('./components/Who-to-follow/People.vue'),
      meta: { requiresAuth: true },
    },
    {
     path: '/notifications',
     component: () => import('./components/Notifications/Notifications.vue'),
     meta: { requiresAuth: true },
    },
    {
      path: '/tweet/:tweetID',
      component: () => import('./components/Posts/Posts.vue'),
      name: 'Posts',
      meta: { requiresAuth: true },
    },
    {
      path: '/profile/:UserTag',
      component: () => import('./components/User/User.vue'),
      name: 'Profile',
      meta: { requiresAuth: true },
    },
    {
      path: '/bookmarks',
      component: () => import('./components/Bookmarks/Bookmarks.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/messages',
      component: () => import('./components/Messages/Messages.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/:UserTag/following',
      component: () => import('./components/Following/Following.vue'),
      name: 'Following',
      meta: { requiresAuth: true },
    },
    {
      path: '/:UserTag/followers',
      component: () => import('./components/Followers/Followers.vue'),
      name: 'Followers',
      meta: { requiresAuth: true },
    },
  ],
});

// Navigation guard
router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth) {
    if (checkAuth()) {
      next();
    } else {
      next('/');
    }
  } else {
    next();
  }
});

export default router;