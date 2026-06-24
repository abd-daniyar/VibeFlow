import echo from './echo'
import { useTaskStore } from '../stores/taskStore'
import { useCommentStore } from '../stores/commentStore'
import { useBoardStore } from '../stores/boardStore'

export const setupRealtimeListeners = (boardId) => {
  const taskStore = useTaskStore()
  const commentStore = useCommentStore()
  const boardStore = useBoardStore()

  // Listen for task updates
  echo.private(`board.${boardId}`).on('task.created', (data) => {
    taskStore.updateTaskLocally(data.task.id, data.task)
  })

  echo.private(`board.${boardId}`).on('task.updated', (data) => {
    taskStore.updateTaskLocally(data.task.id, data.task)
  })

  echo.private(`board.${boardId}`).on('task.deleted', (data) => {
    // Remove task from store
    const taskId = data.task_id
    delete taskStore.taskMap[taskId]
  })

  echo.private(`board.${boardId}`).on('task.moved', (data) => {
    const { task, sourceColumnId, targetColumnId } = data
    // Update task in store
    if (taskStore.tasks[sourceColumnId]) {
      taskStore.tasks[sourceColumnId] = taskStore.tasks[sourceColumnId].filter(
        (t) => t.id !== task.id
      )
    }
    if (!taskStore.tasks[targetColumnId]) {
      taskStore.tasks[targetColumnId] = []
    }
    taskStore.tasks[targetColumnId].push(task)
    taskStore.taskMap[task.id] = task
  })

  // Listen for comment updates
  echo.private(`board.${boardId}`).on('comment.created', (data) => {
    const { comment, taskId } = data
    commentStore.addCommentLocally(taskId, comment)
  })

  echo.private(`board.${boardId}`).on('comment.deleted', (data) => {
    const { commentId, taskId } = data
    if (commentStore.comments[taskId]) {
      commentStore.comments[taskId] = commentStore.comments[taskId].filter(
        (c) => c.id !== commentId
      )
    }
    delete commentStore.commentMap[commentId]
  })

  // Listen for board updates
  echo.private(`board.${boardId}`).on('board.updated', (data) => {
    boardStore.$patch({
      currentBoard: data.board,
    })
  })

  // Listen for presence updates (users online)
  echo.join(`board.${boardId}`)
    .here((users) => {
      console.log('Users online:', users)
    })
    .joining((user) => {
      console.log('User joined:', user)
    })
    .leaving((user) => {
      console.log('User left:', user)
    })
}

export const stopRealtimeListeners = (boardId) => {
  echo.leave(`board.${boardId}`)
}
