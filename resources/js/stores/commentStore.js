import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/axios'

export const useCommentStore = defineStore('comment', () => {
  const comments = ref({})
  const commentMap = ref({})
  const loading = ref(false)
  const error = ref('')

  const getComment = (id) => commentMap.value[id]

  const getTaskComments = (taskId) => comments.value[taskId] || []

  const fetchComments = async (boardId, columnId, taskId) => {
    loading.value = true
    error.value = ''
    try {
      const response = await api.get(
        `/boards/${boardId}/columns/${columnId}/tasks/${taskId}/comments`
      )
      if (!comments.value[taskId]) {
        comments.value[taskId] = []
      }
      comments.value[taskId] = response.data.data || response.data
      comments.value[taskId].forEach((comment) => {
        commentMap.value[comment.id] = comment
      })
      return comments.value[taskId]
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch comments'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createComment = async (boardId, columnId, taskId, content) => {
    loading.value = true
    error.value = ''
    try {
      const response = await api.post(
        `/boards/${boardId}/columns/${columnId}/tasks/${taskId}/comments`,
        { content }
      )
      if (!comments.value[taskId]) {
        comments.value[taskId] = []
      }
      comments.value[taskId].push(response.data)
      commentMap.value[response.data.id] = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create comment'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateComment = async (boardId, columnId, taskId, commentId, content) => {
    loading.value = true
    error.value = ''
    try {
      const response = await api.put(
        `/boards/${boardId}/columns/${columnId}/tasks/${taskId}/comments/${commentId}`,
        { content }
      )
      const taskComments = comments.value[taskId] || []
      const index = taskComments.findIndex((c) => c.id === commentId)
      if (index !== -1) {
        taskComments[index] = response.data
        commentMap.value[commentId] = response.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update comment'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteComment = async (boardId, columnId, taskId, commentId) => {
    loading.value = true
    error.value = ''
    try {
      await api.delete(
        `/boards/${boardId}/columns/${columnId}/tasks/${taskId}/comments/${commentId}`
      )
      if (comments.value[taskId]) {
        comments.value[taskId] = comments.value[taskId].filter(
          (c) => c.id !== commentId
        )
      }
      delete commentMap.value[commentId]
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete comment'
      throw err
    } finally {
      loading.value = false
    }
  }

  const addCommentLocally = (taskId, comment) => {
    if (!comments.value[taskId]) {
      comments.value[taskId] = []
    }
    comments.value[taskId].push(comment)
    commentMap.value[comment.id] = comment
  }

  const clearError = () => {
    error.value = ''
  }

  return {
    comments,
    commentMap,
    loading,
    error,
    getComment,
    getTaskComments,
    fetchComments,
    createComment,
    updateComment,
    deleteComment,
    addCommentLocally,
    clearError,
  }
})
