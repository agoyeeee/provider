<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $isPemilikUmkm = $user && $user->role === 'pemilik_umkm';
        $hasCompleteProfile = false;
        $hasUmkm = false;

        if ($isPemilikUmkm) {
            $hasCompleteProfile = ! empty($user->name)
                && ! empty($user->email)
                && ! empty($user->nik)
                && ! empty($user->kontak);

            $hasUmkm = $user->umkm()->exists();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'hasUmkm' => $hasUmkm,
            'hasCompleteProfile' => $hasCompleteProfile,
        ];
    }
}
