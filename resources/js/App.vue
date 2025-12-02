<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow p-4 mb-6">
      <div class="container mx-auto flex justify-between items-center">
        <router-link :to="homeLink" class="text-xl font-bold text-blue-600">
          E-Class MVP
        </router-link>

        <div>
          <router-link :to="homeLink" class="mr-4 text-gray-600 hover:text-blue-600">
            {{ userRole === 'teacher' ? 'Dashboard' : 'Home' }}
          </router-link>

          <router-link v-if="isLoggedIn" to="/my-bookings" class="mr-4 text-gray-600 hover:text-blue-600 font-medium">
            {{ userRole === 'teacher' ? 'My Schedule' : 'My Bookings' }}
          </router-link>

          <router-link v-if="!isLoggedIn" to="/login" class="text-gray-600 hover:text-blue-600">
            Login
          </router-link>

          <button v-else @click="logout" class="text-red-500 hover:text-red-700 ml-4 font-semibold">
            Logout
          </button>
        </div>
      </div>
    </nav>

    <div class="container mx-auto p-4">
      <router-view @login-success="checkLoginStatus"></router-view>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'App',
  data() {
    return {
      isLoggedIn: false,
      userRole: ''
    }
  },
  computed:{
    homeLink(){
      return this.userRole === 'teacher' ? '/teacher/dashboard' : '/';
    }
  },
  created() {
    this.checkLoginStatus();
  },
  watch: {
    // Watch for route changes to update the navbar (e.g., after redirecting)
    $route() {
      this.checkLoginStatus();
    }
  },
  methods: {
    checkLoginStatus() {
      // Check if a token exists in LocalStorage
      this.isLoggedIn = !!localStorage.getItem('token');
      this.userRole = localStorage.getItem('user_role');
    },
    async logout() {
      try {
        const token = localStorage.getItem('token');

        // 1. Call the Backend API to invalidate token
        if (token) {
          await axios.post('/api/logout', {}, {
            headers: { Authorization: `Bearer ${token}` }
          });
        }
      } catch (error) {
        console.error("Logout error:", error);
      } finally {
        // 2. Clear LocalStorage
        localStorage.removeItem('token');
        localStorage.removeItem('user_role');

        // 3. Update State
        this.isLoggedIn = false;
        this.userRole = ''; // Clear role
        // 4. Redirect to Log in
        this.$router.push('/login');
        // alert('Logged out successfully.');
      }
    }
  }
}
</script>