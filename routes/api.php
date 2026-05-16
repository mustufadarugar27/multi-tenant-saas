<?php


use App\Http\Controllers\Api\V1\Admin\AuditLogController;
use App\Http\Controllers\Api\V1\Billing\PlanController;
use App\Http\Controllers\Api\V1\Billing\WebhookController;
use App\Http\Controllers\Api\V1\Auth\ActivityLogController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Auth\PermissionController;
use App\Http\Controllers\Api\V1\Auth\RoleController;
use App\Http\Controllers\Api\V1\Project\ProjectController;
use App\Http\Controllers\Api\V1\Project\ProjectHistoryController;
use App\Http\Controllers\Api\V1\Task\TaskAttachmentController;
use App\Http\Controllers\Api\V1\Task\TaskCommentController;
use App\Http\Controllers\Api\V1\Task\TaskController;
use App\Http\Controllers\Api\V1\Task\TaskHistoryController;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Support\Facades\Route;

// Stripe webhook — no auth, raw body needed, webhook secret verified inside controller
Route::post('stripe/webhook', [WebhookController::class, 'handle'])
    ->name('stripe.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Public plan listing
Route::prefix('v1')->name('v1.')->group(function (): void {
    Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
    Route::get('plans/{plan}', [PlanController::class, 'show'])->name('plans.show');
});

/*
|--------------------------------------------------------------------------
| API Routes — V1
|--------------------------------------------------------------------------
|
| Middleware groups:
|
|  [public]          No tenant identification required.
|                    Company registration only.
|
|  [tenant.guest]    Tenant identified, not authenticated.
|                    Login + password reset flows.
|
|  [tenant.auth]     IdentifyTenant + auth:sanctum.
|                    Per-DB tenancy makes cross-tenant token attacks
|                    impossible — no EnforceTenantScope needed.
|
*/

// Stripe webhook — no auth, raw body needed, webhook secret verified inside controller
Route::post('stripe/webhook', [WebhookController::class, 'handle'])
    ->name('stripe.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Public plan listing
Route::prefix('v1')->name('v1.')->group(function (): void {
    Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
    Route::get('plans/{plan}', [PlanController::class, 'show'])->name('plans.show');
});

Route::prefix('v1')->name('v1.')->group(function (): void {

    Route::prefix('auth')->name('auth.')->group(function (): void {
        Route::post('register', [AuthController::class, 'register'])->name('register');
    });

    Route::prefix('auth')->name('auth.')->middleware([IdentifyTenant::class])->group(function (): void {
        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:10,1')
            ->name('login');

        Route::post('forgot-password', [PasswordResetController::class, 'forgotPassword'])
            ->middleware('throttle:5,1')
            ->name('password.forgot');

        Route::post('reset-password', [PasswordResetController::class, 'resetPassword'])
            ->middleware('throttle:5,1')
            ->name('password.reset');
    });

    Route::middleware([
        IdentifyTenant::class,
        'auth:sanctum',
        // EnforceTenantScope removed — per-DB isolation makes cross-tenant
        // token attacks structurally impossible (users exist only in their DB).
    ])->group(function (): void {

        Route::prefix('auth')->name('auth.')->group(function (): void {
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('logout.all');
            Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
            Route::get('me', [AuthController::class, 'me'])->name('me');

            Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
                ->middleware('signed')
                ->name('verification.verify');

            Route::post('email/resend', [EmailVerificationController::class, 'resend'])
                ->middleware('throttle:6,1')
                ->name('verification.resend');
        });

        Route::middleware([EnsureEmailIsVerified::class])->group(function (): void {

            // RBAC — roles
            Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
            Route::post('users/{userId}/roles', [RoleController::class, 'assign'])->name('roles.assign');
            Route::delete('users/{userId}/roles/{role}', [RoleController::class, 'revoke'])->name('roles.revoke');

            // RBAC — permissions
            Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
            Route::get('roles/{role}/permissions', [PermissionController::class, 'forRole'])->name('permissions.for-role');
            Route::post('roles/{role}/permissions/{permission}', [PermissionController::class, 'grantToRole'])->name('permissions.grant');
            Route::delete('roles/{role}/permissions/{permission}', [PermissionController::class, 'revokeFromRole'])->name('permissions.revoke');

            // Activity logs
            Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
            Route::get('activity-logs/me', [ActivityLogController::class, 'mine'])->name('activity-logs.mine');

            // Projects
            Route::post('projects/{project}/restore', [ProjectController::class, 'restore'])
                ->name('projects.restore')
                ->withTrashed();
            Route::apiResource('projects', ProjectController::class);

            // Tasks
            Route::post('tasks/{task}/restore', [TaskController::class, 'restore'])
                ->name('tasks.restore')
                ->withTrashed();
            Route::apiResource('tasks', TaskController::class);

            // Task — comments (threaded)
            Route::get('tasks/{task}/comments', [TaskCommentController::class, 'index'])
                ->name('tasks.comments.index');
            Route::post('tasks/{task}/comments', [TaskCommentController::class, 'store'])
                ->name('tasks.comments.store');
            Route::put('tasks/{task}/comments/{comment}', [TaskCommentController::class, 'update'])
                ->name('tasks.comments.update');
            Route::delete('tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])
                ->name('tasks.comments.destroy');

            // Task — attachments
            Route::get('tasks/{task}/attachments', [TaskAttachmentController::class, 'index'])
                ->name('tasks.attachments.index');
            Route::post('tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])
                ->name('tasks.attachments.store');
            Route::delete('tasks/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])
                ->name('tasks.attachments.destroy');

            // Task — history (immutable, read-only)
            Route::get('tasks/{task}/history', [TaskHistoryController::class, 'index'])
                ->name('tasks.history.index');

            // Project — history (immutable, read-only)
            Route::get('projects/{project}/history', [ProjectHistoryController::class, 'index'])
                ->name('projects.history.index');

            // Admin — audit logs (admin/compliance view, company-admin+ only)
            Route::prefix('admin')->name('admin.')->group(function (): void {
                Route::get('audit-logs', [AuditLogController::class, 'index'])
                    ->name('audit-logs.index');
                Route::get('audit-logs/{log}', [AuditLogController::class, 'show'])
                    ->name('audit-logs.show');
            });
        });
    });
});
