<?php


namespace App\Repositories;

use App\Models\ActivityLog;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return ActivityLog::query()
            ->with('user:id,name,email')
            ->when($filters['event'] ?? null, fn ($q, $v) => $q->where('event', $v))
            ->when($filters['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['subject_type'] ?? null, fn ($q, $v) => $q->where('subject_type', $v))
            ->when($filters['subject_id'] ?? null, fn ($q, $v) => $q->where('subject_id', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->where('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->where('created_at', '<=', $v))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function paginateForUser(string $userId, int $perPage): LengthAwarePaginator
    {
        return ActivityLog::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(array $data): ActivityLog
    {
        return ActivityLog::create($data);
    }

    public function archiveOlderThan(int $days): int
    {
        $cutoff = now()->subDays($days)->toDateTimeString();

        return DB::transaction(function () use ($cutoff): int {
            $count = DB::table('activity_logs')
                ->where('created_at', '<', $cutoff)
                ->count();

            if ($count === 0) {
                return 0;
            }

            // Bulk insert into archive (preserves original created_at).
            DB::table('activity_log_archives')->insertUsing(
                ['id', 'user_id', 'event', 'description', 'subject_type', 'subject_id', 'properties', 'ip_address', 'user_agent', 'created_at'],
                DB::table('activity_logs')->where('created_at', '<', $cutoff)
                    ->select(['id', 'user_id', 'event', 'description', 'subject_type', 'subject_id', 'properties', 'ip_address', 'user_agent', 'created_at']),
            );

            DB::table('activity_logs')->where('created_at', '<', $cutoff)->delete();

            return $count;
        });
    }
}
