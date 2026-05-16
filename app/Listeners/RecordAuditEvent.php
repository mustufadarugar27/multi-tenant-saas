<?php


namespace App\Listeners;

use App\Services\AuditLogService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;

class RecordAuditEvent
{
    public function __construct(
        private readonly AuditLogService $auditLog,
    ) {}

    public function handleLogin(Login $event): void
    {
        $this->auditLog->record(
            action: 'auth.login',
            status: 'success',
            actor: $event->user,
        );
    }

    public function handleLogout(Logout $event): void
    {
        $this->auditLog->record(
            action: 'auth.logout',
            status: 'success',
            actor: $event->user,
        );
    }

    public function handleFailed(Failed $event): void
    {
        $this->auditLog->record(
            action: 'auth.login_failed',
            status: 'failure',
            metadata: ['email' => $event->credentials['email'] ?? null],
        );
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        $this->auditLog->record(
            action: 'auth.password_reset',
            status: 'success',
            actor: $event->user,
        );
    }
}
