<?php

namespace App\Http\Controllers\Api;

use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController
{
    /**
     * Get all tasks in a column
     */
    public function index(Request $request, Board $board, Column $column)
    {
        $this->authorize('view', $board);

        if ($column->board_id !== $board->id) {
            return response()->json(['error' => 'Column not found'], 404);
        }

        $tasks = $column->tasks()
            ->with(['assignee', 'comments'])
            ->orderBy('order', 'asc')
            ->get();

        return response()->json($tasks);
    }

    /**
     * Create a new task
     */
    public function store(Request $request, Board $board, Column $column)
    {
        $this->authorize('update', $board);

        if ($column->board_id !== $board->id) {
            return response()->json(['error' => 'Column not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date_format:Y-m-d H:i:s',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $order = Task::where('column_id', $column->id)->max('order') ?? 0;

        $task = Task::create([
            'column_id' => $column->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'] ?? 'medium',
            'assigned_to' => $validated['assigned_to'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'color' => $validated['color'] ?? '#ffffff',
            'order' => $order + 1,
        ]);

        return response()->json($task->load('assignee'), 201);
    }

    /**
     * Get a specific task
     */
    public function show(Request $request, Board $board, Task $task)
    {
        $this->authorize('view', $board);

        $column = $task->column;
        if ($column->board_id !== $board->id) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        return response()->json(
            $task->load(['assignee', 'comments' => function ($query) {
                $query->with('user')->latest();
            }])
        );
    }

    /**
     * Update a task
     */
    public function update(Request $request, Board $board, Task $task)
    {
        $this->authorize('update', $board);

        $column = $task->column;
        if ($column->board_id !== $board->id) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string|max:5000',
            'priority' => 'in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date_format:Y-m-d H:i:s',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $task->update($validated);

        return response()->json($task->load('assignee'));
    }

    /**
     * Delete a task
     */
    public function destroy(Request $request, Board $board, Task $task)
    {
        $this->authorize('delete', $board);

        $column = $task->column;
        if ($column->board_id !== $board->id) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $task->delete();

        return response()->json(null, 204);
    }

    /**
     * Move task to another column
     */
    public function move(Request $request, Board $board, Task $task)
    {
        $this->authorize('update', $board);

        $column = $task->column;
        if ($column->board_id !== $board->id) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $validated = $request->validate([
            'column_id' => 'required|exists:columns,id',
            'order' => 'required|integer|min:1',
        ]);

        $newColumn = Column::findOrFail($validated['column_id']);

        if ($newColumn->board_id !== $board->id) {
            return response()->json(['error' => 'Invalid column'], 422);
        }

        // If moving within same column, just update order
        if ($task->column_id === $newColumn->id) {
            $task->update(['order' => $validated['order']]);
        } else {
            // Moving to different column
            $task->update([
                'column_id' => $newColumn->id,
                'order' => $validated['order'],
            ]);

            // Reorder remaining tasks in old column
            Task::where('column_id', $column->id)
                ->where('id', '!=', $task->id)
                ->where('order', '>', $task->order)
                ->decrement('order');
        }

        // Reorder tasks in new column
        Task::where('column_id', $newColumn->id)
            ->where('id', '!=', $task->id)
            ->where('order', '>=', $validated['order'])
            ->increment('order');

        return response()->json([
            'message' => 'Task moved successfully',
            'task' => $task->load('assignee'),
        ]);
    }

    /**
     * Reorder tasks within a column
     */
    public function reorder(Request $request, Board $board, Column $column)
    {
        $this->authorize('update', $board);

        if ($column->board_id !== $board->id) {
            return response()->json(['error' => 'Column not found'], 404);
        }

        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*' => 'integer|exists:tasks,id',
        ]);

        foreach ($validated['tasks'] as $order => $taskId) {
            Task::where('id', $taskId)
                ->where('column_id', $column->id)
                ->update(['order' => $order + 1]);
        }

        return response()->json([
            'message' => 'Tasks reordered successfully',
        ]);
    }
}
