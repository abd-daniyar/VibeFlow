<?php

namespace App\Http\Controllers\Api;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Http\Request;

class ColumnController
{
    /**
     * Get all columns for a board
     */
    public function index(Request $request, Board $board)
    {
        $this->authorize('view', $board);

        $columns = $board->columns()
            ->with(['tasks' => function ($query) {
                $query->orderBy('order', 'asc');
            }])
            ->orderBy('order', 'asc')
            ->get();

        return response()->json($columns);
    }

    /**
     * Create a new column
     */
    public function store(Request $request, Board $board)
    {
        $this->authorize('update', $board);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $order = Column::where('board_id', $board->id)->max('order') ?? 0;

        $column = Column::create([
            'board_id' => $board->id,
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#6366f1',
            'order' => $order + 1,
        ]);

        return response()->json($column, 201);
    }

    /**
     * Get a specific column with tasks
     */
    public function show(Request $request, Board $board, Column $column)
    {
        $this->authorize('view', $board);

        if ($column->board_id !== $board->id) {
            return response()->json(['error' => 'Column not found'], 404);
        }

        return response()->json(
            $column->load(['tasks' => function ($query) {
                $query->orderBy('order', 'asc');
            }])
        );
    }

    /**
     * Update a column
     */
    public function update(Request $request, Board $board, Column $column)
    {
        $this->authorize('update', $board);

        if ($column->board_id !== $board->id) {
            return response()->json(['error' => 'Column not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
            'order' => 'nullable|integer|min:1',
        ]);

        $column->update($validated);

        return response()->json($column);
    }

    /**
     * Delete a column (cascades to tasks)
     */
    public function destroy(Request $request, Board $board, Column $column)
    {
        $this->authorize('delete', $board);

        if ($column->board_id !== $board->id) {
            return response()->json(['error' => 'Column not found'], 404);
        }

        $column->delete();

        return response()->json(null, 204);
    }

    /**
     * Reorder columns
     */
    public function reorder(Request $request, Board $board)
    {
        $this->authorize('update', $board);

        $validated = $request->validate([
            'columns' => 'required|array',
            'columns.*' => 'integer|exists:columns,id',
        ]);

        foreach ($validated['columns'] as $order => $columnId) {
            Column::where('id', $columnId)
                ->where('board_id', $board->id)
                ->update(['order' => $order + 1]);
        }

        return response()->json([
            'message' => 'Columns reordered successfully',
        ]);
    }
}
