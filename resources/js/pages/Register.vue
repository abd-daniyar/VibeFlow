<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center px-4">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
      <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Join VibeFlow</h2>
      
      <form @submit.prevent="handleRegister" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="input-field"
            placeholder="Your Name"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="input-field"
            placeholder="your@email.com"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
          <input
            v-model="form.password"
            type="password"
            required
            class="input-field"
            placeholder="••••••••"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            required
            class="input-field"
            placeholder="••••••••"
          />
        </div>
        
        <button
          :disabled="loading"
          type="submit"
          class="w-full btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ loading ? 'Creating account...' : 'Sign Up' }}
        </button>
        
        <p v-if="error" class="text-red-500 text-sm text-center">{{ error }}</p>
        
        <p class="text-center text-gray-600">
          Already have an account?
          <router-link to="/login" class="text-indigo-600 hover:text-indigo-700 font-medium">
            Login
          </router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

export default {
  name: 'Register',
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    
    const form = ref({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
    })
    
    const loading = ref(false)
    const error = ref('')
    
    const handleRegister = async () => {
      if (form.value.password !== form.value.password_confirmation) {
        error.value = 'Passwords do not match'
        return
      }
      
      loading.value = true
      error.value = ''
      
      try {
        await authStore.register(
          form.value.name,
          form.value.email,
          form.value.password,
          form.value.password_confirmation
        )
        router.push('/')
      } catch (err) {
        error.value = err.response?.data?.message || 'Registration failed'
      } finally {
        loading.value = false
      }
    }
    
    return {
      form,
      loading,
      error,
      handleRegister,
    }
  },
}
</script>
