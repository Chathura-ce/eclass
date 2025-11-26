<template>
  <div>
    <div class="bg-blue-600 text-white p-12 rounded-lg mb-8 text-center">
      <h2 class="text-4xl font-bold mb-4">Find the Best Tutors in Sri Lanka</h2>
      <p class="text-xl">Book online lessons with verified teachers today.</p>
    </div>

    <div v-if="loading" class="text-center py-10">
      <p class="text-gray-500">Loading teachers...</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="teacher in teachers" :key="teacher.id"
           class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition flex flex-col justify-between">
        <div>
          <div class="flex items-center mb-4">
            <div
                class="bg-gray-200 rounded-full h-12 w-12 flex items-center justify-center text-xl font-bold text-gray-600">
              {{ teacher.user.name.charAt(0) }}
            </div>
            <div class="ml-4">
              <h3 class="font-bold text-lg">{{ teacher.user.name }}</h3>
              <span v-if="teacher.verified"
                    class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Verified</span>
            </div>
          </div>
          <p class="text-gray-600 mb-4 line-clamp-3">
            {{ teacher.bio || 'No bio available.' }}
          </p>
        </div>

        <div class="flex justify-between items-center mt-4 pt-4 border-t">
          <span class="font-bold text-blue-600">LKR {{ teacher.hourly_rate }}/hr</span>

          <button
              v-if="userRole !== 'teacher'"
              @click="openBooking(teacher)"
              class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
            Book Now
          </button>

          <span v-else class="text-xs text-gray-400">
        (Teacher View)
    </span>
        </div>
      </div>
    </div>

    <BookingModal
        v-if="selectedTeacher"
        :show="showModal"
        :teacher="selectedTeacher"
        @close="showModal = false"
    />

  </div>
</template>

<script>
import axios from 'axios';
import BookingModal from './BookingModal.vue'; // <--- Import the modal

export default {
  components: {
    BookingModal // <--- Register the component
  },
  data() {
    return {
      teachers: [],
      loading: true,
      showModal: false,
      selectedTeacher: null,
      userRole: ''
    }
  },
  async mounted() {
    try {
      const response = await axios.get('/api/teachers');
      this.teachers = response.data.data || response.data;
    } catch (error) {
      console.error("Error fetching teachers:", error);
    } finally {
      this.loading = false;
    }
  },
  methods: {
    openBooking(teacher) {
      this.selectedTeacher = teacher; // Set the teacher user clicked on
      this.showModal = true; // Show the modal
    }
  }
}
</script>