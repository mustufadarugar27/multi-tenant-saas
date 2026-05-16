<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if (! $tenant) {
            return $next($request);
        }

        if ($tenant->isSubscriptionActive() || $tenant->isInGracePeriod()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your subscription has expired. Please renew to continue.',
                'code'    => 'subscription_expired',
            ], 402);
        }

        return redirect()->route('billing.expired');
    }
}
