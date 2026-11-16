<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\Tiang;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Inertia\Inertia;

class ProviderController extends Controller
{
    /**
     * Parse coordinate value that may be in Indonesian format (dot as thousands separator).
     * Examples:
     * - "1.108.347.869" → 108.347869 (Indonesian format with leading "1.")
     * - "108.347869" → 108.347869 (standard format)
     * - "-7.532.665.349" → -7.532665349 (negative with Indonesian format)
     * - 108.347869 → 108.347869 (already a float)
     */
    private function parseCoordinate($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        // If it's already a proper float, return it
        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);

        // Check if the value uses Indonesian format (multiple dots as thousands separator)
        // Indonesian format: "1.108.347.869" means "1108347869" which should be "108.347869"
        $dotCount = substr_count($value, '.');

        if ($dotCount >= 2) {
            // Handle negative values
            $isNegative = str_starts_with($value, '-');
            $cleanValue = ltrim($value, '-');

            // Remove all dots (thousands separators) and treat as integer-like string
            $withoutDots = str_replace('.', '', $cleanValue);

            // For Indonesian coordinates:
            // Longitude (x): typically 95-141 degrees (3 digits before decimal)
            // Latitude (y): typically -11 to 6 degrees (1-2 digits before decimal)

            // Determine where to place the decimal point based on expected coordinate range
            $length = strlen($withoutDots);

            if ($isNegative) {
                // Latitude (negative values for Indonesia, typically -1 to -11)
                // The format "-7.532.665.349" should become "-7.532665349"
                // First part before first dot is the integer part
                $parts = explode('.', $cleanValue);
                $integerPart = $parts[0];
                $decimalParts = array_slice($parts, 1);
                $decimalPart = implode('', $decimalParts);
                $result = (float) ($integerPart . '.' . $decimalPart);
                return -$result;
            } else {
                // Longitude (positive values for Indonesia, typically 95-141)
                // The format "1.108.347.869" should become "108.347869"
                // Check if first part is "1" (common prefix issue)
                $parts = explode('.', $cleanValue);

                if ($parts[0] === '1' && count($parts) >= 2) {
                    // Skip the leading "1." and reconstruct
                    // "1.108.347.869" → integer part is "108", decimal is "347869"
                    $integerPart = $parts[1];
                    $decimalParts = array_slice($parts, 2);
                    $decimalPart = implode('', $decimalParts);
                    return (float) ($integerPart . '.' . $decimalPart);
                } else {
                    // Standard case: first part is integer, rest is decimal
                    $integerPart = $parts[0];
                    $decimalParts = array_slice($parts, 1);
                    $decimalPart = implode('', $decimalParts);
                    return (float) ($integerPart . '.' . $decimalPart);
                }
            }
        }

        // Standard format with single dot as decimal separator
        return (float) $value;
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
    public function export(Request $request)
    {
        $query = Provider::with(['user', 'tiang']);

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
            $query->whereHas('tiang', fn($q) => $q->where('n_provider', $request->n_provider));
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
            'jml_tiang' => 'jml_tiang',
        ];

        $processed = $created = $updated = 0;
        $failed = [];
        $userId = $request->user()?->id;
        $rowNum = 1; // header = baris 1

        foreach ($sheet as $row) {
            $rowNum++;
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

            // Tiang: satu provider = satu tiang. ODP dicocokkan per nama provider.
            $names = collect(explode(',', (string) ($data['n_provider'] ?? '')))
                ->map(fn($n) => trim($n))
                ->filter()
                ->unique(fn($n) => mb_strtolower($n))
                ->values();
            $odpMap = $this->parseOdp($data['odp'] ?? null);

            if ($names->isEmpty()) {
                $failed[] = "Baris {$rowNum}: N_PROVIDER kosong";
                continue;
            }
            if (!empty($data['odp'])) {
                $unmatched = $names->first(fn($n) => !array_key_exists(mb_strtolower($n), $odpMap));
                if ($unmatched !== null) {
                    $failed[] = "Baris {$rowNum}: ODP untuk '{$unmatched}' tidak ditemukan";
                    continue;
                }
            }
            if (isset($data['jml_tiang']) && $data['jml_tiang'] !== '' && (int) $data['jml_tiang'] !== $names->count()) {
                $failed[] = "Baris {$rowNum}: JML_TIANG {$data['jml_tiang']} != jumlah provider {$names->count()}";
                continue;
            }

            $payload = [
                'provinsi' => $data['provinsi'] ?? null,
                'kota' => $data['kota'] ?? null,
                'kecamatan' => $data['kecamatan'] ?? null,
                'kelurahan' => $data['kelurahan'] ?? null,
                'k_ref_wil' => $data['k_ref_wil'] ?? null,
                'utilitas' => $data['utilitas'] ?? null,
                'sijali' => $data['sijali'] ?? null,
                'sijali_link' => $data['sijali_link'] ?? null,
                'x' => $this->parseCoordinate($data['x'] ?? null),
                'y' => $this->parseCoordinate($data['y'] ?? null),
                'foto' => $data['foto'] ?? null,
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
            $payload = array_map(fn($v) => $v === '' ? null : $v, $payload);

            DB::transaction(function () use ($payload, $names, $odpMap, &$created, &$updated) {
                // Titik yang sama = koordinat yang sama
                $existing = ($payload['x'] !== null && $payload['y'] !== null)
                    ? Provider::where('x', number_format($payload['x'], 10, '.', ''))
                        ->where('y', number_format($payload['y'], 10, '.', ''))
                        ->first()
                    : null;

                if ($existing) {
                    // merge without wiping existing values when the new cell is empty/null
                    $existing->update(array_filter($payload, fn($v) => $v !== null));
                    $provider = $existing;
                    $updated++;
                } else {
                    $provider = Provider::create($payload);
                    $created++;
                }

                foreach ($names as $name) {
                    // updateOrCreate: status_izin yang sudah diisi manual tidak ikut tertimpa
                    $provider->tiang()->updateOrCreate(
                        ['n_provider' => $name],
                        ['odp' => $odpMap[mb_strtolower($name)] ?? false]
                    );
                }
            });

            $processed++;
        }

        if ($failed) {
            Log::warning('Import provider: baris gagal', $failed);
        }

        $message = "Import selesai. Diproses: {$processed}, ditambahkan: {$created}, diperbarui: {$updated}, gagal: " . count($failed);
        if ($failed) {
            $message .= '. ' . implode('; ', array_slice($failed, 0, 5)) . (count($failed) > 5 ? '; ...' : '');
        }

        return redirect()->route('admin.provider.index')->with('success', $message);
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

    /**
     * Parse "Link Net Ada, Telkom Tidak Ada" menjadi ['link net' => true, 'telkom' => false].
     */
    private function parseOdp(?string $odp): array
    {
        $map = [];
        foreach (explode(',', (string) $odp) as $part) {
            if (preg_match('/^(.+?)\s+(tidak\s+ada|ada)$/iu', trim($part), $m)) {
                $map[mb_strtolower(trim($m[1]))] = !str_starts_with(mb_strtolower($m[2]), 'tidak');
            }
        }
        return $map;
    }
}
