<template>
  <div v-if="show" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">

      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold">Book {{ teacher.user.name }}</h3>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">&times;</button>
      </div>

      <div class="mb-6 bg-blue-50 p-4 rounded">
        <p class="text-sm text-gray-600">Hourly Rate</p>
        <p class="text-lg font-bold text-blue-600">LKR {{ teacher.hourly_rate }} / hr</p>
      </div>

      <form @submit.prevent="submitBooking">
        <div class="mb-4">
          <label class="block text-gray-700 mb-2">Select Date & Time</label>
          <input
              v-model="scheduledAt"
              type="datetime-local"
              class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500"
              required
              :min="minDate"
          >
          <p class="text-xs text-gray-500 mt-1">Please select a time at least 24 hours from now.</p>
        </div>

        <div v-if="errorMessage" class="mb-4 text-red-500 text-sm">
          {{ errorMessage }}
        </div>

        <div class="flex justify-end space-x-2">
          <button
              type="button"
              @click="$emit('close')"
              class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-100">
            Cancel
          </button>
          <button
              type="submit"
              :disabled="isSubmitting"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
            {{ isSubmitting ? 'Booking...' : 'Confirm Booking' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    show: Boolean,
    teacher: Object
  },
  data() {
    return {
      scheduledAt: '',
      isSubmitting: false,
      errorMessage: ''
    }
  },
  computed: {
    minDate() {
      // Helper to prevent selecting past dates (simple implementation)
      const now = new Date();
      now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
      return now.toISOString().slice(0, 16);
    }
  },
  methods: {
    async submitBooking() {
      this.errorMessage = '';
      this.isSubmitting = true;

      // 1. Check if user is logged in
      const token = localStorage.getItem('token');
      if (!token) {
        alert('You must be logged in to book a teacher.');
        this.$router.push('/login');
        return;
      }

      try {
        // 2. Send API Request
        await axios.post('/api/bookings', {
          teacher_id: this.teacher.user.id,
          scheduled_at: this.scheduledAt
        }, {
          headers: { Authorization: `Bearer ${token}` } // Attach Token
        });

        // 3. Success!
        alert('Booking request sent successfully!');
        this.$emit('close'); // Close modal
        this.scheduledAt = ''; // Reset form

      } catch (error) {
        // 4. Handle Errors
        if (error.response && error.response.status === 401) {
          alert('Session expired. Please login again.');
          this.$router.push('/login');
        } else if (error.response && error.response.data.errors) {
          this.errorMessage = Object.values(error.response.data.errors).flat().join(' ');
        } else {
          this.errorMessage = error.response?.data?.message || 'Failed to book. Try again.';
        }
      } finally {
        this.isSubmitting = false;
      }
    }
  }
}
</script>