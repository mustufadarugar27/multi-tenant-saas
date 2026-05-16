<?php


namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    /**
     * Record a security or compliance audit event.
     * Audit logs are always written synchronously — they are compliance records
     * and must not be lost if the queue worker is down.
     */
    public function record(
        string $action,
        string $status = 'success',
        ?User $actor = null,
        ?Model $resource = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = [],
    ): AuditLog {
        return AuditLog::create([
            'actor_id' => $actor?->id,
            'actor_type' => $actor ? 'user' : 'system',
            'action' => $action,
            'resource_type' => $resource?->getMorphClass(),
            'resource_id' => $resource?->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'metadata' => $metadata ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => $status,
        ]);
    }

    /**
     * Shorthand for recording a failed security event (login failure, unauthorized access, etc.).
     */
    public function failure(
        string $action,
        ?User $actor = null,
        array $metadata = [],
    ): AuditLog {
        return $this->record(
            action: $action,
            status: 'failure',
            actor: $actor,
            metadata: $metadata,
        );
    }

    /**
     * Record a data mutation with before/after snapshots for compliance diff.
     */
    public function mutation(
        string $action,
        Model $resource,
        array $oldValues,
        array $newValues,
        ?User $actor = null,
    ): AuditLog {
        return $this->record(
            action: $action,
            status: 'success',
            actor: $actor,
            resource: $resource,
            oldValues: $oldValues,
            newValues: $newValues,
        );
    }
}
