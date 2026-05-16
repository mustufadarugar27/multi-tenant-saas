<?php


namespace App\Models;

use App\Support\Concerns\HasUuid;
use App\Support\EnumConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'assigned_to',
        'created_by',
        'priority',
        'status',
        'due_date',
        'estimated_hours',
        'actual_hours',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'string',
            'status'   => 'string',
            'due_date' => 'date',
            'estimated_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }


    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class)->orderBy('created_at');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TaskHistory::class)->orderByDesc('created_at');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'subject_id')
                    ->where('subject_type', self::class);
    }


    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && ! EnumConfig::isTaskStatusTerminal($this->status);
    }

    public function isDueSoon(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isFuture()
            && $this->due_date->diffInDays(now()) <= 2
            && ! EnumConfig::isTaskStatusTerminal($this->status);
    }

    public function isAssignedTo(User $user): bool
    {
        return $this->assigned_to === $user->id;
    }
}
