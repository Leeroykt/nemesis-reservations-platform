<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $levels = [
            'host'    => 1,
            'manager' => 2,
            'owner'   => 3,
        ];

        if (!isset($levels[$user->role]) || $levels[$user->role] < $levels[$role]) {
            abort(403, 'Insufficient permissions. Required: ' . $role);
        }

        return $next($request);
    }
}