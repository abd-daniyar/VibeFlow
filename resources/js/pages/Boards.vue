<template>
  <div class="max-w-7xl mx-auto py-8 px-4">
    <div class="flex justify-between items-center mb-8">
      <h2 class="text-3xl font-bold text-gray-800">Your Boards</h2>
      <button
        @click="showCreateModal = true"
        class="btn-primary"
      >
        + New Board
      </button>
    </div>
    
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>
    
    <!-- Empty State -->
    <div v-else-if="boards.length === 0" class="text-center py-12">
      <p class="text-gray-500 text-lg mb-4">No boards yet. Create your first one!</p>
      <button
        @click="showCreateModal = true"
        class="btn-primary"
      >
        Create First Board
      </button>
    </div>
    
    <!-- Boards Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="board in boards"
        :key="board.id"
        class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow cursor-pointer"
        @click="$router.push(`/board/${board.id}`)"
      >
        <div class="p-6">
          <h3 class="text-xl font-bold text-gray-800 mb-2">{{ board.name }}</h3>
          <p class="text-gray-600 mb-4 line-clamp-2">{{ board.description || 'No description' }}</p>
          <div class="flex items-center justify-between pt-4 border-t">
            <div class="flex -space-x-2">
              <img
                v-for="user in board.users.slice(0, 3)"
                :key="user.id"
                :src="user.avatar_url || `https://ui-avatars.com/api/?name=${user.name}`"
                :alt="user.name"
                class="h-8 w-8 rounded-full border-2 border-white"
                :title="user.name"
              />
            </div>
            <span class="text-sm text-gray-500">{{ board.columns.length }} columns</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Create Board Modal -->
    <CreateBoardModal
      v-if="showCreateModal"
      @close="showCreateModal = false"
      @created="handleBoardCreated"
    />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useBoardStore } from '../stores/boardStore'
import CreateBoardModal from '../components/CreateBoardModal.vue'

export default {
  name: 'Boards',
  components: {
    CreateBoardModal,
  },
  setup() {
    const boardStore = useBoardStore()
    const boards = ref([])
    const loading = ref(false)
    const showCreateModal = ref(false)
    
    const fetchBoards = async () => {
      loading.value = true
      try {
        await boardStore.fetchBoards()
        boards.value = boardStore.boards.data || boardStore.boards
      } finally {
        loading.value = false
      }
    }
    
    const handleBoardCreated = () => {
      showCreateModal.value = false
      fetchBoards()
    }
    
    onMounted(() => {
      fetchBoards()
    })
    
    return {
      boards,
      loading,
      showCreateModal,
      handleBoardCreated,
    }
  },
}
</script>
