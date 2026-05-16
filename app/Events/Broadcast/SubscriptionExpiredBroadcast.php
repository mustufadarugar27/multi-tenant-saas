<?php


namespace App\Events\Broadcast;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiredBroadcast implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $planName,
        public readonly string $expiredAt,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'subscription.expired';
    }

    public function broadcastWith(): array
    {
        return [
            'plan_name' => $this->planName,
            'expired_at' => $this->expiredAt,
            'message' => "Your {$this->planName} subscription has expired. Please renew to continue using all features.",
        ];
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }
}
