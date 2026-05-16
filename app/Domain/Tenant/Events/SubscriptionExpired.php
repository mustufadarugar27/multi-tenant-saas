<?php


namespace App\Domain\Tenant\Events;

use App\Models\Tenant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpired
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly string $planName,
        public readonly \DateTimeInterface $expiredAt,
    ) {}
}
