<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks access until the user's email is verified.
 *
 * Apply after auth:sanctum on routes that require verified email.
 */
class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401, 'Unauthenticated.');
        }

        if (! $user->hasVerifiedEmail()) {
            abort(403, 'Your email address is not verified.');
        }

        return $next($request);
    }
}
