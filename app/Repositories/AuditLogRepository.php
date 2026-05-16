<?php


namespace App\Repositories;

use App\Models\AuditLog;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AuditLogRepository implements AuditLogRepositoryInterface
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return AuditLog::query()
            ->with('actor:id,name,email')
            ->when($filters['actor_id'] ?? null, fn ($q, $v) => $q->where('actor_id', $v))
            ->when($filters['action'] ?? null, fn ($q, $v) => $q->where('action', $v))
            ->when($filters['resource_type'] ?? null, fn ($q, $v) => $q->where('resource_type', $v))
            ->when($filters['resource_id'] ?? null, fn ($q, $v) => $q->where('resource_id', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->where('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->where('created_at', '<=', $v))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(array $data): AuditLog
    {
        return AuditLog::create($data);
    }

    public function pruneOlderThan(int $days): int
    {
        $cutoff = now()->subDays($days)->toDateTimeString();

        return DB::table('audit_logs')->where('created_at', '<', $cutoff)->delete();
    }
}
