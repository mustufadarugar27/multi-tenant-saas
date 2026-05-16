<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// prevents cross-tenant token reuse — runs after Sanctum auth
class EnforceTenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($user->tenant_id !== tenant()->getTenantKey()) {
            logger()->warning('Cross-tenant token attempt detected.', [
                'user_id' => $user->id,
                'user_tenant_id' => $user->tenant_id,
                'requested_tenant_id' => tenant()->getTenantKey(),
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            abort(403, 'Token is not valid for this tenant.');
        }

        return $next($request);
    }
}
