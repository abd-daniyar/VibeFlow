<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-50">
    <nav class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
          <h1 class="text-2xl font-bold text-indigo-600">VibeFlow</h1>
          <router-link to="/" class="text-gray-700 hover:text-indigo-600">Boards</router-link>
        </div>
      </div>
    </nav>
    <router-view />
  </div>
</template>

<script>
export default {
  name: 'App',
}
</script>
