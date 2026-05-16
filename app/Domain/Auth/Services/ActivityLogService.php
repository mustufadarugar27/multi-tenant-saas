<?php


namespace App\Domain\Auth\Services;

use App\Jobs\LogActivityJob;
use Illuminate\Http\Request;

class ActivityLogService
{
    private const QUEUE = 'activity-logs';

    public function log(
        string $event,
        string $description,
        ?string $userId = null,
        ?Request $request = null,
        array $properties = [],
    ): void {
        LogActivityJob::dispatch([
            'user_id'     => $userId,
            'event'       => $event,
            'description' => $description,
            'subject_type' => null,
            'subject_id'   => null,
            'properties'   => $properties ?: null,
            'ip_address'   => $request?->ip(),
            'user_agent'   => $request?->userAgent(),
        ])->onQueue(self::QUEUE);
    }
}
