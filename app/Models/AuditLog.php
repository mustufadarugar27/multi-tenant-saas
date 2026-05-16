<?php


namespace App\Models;

use App\Support\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasUuid;

    // Audit logs are immutable append-only compliance records.
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'actor_type',
        'action',
        'resource_type',
        'resource_id',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
