<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Auth middleware should handle authentication
        // This middleware only checks role
        $user = Auth::user();

        if (! $user || $user->role !== 'super_admin') {
            abort(403, 'Unauthorized access. Only Super Admin can access this page.');
        }

        return $next($request);
    }
}
