<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
      <h3 class="text-xl font-bold text-gray-800 mb-4">Board Settings</h3>
      
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Board Name</label>
          <input
            v-model="form.name"
            type="text"
            class="input-field"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea
            v-model="form.description"
            class="input-field"
            rows="3"
          ></textarea>
        </div>
        
        <div class="pt-4 border-t">
          <h4 class="font-semibold text-gray-800 mb-3">Team Members ({{ board.users?.length || 0 }})</h4>
          <div class="space-y-2 max-h-32 overflow-y-auto">
            <div
              v-for="user in board.users"
              :key="user.id"
              class="flex items-center justify-between p-2 bg-gray-50 rounded"
            >
              <span class="text-sm text-gray-800">{{ user.name }}</span>
              <span class="text-xs px-2 py-1 bg-gray-200 rounded">{{ user.pivot?.role || 'member' }}</span>
            </div>
          </div>
        </div>
        
        <div class="flex gap-3 pt-4">
          <button
            type="button"
            @click="$emit('close')"
            class="flex-1 btn-secondary"
          >
            Close
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="flex-1 btn-primary disabled:opacity-50"
          >
            {{ loading ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue'
import api from '../api/axios'

export default {
  name: 'BoardSettingsModal',
  props: {
    board: Object,
  },
  emits: ['close', 'updated'],
  setup(props, { emit }) {
    const form = ref({
      name: '',
      description: '',
    })
    
    const loading = ref(false)
    
    watch(
      () => props.board,
      (newBoard) => {
        if (newBoard) {
          form.value = {
            name: newBoard.name,
            description: newBoard.description,
          }
        }
      },
      { immediate: true }
    )
    
    const handleSubmit = async () => {
      loading.value = true
      try {
        await api.put(`/boards/${props.board.id}`, form.value)
        emit('updated')
      } catch (error) {
        console.error('Error updating board:', error)
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
