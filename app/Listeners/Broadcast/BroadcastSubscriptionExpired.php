<?php


namespace App\Listeners\Broadcast;

use App\Domain\Tenant\Events\SubscriptionExpired;
use App\Events\Broadcast\SubscriptionExpiredBroadcast;
use App\Notifications\Billing\SubscriptionExpiredNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Queued listener: broadcasts subscription expiry to the whole tenant channel
 * and notifies every active user via database + mail.
 *
 * Runs on the 'notifications' queue (heavier) to avoid blocking the fast
 * 'broadcasts' queue with per-user notification fan-out.
 */
class BroadcastSubscriptionExpired implements ShouldQueue
{
    public string $queue = 'notifications';

    public bool $afterCommit = true;

    public function handle(SubscriptionExpired $event): void
    {
        $tenantId = $event->tenant->id;
        $expiredAt = $event->expiredAt instanceof \DateTimeImmutable
            ? \DateTimeImmutable::createFromInterface($event->expiredAt)->format(\DateTimeInterface::ISO8601)
            : (new \DateTime())->setTimestamp($event->expiredAt->getTimestamp())->format(\DateTimeInterface::ISO8601);

        // Broadcast to all connected clients in this tenant.
        broadcast(new SubscriptionExpiredBroadcast(
            tenantId: $tenantId,
            planName: $event->planName,
            expiredAt: $event->expiredAt->format(\DateTimeInterface::ISO8601),
        ));

        // Fan-out database + mail notifications to every active user.
        User::where('is_active', true)->each(function (User $user) use ($event): void {
            $user->notify(new SubscriptionExpiredNotification(
                tenant: $event->tenant,
                planName: $event->planName,
                expiredAt: $event->expiredAt,
            ));
        });
    }
}
