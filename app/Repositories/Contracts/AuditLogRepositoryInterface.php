<?php


namespace App\Repositories\Contracts;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AuditLogRepositoryInterface
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    public function create(array $data): AuditLog;

    /**
     * Delete audit logs older than $days days.
     * Audit logs should only be pruned after regulatory retention period has passed.
     */
    public function pruneOlderThan(int $days): int;
}
