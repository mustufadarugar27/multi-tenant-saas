<?php


namespace App\Models;

use App\Support\EnumConfig;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, SoftDeletes;

    protected $fillable = [
        'id',
        'name',
        'slug',
        'status',
        'settings',
        'trial_ends_at',
        'suspended_at',
        'data',
        'stripe_customer_id',
        'plan_id',
        'billing_cycle',
        'subscription_status',
        'stripe_subscription_id',
        'subscription_ends_at',
        'grace_period_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'status'              => 'string',
            'subscription_status' => 'string',
            'billing_cycle'       => 'string',
            'settings'            => 'array',
            'data'                => 'array',
            'trial_ends_at'       => 'datetime',
            'suspended_at'        => 'datetime',
            'subscription_ends_at'   => 'datetime',
            'grace_period_ends_at'   => 'datetime',
        ];
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
            'status',
            'settings',
            'trial_ends_at',
            'suspended_at',
            'stripe_customer_id',
            'plan_id',
            'billing_cycle',
            'subscription_status',
            'stripe_subscription_id',
            'subscription_ends_at',
            'grace_period_ends_at',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return EnumConfig::isTenantStatusActive($this->status);
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isSubscriptionActive(): bool
    {
        if ($this->subscription_status === null) {
            return false;
        }

        return EnumConfig::isSubscriptionAccessAllowed($this->subscription_status);
    }

    public function isInGracePeriod(): bool
    {
        return $this->grace_period_ends_at !== null
            && $this->grace_period_ends_at->isFuture();
    }

    public function hasExpiredSubscription(): bool
    {
        return $this->subscription_ends_at !== null
            && $this->subscription_ends_at->isPast()
            && ! $this->isInGracePeriod();
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }
}
