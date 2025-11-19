<template>
  <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">My Bookings</h2>

    <div v-if="loading" class="text-center py-10">
      <p class="text-gray-500">Loading your schedule...</p>
    </div>

    <div v-else-if="bookings.length === 0" class="text-center py-10 bg-gray-50 rounded">
      <p class="text-gray-500 mb-4">You have no bookings yet.</p>
      <router-link to="/" class="text-blue-600 hover:underline">Find a teacher</router-link>
    </div>

    <div v-else class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
        <tr class="bg-gray-100 text-gray-600 uppercase text-sm">
          <th class="p-3">ID</th>
          <th class="p-3">Date & Time</th>
          <th class="p-3">{{ role === 'student' ? 'Teacher' : 'Student' }}</th>
          <th class="p-3">Price</th>
          <th class="p-3">Status</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="booking in bookings" :key="booking.id" class="border-b hover:bg-gray-50">
          <td class="p-3 text-gray-500">#{{ booking.id }}</td>

          <td class="p-3 font-medium">
            {{ formatDate(booking.scheduled_at) }}
          </td>

          <td class="p-3">
                            <span class="font-bold text-gray-700">
                                {{ role === 'student' ? booking.teacher?.name : booking.student?.name }}
                            </span>
          </td>

          <td class="p-3 text-gray-600">
            LKR {{ booking.price }}
          </td>

          <td class="p-3">
                            <span :class="statusColor(booking.status)" class="px-2 py-1 rounded-full text-xs font-bold uppercase">
                                {{ booking.status }}
                            </span>
          </td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      bookings: [],
      loading: true,
      role: ''
    }
  },
  mounted() {
    this.fetchBookings();
    this.role = localStorage.getItem('user_role');
  },
  methods: {
    async fetchBookings() {
      try {
        const token = localStorage.getItem('token');
        // Redirect if not logged in
        if (!token) return this.$router.push('/login');

        const response = await axios.get('/api/my-bookings', {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.bookings = response.data.data;
      } catch (error) {
        console.error("Error loading bookings:", error);
      } finally {
        this.loading = false;
      }
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },
    statusColor(status) {
      if (status === 'confirmed') return 'bg-green-100 text-green-800';
      if (status === 'pending') return 'bg-yellow-100 text-yellow-800';
      if (status === 'cancelled') return 'bg-red-100 text-red-800';
      return 'bg-gray-100 text-gray-800';
    }
  }
}
</script>