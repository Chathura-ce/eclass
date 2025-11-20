<template>
  <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">My Bookings</h2>

    <div v-if="loading" class="text-center py-10"><p class="text-gray-500">Loading...</p></div>
    <div v-else-if="bookings.length === 0" class="text-center py-10 bg-gray-50 rounded"><p>No bookings.</p></div>

    <div v-else class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
        <tr class="bg-gray-100 text-gray-600 uppercase text-sm">
          <th class="p-3">ID</th>
          <th class="p-3">Date</th>
          <th class="p-3">Teacher</th>
          <th class="p-3">Price</th>
          <th class="p-3">Status</th>
          <th class="p-3">Action</th> </tr>
        </thead>
        <tbody>
        <tr v-for="booking in bookings" :key="booking.id" class="border-b hover:bg-gray-50">
          <td class="p-3 text-gray-500">#{{ booking.id }}</td>
          <td class="p-3">{{ formatDate(booking.scheduled_at) }}</td>
          <td class="p-3 font-bold text-gray-700">{{ booking.teacher?.name }}</td>
          <td class="p-3">LKR {{ booking.price }}</td>

          <td class="p-3">
                            <span :class="statusColor(booking.status)" class="px-2 py-1 rounded-full text-xs font-bold uppercase">
                                {{ booking.status }}
                            </span>
          </td>

          <td class="p-3">
            <button
                v-if="booking.status === 'pending'"
                @click="payNow(booking)"
                class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
              Pay Now
            </button>
          </td>
        </tr>
        </tbody>
      </table>
    </div>

    <form ref="payhereForm" method="POST" :action="payhereUrl" v-if="paymentConfig">
      <input type="hidden" name="merchant_id" :value="paymentConfig.merchant_id">
      <input type="hidden" name="return_url" :value="paymentConfig.return_url">
      <input type="hidden" name="cancel_url" :value="paymentConfig.cancel_url">
      <input type="hidden" name="notify_url" :value="paymentConfig.notify_url">
      <input type="hidden" name="order_id" :value="paymentConfig.order_id">
      <input type="hidden" name="items" :value="paymentConfig.items">
      <input type="hidden" name="currency" :value="paymentConfig.currency">
      <input type="hidden" name="amount" :value="paymentConfig.amount">
      <input type="hidden" name="first_name" :value="paymentConfig.first_name">
      <input type="hidden" name="last_name" :value="paymentConfig.last_name">
      <input type="hidden" name="email" :value="paymentConfig.email">
      <input type="hidden" name="phone" :value="paymentConfig.phone">
      <input type="hidden" name="address" :value="paymentConfig.address">
      <input type="hidden" name="city" :value="paymentConfig.city">
      <input type="hidden" name="country" :value="paymentConfig.country">
      <input type="hidden" name="hash" :value="paymentConfig.hash">
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      bookings: [],
      loading: true,
      role: '',
      // Payment Data
      paymentConfig: null,
      payhereUrl: 'https://sandbox.payhere.lk/pay/checkout'
    }
  },
  mounted() {
    this.role = localStorage.getItem('user_role');
    this.fetchBookings();
  },
  methods: {
    // ... keep fetchBookings, formatDate, statusColor from previous step ...
    async fetchBookings() {
      const token = localStorage.getItem('token');
      if (!token) return this.$router.push('/login');
      const response = await axios.get('/api/my-bookings', { headers: { Authorization: `Bearer ${token}` } });
      this.bookings = response.data.data;
      this.loading = false;
    },
    formatDate(date) { return new Date(date).toLocaleString(); },
    statusColor(status) { return status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; },

    async payNow(booking) {
      try {
        const token = localStorage.getItem('token');

        // 1. Get the Hash and Config from Backend
        const response = await axios.get(`/api/payment/initiate/${booking.id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });

        this.paymentConfig = response.data;

        // Set correct URL based on sandbox/production
        this.payhereUrl = this.paymentConfig.sandbox
            ? 'https://sandbox.payhere.lk/pay/checkout'
            : 'https://www.payhere.lk/pay/checkout';

        // 2. Wait for Vue to update DOM, then submit form
        this.$nextTick(() => {
          this.$refs.payhereForm.submit();
        });

      } catch (error) {
        alert('Payment initialization failed.');
        console.error(error);
      }
    }
  }
}
</script>