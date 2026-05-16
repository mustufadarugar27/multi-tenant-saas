<?php


namespace App\Models;

use App\Support\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskHistory extends Model
{
    use HasUuid;

    // History is append-only — no updates, no deletes.
    public const UPDATED_AT = null;

    protected $fillable = [
        'task_id',
        'user_id',
        'event',
        'field',
        'old_value',
        'new_value',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }


    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
