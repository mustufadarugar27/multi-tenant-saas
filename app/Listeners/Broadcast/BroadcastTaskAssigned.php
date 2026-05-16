<?php


namespace App\Listeners\Broadcast;

use App\Events\Broadcast\TaskAssignedBroadcast;
use App\Events\TaskAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Queued listener: dispatches the broadcast event after the DB commit.
 * Running on the 'broadcasts' queue keeps broadcast latency low and isolated
 * from heavier application queues.
 */
class BroadcastTaskAssigned implements ShouldQueue
{
    public string $queue = 'broadcasts';

    public bool $afterCommit = true;

    public function handle(TaskAssigned $event): void
    {
        $tenantId = tenant()?->id;

        if ($tenantId === null) {
            return;
        }

        broadcast(new TaskAssignedBroadcast(
            task: $event->task,
            actor: $event->actor,
            assignee: $event->assignee,
            tenantId: $tenantId,
        ));
    }
}
