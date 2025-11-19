<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow p-4 mb-6">
      <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-xl font-bold text-blue-600">E-Class MVP</h1>

        <div>
          <router-link to="/" class="mr-4 text-gray-600 hover:text-blue-600">Home</router-link>

          <router-link v-if="!isLoggedIn" to="/login" class="text-gray-600 hover:text-blue-600">
            Login
          </router-link>

          <template v-if="isLoggedIn">
            <router-link to="/my-bookings" class="mr-4 text-gray-600 hover:text-blue-600 font-medium">
              My Bookings
            </router-link>

            <button @click="logout" class="text-red-500 hover:text-red-700 ml-4 font-semibold">
              Logout
            </button>
          </template>
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
      isLoggedIn: false
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

        // 4. Redirect to Login
        this.$router.push('/login');
        alert('Logged out successfully.');
      }
    }
  }
}
</script>