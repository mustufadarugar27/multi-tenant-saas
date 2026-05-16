<?php


namespace App\Console\Commands;

use App\Domain\Billing\Events\SubscriptionExpired;
use App\Models\Tenant;
use App\Notifications\Billing\SubscriptionExpiredNotification;
use Illuminate\Console\Command;

final class CheckExpiredSubscriptionsCommand extends Command
{
    protected $signature = 'billing:check-expired';

    protected $description = 'Mark expired subscriptions and suspend tenants';

    public function handle(): int
    {
        $expired = Tenant::query()
            ->where('subscription_status', 'active')
            ->where('subscription_ends_at', '<', now())
            ->whereNull('grace_period_ends_at')
            ->orWhere(function ($q): void {
                $q->where('subscription_status', 'active')
                  ->where('grace_period_ends_at', '<', now());
            })
            ->get();

        foreach ($expired as $tenant) {
            $planName    = $tenant->plan?->name ?? 'Unknown';
            $expiredAt   = $tenant->subscription_ends_at ?? now();

            $tenant->update([
                'subscription_status' => 'expired',
                'status'              => 'suspended',
            ]);

            tenancy()->initialize($tenant);

            $adminUser = \App\Models\User::role('SuperAdmin')->first();
            $adminUser?->notify(new SubscriptionExpiredNotification($tenant, $planName, $expiredAt));

            tenancy()->end();

            event(new SubscriptionExpired($tenant));

            $this->info("Expired: {$tenant->name} ({$tenant->id})");
        }

        $this->info("Processed {$expired->count()} expired subscription(s).");

        return self::SUCCESS;
    }
}
