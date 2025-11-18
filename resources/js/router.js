import { createRouter, createWebHistory } from 'vue-router';

// We will create these components in a moment
import Login from './components/Login.vue';
import Home from './components/Home.vue';

const routes = [
    { path: '/', component: Home },
    { path: '/login', component: Login },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;