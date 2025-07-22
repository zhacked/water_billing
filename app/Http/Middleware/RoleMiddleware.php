<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Access\AuthorizationException;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            abort(401, 'Not logged in');
        }

        $user = $request->user();

        // ✅ Spatie-based role check
        if (!$user->hasAnyRole($roles)) {
            throw new AuthorizationException('You do not have permission to access this page.');
        }

        return $next($request);
    }
}
