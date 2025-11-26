import {createRouter, createWebHistory} from 'vue-router';

// We will create these components in a moment
import Login from './components/Login.vue';
import Home from './components/Home.vue';
import MyBookings from "./components/MyBookings.vue";
import PaymentSuccess from './components/PaymentSuccess.vue';
import TeacherDashboard from './components/TeacherDashboard.vue';

const routes = [
    {path: '/', component: Home},
    {
        path: '/login',
        component: Login,
        meta: {guestOnly: true} // Only for users who are NOT logged in
    },
    {
        path: '/my-bookings',
        component: MyBookings,
        meta: {requiresAuth: true} // For any logged-in user
    },
    {
        path: '/payment/success',
        component: PaymentSuccess,
        meta: {requiresAuth: true}
    },
    {
        path: '/teacher/dashboard',
        component: TeacherDashboard,
        meta: {requiresAuth: true, role: 'teacher'} // Only for Teachers
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Global Navigation Guard
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token');
    const userRole = localStorage.getItem('user_role');
    const isLoggedIn = !!token;

    // 1. Handle "Requires Auth" Routes
    if (to.meta.requiresAuth && !isLoggedIn) {
        return next('/login'); // Not logged in? Go to login
    }

    // 2. Handle "Guest Only" Routes (like Login page)
    if (to.meta.guestOnly && isLoggedIn) {
        // If already logged in, redirect based on role
        if (userRole === 'teacher') {
            return next('/teacher/dashboard');
        }
        return next('/'); // Students go home
    }

    // 3. Handle Role-Specific Routes
    if (to.meta.role && to.meta.role !== userRole) {
        // If user tries to access a teacher page but isn't a teacher
        return next('/'); // Redirect to home (or a 403 page)
    }

    // 4. Special Rule: Redirect Teachers away from the Home Page Search
    // If a teacher tries to visit Home ('/'), force them to Dashboard
    if (to.path === '/' && userRole === 'teacher') {
        return next('/teacher/dashboard');
    }

    // 5. Allow navigation
    next();
});

export default router;