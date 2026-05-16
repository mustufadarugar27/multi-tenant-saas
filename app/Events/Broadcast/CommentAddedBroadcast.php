<?php


namespace App\Events\Broadcast;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast to everyone watching the task channel.
 * The sender is excluded on the client side via socket_id.
 */
class CommentAddedBroadcast implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public readonly string $tenantId;

    public function __construct(
        public readonly Task $task,
        public readonly TaskComment $comment,
        public readonly User $actor,
        string $tenantId,
    ) {
        $this->tenantId = $tenantId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}.task.{$this->task->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'comment.added';
    }

    public function broadcastWith(): array
    {
        return [
            'comment' => [
                'id' => $this->comment->id,
                'content' => $this->comment->content,
                'created_at' => $this->comment->created_at?->toIso8601String(),
            ],
            'task_id' => $this->task->id,
            'actor' => [
                'id' => $this->actor->id,
                'name' => $this->actor->name,
            ],
        ];
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }
}
