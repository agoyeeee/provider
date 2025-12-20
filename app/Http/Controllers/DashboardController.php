<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Provider;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public static function statsSummary(): array
    {
        return [
            'total_users' => User::count(),
            'total_provider' => Provider::count(),
            'total_provider_unique' => Provider::distinct('n_provider')->count('n_provider'),
            'total_kecamatan' => Provider::distinct('kecamatan')->count('kecamatan'),
            'total_kelurahan' => Provider::distinct('kelurahan')->count('kelurahan'),
        ];
    }

    public function index()
    {
        $stats = self::statsSummary();

        // Get recent providers
        $recent_providers = Provider::with('user')
            ->latest()
            ->limit(5)
            ->get();

        // Provider by kecamatan
        $provider_by_kecamatan = Provider::selectRaw('kecamatan, count(*) as count')
            ->whereNotNull('kecamatan')
            ->groupBy('kecamatan')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->kecamatan,
                    'value' => $item->count,
                    'color' => $this->getRandomColor()
                ];
            });

        // Provider by kelurahan
        $provider_by_kelurahan = Provider::selectRaw('kelurahan, count(*) as count')
            ->whereNotNull('kelurahan')
            ->groupBy('kelurahan')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->kelurahan,
                    'value' => $item->count,
                    'color' => $this->getRandomColor()
                ];
            });

        return Inertia::render('Admin/DashboardView', [
            'stats' => $stats,
            'recent_providers' => $recent_providers,
            'provider_by_kecamatan' => $provider_by_kecamatan,
            'provider_by_kelurahan' => $provider_by_kelurahan,
        ]);
    }

    private function getRandomColor()
    {
        $colors = ['#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#EF4444', '#06B6D4', '#84CC16', '#F97316'];
        return $colors[array_rand($colors)];
    }
}
