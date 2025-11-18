<template>
  <div class="min-h-screen">
    <nav class="bg-white shadow p-4 mb-6">
      <div class="container mx-auto flex justify-between">
        <h1 class="text-xl font-bold text-blue-600">E-Class MVP</h1>
        <div>
          <router-link to="/" class="mr-4 text-gray-600">Home</router-link>
          <router-link to="/login" class="text-gray-600">Login</router-link>
        </div>
      </div>
    </nav>

    <div class="container mx-auto p-4">
      <router-view></router-view>
    </div>
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>