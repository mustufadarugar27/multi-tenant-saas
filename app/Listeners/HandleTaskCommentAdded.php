<?php


namespace App\Listeners;

use App\Events\TaskCommentAdded;
use App\Notifications\Task\TaskCommentAddedNotification;

class HandleTaskCommentAdded
{
    public function handle(TaskCommentAdded $event): void
    {
        $task = $event->task;

        if (! $task->assignee) {
            return;
        }

        // Notify the assignee when someone else comments on their task.
        if ($task->assignee->id !== $event->actor->id) {
            $task->assignee->notify(new TaskCommentAddedNotification($task, $event->comment, $event->actor));
        }
    }
}
