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
    public function handle(Request $request, Closure $next, ...$role): Response
    {
        if (!Auth::check()) {
            abort(401, 'Not logged in');
        }

        $user = $request->user();

        logger()->debug('RoleMiddleware Check', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'allowed_roles' => $role,
        ]);


        if (!in_array($user->role, $role)) {
            throw new AuthorizationException('You do not have permission to access this page.');
        }

        return $next($request);
    }
}
