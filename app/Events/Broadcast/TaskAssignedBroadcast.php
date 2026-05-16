<?php


namespace App\Events\Broadcast;

use App\Models\Task;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class TaskAssignedBroadcast implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public readonly string $tenantId;

    public function __construct(
        public readonly Task $task,
        public readonly User $actor,
        public readonly User $assignee,
        string $tenantId,
    ) {
        $this->tenantId = $tenantId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}.user.{$this->assignee->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'task.assigned';
    }

    public function broadcastWith(): array
    {
        return [
            'task' => [
                'id' => $this->task->id,
                'title' => $this->task->title,
                'priority' => $this->task->priority,
                'due_date' => $this->task->due_date?->toDateString(),
                'status' => $this->task->status,
            ],
            'project_id' => $this->task->project_id,
            'assigned_by' => [
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
