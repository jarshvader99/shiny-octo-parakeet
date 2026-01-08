<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (!$request->user()) {
            return $next($request);
        }

        // Skip check if already on onboarding or logout routes
        if ($request->routeIs('onboarding.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        // Redirect to onboarding if profile incomplete
        if (!$request->user()->hasCompletedProfile()) {
            return redirect()->route('onboarding.zip-code');
        }

        return $next($request);
    }
}
