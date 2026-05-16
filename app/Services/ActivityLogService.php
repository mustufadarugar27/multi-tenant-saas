<?php


namespace App\Services;

use App\Jobs\LogActivityJob;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    private const QUEUE = 'activity-logs';

    /**
     * Dispatch an activity log entry asynchronously.
     * Falls back to synchronous write if the queue connection is `sync`.
     */
    public function log(
        User $actor,
        string $event,
        string $description,
        Model $subject,
        array $properties = [],
    ): void {
        $payload = [
            'user_id' => $actor->id,
            'event' => $event,
            'description' => $description,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        LogActivityJob::dispatch($payload)->onQueue(self::QUEUE);
    }

    /**
     * Synchronous write — use only when the caller cannot tolerate a queue.
     */
    public function logSync(
        User $actor,
        string $event,
        string $description,
        Model $subject,
        array $properties = [],
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $actor->id,
            'event' => $event,
            'description' => $description,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log a system-initiated event with no human actor.
     */
    public function logSystem(
        string $event,
        string $description,
        Model $subject,
        array $properties = [],
    ): void {
        $payload = [
            'user_id' => null,
            'event' => $event,
            'description' => $description,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => null,
            'user_agent' => null,
        ];

        LogActivityJob::dispatch($payload)->onQueue(self::QUEUE);
    }
}
