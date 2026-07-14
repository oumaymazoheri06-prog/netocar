<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;

class EnsureAgencyLicenseActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || $user->role === UserRole::Admin->value) {
            return $next($request);
        }

        if (! in_array($user->role, [UserRole::Manager->value, UserRole::Staff->value], true)) {
            return $next($request);
        }

        $agency = $user->agency;

        abort_unless($agency, 403, 'Aucune agence n’est liée à ce compte.');

        if ($agency->hasActiveLicense()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, $agency->license_block_reason);
        }

        if ($user->role === UserRole::Manager->value) {
            return redirect()
                ->route('agency-billing.edit')
                ->with('license_error', $agency->license_block_reason);
        }

        abort(403, $agency->license_block_reason);
    }
}
