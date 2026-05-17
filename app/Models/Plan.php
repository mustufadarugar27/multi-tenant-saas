<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'features',
        'limits',
        'max_users',
        'storage_gb',
        'stripe_monthly_price_id',
        'stripe_yearly_price_id',
        'is_active',
        'is_free',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features'      => 'array',
            'limits'        => 'array',
            'price_monthly' => 'float',
            'price_yearly'  => 'float',
            'is_active'     => 'boolean',
            'is_free'       => 'boolean',
            'is_default'    => 'boolean',
            'max_users'     => 'integer',
            'storage_gb'    => 'integer',
            'sort_order'    => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function stripePriceId(string $cycle): ?string
    {
        return match ($cycle) {
            'monthly' => $this->stripe_monthly_price_id,
            'yearly'  => $this->stripe_yearly_price_id,
            default   => null,
        };
    }

    public function priceFor(string $cycle): float
    {
        return match ($cycle) {
            'monthly' => (float) $this->price_monthly,
            'yearly'  => (float) $this->price_yearly,
            default   => 0.0,
        };
    }
}
