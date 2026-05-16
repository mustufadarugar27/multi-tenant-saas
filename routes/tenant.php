<?php


use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Billing\BillingController;
use App\Http\Controllers\Web\Notification\NotificationController as WebNotificationController;
use App\Http\Controllers\Web\Project\ProjectController as WebProjectController;
use App\Http\Controllers\Web\Task\TaskController as WebTaskController;
use App\Http\Controllers\Web\User\UserController as WebUserController;
use App\Http\Middleware\CheckSubscriptionActive;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| All routes here are tenant-scoped via domain-based tenancy initialization.
| Two surface areas:
|
|  [web]   Session auth, Blade views.
|          Auth: auth (web guard / session)
|
|  [api]   Sanctum token auth, JSON responses.
|          Auth: auth:sanctum
|          Prefix: api/v1
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function (): void {

    // Guest-only
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [LoginController::class, 'showForm'])->name('tenant.login');
        Route::post('/login', [LoginController::class, 'login'])
            ->middleware('throttle:10,1')
            ->name('tenant.login.post');
    });

    // Authenticated
    Route::middleware('auth')->group(function (): void {

        Route::get('/', fn () => redirect()->route('tenant.projects.index'));

        Route::post('/logout', [LoginController::class, 'logout'])->name('tenant.logout');

        // User management & profile
        Route::get('/users/create', [WebUserController::class, 'create'])->name('tenant.users.create');
        Route::post('/users', [WebUserController::class, 'store'])->name('tenant.users.store');
        Route::get('/profile', [WebUserController::class, 'profile'])->name('tenant.profile');
        Route::patch('/profile', [WebUserController::class, 'updateProfile'])->name('tenant.profile.update');

        // Billing & invoices (tenant admin)
        Route::middleware([CheckSubscriptionActive::class])->group(function (): void {
            Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
            Route::get('/billing/invoices/{invoice}/download', [BillingController::class, 'downloadInvoice'])
                ->name('billing.invoices.download');
        });

        // Notifications
        Route::get('/notifications', [WebNotificationController::class, 'index'])->name('tenant.notifications.index');
        Route::post('/notifications/{id}/read', [WebNotificationController::class, 'markRead'])->name('tenant.notifications.read');
        Route::post('/notifications/read-all', [WebNotificationController::class, 'markAllRead'])->name('tenant.notifications.read-all');

        // Restore before resource to avoid {project} binding collision
        Route::post('projects/{project}/restore', [WebProjectController::class, 'restore'])
            ->name('tenant.projects.restore')
            ->withTrashed();

        Route::resource('projects', WebProjectController::class)
            ->names([
                'index'   => 'tenant.projects.index',
                'create'  => 'tenant.projects.create',
                'store'   => 'tenant.projects.store',
                'show'    => 'tenant.projects.show',
                'edit'    => 'tenant.projects.edit',
                'update'  => 'tenant.projects.update',
                'destroy' => 'tenant.projects.destroy',
            ]);


        Route::post('tasks/{task}/restore', [WebTaskController::class, 'restore'])
            ->name('tenant.tasks.restore')
            ->withTrashed();

        // Task comments & attachments (nested actions on the task show page)
        Route::post('tasks/{task}/comments', [WebTaskController::class, 'storeComment'])
            ->name('tenant.tasks.comments.store');
        Route::patch('tasks/{task}/comments/{comment}', [WebTaskController::class, 'updateComment'])
            ->name('tenant.tasks.comments.update');
        Route::delete('tasks/{task}/comments/{comment}', [WebTaskController::class, 'destroyComment'])
            ->name('tenant.tasks.comments.destroy');

        Route::post('tasks/{task}/attachments', [WebTaskController::class, 'storeAttachment'])
            ->name('tenant.tasks.attachments.store');
        Route::get('tasks/{task}/attachments/{attachment}/download', [WebTaskController::class, 'downloadAttachment'])
            ->name('tenant.tasks.attachments.download');
        Route::delete('tasks/{task}/attachments/{attachment}', [WebTaskController::class, 'destroyAttachment'])
            ->name('tenant.tasks.attachments.destroy');

        Route::resource('tasks', WebTaskController::class)
            ->names([
                'index'   => 'tenant.tasks.index',
                'create'  => 'tenant.tasks.create',
                'store'   => 'tenant.tasks.store',
                'show'    => 'tenant.tasks.show',
                'edit'    => 'tenant.tasks.edit',
                'update'  => 'tenant.tasks.update',
                'destroy' => 'tenant.tasks.destroy',
            ]);
    });
});

