<?php


use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels — Tenant-Aware Private Channels
|--------------------------------------------------------------------------
|
| Channel naming convention:
|   private-tenant.{tenantId}                     — tenant-wide events
|   private-tenant.{tenantId}.project.{projectId} — project-level events
|   private-tenant.{tenantId}.task.{taskId}       — task-level events (comments)
|   private-tenant.{tenantId}.user.{userId}       — per-user events (assignments)
|
| All channels are private: the auth callback MUST return a truthy value.
| The per-DB isolation guarantees users only exist in their own tenant DB,
| so verifying tenant()->id is sufficient for cross-tenant protection.
|
*/

/*
|--------------------------------------------------------------------------
| Tenant-wide channel
|--------------------------------------------------------------------------
| Subscription expiry, system-level announcements to the whole tenant.
*/
Broadcast::channel('tenant.{tenantId}', function (User $user, string $tenantId): bool {
    return tenant()?->id === $tenantId;
});

/*
|--------------------------------------------------------------------------
| Project channel
|--------------------------------------------------------------------------
| Project-level updates: status changes, metadata edits, member additions.
| Only users who can see the project (i.e. authenticated in this tenant) allowed.
*/
Broadcast::channel('tenant.{tenantId}.project.{projectId}', function (
    User $user,
    string $tenantId,
    string $projectId,
): bool {
    if (tenant()?->id !== $tenantId) {
        return false;
    }

    return Project::where('id', $projectId)->exists();
});

/*
|--------------------------------------------------------------------------
| Task channel
|--------------------------------------------------------------------------
| Task-level updates: new comments, status changes pushed to task watchers.
*/
Broadcast::channel('tenant.{tenantId}.task.{taskId}', function (
    User $user,
    string $tenantId,
    string $taskId,
): bool {
    if (tenant()?->id !== $tenantId) {
        return false;
    }

    return Task::where('id', $taskId)->exists();
});

/*
|--------------------------------------------------------------------------
| User-private channel
|--------------------------------------------------------------------------
| Direct notifications to a specific user: task assignments, mentions.
| Strictly limited to the authenticated user themselves.
*/
Broadcast::channel('tenant.{tenantId}.user.{userId}', function (
    User $user,
    string $tenantId,
    string $userId,
): bool {
    return tenant()?->id === $tenantId
        && $user->id === $userId;
});
