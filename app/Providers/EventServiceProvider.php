<?php


namespace App\Providers;

use App\Domain\Tenant\Events\SubscriptionExpired;
use App\Events\AttachmentDeleted;
use App\Events\AttachmentUploaded;
use App\Events\CommentDeleted;
use App\Events\CommentUpdated;
use App\Events\ProjectUpdated;
use App\Events\TaskAssigned;
use App\Events\TaskCommentAdded;
use App\Listeners\Broadcast\BroadcastCommentAdded;
use App\Listeners\Broadcast\BroadcastProjectUpdated;
use App\Listeners\Broadcast\BroadcastSubscriptionExpired;
use App\Listeners\Broadcast\BroadcastTaskAssigned;
use App\Listeners\HandleTaskAssigned;
use App\Listeners\HandleTaskCommentAdded;
use App\Listeners\RecordAuditEvent;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ProjectUpdated::class => [
            BroadcastProjectUpdated::class,
        ],

        TaskAssigned::class => [
            HandleTaskAssigned::class,
            BroadcastTaskAssigned::class,
        ],
        TaskCommentAdded::class => [
            HandleTaskCommentAdded::class,
            BroadcastCommentAdded::class,
        ],
        CommentUpdated::class => [],
        CommentDeleted::class => [],

        AttachmentUploaded::class => [],
        AttachmentDeleted::class  => [],

        SubscriptionExpired::class => [
            BroadcastSubscriptionExpired::class,
        ],

        Login::class => [
            [RecordAuditEvent::class, 'handleLogin'],
        ],
        Logout::class => [
            [RecordAuditEvent::class, 'handleLogout'],
        ],
        Failed::class => [
            [RecordAuditEvent::class, 'handleFailed'],
        ],
        PasswordReset::class => [
            [RecordAuditEvent::class, 'handlePasswordReset'],
        ],
    ];

    public function boot(): void
    {
        //
    }
}
