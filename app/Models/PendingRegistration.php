<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingRegistration extends Model
{
    protected $fillable = [
        'token',
        'plan_id',
        'billing_cycle',
        'company_name',
        'slug',
        'name',
        'email',
        'password',
        'stripe_session_id',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status'     => 'string',
            'expires_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && ! $this->isExpired();
    }
}
