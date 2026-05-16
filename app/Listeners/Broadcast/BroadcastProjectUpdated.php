<?php


namespace App\Listeners\Broadcast;

use App\Events\Broadcast\ProjectUpdatedBroadcast;
use App\Events\ProjectUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;

class BroadcastProjectUpdated implements ShouldQueue
{
    public string $queue = 'broadcasts';

    public bool $afterCommit = true;

    public function handle(ProjectUpdated $event): void
    {
        $tenantId = tenant()?->id;

        if ($tenantId === null) {
            return;
        }

        broadcast(new ProjectUpdatedBroadcast(
            project: $event->project,
            actor: $event->actor,
            changes: $event->changes,
            tenantId: $tenantId,
        ));
    }
}
