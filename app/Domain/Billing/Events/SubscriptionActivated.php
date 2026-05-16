<?php


namespace App\Domain\Billing\Events;

use App\Models\Tenant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class SubscriptionActivated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
    ) {}
}
