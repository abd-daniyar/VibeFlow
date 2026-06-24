<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskMoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $sourceColumnId;
    public $targetColumnId;

    public function __construct(Task $task, $sourceColumnId, $targetColumnId)
    {
        $this->task = $task;
        $this->sourceColumnId = $sourceColumnId;
        $this->targetColumnId = $targetColumnId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('board.' . $this->task->column->board_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'task.moved';
    }
}
