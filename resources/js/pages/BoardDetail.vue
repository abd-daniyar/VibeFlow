<template>
  <div class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <div class="bg-white shadow-sm">
      <div class="max-w-full mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ board.name }}</h1>
            <p class="text-gray-600 mt-1">{{ board.description }}</p>
          </div>
          <div class="flex items-center space-x-4">
            <button
              @click="showAddColumn = true"
              class="btn-primary"
            >
              + Add Column
            </button>
            <button
              @click="showSettings = true"
              class="btn-secondary"
            >
              Settings
            </button>
            <router-link to="/" class="text-gray-600 hover:text-gray-800">
              ← Back
            </router-link>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center h-96">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>
    
    <!-- Kanban Board -->
    <div v-else class="p-6 overflow-x-auto">
      <div class="flex gap-6 min-w-max">
        <!-- Column -->
        <div
          v-for="column in columns"
          :key="column.id"
          class="bg-white rounded-lg shadow-sm w-96 flex flex-col"
        >
          <!-- Column Header -->
          <div
            class="p-4 flex justify-between items-center border-b"
            :style="{ borderTopColor: column.color, borderTopWidth: '3px' }"
          >
            <div>
              <h3 class="font-bold text-lg text-gray-800">{{ column.name }}</h3>
              <p class="text-sm text-gray-500">{{ column.tasks.length }} tasks</p>
            </div>
            <button
              @click="deleteColumn(column.id)"
              class="text-gray-400 hover:text-red-600"
              title="Delete column"
            >
              ✕
            </button>
          </div>
          
          <!-- Tasks Container (Draggable) -->
          <div
            v-if="column.tasks.length > 0"
            class="flex-1 p-4 space-y-3 overflow-y-auto"
            @drop="handleDrop($event, column.id)"
            @dragover.prevent
            @dragenter.prevent
          >
            <TaskCard
              v-for="task in column.tasks"
              :key="task.id"
              :task="task"
              @click="selectTask(task)"
            />
          </div>
          
          <div v-else class="flex-1 p-4 text-center text-gray-400">No tasks yet</div>
          
          <!-- Add Task Button -->
          <button
            @click="showAddTask = true; selectedColumnId = column.id"
            class="m-4 p-2 text-gray-600 hover:bg-gray-100 rounded font-medium text-sm"
          >
            + Add Task
          </button>
        </div>
      </div>
    </div>
    
    <!-- Modals -->
    <AddTaskModal
      v-if="showAddTask"
      :column-id="selectedColumnId"
      :board-id="board.id"
      @close="showAddTask = false"
      @created="handleTaskCreated"
    />
    
    <AddColumnModal
      v-if="showAddColumn"
      :board-id="board.id"
      @close="showAddColumn = false"
      @created="handleColumnCreated"
    />
    
    <TaskDetailModal
      v-if="selectedTask"
      :task="selectedTask"
      :board-id="board.id"
      @close="selectedTask = null"
      @updated="handleTaskUpdated"
    />
    
    <BoardSettingsModal
      v-if="showSettings"
      :board="board"
      @close="showSettings = false"
      @updated="handleBoardUpdated"
    />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useBoardStore } from '../stores/boardStore'
import TaskCard from '../components/TaskCard.vue'
import AddTaskModal from '../components/AddTaskModal.vue'
import AddColumnModal from '../components/AddColumnModal.vue'
import TaskDetailModal from '../components/TaskDetailModal.vue'
import BoardSettingsModal from '../components/BoardSettingsModal.vue'

export default {
  name: 'BoardDetail',
  components: {
    TaskCard,
    AddTaskModal,
    AddColumnModal,
    TaskDetailModal,
    BoardSettingsModal,
  },
  setup() {
    const route = useRoute()
    const boardStore = useBoardStore()
    
    const board = ref({})
    const columns = ref([])
    const loading = ref(false)
    const showAddTask = ref(false)
    const showAddColumn = ref(false)
    const showSettings = ref(false)
    const selectedTask = ref(null)
    const selectedColumnId = ref(null)
    
    const fetchBoard = async () => {
      loading.value = true
      try {
        await boardStore.fetchBoard(route.params.id)
        board.value = boardStore.currentBoard
        columns.value = board.value.columns || []
      } finally {
        loading.value = false
      }
    }
    
    const handleDrop = async (event, columnId) => {
      event.preventDefault()
      const taskId = event.dataTransfer.getData('taskId')
      
      if (!taskId) return
      
      // Move task to new column
      try {
        const sourceColumnId = parseInt(event.dataTransfer.getData('columnId'))
        
        if (sourceColumnId === columnId) {
          // Same column - just reorder
          const tasks = columns.value.find(c => c.id === columnId).tasks
          const order = tasks.length
          // Reorder endpoint could be called here
        } else {
          // Different column - move task
          const response = await fetch(
            `/api/boards/${board.value.id}/columns/${sourceColumnId}/tasks/${taskId}/move`,
            {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
              },
              body: JSON.stringify({
                column_id: columnId,
                order: 1,
              }),
            }
          )
          
          if (response.ok) {
            await fetchBoard()
          }
        }
      } catch (error) {
        console.error('Error moving task:', error)
      }
    }
    
    const deleteColumn = async (columnId) => {
      if (!confirm('Delete this column? All tasks will be deleted.')) return
      
      try {
        await fetch(
          `/api/boards/${board.value.id}/columns/${columnId}`,
          {
            method: 'DELETE',
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
          }
        )
        await fetchBoard()
      } catch (error) {
        console.error('Error deleting column:', error)
      }
    }
    
    const selectTask = (task) => {
      selectedTask.value = task
    }
    
    const handleTaskCreated = () => {
      showAddTask.value = false
      fetchBoard()
    }
    
    const handleColumnCreated = () => {
      showAddColumn.value = false
      fetchBoard()
    }
    
    const handleTaskUpdated = () => {
      selectedTask.value = null
      fetchBoard()
    }
    
    const handleBoardUpdated = () => {
      showSettings.value = false
      fetchBoard()
    }
    
    onMounted(() => {
      fetchBoard()
    })
    
    return {
      board,
      columns,
      loading,
      showAddTask,
      showAddColumn,
      showSettings,
      selectedTask,
      selectedColumnId,
      handleDrop,
      deleteColumn,
      selectTask,
      handleTaskCreated,
      handleColumnCreated,
      handleTaskUpdated,
      handleBoardUpdated,
    }
  },
}
</script>
