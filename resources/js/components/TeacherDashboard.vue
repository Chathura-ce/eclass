<template>
  <div class="max-w-6xl mx-auto">
    <h2 class="text-3xl font-bold mb-8 text-gray-800">Teacher Dashboard</h2>

    <div v-if="loading" class="text-center py-20">
      <p class="text-gray-500 text-lg">Loading your stats...</p>
    </div>

    <div v-else>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
          <div class="text-gray-500 text-sm uppercase font-bold mb-1">Total Earnings</div>
          <div class="text-3xl font-bold text-gray-800">LKR {{ stats.earnings }}</div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-500">
          <div class="text-gray-500 text-sm uppercase font-bold mb-1">Total Classes</div>
          <div class="text-3xl font-bold text-gray-800">{{ stats.total_bookings }}</div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-yellow-500">
          <div class="text-gray-500 text-sm uppercase font-bold mb-1">Pending Requests</div>
          <div class="text-3xl font-bold text-gray-800">{{ stats.pending_requests }}</div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
          <h3 class="text-lg font-bold text-gray-700">Upcoming Classes</h3>
          <router-link to="/my-bookings" class="text-blue-600 text-sm hover:underline">View All</router-link>
        </div>

        <div v-if="stats.upcoming_classes.length === 0" class="p-8 text-center text-gray-500">
          No upcoming classes scheduled.
        </div>

        <table v-else class="w-full text-left">
          <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
          <tr>
            <th class="px-6 py-3">Student</th>
            <th class="px-6 py-3">Date & Time</th>
            <th class="px-6 py-3">Status</th>
          </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
          <tr v-for="cls in stats.upcoming_classes" :key="cls.id">
            <td class="px-6 py-4 font-medium text-gray-900">{{ cls.student.name }}</td>
            <td class="px-6 py-4 text-gray-600">{{ formatDate(cls.scheduled_at) }}</td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800" v-if="cls.status === 'confirmed'">Confirmed</span>
              <span class="px-2 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800" v-else>Pending</span>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      stats: {
        earnings: '0.00',
        total_bookings: 0,
        pending_requests: 0,
        upcoming_classes: []
      },
      loading: true
    }
  },
  mounted() {
    this.fetchDashboard();
  },
  methods: {
    async fetchDashboard() {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('/api/teacher/dashboard', {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.stats = response.data;
      } catch (error) {
        console.error("Error loading dashboard:", error);
      } finally {
        this.loading = false;
      }
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
  }
}
</script>