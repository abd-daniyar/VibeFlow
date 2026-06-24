import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/axios'

export const useColumnStore = defineStore('column', () => {
  const columns = ref([])
  const columnMap = ref({}) // For quick lookup
  const loading = ref(false)
  const error = ref('')

  const getColumn = (id) => columnMap.value[id]

  const fetchColumns = async (boardId) => {
    loading.value = true
    error.value = ''
    try {
      const response = await api.get(`/boards/${boardId}/columns`)
      columns.value = response.data
      columnMap.value = {}
      columns.value.forEach((col) => {
        columnMap.value[col.id] = col
      })
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch columns'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createColumn = async (boardId, data) => {
    loading.value = true
    error.value = ''
    try {
      const response = await api.post(`/boards/${boardId}/columns`, data)
      columns.value.push(response.data)
      columnMap.value[response.data.id] = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create column'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateColumn = async (boardId, columnId, data) => {
    loading.value = true
    error.value = ''
    try {
      const response = await api.put(`/boards/${boardId}/columns/${columnId}`, data)
      const index = columns.value.findIndex((c) => c.id === columnId)
      if (index !== -1) {
        columns.value[index] = response.data
        columnMap.value[columnId] = response.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update column'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteColumn = async (boardId, columnId) => {
    loading.value = true
    error.value = ''
    try {
      await api.delete(`/boards/${boardId}/columns/${columnId}`)
      columns.value = columns.value.filter((c) => c.id !== columnId)
      delete columnMap.value[columnId]
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete column'
      throw err
    } finally {
      loading.value = false
    }
  }

  const reorderColumns = async (boardId, columnIds) => {
    try {
      const response = await api.post(`/boards/${boardId}/columns/reorder`, {
        columns: columnIds,
      })
      // Reorder locally
      const newOrder = columnIds.map((id) => columnMap.value[id])
      columns.value = newOrder
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to reorder columns'
      throw err
    }
  }

  const updateColumnLocally = (columnId, updates) => {
    const column = columnMap.value[columnId]
    if (column) {
      Object.assign(column, updates)
    }
  }

  const clearError = () => {
    error.value = ''
  }

  return {
    columns,
    columnMap,
    loading,
    error,
    getColumn,
    fetchColumns,
    createColumn,
    updateColumn,
    deleteColumn,
    reorderColumns,
    updateColumnLocally,
    clearError,
  }
})
