<template>
  <div
    draggable="true"
    @dragstart="handleDragStart"
    class="bg-gray-50 rounded-lg p-3 cursor-move hover:shadow-md transition-shadow border-l-4"
    :style="{ borderLeftColor: task.color || '#6366f1' }"
  >
    <div @click="$emit('click')" class="cursor-pointer">
      <h4 class="font-semibold text-gray-800 mb-2 line-clamp-2">{{ task.title }}</h4>
      
      <div class="flex items-center justify-between mb-2">
        <div class="flex gap-2">
          <span
            v-if="task.priority"
            class="text-xs px-2 py-1 rounded font-medium"
            :class="priorityClass"
          >
            {{ task.priority }}
          </span>
        </div>
      </div>
      
      <div v-if="task.due_date" class="text-xs text-gray-500 mb-2">
        Due: {{ formatDate(task.due_date) }}
      </div>
      
      <div v-if="task.assignee" class="flex items-center gap-2 pt-2 border-t">
        <img
          :src="task.assignee.avatar_url || `https://ui-avatars.com/api/?name=${task.assignee.name}`"
          :alt="task.assignee.name"
          class="h-6 w-6 rounded-full"
          :title="task.assignee.name"
        />
        <span class="text-xs text-gray-600">{{ task.assignee.name }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'TaskCard',
  props: {
    task: {
      type: Object,
      required: true,
    },
  },
  emits: ['click'],
  setup(props) {
    const priorityClass = computed(() => {
      const classes = {
        low: 'bg-green-100 text-green-800',
        medium: 'bg-yellow-100 text-yellow-800',
        high: 'bg-orange-100 text-orange-800',
        urgent: 'bg-red-100 text-red-800',
      }
      return classes[props.task.priority] || 'bg-gray-100 text-gray-800'
    })
    
    const handleDragStart = (event) => {
      event.dataTransfer.effectAllowed = 'move'
      event.dataTransfer.setData('taskId', props.task.id)
      event.dataTransfer.setData('columnId', props.task.column_id)
    }
    
    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
      })
    }
    
    return {
      priorityClass,
      handleDragStart,
      formatDate,
    }
  },
}
</script>
