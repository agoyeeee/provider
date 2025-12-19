<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceFullPageRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Force full page redirect for SSO routes to avoid CORS issues
        if ($request->routeIs('sso.redirect') || $request->routeIs('sso.callback')) {
            $response->headers->set('X-Inertia', false);
        }

        return $response;
    }
}
