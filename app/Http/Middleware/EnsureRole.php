<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        /** @var User|null $user */
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