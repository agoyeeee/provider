<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HandleSsoErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (\Exception $e) {
            // Log SSO errors
            Log::error('SSO Middleware Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'url' => $request->fullUrl()
            ]);

            // Redirect back to login with error message
            if ($request->routeIs('sso.*')) {
                return redirect()->route('login')->withErrors([
                    'sso' => 'Terjadi kesalahan saat proses SSO. Silakan coba lagi.'
                ]);
            }

            throw $e;
        }
    }
}
