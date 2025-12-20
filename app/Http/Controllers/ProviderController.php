<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Inertia\Inertia;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Provider::with('user');

        // Filter by kecamatan/kelurahan
        if ($request->filled('kecamatan')) {
            $query->where('kecamatan', $request->kecamatan);
        }
        if ($request->filled('kelurahan')) {
            $query->where('kelurahan', $request->kelurahan);
        }

        // Filter by provider name
        if ($request->filled('n_provider')) {
            $query->where('n_provider', 'like', '%' . $request->n_provider . '%');
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fid', 'like', '%' . $search . '%')
                    ->orWhere('provinsi', 'like', '%' . $search . '%')
                    ->orWhere('kota', 'like', '%' . $search . '%')
                    ->orWhere('kecamatan', 'like', '%' . $search . '%')
                    ->orWhere('kelurahan', 'like', '%' . $search . '%')
                    ->orWhere('n_provider', 'like', '%' . $search . '%')
                    ->orWhere('odp', 'like', '%' . $search . '%');
            });
        }

        $providers = $query->orderBy('created_at', 'desc')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'fid' => $item->fid,
                'provinsi' => $item->provinsi,
                'kota' => $item->kota,
                'kecamatan' => $item->kecamatan,
                'kelurahan' => $item->kelurahan,
                'k_ref_wil' => $item->k_ref_wil,
                'utilitas' => $item->utilitas,
                'n_provider' => $item->n_provider,
                'odp' => $item->odp,
                'sijali' => $item->sijali,
                'sijali_link' => $item->sijali_link,
                'x' => $item->x,
                'y' => $item->y,
                'foto' => $item->foto,
                'foto_url' => $item->foto_url,
                'tgl_survey' => $item->tgl_survey?->format('d-m-Y'),
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
            'n_provider' => Provider::distinct()->pluck('n_provider')->filter()->values(),
        ];

        return Inertia::render('Admin/ProviderView', [
            'providers' => $providers,
            'users' => $users,
            'filterOptions' => $filterOptions,
            'filters' => $request->only(['kelurahan', 'kecamatan', 'n_provider', 'search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fid' => 'nullable|string|max:255',
            'provinsi' => 'required|string|max:255',
            'kota' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'kelurahan' => 'required|string|max:255',
            'k_ref_wil' => 'nullable|string|max:255',
            'utilitas' => 'nullable|string|max:255',
            'n_provider' => 'required|string|max:255',
            'odp' => 'nullable|string|max:255',
            'sijali' => 'nullable|string|max:255',
            'sijali_link' => 'nullable|url|max:2048',
            'x' => 'nullable|numeric',
            'y' => 'nullable|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tgl_survey' => 'nullable|date',
            'id_user' => 'nullable|exists:users,id',
        ]);

        $data = $request->only([
            'fid',
            'provinsi',
            'kota',
            'kecamatan',
            'kelurahan',
            'k_ref_wil',
            'utilitas',
            'n_provider',
            'odp',
            'sijali',
            'sijali_link',
            'x',
            'y',
            'tgl_survey',
            'id_user',
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('provider_photos', 'public');
        }

        Provider::create($data);

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
            'fid' => 'nullable|string|max:255',
            'provinsi' => 'required|string|max:255',
            'kota' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'kelurahan' => 'required|string|max:255',
            'k_ref_wil' => 'nullable|string|max:255',
            'utilitas' => 'nullable|string|max:255',
            'n_provider' => 'required|string|max:255',
            'odp' => 'nullable|string|max:255',
            'sijali' => 'nullable|string|max:255',
            'sijali_link' => 'nullable|url|max:2048',
            'x' => 'nullable|numeric',
            'y' => 'nullable|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tgl_survey' => 'nullable|date',
            'id_user' => 'nullable|exists:users,id',
        ]);

        $data = $request->only([
            'fid',
            'provinsi',
            'kota',
            'kecamatan',
            'kelurahan',
            'k_ref_wil',
            'utilitas',
            'n_provider',
            'odp',
            'sijali',
            'sijali_link',
            'x',
            'y',
            'tgl_survey',
            'id_user',
        ]);

        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($provider->foto) {
                Storage::disk('public')->delete($provider->foto);
            }
            $data['foto'] = $request->file('foto')->store('provider_photos', 'public');
        }

        $provider->update($data);

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
    public function export(Request $request)
    {
        $query = Provider::with('user');

        // Apply same filters as index
        if ($request->filled('provinsi')) {
            $query->where('provinsi', $request->provinsi);
        }
        if ($request->filled('kota')) {
            $query->where('kota', $request->kota);
        }
        if ($request->filled('kecamatan')) {
            $query->where('kecamatan', $request->kecamatan);
        }
        if ($request->filled('n_provider')) {
            $query->where('n_provider', 'like', '%' . $request->n_provider . '%');
        }

        $providers = $query->orderBy('created_at', 'desc')->get();

        return response()->json($providers);
    }

    /**
     * Import providers from XLSX.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        if (!file_exists($path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        if (count($sheet) < 1) {
            return back()->with('error', 'Sheet kosong.');
        }

        $headerRow = array_shift($sheet);
        $normalize = static function ($value) {
            return strtolower(trim(str_replace([' ', '.', '-', '/'], '_', (string) $value)));
        };

        $headerMap = array_map($normalize, array_values($headerRow));

        // Handle merged "Koordinat" header without sublabels
        if (in_array('koordinat', $headerMap, true)) {
            foreach ($headerMap as $idx => $val) {
                if ($val === 'koordinat') {
                    // force next two columns to x/y if not explicitly labeled
                    $headerMap[$idx] = 'x';
                    $headerMap[$idx + 1] = $headerMap[$idx + 1] ?? 'y';
                    if (empty($headerMap[$idx + 1])) {
                        $headerMap[$idx + 1] = 'y';
                    }
                }
            }
        }

        // fallback header order if normalization fails
        if (!array_filter($headerMap, fn($h) => !empty($h))) {
            $headerMap = [
                'fid',
                'provinsi',
                'kota',
                'kecamatan',
                'kelurahan',
                'k_ref_wil',
                'utilitas',
                'n_provider',
                'odp',
                'sijali',
                'sijali_link',
                'x',
                'y',
                'tgl_survey',
            ];
        }

        $fieldMap = [
            'no' => 'fid',
            'fid' => 'fid',
            'provinsi' => 'provinsi',
            'kota' => 'kota',
            'kecamatan' => 'kecamatan',
            'kelurahan' => 'kelurahan',
            'kode_referensi_wilayah' => 'k_ref_wil',
            'k_ref_wil' => 'k_ref_wil',
            'utilitas' => 'utilitas',
            'nama_provider' => 'n_provider',
            'n_provider' => 'n_provider',
            'odp' => 'odp',
            'nama_sijali' => 'sijali',
            'nama_sijali_21' => 'sijali',
            'sijali_21' => 'sijali',
            'sijali' => 'sijali',
            'sijali_link' => 'sijali_link',
            'Sijali Link' => 'sijali_link',
            'link_sijali' => 'sijali_link',
            'url_sijali' => 'sijali_link',
            'url_detail_sijali' => 'sijali_link',
            'x' => 'x',
            'lon' => 'x',
            'longitude' => 'x',
            'y' => 'y',
            'lat' => 'y',
            'latitude' => 'y',
            'koordinat_x' => 'x',
            'koordinat_y' => 'y',
            'tgl_survey' => 'tgl_survey',
            'tanggal_survey' => 'tgl_survey',
            'kode_foto' => 'foto',
            'foto' => 'foto',
            'foto_url' => 'foto_url',
        ];

        $processed = $created = $updated = 0;
        $userId = $request->user()?->id;

        foreach ($sheet as $row) {
            if (!is_array($row) || !array_filter($row)) {
                continue;
            }

            $data = [];
            $values = array_values($row);
            foreach ($values as $index => $value) {
                $normalized = $headerMap[$index] ?? null;
                $field = $fieldMap[$normalized] ?? null;
                if (!$field) {
                    continue;
                }
                $data[$field] = is_string($value) ? trim($value) : $value;
            }

            $payload = [
                'fid' => $data['fid'] ?? null,
                'provinsi' => $data['provinsi'] ?? null,
                'kota' => $data['kota'] ?? null,
                'kecamatan' => $data['kecamatan'] ?? null,
                'kelurahan' => $data['kelurahan'] ?? null,
                'k_ref_wil' => $data['k_ref_wil'] ?? null,
                'utilitas' => $data['utilitas'] ?? null,
                'n_provider' => $data['n_provider'] ?? null,
                'odp' => $data['odp'] ?? null,
                'sijali' => $data['sijali'] ?? null,
                'sijali_link' => $data['sijali_link'] ?? null,
                'x' => isset($data['x']) && $data['x'] !== '' ? (float) $data['x'] : null,
                'y' => isset($data['y']) && $data['y'] !== '' ? (float) $data['y'] : null,
                'foto' => $data['foto'] ?? ($data['kode_foto'] ?? null),
                'foto_url' => $data['foto_url'] ?? null,
                'tgl_survey' => null,
                'id_user' => $userId,
            ];

            if (!empty($data['tgl_survey'])) {
                try {
                    if (is_numeric($data['tgl_survey'])) {
                        $payload['tgl_survey'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data['tgl_survey'])->format('Y-m-d');
                    } else {
                        $payload['tgl_survey'] = Carbon::parse($data['tgl_survey'])->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    $payload['tgl_survey'] = null;
                }
            }

            // remove empty strings
            $payload = array_map(function ($v) {
                return $v === '' ? null : $v;
            }, $payload);

            // determine if there is any meaningful data besides fid/id_user
            $dataForCheck = $payload;
            unset($dataForCheck['fid'], $dataForCheck['id_user']);
            $hasNewData = array_filter($dataForCheck, fn($v) => $v !== null && $v !== '') !== [];

            if (!empty($payload['fid'])) {
                $existing = Provider::where('fid', $payload['fid'])->first();
                if (!$hasNewData && !$existing) {
                    // skip empty rows that only carry numbering
                    continue;
                }
                if (!$hasNewData && $existing) {
                    // nothing to update
                    continue;
                }
                if ($existing) {
                    // merge without wiping existing values when the new cell is empty/null
                    $merged = $existing->only(array_keys($payload));
                    foreach ($payload as $key => $value) {
                        if ($value !== null && $value !== '') {
                            $merged[$key] = $value;
                        }
                    }
                    $existing->update($merged);
                    $updated++;
                } else {
                    Provider::create($payload);
                    $created++;
                }
            } else {
                Provider::create($payload);
                $created++;
            }

            $processed++;
        }

        return redirect()->route('admin.provider.index')
            ->with('success', "Import selesai. Diproses: {$processed}, ditambahkan: {$created}, diperbarui: {$updated}");
    }
}
