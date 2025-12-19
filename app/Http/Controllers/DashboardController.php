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
            'total_provinsi' => Provider::distinct('provinsi')->count('provinsi'),
            'total_kota' => Provider::distinct('kota')->count('kota'),
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

        // Provider by province
        $provider_by_provinsi = Provider::selectRaw('provinsi, count(*) as count')
            ->whereNotNull('provinsi')
            ->groupBy('provinsi')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->provinsi,
                    'value' => $item->count,
                    'color' => $this->getRandomColor()
                ];
            });

        // Provider by provider name
        $provider_by_name = Provider::selectRaw('n_provider, count(*) as count')
            ->whereNotNull('n_provider')
            ->groupBy('n_provider')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->n_provider,
                    'value' => $item->count,
                    'color' => $this->getRandomColor()
                ];
            });

        return Inertia::render('Admin/DashboardView', [
            'stats' => $stats,
            'recent_providers' => $recent_providers,
            'provider_by_provinsi' => $provider_by_provinsi,
            'provider_by_name' => $provider_by_name,
        ]);
    }

    private function getRandomColor()
    {
        $colors = ['#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#EF4444', '#06B6D4', '#84CC16', '#F97316'];
        return $colors[array_rand($colors)];
    }
}
