<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated This middleware is no longer in use since pemilik-umkm routes have been removed.
 * Kept for reference. Can be safely deleted.
 */
class CheckPemilikUmkm
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->role !== 'pemilik_umkm') {
            abort(403, 'Unauthorized access. Only Pemilik UMKM can access this page.');
        }

        return $next($request);
    }
}
