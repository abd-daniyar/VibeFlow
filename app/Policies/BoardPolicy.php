<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BoardPolicy
{
    /**
     * Determine if the user can view the board
     */
    public function view(User $user, Board $board): bool
    {
        return $board->users()->where('user_id', $user->id)->exists() ||
               $board->created_by === $user->id;
    }

    /**
     * Determine if the user can update the board
     */
    public function update(User $user, Board $board): bool
    {
        $userRole = $board->users()->where('user_id', $user->id)->first()?->pivot?->role;
        
        return $board->created_by === $user->id || 
               $userRole === 'owner' || 
               $userRole === 'editor';
    }

    /**
     * Determine if the user can delete the board
     */
    public function delete(User $user, Board $board): bool
    {
        return $board->created_by === $user->id;
    }
}
