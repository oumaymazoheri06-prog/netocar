<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsolation
{
    /**
     * Prevent a tenant from accessing a route-bound model owned by another agency.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role === UserRole::Admin->value) {
            return $next($request);
        }

        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if (! $parameter instanceof Model || ! array_key_exists('agency_id', $parameter->getAttributes())) {
                continue;
            }

            abort_unless(
                $user->agency_id && (int) $parameter->getAttribute('agency_id') === (int) $user->agency_id,
                404
            );
        }

        return $next($request);
    }
}
