<?php


namespace App\Listeners\Broadcast;

use App\Events\Broadcast\CommentAddedBroadcast;
use App\Events\TaskCommentAdded;
use Illuminate\Contracts\Queue\ShouldQueue;

class BroadcastCommentAdded implements ShouldQueue
{
    public string $queue = 'broadcasts';

    public bool $afterCommit = true;

    public function handle(TaskCommentAdded $event): void
    {
        $tenantId = tenant()?->id;

        if ($tenantId === null) {
            return;
        }

        broadcast(new CommentAddedBroadcast(
            task: $event->task,
            comment: $event->comment,
            actor: $event->actor,
            tenantId: $tenantId,
        ));
    }
}
