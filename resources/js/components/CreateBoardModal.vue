<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
      <h3 class="text-xl font-bold text-gray-800 mb-4">Create New Board</h3>
      
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Board Name</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="input-field"
            placeholder="e.g., Project Alpha"
            autofocus
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea
            v-model="form.description"
            class="input-field"
            placeholder="Board description (optional)"
            rows="3"
          ></textarea>
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
            {{ loading ? 'Creating...' : 'Create Board' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useBoardStore } from '../stores/boardStore'

export default {
  name: 'CreateBoardModal',
  emits: ['close', 'created'],
  setup(props, { emit }) {
    const boardStore = useBoardStore()
    
    const form = ref({
      name: '',
      description: '',
    })
    
    const loading = ref(false)
    
    const handleSubmit = async () => {
      loading.value = true
      try {
        await boardStore.createBoard(form.value)
        emit('created')
      } catch (error) {
        console.error('Error creating board:', error)
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
