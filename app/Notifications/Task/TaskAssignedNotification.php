<?php


namespace App\Notifications\Task;

use App\Models\Task;
use App\Models\User;
use App\Support\EnumConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Task $task,
        public readonly User $assignedBy,
    ) {}

    public function via(object $notifiable): array
    {
        // 'database' is handled synchronously in HandleTaskAssigned to guarantee
        // immediate insertion regardless of queue worker availability.
        // 'broadcast' is handled by BroadcastTaskAssigned listener via the tenant-aware channel.
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Task Assigned: {$this->task->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->assignedBy->name} has assigned a task to you.")
            ->line("**Task:** {$this->task->title}")
            ->line("**Priority:** " . EnumConfig::label('task_priority', $this->task->priority))
            ->when(
                $this->task->due_date,
                fn (MailMessage $m) => $m->line("**Due Date:** {$this->task->due_date->toFormattedDateString()}")
            )
            ->action('View Task', url("/tasks/{$this->task->id}"))
            ->line('Thank you for using our platform.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_assigned',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'priority' => $this->task->priority->value,
            'due_date' => $this->task->due_date?->toDateString(),
            'assigned_by' => [
                'id' => $this->assignedBy->id,
                'name' => $this->assignedBy->name,
            ],
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'task_assigned',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'priority' => $this->task->priority->value,
            'due_date' => $this->task->due_date?->toDateString(),
            'assigned_by' => [
                'id' => $this->assignedBy->id,
                'name' => $this->assignedBy->name,
            ],
        ]);
    }
}
