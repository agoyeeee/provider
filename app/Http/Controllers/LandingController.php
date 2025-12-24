<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class LandingController extends Controller
{
    private function normalizeProviderKey(string $value): string
    {
        $normalized = strtolower(trim($value));
        return preg_replace('/[^a-z0-9]/', '', $normalized) ?? '';
    }

    private function normalizeProviderName(?string $value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }

        if (str_contains($raw, '/')) {
            $parts = array_filter(array_map('trim', explode('/', $raw)), fn ($part) => $part !== '');
            $normalizedParts = array_values(array_filter(array_map(fn ($part) => $this->normalizeProviderKey($part), $parts)));
            if ($normalizedParts && count(array_unique($normalizedParts)) === 1) {
                return $normalizedParts[0];
            }
        }

        return $this->normalizeProviderKey($raw);
    }

    /**
     * Show the public landing page with provider highlights.
     */
    public function index()
    {
        $latestSurvey = Provider::whereNotNull('tgl_survey')
            ->orderByDesc('tgl_survey')
            ->first();

        $stats = [
            'totalRecords' => Provider::count(),
            'uniqueProviders' => Provider::whereNotNull('n_provider')
                ->pluck('n_provider')
                ->flatMap(function ($value) {
                    return collect(explode(',', $value))
                        ->map(fn ($name) => $this->normalizeProviderName($name))
                        ->filter(fn ($name) => $name !== '');
                })
                ->unique()
                ->count(),
            'coverageDistricts' => Provider::whereNotNull('kecamatan')
                ->distinct('kecamatan')
                ->count('kecamatan'),
            'coverageVillages' => Provider::whereNotNull('kelurahan')
                ->distinct('kelurahan')
                ->count('kelurahan'),
            // legacy values kept for backward compatibility
            'coverageCities' => Provider::whereNotNull('kota')
                ->distinct('kota')
                ->count('kota'),
            'coverageProvinces' => Provider::whereNotNull('provinsi')
                ->distinct('provinsi')
                ->count('provinsi'),
            'latestSurvey' => optional($latestSurvey?->tgl_survey)->format('Y-m-d'),
        ];

        $featuredProviders = Provider::query()
            ->latest('tgl_survey')
            ->limit(6)
            ->get()
            ->map(function (Provider $provider) {
                return [
                    'id' => $provider->id,
                    'fid' => $provider->fid,
                    'name' => $provider->n_provider,
                    'utilitas' => $provider->utilitas,
                    'kota' => $provider->kota,
                    'provinsi' => $provider->provinsi,
                    'kecamatan' => $provider->kecamatan,
                    'kelurahan' => $provider->kelurahan,
                    'odp' => $provider->odp,
                    'sijali' => $provider->sijali,
                    'surveyed_at' => $provider->tgl_survey?->format('Y-m-d'),
                    'coordinate' => [
                        'x' => $provider->x,
                        'y' => $provider->y,
                    ],
                    'foto_url' => $provider->foto_url,
                ];
            });

        $coverage = Provider::query()
            ->select('kecamatan', DB::raw('COUNT(*) as total'))
            ->whereNotNull('kecamatan')
            ->groupBy('kecamatan')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'kecamatan' => $row->kecamatan,
                'total' => $row->total,
            ]);

        $timeline = Provider::query()
            ->select(DB::raw('DATE(tgl_survey) as date'), DB::raw('COUNT(*) as total'))
            ->whereNotNull('tgl_survey')
            ->groupBy(DB::raw('DATE(tgl_survey)'))
            ->orderByDesc(DB::raw('DATE(tgl_survey)'))
            ->limit(6)
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'total' => $row->total,
            ]);

        $mapProviders = Provider::query()
            ->orderByDesc('tgl_survey')
            ->get()
            ->map(function (Provider $provider) {
                return [
                    'id' => $provider->id,
                    'name' => $provider->n_provider,
                    'location' => trim(
                        collect([$provider->kelurahan, $provider->kecamatan, $provider->kota, $provider->provinsi])
                            ->filter()
                            ->implode(', ')
                    ),
                    'kecamatan' => $provider->kecamatan,
                    'kelurahan' => $provider->kelurahan,
                    'odp' => $provider->odp,
                    'sijali' => $provider->sijali,
                    'utilitas' => $provider->utilitas,
                    'surveyed_at' => $provider->tgl_survey?->format('Y-m-d'),
                    'coordinates' => [
                        'lat' => $provider->y,
                        'lng' => $provider->x,
                    ],
                    'maps_url' => $provider->x && $provider->y
                        ? sprintf('https://www.google.com/maps?q=%s,%s', $provider->y, $provider->x)
                        : null,
                    'foto_url' => $provider->foto_url,
                ];
            });

        return Inertia::render('Landing/Home', [
            'stats' => $stats,
            'featuredProviders' => $featuredProviders,
            'coverage' => $coverage,
            'timeline' => $timeline,
            'mapProviders' => $mapProviders,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'appName' => config('app.name'),
        ]);
    }

    /**
     * Full map view page.
     */
    public function map()
    {
        $mapProviders = Provider::query()
            ->orderByDesc('tgl_survey')
            ->get()
            ->map(function (Provider $provider) {
                return [
                    'id' => $provider->id,
                    'fid' => $provider->fid,
                    'name' => $provider->n_provider,
                    'location' => trim(
                        collect([$provider->kelurahan, $provider->kecamatan, $provider->kota, $provider->provinsi])
                            ->filter()
                            ->implode(', ')
                    ),
                    'kecamatan' => $provider->kecamatan,
                    'kelurahan' => $provider->kelurahan,
                    'utilitas' => $provider->utilitas,
                    'odp' => $provider->odp,
                    'sijali' => $provider->sijali,
                    'sijali_link' => $provider->sijali_link,
                    'surveyed_at' => $provider->tgl_survey?->format('Y-m-d'),
                    'coordinates' => [
                        'lat' => $provider->y,
                        'lng' => $provider->x,
                    ],
                    'maps_url' => $provider->x && $provider->y
                        ? sprintf('https://www.google.com/maps?q=%s,%s', $provider->y, $provider->x)
                        : null,
                    'foto_url' => $provider->foto_url,
                ];
            });

        return Inertia::render('Landing/Map', [
            'mapProviders' => $mapProviders,
            'appName' => config('app.name'),
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }
}
