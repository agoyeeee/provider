<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class LandingController extends Controller
{
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
                ->distinct('n_provider')
                ->count('n_provider'),
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
            ->select('provinsi', DB::raw('COUNT(*) as total'))
            ->whereNotNull('provinsi')
            ->groupBy('provinsi')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'provinsi' => $row->provinsi,
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
            ->whereNotNull('x')
            ->whereNotNull('y')
            ->orderByDesc('tgl_survey')
            ->limit(50)
            ->get()
            ->map(function (Provider $provider) {
                return [
                    'id' => $provider->id,
                    'name' => $provider->n_provider ?? 'Provider',
                    'location' => trim(
                        collect([$provider->kelurahan, $provider->kecamatan, $provider->kota, $provider->provinsi])
                            ->filter()
                            ->implode(', ')
                    ),
                    'odp' => $provider->odp,
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
            ->whereNotNull('x')
            ->whereNotNull('y')
            ->orderByDesc('tgl_survey')
            ->limit(500)
            ->get()
            ->map(function (Provider $provider) {
                return [
                    'id' => $provider->id,
                    'fid' => $provider->fid,
                    'name' => $provider->n_provider ?? 'Provider',
                    'location' => trim(
                        collect([$provider->kelurahan, $provider->kecamatan, $provider->kota, $provider->provinsi])
                            ->filter()
                            ->implode(', ')
                    ),
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
