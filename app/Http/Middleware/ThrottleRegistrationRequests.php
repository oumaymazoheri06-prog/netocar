<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRegistrationRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('post') || ! $request->routeIs('register.store')) {
            return $next($request);
        }

        $key = 'registration:'.Str::lower((string) $request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $retryAfter = RateLimiter::availableIn($key);

            throw new ThrottleRequestsException(
                'Trop de tentatives d’inscription. Réessayez dans quelques minutes.',
                null,
                ['Retry-After' => $retryAfter]
            );
        }

        RateLimiter::hit($key, 600);

        return $next($request);
    }
}
