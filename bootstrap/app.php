<?php


use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\RequirePermission;
use App\Http\Middleware\RequireRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withProviders([
        App\Providers\LangTranslationServiceProvider::class,
        App\Providers\TenancyServiceProvider::class,
        App\Providers\RepositoryServiceProvider::class,
        App\Providers\AuthorizationServiceProvider::class,
        App\Providers\EventServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('tenant.login'));

        $middleware->alias([
            'tenant'     => IdentifyTenant::class,
            'role'       => RequireRole::class,
            'permission' => RequirePermission::class,
            'verified'   => EnsureEmailIsVerified::class,
            'feature'    => App\Http\Middleware\RequireFeature::class,
            // tenant.scope (EnforceTenantScope) removed — per-DB isolation makes
            // cross-tenant token attacks structurally impossible.
        ]);

        $middleware->statefulApi();

        // Global API throttle — 120 req/min per user (fallback; route-level throttle takes priority)
        $middleware->throttleApi(120, 1);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request): bool {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage() ?: 'Not found.'], 404);
            }
        });

        $exceptions->render(function (\App\Domain\Auth\Exceptions\AccountLockedException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 429);
            }
        });

        $exceptions->render(function (\App\Domain\Auth\Exceptions\PrivilegeEscalationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 403);
            }
        });

        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Forbidden. Insufficient permissions.'], 403);
            }
        });
    })
    ->create();
