<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\Tiang;
use Illuminate\Http\Request;
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
            $parts = array_filter(array_map('trim', explode('/', $raw)), fn($part) => $part !== '');
            $normalizedParts = array_values(array_filter(array_map(fn($part) => $this->normalizeProviderKey($part), $parts)));
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
            'uniqueProviders' => Tiang::pluck('n_provider')
                ->map(fn($name) => $this->normalizeProviderName($name))
                ->filter(fn($name) => $name !== '')
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

        $featuredProviders = Provider::with('tiang')
            ->latest('tgl_survey')
            ->limit(6)
            ->get()
            ->map(function (Provider $provider) {
                return [
                    'id' => $provider->id,
                    'name' => $provider->n_provider_label,
                    'utilitas' => $provider->utilitas,
                    'kota' => $provider->kota,
                    'provinsi' => $provider->provinsi,
                    'kecamatan' => $provider->kecamatan,
                    'kelurahan' => $provider->kelurahan,
                    'odp' => $provider->odp_label,
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
            ->map(fn($row) => [
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
            ->map(fn($row) => [
                'date' => $row->date,
                'total' => $row->total,
            ]);

        $mapProviders = Provider::with('tiang')
            ->orderByDesc('tgl_survey')
            ->get()
            ->map(function (Provider $provider) {
                return [
                    'id' => $provider->id,
                    'name' => $provider->n_provider_label,
                    'location' => trim(
                        collect([$provider->kelurahan, $provider->kecamatan, $provider->kota, $provider->provinsi])
                            ->filter()
                            ->implode(', ')
                    ),
                    'kecamatan' => $provider->kecamatan,
                    'kelurahan' => $provider->kelurahan,
                    'odp' => $provider->odp_label,
                    'tiang' => $provider->tiangList(),
                    'jml_tiang' => $provider->tiang->count(),
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

        $permitCounts = Tiang::query()
            ->select('status_izin', DB::raw('COUNT(*) as total'))
            ->groupBy('status_izin')
            ->pluck('total', 'status_izin');

        $permits = [
            'counts' => [
                'all' => $permitCounts->sum(),
                'berizin' => (int) ($permitCounts['berizin'] ?? 0),
                'tidak_berizin' => (int) ($permitCounts['tidak_berizin'] ?? 0),
            ],
            'list' => Tiang::with('provider:id,kecamatan,kelurahan,tgl_survey')
                ->latest('id')
                ->limit(12)
                ->get()
                ->map(fn(Tiang $t) => [
                    'id' => $t->id,
                    'provider' => $t->n_provider,
                    'kecamatan' => $t->provider?->kecamatan,
                    'kelurahan' => $t->provider?->kelurahan,
                    'status' => $t->status_izin,
                    'submitted_at' => $t->provider?->tgl_survey?->format('Y-m-d'),
                ]),
        ];

        return Inertia::render('Landing/Home', [
            'stats' => $stats,
            'permits' => $permits,
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
    /** Halaman peta: hanya opsi filter (query ringan); titik diambil lewat mapPoints() sesuai area yang tampil. */
    public function map()
    {
        return Inertia::render('Landing/Map', [
            'wilayah' => Provider::query()->whereNotNull('kelurahan')
                ->distinct()->orderBy('kecamatan')->orderBy('kelurahan')->get(['kecamatan', 'kelurahan']),
            'providerOptions' => Tiang::query()->distinct()->orderBy('n_provider')->pluck('n_provider'),
            'jalanOptions' => Provider::query()->whereNotNull('sijali')->distinct()->orderBy('sijali')->pluck('sijali'),
            'appName' => config('app.name'),
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    /** Titik peta di dalam bbox + filter. Maksimal LIMIT titik; 'truncated' true jika ada yang terpotong. */
    public function mapPoints(Request $request)
    {
        $limit = 3000;
        $v = $request->validate([
            'west' => 'nullable|numeric', 'east' => 'nullable|numeric',
            'south' => 'nullable|numeric', 'north' => 'nullable|numeric',
            'kecamatan' => 'nullable|string', 'kelurahan' => 'nullable|string',
            'jalan' => 'nullable|string', 'provider' => 'nullable|string', 'q' => 'nullable|string|max:100',
        ]);

        $q = Provider::query()->whereNotNull('x')->whereNotNull('y');
        if (isset($v['west'], $v['east'], $v['south'], $v['north'])) {
            $q->whereBetween('x', [$v['west'], $v['east']])->whereBetween('y', [$v['south'], $v['north']]);
        }
        foreach (['kecamatan', 'kelurahan'] as $f) {
            if (!empty($v[$f])) $q->where($f, $v[$f]);
        }
        if (!empty($v['jalan'])) $q->where('sijali', $v['jalan']);
        if (!empty($v['provider'])) {
            $q->whereHas('tiang', fn($t) => $t->where('n_provider', $v['provider']));
        }
        if (!empty($v['q'])) {
            $like = '%' . addcslashes($v['q'], '%_\\') . '%';
            $q->where(fn($w) => $w->where('kecamatan', 'like', $like)->orWhere('kelurahan', 'like', $like)
                ->orWhere('sijali', 'like', $like)
                ->orWhereHas('tiang', fn($t) => $t->where('n_provider', 'like', $like)));
        }

        // 2 query total: providers + tiang (eager load)
        $rows = $q->with('tiang:id,provider_id,n_provider,odp,status_izin')
            ->select('id', 'kecamatan', 'kelurahan', 'kota', 'provinsi', 'utilitas', 'sijali', 'sijali_link', 'x', 'y', 'foto', 'tgl_survey')
            ->orderBy('id')->limit($limit + 1)->get();

        $points = $rows->take($limit)->map(fn(Provider $p) => [
            'id' => $p->id,
            'name' => $p->n_provider_label,
            'location' => collect([$p->kelurahan, $p->kecamatan, $p->kota, $p->provinsi])->filter()->implode(', '),
            'kecamatan' => $p->kecamatan,
            'kelurahan' => $p->kelurahan,
            'utilitas' => $p->utilitas,
            'odp' => $p->odp_label,
            'tiang' => $p->tiangList(),
            'sijali' => $p->sijali,
            'sijali_link' => $p->sijali_link,
            'surveyed_at' => $p->tgl_survey?->format('Y-m-d'),
            'coordinates' => ['lat' => (float) $p->y, 'lng' => (float) $p->x],
            'maps_url' => sprintf('https://www.google.com/maps?q=%s,%s', $p->y, $p->x),
            'foto_url' => $p->foto_url,
        ])->values();

        return response()->json(['points' => $points, 'truncated' => $rows->count() > $limit]);
    }
}
