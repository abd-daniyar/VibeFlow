<?php

namespace App\Http\Controllers\Api;

use App\Models\Board;
use Illuminate\Http\Request;

class BoardController
{
    /**
     * Get all boards for the authenticated user
     */
    public function index(Request $request)
    {
        $boards = $request->user()
            ->boards()
            ->with(['creator', 'users', 'columns'])
            ->latest()
            ->paginate(15);

        return response()->json($boards);
    }

    /**
     * Create a new board
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $board = Board::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'slug' => str()->slug($validated['name']) . '-' . uniqid(),
            'created_by' => $request->user()->id,
        ]);

        // Add creator as owner
        $board->users()->attach($request->user()->id, ['role' => 'owner']);

        return response()->json($board->load('creator', 'users'), 201);
    }

    /**
     * Get a specific board with all relationships
     */
    public function show(Request $request, Board $board)
    {
        $this->authorize('view', $board);

        return response()->json(
            $board->load([
                'creator',
                'users',
                'columns' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
            ])
        );
    }

    /**
     * Update a board
     */
    public function update(Request $request, Board $board)
    {
        $this->authorize('update', $board);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $board->update($validated);

        return response()->json($board);
    }

    /**
     * Delete a board
     */
    public function destroy(Request $request, Board $board)
    {
        $this->authorize('delete', $board);

        $board->delete();

        return response()->json(null, 204);
    }

    /**
     * Add a user to the board
     */
    public function addUser(Request $request, Board $board)
    {
        $this->authorize('update', $board);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:owner,editor,viewer',
        ]);

        $board->users()->syncWithoutDetaching([
            $validated['user_id'] => ['role' => $validated['role']],
        ]);

        return response()->json([
            'message' => 'User added to board successfully',
            'board' => $board->load('users'),
        ]);
    }

    /**
     * Remove a user from the board
     */
    public function removeUser(Request $request, Board $board, int $userId)
    {
        $this->authorize('update', $board);

        $board->users()->detach($userId);

        return response()->json([
            'message' => 'User removed from board successfully',
            'board' => $board->load('users'),
        ]);
    }

    /**
     * Update user role in board
     */
    public function updateUserRole(Request $request, Board $board, int $userId)
    {
        $this->authorize('update', $board);

        $validated = $request->validate([
            'role' => 'required|in:owner,editor,viewer',
        ]);

        $board->users()->updateExistingPivot($userId, ['role' => $validated['role']]);

        return response()->json([
            'message' => 'User role updated successfully',
            'board' => $board->load('users'),
        ]);
    }
}
