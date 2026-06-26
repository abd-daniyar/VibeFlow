import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../api/axios'

export const useBoardStore = defineStore('board', () => {
  const boards = ref([])
  const currentBoard = ref(null)
  const boardColumns = ref([])
  const loading = ref(false)
  const error = ref('')
  const filter = ref({
    search: '',
    sortBy: 'updated',
    sortOrder: 'desc',
  })

  const filteredBoards = computed(() => {
    let result = [...boards.value]

    if (filter.value.search) {
      result = result.filter((b) =>
        b.name.toLowerCase().includes(filter.value.search.toLowerCase())
      )
    }

    result.sort((a, b) => {
      let aVal, bVal

      if (filter.value.sortBy === 'name') {
        aVal = a.name.toLowerCase()
        bVal = b.name.toLowerCase()
      } else if (filter.value.sortBy === 'created') {
        aVal = new Date(a.created_at)
        bVal = new Date(b.created_at)
      } else {
        aVal = new Date(a.updated_at)
        bVal = new Date(b.updated_at)
      }

      if (filter.value.sortOrder === 'asc') {
        return aVal > bVal ? 1 : -1
      } else {
        return aVal < bVal ? 1 : -1
      }
    })

    return result
  })

  const boardCount = computed(() => filteredBoards.value.length)

  const fetchBoards = async () => {
    loading.value = true
    error.value = ''
    try {
      const response = await api.get('/boards')
      boards.value = response.data.data || response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch boards'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchBoard = async (id) => {
    loading.value = true
    error.value = ''
    try {
      const response = await api.get(`/boards/${id}`)
      currentBoard.value = response.data
      boardColumns.value = response.data.columns || []
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch board'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createBoard = async (data) => {
    loading.value = true
    error.value = ''
    try {
      const response = await api.post('/boards', data)
      boards.value.push(response.data)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create board'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateBoard = async (id, data) => {
    loading.value = true
    error.value = ''
    try {
      const response = await api.put(`/boards/${id}`, data)
      const index = boards.value.findIndex((b) => b.id === id)
      if (index !== -1) {
        boards.value[index] = response.data
      }
      if (currentBoard.value?.id === id) {
        currentBoard.value = response.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update board'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteBoard = async (id) => {
    loading.value = true
    error.value = ''
    try {
      await api.delete(`/boards/${id}`)
      boards.value = boards.value.filter((b) => b.id !== id)
      if (currentBoard.value?.id === id) {
        currentBoard.value = null
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete board'
      throw err
    } finally {
      loading.value = false
    }
  }

  const addUserToBoard = async (boardId, userId, role = 'editor') => {
    try {
      const response = await api.post(`/boards/${boardId}/users`, {
        user_id: userId,
        role,
      })
      if (currentBoard.value?.id === boardId) {
        currentBoard.value.users = response.data.board.users
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to add user'
      throw err
    }
  }

  const removeUserFromBoard = async (boardId, userId) => {
    try {
      const response = await api.delete(`/boards/${boardId}/users/${userId}`)
      if (currentBoard.value?.id === boardId) {
        currentBoard.value.users = response.data.board.users
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to remove user'
      throw err
    }
  }

  const setFilter = (newFilter) => {
    filter.value = { ...filter.value, ...newFilter }
  }

  const clearError = () => {
    error.value = ''
  }

  return {
    boards,
    currentBoard,
    boardColumns,
    loading,
    error,
    filter,
    filteredBoards,
    boardCount,
    fetchBoards,
    fetchBoard,
    createBoard,
    updateBoard,
    deleteBoard,
    addUserToBoard,
    removeUserFromBoard,
    setFilter,
    clearError,
  }
})
