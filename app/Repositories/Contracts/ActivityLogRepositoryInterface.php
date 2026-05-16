<?php


namespace App\Repositories\Contracts;

use App\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ActivityLogRepositoryInterface
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    public function paginateForUser(string $userId, int $perPage): LengthAwarePaginator;

    public function create(array $data): ActivityLog;

    /**
     * Move logs older than $days days to the archive table and delete originals.
     * Returns number of rows archived.
     */
    public function archiveOlderThan(int $days): int;
}
