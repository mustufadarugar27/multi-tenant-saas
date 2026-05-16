<?php


namespace App\Models;

use App\Support\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class TaskAttachment extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'task_id',
        'user_id',
        'filename',
        'disk',
        'path',
        'mime_type',
        'size',
        'virus_scan_status',
    ];


    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function isSafe(): bool
    {
        return $this->virus_scan_status === 'clean';
    }

    public function formattedSize(): string
    {
        $bytes = (int) $this->size;

        return match (true) {
            $bytes >= 1_048_576 => round($bytes / 1_048_576, 2) . ' MB',
            $bytes >= 1_024     => round($bytes / 1_024, 2) . ' KB',
            default             => $bytes . ' B',
        };
    }

    public function downloadUrl(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
