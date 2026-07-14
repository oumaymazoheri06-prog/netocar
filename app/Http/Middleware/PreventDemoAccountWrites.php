<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventDemoAccountWrites
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isDemoAccount()) {
            return $next($request);
        }

        if ($request->isMethodSafe() || $request->routeIs('logout')) {
            return $next($request);
        }

        $message = 'Mode demo : les actions de modification sont bloquees pour garder les donnees propres.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 423);
        }

        return back()->with('error', $message);
    }
}
