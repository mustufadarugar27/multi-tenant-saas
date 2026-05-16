<?php


namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectHistory;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\User;
use App\Policies\ActivityLogPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ProjectHistoryPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TaskAttachmentPolicy;
use App\Policies\TaskCommentPolicy;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use App\Support\EnumConfig;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;

class AuthorizationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(ActivityLog::class, ActivityLogPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(ProjectHistory::class, ProjectHistoryPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(TaskComment::class, TaskCommentPolicy::class);
        Gate::policy(TaskAttachment::class, TaskAttachmentPolicy::class);


        Gate::before(function (User $user): ?bool {
            if ($user->isSuperAdmin()) {
                return true;
            }

            return null;
        });

        Gate::define('manage-tenant', function (User $user): bool {
            return $user->canManageTenant();
        });

        Gate::define('manage-users', function (User $user): bool {
            return $user->canManageUsers();
        });

        Gate::define('manage-projects', function (User $user): bool {
            return EnumConfig::userRoleCanManageProjects($user->role);
        });

        Gate::define('view-activity-logs', function (User $user): bool {
            return $user->canManageTenant();
        });

        Gate::define('manage-rbac', function (User $user): bool {
            return in_array($user->role, ['super_admin', 'company_admin'], true);
        });

    }
}
