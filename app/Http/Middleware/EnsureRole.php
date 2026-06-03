<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes: ->middleware('role:admin') or ->middleware('role:admin,tim_it')
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            // not authenticated: let auth middleware handle redirect
            abort(403);
        }

        // normalize role strings (allow comma separated)
        if (count($roles) === 1 && str_contains($roles[0], ',')) {
            $roles = array_map('trim', explode(',', $roles[0]));
        }

        // jika user role cocok salah satu role yang dibolehkan -> lanjut
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // otherwise forbidden
        abort(403, 'Akses ditolak (role tidak memenuhi syarat).');
    }
}
