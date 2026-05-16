<?php


namespace App\Http\Middleware;

use App\Domain\Tenant\Services\TenantResolverService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(private readonly TenantResolverService $resolver) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolver->resolveFromRequest($request);

        if ($tenant === null) {
            abort(404, 'Tenant not found.');
        }

        if ($tenant->isSuspended()) {
            abort(503, 'This account has been suspended. Please contact support.');
        }

        tenancy()->initialize($tenant);

        $response = $next($request);

        tenancy()->end();

        return $response;
    }
}
