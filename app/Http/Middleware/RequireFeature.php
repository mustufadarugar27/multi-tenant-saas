<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireFeature
{
    /**
     * Usage: ->middleware('feature:api_access')
     * Feature gating is a no-op — billing/plan system has been removed.
     */
    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        return $next($request);
    }
}
