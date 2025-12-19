<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Auth middleware should handle authentication
        // This middleware only checks role
        $user = Auth::user();

        if (! $user || ! in_array($user->role, ['admin', 'super_admin'], true)) {
            abort(403, 'Unauthorized access. Only Admin can access this page.');
        }

        return $next($request);
    }
}
