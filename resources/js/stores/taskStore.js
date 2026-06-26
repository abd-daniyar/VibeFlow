import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/axios'

export const useTaskStore = defineStore('task', () => {
  const tasks = ref({})
  const taskMap = ref({})
  const loading = ref(false)
  const error = ref('')

  const getTask = (id) => taskMap.value[id]

  const getTasksByColumn = (columnId) => tasks.value[columnId] || []

  const fetchTasks = async (boardId, columnId) => {
    loading.value = true
    error.value = ''
    try {
      const response = await api.get(
        `/boards/${boardId}/columns/${columnId}/tasks`
      )
      if (!tasks.value[columnId]) {
        tasks.value[columnId] = []
      }
      tasks.value[columnId] = response.data
      response.data.forEach((task) => {
        taskMap.value[task.id] = task
      })
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch tasks'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createTask = async (boardId, columnId, data) => {
    loading.value = true
    error.value = ''
    try {
      const response = await api.post(
        `/boards/${boardId}/columns/${columnId}/tasks`,
        data
      )
      if (!tasks.value[columnId]) {
        tasks.value[columnId] = []
      }
      tasks.value[columnId].push(response.data)
      taskMap.value[response.data.id] = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create task'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateTask = async (boardId, columnId, taskId, data) => {
    loading.value = true
    error.value = ''
    try {
      const response = await api.put(
        `/boards/${boardId}/columns/${columnId}/tasks/${taskId}`,
        data
      )
      const columnTasks = tasks.value[columnId] || []
      const index = columnTasks.findIndex((t) => t.id === taskId)
      if (index !== -1) {
        columnTasks[index] = response.data
        taskMap.value[taskId] = response.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update task'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteTask = async (boardId, columnId, taskId) => {
    loading.value = true
    error.value = ''
    try {
      await api.delete(
        `/boards/${boardId}/columns/${columnId}/tasks/${taskId}`
      )
      if (tasks.value[columnId]) {
        tasks.value[columnId] = tasks.value[columnId].filter(
          (t) => t.id !== taskId
        )
      }
      delete taskMap.value[taskId]
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete task'
      throw err
    } finally {
      loading.value = false
    }
  }

  const moveTask = async (boardId, sourceColumnId, taskId, targetColumnId, order) => {
    loading.value = true
    error.value = ''
    try {
      const response = await api.post(
        `/boards/${boardId}/columns/${sourceColumnId}/tasks/${taskId}/move`,
        {
          column_id: targetColumnId,
          order,
        }
      )

      // Remove from source column
      if (tasks.value[sourceColumnId]) {
        tasks.value[sourceColumnId] = tasks.value[sourceColumnId].filter(
          (t) => t.id !== taskId
        )
      }

      // Add to target column
      if (!tasks.value[targetColumnId]) {
        tasks.value[targetColumnId] = []
      }
      tasks.value[targetColumnId].push(response.data.task)
      taskMap.value[taskId] = response.data.task

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to move task'
      throw err
    } finally {
      loading.value = false
    }
  }

  const reorderTasks = async (boardId, columnId, taskIds) => {
    try {
      const response = await api.post(
        `/boards/${boardId}/columns/${columnId}/tasks/reorder`,
        {
          tasks: taskIds,
        }
      )
      // Reorder locally
      const newOrder = taskIds.map((id) => taskMap.value[id])
      tasks.value[columnId] = newOrder
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to reorder tasks'
      throw err
    }
  }

  const updateTaskLocally = (taskId, updates) => {
    const task = taskMap.value[taskId]
    if (task) {
      Object.assign(task, updates)
    }
  }

  const clearError = () => {
    error.value = ''
  }

  return {
    tasks,
    taskMap,
    loading,
    error,
    getTask,
    getTasksByColumn,
    fetchTasks,
    createTask,
    updateTask,
    deleteTask,
    moveTask,
    reorderTasks,
    updateTaskLocally,
    clearError,
  }
})
