<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

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

        // Check if profile is complete
        $hasCompleteProfile = $this->hasCompleteProfile($user);

        // Share hasUmkm data with Inertia for sidebar
        $hasUmkm = $user->umkm()->exists();
        Inertia::share('hasUmkm', $hasUmkm);
        Inertia::share('hasCompleteProfile', $hasCompleteProfile);

        // Redirect to profile completion if accessing UMKM routes without complete profile
        $umkmRoutes = [
            'pemilik-umkm.umkm.create',
            'pemilik-umkm.umkm.store',
            'pemilik-umkm.umkm.show',
            'pemilik-umkm.cabang.index',
            'pemilik-umkm.cabang.store',
            'pemilik-umkm.cabang.update',
            'pemilik-umkm.cabang.destroy',
        ];
        $currentRoute = $request->route()->getName();

        if (!$hasCompleteProfile && in_array($currentRoute, $umkmRoutes)) {
            return redirect()->route('pemilik-umkm.profile')
                ->with('error', 'Untuk mengakses fitur UMKM, lengkapi data diri terlebih dahulu. Data yang dibutuhkan: Nama lengkap, Email, NIK (16 digit), dan Nomor kontak.');
        }

        return $next($request);
    }

    /**
     * Check if user has complete profile
     */
    private function hasCompleteProfile($user)
    {
        return !empty($user->name) &&
               !empty($user->email) &&
               !empty($user->nik) &&
               !empty($user->kontak);
    }
}
