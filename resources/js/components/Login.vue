<template>
  <div class="max-w-md mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Login</h2>

    <form @submit.prevent="handleLogin">
      <div class="mb-4">
        <label class="block mb-1">Email</label>
        <input v-model="form.email" type="email" class="w-full border p-2 rounded" required>
      </div>

      <div class="mb-6">
        <label class="block mb-1">Password</label>
        <input v-model="form.password" type="password" class="w-full border p-2 rounded" required>
      </div>

      <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">
        Login
      </button>

      <p v-if="errorMessage" class="text-red-500 mt-4 text-sm">{{ errorMessage }}</p>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      form: {
        email: '',
        password: ''
      },
      errorMessage: ''
    }
  },
  methods: {
    async handleLogin() {
      this.errorMessage = '';
      try {
        // Call the Laravel API we built
        const response = await axios.post('/api/login', this.form);

        // 1. Get the token
        const token = response.data.access_token;
        const user = response.data.user;

        // 2. Save it to LocalStorage (so we stay logged in)
        localStorage.setItem('token', token);
        localStorage.setItem('user_role', user.role);

        // 3. Alert and Redirect
        // alert('Login Successful! Welcome ' + user.name);

        // This forces App.vue to re-check localStorage immediately
        this.$emit('login-success');

        // Redirect based on role
        if (user.role === 'teacher') {
          this.$router.push('/teacher/dashboard');
        } else {
          this.$router.push('/');
        }

        // this.$router.push('/'); // Go Home

      } catch (error) {
        if (error.response && error.response.data.errors) {
          this.errorMessage = error.response.data.message || 'Invalid credentials.';
        } else {
          this.errorMessage = 'Something went wrong.';
        }
      }
    }
  }
}
</script>