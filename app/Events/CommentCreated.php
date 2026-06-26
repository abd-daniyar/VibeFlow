<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;
    public $taskId;

    public function __construct(Comment $comment, $taskId)
    {
        $this->comment = $comment->load('user');
        $this->taskId = $taskId;
    }

    public function broadcastOn(): array
    {
        $boardId = $this->comment->task->column->board_id;
        return [
            new PrivateChannel('board.' . $boardId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'comment.created';
    }
}
