<?php

namespace App\Http\Controllers\Api;

use App\Models\Board;
use App\Models\Task;
use App\Models\Comment;
use App\Events\CommentCreated;
use App\Events\CommentDeleted;
use Illuminate\Http\Request;

class CommentController
{
    /**
     * Get all comments for a task
     */
    public function index(Request $request, Board $board, Task $task)
    {
        $this->authorize('view', $board);

        $column = $task->column;
        if ($column->board_id !== $board->id) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $comments = $task->comments()
            ->with('user')
            ->latest()
            ->paginate(50);

        return response()->json($comments);
    }

    /**
     * Create a new comment
     */
    public function store(Request $request, Board $board, Task $task)
    {
        $this->authorize('view', $board);

        $column = $task->column;
        if ($column->board_id !== $board->id) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $comment = Comment::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        $comment->load('user');

        // Broadcast comment creation
        broadcast(new CommentCreated($comment, $task->id))->toOthers();

        return response()->json($comment, 201);
    }

    /**
     * Update a comment
     */
    public function update(Request $request, Board $board, Task $task, Comment $comment)
    {
        if ($comment->task_id !== $task->id) {
            return response()->json(['error' => 'Comment not found'], 404);
        }

        $column = $task->column;
        if ($column->board_id !== $board->id) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        // Only comment author or board owner can update
        if ($request->user()->id !== $comment->user_id && $column->board->created_by !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $comment->update($validated);

        return response()->json($comment->load('user'));
    }

    /**
     * Delete a comment
     */
    public function destroy(Request $request, Board $board, Task $task, Comment $comment)
    {
        if ($comment->task_id !== $task->id) {
            return response()->json(['error' => 'Comment not found'], 404);
        }

        $column = $task->column;
        if ($column->board_id !== $board->id) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        // Only comment author or board owner can delete
        if ($request->user()->id !== $comment->user_id && $column->board->created_by !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $boardId = $column->board_id;
        $commentId = $comment->id;
        $comment->delete();

        // Broadcast comment deletion
        broadcast(new CommentDeleted($commentId, $task->id, $boardId))->toOthers();

        return response()->json(null, 204);
    }
}
