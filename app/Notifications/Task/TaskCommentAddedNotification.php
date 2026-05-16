<?php


namespace App\Notifications\Task;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCommentAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Task $task,
        public readonly TaskComment $comment,
        public readonly User $commenter,
    ) {}

    public function via(object $notifiable): array
    {
        // 'broadcast' is handled by BroadcastCommentAdded listener via the tenant-aware channel.
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New comment on task \"{$this->task->title}\"")
            ->line("{$this->commenter->name} commented on a task assigned to you.")
            ->line("\"{$this->comment->content}\"")
            ->action('View Task', url("/tasks/{$this->task->id}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'comment_added',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'comment_id' => $this->comment->id,
            'commenter_id' => $this->commenter->id,
            'commenter' => $this->commenter->name,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'comment_added',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'comment_id' => $this->comment->id,
            'comment' => $this->comment->content,
            'commenter_id' => $this->commenter->id,
            'commenter' => $this->commenter->name,
        ]);
    }
}
