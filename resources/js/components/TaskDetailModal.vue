<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-96 overflow-y-auto">
      <div class="flex justify-between items-start mb-4">
        <h3 class="text-xl font-bold text-gray-800">{{ task.title }}</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
      </div>
      
      <div class="grid grid-cols-2 gap-6 mb-6">
        <!-- Left Column -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <p class="text-gray-600 text-sm">{{ task.description || 'No description' }}</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
            <span
              class="inline-block px-3 py-1 rounded-full text-sm font-medium"
              :class="priorityClass"
            >
              {{ task.priority || 'Not set' }}
            </span>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
            <p class="text-gray-600 text-sm">{{ task.assignee?.name || 'Unassigned' }}</p>
          </div>
        </div>
        
        <!-- Right Column -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
            <p class="text-gray-600 text-sm">{{ task.due_date ? formatDate(task.due_date) : 'No due date' }}</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Comments</label>
            <p class="text-gray-600 text-sm">{{ task.comments?.length || 0 }} comments</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Created</label>
            <p class="text-gray-600 text-sm">{{ formatDate(task.created_at) }}</p>
          </div>
        </div>
      </div>
      
      <!-- Comments Section -->
      <div v-if="task.comments && task.comments.length > 0" class="border-t pt-4">
        <h4 class="font-semibold text-gray-800 mb-3">Comments ({{ task.comments.length }})</h4>
        <div class="space-y-2 max-h-32 overflow-y-auto">
          <div v-for="comment in task.comments" :key="comment.id" class="text-sm bg-gray-50 p-2 rounded">
            <p class="font-medium text-gray-800">{{ comment.user?.name }}</p>
            <p class="text-gray-600">{{ comment.content }}</p>
          </div>
        </div>
      </div>
      
      <div class="flex gap-3 pt-4 mt-4 border-t">
        <button @click="$emit('close')" class="flex-1 btn-secondary">Close</button>
        <button @click="deleteTask" class="btn-secondary text-red-600">Delete Task</button>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import api from '../api/axios'

export default {
  name: 'TaskDetailModal',
  props: {
    task: Object,
    boardId: Number,
  },
  emits: ['close', 'updated'],
  setup(props, { emit }) {
    const priorityClass = computed(() => {
      const classes = {
        low: 'bg-green-100 text-green-800',
        medium: 'bg-yellow-100 text-yellow-800',
        high: 'bg-orange-100 text-orange-800',
        urgent: 'bg-red-100 text-red-800',
      }
      return classes[props.task.priority] || 'bg-gray-100 text-gray-800'
    })
    
    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    }
    
    const deleteTask = async () => {
      if (!confirm('Delete this task?')) return
      
      try {
        const column = props.task.column_id
        await api.delete(
          `/boards/${props.boardId}/columns/${column}/tasks/${props.task.id}`
        )
        emit('updated')
      } catch (error) {
        console.error('Error deleting task:', error)
      }
    }
    
    return {
      priorityClass,
      formatDate,
      deleteTask,
    }
  },
}
</script>
