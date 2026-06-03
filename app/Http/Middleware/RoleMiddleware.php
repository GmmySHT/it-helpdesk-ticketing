<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, \Closure $next, ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if (count($roles) === 1 && is_string($roles[0]) && str_contains($roles[0], ',')) {
            $roles = array_map('trim', explode(',', $roles[0]));
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
