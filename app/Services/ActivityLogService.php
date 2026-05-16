<?php


namespace App\Services;

use App\Jobs\LogActivityJob;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    private const QUEUE = 'activity-logs';

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
