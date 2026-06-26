<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
      <h3 class="text-xl font-bold text-gray-800 mb-4">Add New Task</h3>
      
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
          <input
            v-model="form.title"
            type="text"
            required
            class="input-field"
            placeholder="Task title"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea
            v-model="form.description"
            class="input-field"
            placeholder="Task description"
            rows="3"
          ></textarea>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
          <select v-model="form.priority" class="input-field">
            <option value="low">Low</option>
            <option value="medium" selected>Medium</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
          <input
            v-model="form.due_date"
            type="datetime-local"
            class="input-field"
          />
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
            {{ loading ? 'Creating...' : 'Create Task' }}
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
  name: 'AddTaskModal',
  props: {
    columnId: Number,
    boardId: Number,
  },
  emits: ['close', 'created'],
  setup(props, { emit }) {
    const form = ref({
      title: '',
      description: '',
      priority: 'medium',
      due_date: '',
    })
    
    const loading = ref(false)
    
    const handleSubmit = async () => {
      loading.value = true
      try {
        await api.post(
          `/boards/${props.boardId}/columns/${props.columnId}/tasks`,
          form.value
        )
        emit('created')
      } catch (error) {
        console.error('Error creating task:', error)
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
