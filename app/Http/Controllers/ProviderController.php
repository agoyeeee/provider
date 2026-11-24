<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\Tiang;
use App\Services\ProviderImportService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Inertia\Inertia;

class ProviderController extends Controller
{
    private const PREVIEW_LIMIT = 500;

    public function __construct(private ProviderImportService $import)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Provider::with(['user', 'tiang']);

        // Filter by kecamatan/kelurahan
        if ($request->filled('kecamatan')) {
            $query->where('kecamatan', $request->kecamatan);
        }
        if ($request->filled('kelurahan')) {
            $query->where('kelurahan', $request->kelurahan);
        }

        // Filter by nama jalan (sijali)
        if ($request->filled('sijali')) {
            $query->where('sijali', $request->sijali);
        }

        // Filter by provider name
        if ($request->filled('n_provider')) {
            $query->whereHas('tiang', fn($q) => $q->where('n_provider', $request->n_provider));
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('provinsi', 'like', '%' . $search . '%')
                    ->orWhere('kota', 'like', '%' . $search . '%')
                    ->orWhere('kecamatan', 'like', '%' . $search . '%')
                    ->orWhere('kelurahan', 'like', '%' . $search . '%')
                    ->orWhere('sijali', 'like', '%' . $search . '%')
                    ->orWhereHas('tiang', fn($t) => $t->where('n_provider', 'like', '%' . $search . '%'));
            });
        }

        $providers = $query->orderBy('created_at', 'desc')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'provinsi' => $item->provinsi,
                'kota' => $item->kota,
                'kecamatan' => $item->kecamatan,
                'kelurahan' => $item->kelurahan,
                'k_ref_wil' => $item->k_ref_wil,
                'utilitas' => $item->utilitas,
                'n_provider' => $item->n_provider_label,
                'odp' => $item->odp_label,
                'tiang' => $item->tiangList(),
                'jml_tiang' => $item->tiang->count(),
                'sijali' => $item->sijali,
                'sijali_link' => $item->sijali_link,
                'x' => $item->x,
                'y' => $item->y,
                'foto' => $item->foto,
                'foto_url' => $item->foto_url,
                'tgl_survey' => $item->tgl_survey?->format('Y-m-d'),
                'id_user' => $item->id_user,
                'user' => $item->user,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        $users = User::select('id', 'name')->get();

        // Get unique values for filter dropdowns
        $filterOptions = [
            'kecamatan' => Provider::distinct()->pluck('kecamatan')->filter()->values(),
            'kelurahan' => Provider::distinct()->pluck('kelurahan')->filter()->values(),
            'sijali' => Provider::distinct()->whereNotNull('sijali')->pluck('sijali')->filter()->values(),
            'n_provider' => Tiang::distinct()->orderBy('n_provider')->pluck('n_provider')->values(),
        ];

        return Inertia::render('Admin/ProviderView', [
            'providers' => $providers,
            'users' => $users,
            'filterOptions' => $filterOptions,
            'filters' => $request->only(['kelurahan', 'kecamatan', 'sijali', 'n_provider', 'search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kecamatan' => 'required|string|max:255',
            'kelurahan' => 'required|string|max:255',
            'utilitas' => 'nullable|string|max:255',
            'tiang' => 'required|array|min:1',
            'tiang.*.n_provider' => 'required|string|max:255|distinct:ignore_case',
            'tiang.*.odp' => 'nullable|boolean',
            'tiang.*.status_izin' => 'nullable|in:' . implode(',', Tiang::STATUS_IZIN),
            'sijali' => 'nullable|string|max:255',
            'x' => 'nullable|numeric',
            'y' => 'nullable|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tgl_survey' => 'nullable|date',
        ]);

        $data = $request->only([
            'kecamatan',
            'kelurahan',
            'utilitas',
            'sijali',
            'x',
            'y',
            'tgl_survey',
        ]);

        // Wilayah tetap (hanya Surakarta); pemilik data = user yang login
        $data += ['provinsi' => 'Jawa Tengah', 'kota' => 'Surakarta', 'id_user' => $request->user()?->id];

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('provider_photos', 'public');
        }

        DB::transaction(function () use ($data, $request) {
            $provider = Provider::create($data);
            $this->syncTiang($provider, $request->input('tiang', []));
        });

        return redirect()->route('admin.provider.index')
            ->with('success', 'Data Provider berhasil ditambahkan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $provider = Provider::findOrFail($id);

        $request->validate([
            'kecamatan' => 'required|string|max:255',
            'kelurahan' => 'required|string|max:255',
            'utilitas' => 'nullable|string|max:255',
            'tiang' => 'required|array|min:1',
            'tiang.*.n_provider' => 'required|string|max:255|distinct:ignore_case',
            'tiang.*.odp' => 'nullable|boolean',
            'tiang.*.status_izin' => 'nullable|in:' . implode(',', Tiang::STATUS_IZIN),
            'sijali' => 'nullable|string|max:255',
            'x' => 'nullable|numeric',
            'y' => 'nullable|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tgl_survey' => 'nullable|date',
        ]);

        $data = $request->only([
            'kecamatan',
            'kelurahan',
            'utilitas',
            'sijali',
            'x',
            'y',
            'tgl_survey',
        ]);

        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($provider->foto) {
                Storage::disk('public')->delete($provider->foto);
            }
            $data['foto'] = $request->file('foto')->store('provider_photos', 'public');
        }

        DB::transaction(function () use ($provider, $data, $request) {
            $provider->update($data);
            $this->syncTiang($provider, $request->input('tiang', []));
        });

        return redirect()->route('admin.provider.index')
            ->with('success', 'Data Provider berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $provider = Provider::findOrFail($id);

        // Delete photo if exists
        if ($provider->foto) {
            Storage::disk('public')->delete($provider->foto);
        }

        $provider->delete();

        return redirect()->route('admin.provider.index')
            ->with('success', 'Data Provider berhasil dihapus');
    }

    /**
     * Export data to Excel/CSV
     */
    public function export()
    {
        $book = $this->import->export(Provider::with('tiang')->orderBy('id')->get());

        return response()->streamDownload(function () use ($book) {
            (new Xlsx($book))->save('php://output');
        }, 'data_provider_' . now()->format('Ymd_His') . '.xlsx');
    }

    /**
     * Download template import (sheet Data + Petunjuk).
     */
    public function importTemplate()
    {
        $book = $this->import->template();

        return response()->streamDownload(function () use ($book) {
            (new Xlsx($book))->save('php://output');
        }, 'template_import_provider.xlsx');
    }

    /**
     * Langkah 1: baca + periksa file, belum menyimpan data. File ditahan di storage sampai commit.
     */
    public function importPreview(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:20480']);

        $token = (string) Str::uuid();
        $path = $request->file('file')->storeAs('provider_imports', $token . '.xlsx', 'local');

        try {
            $analysis = $this->import->analyze(Storage::disk('local')->path($path));
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            return response()->json(['message' => $e instanceof \InvalidArgumentException ? $e->getMessage() : 'File tidak bisa dibaca: ' . $e->getMessage()], 422);
        }

        // Kirim baris bermasalah lebih dulu; batasi agar respons tetap ringan
        $order = ['error' => 0, 'duplikat' => 1, 'timpa' => 2, 'baru' => 3];
        $rows = collect($analysis['rows'])
            ->sortBy(fn($r) => $order[$r['status']])
            ->take(self::PREVIEW_LIMIT)
            ->map(fn($r) => Arr::only($r, ['row', 'status', 'label', 'messages']))
            ->values();

        return response()->json([
            'token' => $token,
            'summary' => $analysis['summary'],
            'rows' => $rows,
            'truncated' => $analysis['summary']['total'] > $rows->count(),
        ]);
    }

    /**
     * Langkah 2: simpan baris valid. Baris yang akan menimpa hanya disimpan jika allow_overwrite.
     */
    public function importCommit(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|uuid',
            'allow_overwrite' => 'boolean',
        ]);

        $path = Storage::disk('local')->path('provider_imports/' . $data['token'] . '.xlsx');
        if (!file_exists($path)) {
            return response()->json(['message' => 'Sesi import kedaluwarsa. Upload ulang file.'], 422);
        }

        $result = $this->import->commit($path, (bool) ($data['allow_overwrite'] ?? false), $request->user()?->id);
        Storage::disk('local')->delete('provider_imports/' . $data['token'] . '.xlsx');

        return response()->json($result);
    }

    /**
     * Unduh baris error/duplikat dari file yang sudah dipreview.
     */
    public function importErrors(string $token)
    {
        abort_unless(Str::isUuid($token), 404);
        $path = Storage::disk('local')->path('provider_imports/' . $token . '.xlsx');
        abort_unless(file_exists($path), 404);

        $book = $this->import->errorReport($this->import->analyze($path));

        return response()->streamDownload(function () use ($book) {
            (new Xlsx($book))->save('php://output');
        }, 'import_provider_perlu_diperbaiki.xlsx');
    }

    /**
     * Ganti daftar tiang titik dengan input form. status_izin lama dipertahankan per provider.
     */
    private function syncTiang(Provider $provider, array $rows): void
    {
        $names = collect($rows)->pluck('n_provider')->map(fn($n) => trim($n));
        $provider->tiang()->whereNotIn('n_provider', $names)->delete();

        foreach ($rows as $row) {
            $provider->tiang()->updateOrCreate(
                ['n_provider' => trim($row['n_provider'])],
                ['odp' => filter_var($row['odp'] ?? false, FILTER_VALIDATE_BOOLEAN), 'status_izin' => $row['status_izin'] ?: null]
            );
        }
    }
}
