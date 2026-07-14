<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  string  ...$roles  roles required for this route
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $allowedRoles = collect($roles)
            ->flatMap(fn (string $role) => explode(',', $role))
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->all();

        // If user is not logged in or role does not match, block access
        if (! $request->user() || ! in_array($request->user()->role, $allowedRoles, true)) {
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}
