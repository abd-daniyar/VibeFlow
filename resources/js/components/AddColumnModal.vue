<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
      <h3 class="text-xl font-bold text-gray-800 mb-4">Add New Column</h3>
      
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Column Name</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="input-field"
            placeholder="e.g., To Do, In Progress, Done"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
          <div class="flex gap-2">
            <input
              v-model="form.color"
              type="color"
              class="h-10 w-20 rounded cursor-pointer"
            />
            <input
              v-model="form.color"
              type="text"
              class="input-field flex-1"
              placeholder="#6366f1"
            />
          </div>
        </div>
        
        <div class="flex gap-3 pt-4">
          <button
            type="button"
            @click="$emit('close')"
            class="flex-1 btn-secondary"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="flex-1 btn-primary disabled:opacity-50"
          >
            {{ loading ? 'Creating...' : 'Create Column' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import api from '../api/axios'

export default {
  name: 'AddColumnModal',
  props: {
    boardId: Number,
  },
  emits: ['close', 'created'],
  setup(props, { emit }) {
    const form = ref({
      name: '',
      color: '#6366f1',
    })
    
    const loading = ref(false)
    
    const handleSubmit = async () => {
      loading.value = true
      try {
        await api.post(`/boards/${props.boardId}/columns`, form.value)
        emit('created')
      } catch (error) {
        console.error('Error creating column:', error)
      } finally {
        loading.value = false
      }
    }
    
    return {
      form,
      loading,
      handleSubmit,
    }
  },
}
</script>
