<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\Tiang;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Import Excel data provider dengan alur preview -> commit.
 * analyze() hanya membaca dan memeriksa (tidak menulis); commit() memakai hasil analyze() terbaru.
 */
class ProviderImportService
{
    public const STATUS_BARU = 'baru';
    public const STATUS_TIMPA = 'timpa';
    public const STATUS_DUPLIKAT = 'duplikat';
    public const STATUS_ERROR = 'error';

    public const HEADERS = [
        'PROVINSI', 'KOTA', 'KECAMATAN', 'KELURAHAN', 'K_REF_WIL', 'UTILITAS',
        'N_PROVIDER', 'ODP', 'STATUS_IZIN', 'SIJALI', 'SIJALI_LINK',
        'X', 'Y', 'FOTO', 'TGL_SURVEY', 'JML_TIANG',
    ];

    private const REQUIRED_FIELDS = ['kecamatan', 'kelurahan', 'n_provider', 'odp', 'x', 'y'];

    private const FIELD_MAP = [
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
        'status_izin' => 'status_izin',
        'nama_sijali' => 'sijali',
        'nama_sijali_21' => 'sijali',
        'sijali_21' => 'sijali',
        'sijali' => 'sijali',
        'sijali_link' => 'sijali_link',
        'link_sijali' => 'sijali_link',
        'url_sijali' => 'sijali_link',
        'url_detail_sijali' => 'sijali_link',
        'x' => 'x',
        'lon' => 'x',
        'longitude' => 'x',
        'koordinat_x' => 'x',
        'y' => 'y',
        'lat' => 'y',
        'latitude' => 'y',
        'koordinat_y' => 'y',
        'tgl_survey' => 'tgl_survey',
        'tanggal_survey' => 'tgl_survey',
        'kode_foto' => 'foto',
        'foto' => 'foto',
        'jml_tiang' => 'jml_tiang',
    ];

    /**
     * Baca + periksa semua baris. Tidak menulis ke database.
     *
     * @return array{rows: array, summary: array, headers: array}
     * @throws \InvalidArgumentException jika file/kolom wajib tidak valid
     */
    public function analyze(string $path): array
    {
        $sheet = IOFactory::load($path)->getActiveSheet()->toArray(null, true, true, true);
        if (count($sheet) < 2) {
            throw new \InvalidArgumentException('File kosong atau hanya berisi header.');
        }

        $headerRow = array_shift($sheet);
        $headerNames = array_values(array_map(fn($h) => trim((string) $h), $headerRow));
        $fieldByIndex = [];
        foreach ($headerNames as $i => $h) {
            $fieldByIndex[$i] = self::FIELD_MAP[$this->normalizeHeader($h)] ?? null;
        }

        $missing = array_diff(self::REQUIRED_FIELDS, $fieldByIndex);
        if ($missing) {
            throw new \InvalidArgumentException('Kolom wajib tidak ditemukan: ' . strtoupper(implode(', ', $missing)) . '. Gunakan template.');
        }

        // Titik yang sudah ada di database, dikunci koordinat (6 desimal ~ 11 cm)
        $existing = Provider::select('id', 'x', 'y')->get()
            ->mapWithKeys(fn($p) => [$this->coordKey($p->x, $p->y) => $p->id]);

        $rows = [];
        $seen = [];
        $rowNum = 1;
        foreach ($sheet as $rawRow) {
            $rowNum++;
            $values = array_values($rawRow);
            if (!array_filter($values, fn($v) => $v !== null && trim((string) $v) !== '')) {
                continue;
            }

            $data = [];
            foreach ($values as $i => $v) {
                if ($f = $fieldByIndex[$i] ?? null) {
                    $data[$f] = is_string($v) ? trim($v) : $v;
                }
            }

            $row = $this->analyzeRow($data, $rowNum);
            $row['raw'] = $values;

            if ($row['status'] !== self::STATUS_ERROR) {
                $key = $this->coordKey($row['payload']['x'], $row['payload']['y']);
                if (isset($seen[$key])) {
                    $row['status'] = self::STATUS_DUPLIKAT;
                    $row['messages'][] = "Koordinat sama dengan baris {$seen[$key]} di file ini";
                } else {
                    $seen[$key] = $rowNum;
                    if (isset($existing[$key])) {
                        $row['status'] = self::STATUS_TIMPA;
                        $row['provider_id'] = $existing[$key];
                    }
                }
            }
            $rows[] = $row;
        }

        $this->markExtraTiang($rows);

        $summary = ['total' => count($rows), 'baru' => 0, 'timpa' => 0, 'duplikat' => 0, 'error' => 0];
        foreach ($rows as $r) {
            $summary[$r['status']]++;
        }

        return ['rows' => $rows, 'summary' => $summary, 'headers' => $headerNames];
    }

    /**
     * Simpan baris valid. Baris 'timpa' hanya disimpan jika $allowOverwrite.
     *
     * @return array{created: int, updated: int, skipped: int}
     */
    public function commit(string $path, bool $allowOverwrite, ?int $userId): array
    {
        $created = $updated = $skipped = 0;

        foreach ($this->analyze($path)['rows'] as $row) {
            $status = $row['status'];
            if ($status === self::STATUS_BARU || ($status === self::STATUS_TIMPA && $allowOverwrite)) {
                DB::transaction(function () use ($row, $userId, &$created, &$updated) {
                    $payload = $row['payload'] + ['id_user' => $userId];
                    $provider = $row['provider_id'] ? Provider::find($row['provider_id']) : null;

                    if ($provider) {
                        // kolom kosong di Excel tidak menghapus nilai lama
                        $provider->update(array_filter($payload, fn($v) => $v !== null));
                        $updated++;
                    } else {
                        $provider = Provider::create($payload);
                        $created++;
                    }

                    foreach ($row['tiang'] as $t) {
                        $attrs = ['odp' => $t['odp']];
                        // status_izin hanya berubah jika file mengisinya
                        if ($t['status_izin'] !== null) {
                            $attrs['status_izin'] = $t['status_izin'];
                        }
                        $provider->tiang()->updateOrCreate(['n_provider' => $t['n_provider']], $attrs);
                    }
                });
            } else {
                $skipped++;
            }
        }

        return compact('created', 'updated', 'skipped');
    }

    /**
     * Template: sheet "Data" hanya header; sheet "Petunjuk" berisi contoh + aturan.
     */
    public function template(): Spreadsheet
    {
        $book = new Spreadsheet();

        $data = $book->getActiveSheet()->setTitle('Data');
        $data->fromArray(self::HEADERS, null, 'A1');
        $data->getStyle('A1:P1')->getFont()->setBold(true);
        foreach (range('A', 'P') as $col) {
            $data->getColumnDimension($col)->setAutoSize(true);
        }

        $help = $book->createSheet()->setTitle('Petunjuk');
        $help->fromArray(self::HEADERS, null, 'A1');
        $help->fromArray([
            'Jawa Tengah', 'Surakarta', 'Jebres', 'Kepatihan Kulon', '3372041001', 'Tiang Provider',
            'Link Net, Telkom', 'Link Net Ada, Telkom Tidak Ada', 'Link Net Berizin, Telkom Tidak Berizin',
            'Kemasan 3', '', '110.8298474', '-7.5617317968', 'F001', '12-10-2026', '2',
        ], null, 'A2');
        $help->getStyle('A1:P1')->getFont()->setBold(true);

        $help->fromArray([
            ['Aturan'],
            ['Isi data di sheet "Data". Baris 2 di sheet ini hanyalah contoh dan tidak ikut diimport.'],
            ['Wajib: KECAMATAN, KELURAHAN, N_PROVIDER, ODP, X, Y.'],
            ['N_PROVIDER: pisahkan provider dengan koma. Satu provider = satu tiang.'],
            ['ODP: "<provider> Ada" atau "<provider> Tidak Ada" untuk SETIAP provider di N_PROVIDER.'],
            ['STATUS_IZIN (opsional): "<provider> Berizin" atau "<provider> Tidak Berizin". Kosong = status lama tidak diubah.'],
            ['X = longitude (95-141), Y = latitude (-11 sampai 6). Titik dianggap sama jika X dan Y sama (6 desimal).'],
            ['JML_TIANG (opsional): jika diisi harus sama dengan jumlah provider di N_PROVIDER.'],
            ['PROVINSI/KOTA kosong = Jawa Tengah / Surakarta.'],
        ], null, 'A4');
        $help->getStyle('A4')->getFont()->setBold(true);

        $book->setActiveSheetIndex(0);
        return $book;
    }

    /**
     * Spreadsheet berisi baris bermasalah (status error / duplikat) untuk diperbaiki lalu diupload ulang.
     */
    public function errorReport(array $analysis): Spreadsheet
    {
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet()->setTitle('Perlu diperbaiki');
        $sheet->fromArray(array_merge(['BARIS', 'STATUS', 'PESAN'], $analysis['headers']), null, 'A1');
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        $r = 2;
        foreach ($analysis['rows'] as $row) {
            if (!in_array($row['status'], [self::STATUS_ERROR, self::STATUS_DUPLIKAT], true)) {
                continue;
            }
            $sheet->fromArray(
                array_merge([$row['row'], $row['status'], implode('; ', $row['messages'])], $row['raw']),
                null,
                'A' . $r++
            );
        }
        return $book;
    }

    // ---------------------------------------------------------------------

    private function analyzeRow(array $data, int $rowNum): array
    {
        $errors = [];

        foreach (['kecamatan', 'kelurahan', 'n_provider', 'odp'] as $f) {
            if (($data[$f] ?? '') === '' || $data[$f] === null) {
                $errors[] = strtoupper($f) . ' kosong';
            }
        }

        // Koordinat
        $x = $this->coordinate($data['x'] ?? null, 'X', $errors);
        $y = $this->coordinate($data['y'] ?? null, 'Y', $errors);
        if ($x !== null && ($x < 95 || $x > 141)) {
            $errors[] = "X {$x} di luar Indonesia (X/Y tertukar?)";
        }
        if ($y !== null && ($y < -11 || $y > 6)) {
            $errors[] = "Y {$y} di luar Indonesia (X/Y tertukar?)";
        }

        // Tanggal
        $tgl = null;
        if (!empty($data['tgl_survey'])) {
            try {
                $tgl = is_numeric($data['tgl_survey'])
                    ? XlsDate::excelToDateTimeObject($data['tgl_survey'])->format('Y-m-d')
                    : Carbon::parse($data['tgl_survey'])->format('Y-m-d');
            } catch (\Exception $e) {
                $errors[] = "TGL_SURVEY '{$data['tgl_survey']}' tidak valid";
            }
        }

        // Tiang: satu provider = satu tiang
        $names = collect(explode(',', (string) ($data['n_provider'] ?? '')))
            ->map(fn($n) => trim($n))->filter()
            ->unique(fn($n) => mb_strtolower($n))->values();

        [$odpMap, $odpBad] = $this->parseFlags($data['odp'] ?? null, 'tidak\s+ada|ada');
        [$izinMap, $izinBad] = $this->parseFlags($data['status_izin'] ?? null, 'tidak\s+berizin|berizin');
        $lower = $names->map(fn($n) => mb_strtolower($n));

        foreach ($odpBad as $bad) {
            $errors[] = "Format ODP '{$bad}' tidak dikenali (pakai '<provider> Ada' / '<provider> Tidak Ada')";
        }
        foreach ($izinBad as $bad) {
            $errors[] = "Format STATUS_IZIN '{$bad}' tidak dikenali (pakai '<provider> Berizin' / '<provider> Tidak Berizin')";
        }
        foreach (array_diff(array_keys($odpMap), $lower->all()) as $n) {
            $errors[] = "ODP menyebut '{$n}' yang tidak ada di N_PROVIDER";
        }
        foreach (array_diff(array_keys($izinMap), $lower->all()) as $n) {
            $errors[] = "STATUS_IZIN menyebut '{$n}' yang tidak ada di N_PROVIDER";
        }
        if (($data['odp'] ?? '') !== '') {
            foreach ($names as $n) {
                if (!isset($odpMap[mb_strtolower($n)])) {
                    $errors[] = "ODP untuk '{$n}' tidak ditemukan";
                }
            }
        }
        if (($data['jml_tiang'] ?? '') !== '' && (int) $data['jml_tiang'] !== $names->count()) {
            $errors[] = "JML_TIANG {$data['jml_tiang']} tidak sama dengan jumlah provider ({$names->count()})";
        }

        $tiang = $names->map(fn($n) => [
            'n_provider' => $n,
            'odp' => ($odpMap[mb_strtolower($n)] ?? 'tidak ada') === 'ada',
            'status_izin' => isset($izinMap[mb_strtolower($n)])
                ? ($izinMap[mb_strtolower($n)] === 'berizin' ? 'berizin' : 'tidak_berizin')
                : null,
        ])->all();

        $blank = fn($v) => ($v === '' || $v === null) ? null : $v;
        $payload = [
            'provinsi' => $blank($data['provinsi'] ?? null) ?? 'Jawa Tengah',
            'kota' => $blank($data['kota'] ?? null) ?? 'Surakarta',
            'kecamatan' => $blank($data['kecamatan'] ?? null),
            'kelurahan' => $blank($data['kelurahan'] ?? null),
            'k_ref_wil' => $blank($data['k_ref_wil'] ?? null),
            'utilitas' => $blank($data['utilitas'] ?? null),
            'sijali' => $blank($data['sijali'] ?? null),
            'sijali_link' => $blank($data['sijali_link'] ?? null),
            'x' => $x,
            'y' => $y,
            'foto' => $blank($data['foto'] ?? null),
            'tgl_survey' => $tgl,
        ];

        return [
            'row' => $rowNum,
            'status' => $errors ? self::STATUS_ERROR : self::STATUS_BARU,
            'messages' => $errors,
            'label' => trim(($payload['kelurahan'] ?? '-') . ' / ' . ($payload['sijali'] ?? '-')),
            'provider_id' => null,
            'payload' => $payload,
            'tiang' => $tiang,
        ];
    }

    /** Tandai tiang di database yang tidak ada di file (tidak dihapus). */
    private function markExtraTiang(array &$rows): void
    {
        $ids = collect($rows)->where('status', self::STATUS_TIMPA)->pluck('provider_id')->all();
        if (!$ids) {
            return;
        }
        $db = Tiang::whereIn('provider_id', $ids)->get()->groupBy('provider_id');

        foreach ($rows as &$row) {
            if ($row['status'] !== self::STATUS_TIMPA) {
                continue;
            }
            $inFile = collect($row['tiang'])->pluck('n_provider')->map(fn($n) => mb_strtolower($n));
            $extra = ($db[$row['provider_id']] ?? collect())
                ->pluck('n_provider')
                ->reject(fn($n) => $inFile->contains(mb_strtolower($n)))
                ->values()->all();

            $row['messages'][] = "Menimpa titik #{$row['provider_id']}";
            if ($extra) {
                $row['messages'][] = 'Tiang ada di database tapi tidak di file (tidak dihapus): ' . implode(', ', $extra);
            }
        }
    }

    /** "Link Net Ada, Telkom Tidak Ada" -> [['link net' => 'ada', 'telkom' => 'tidak ada'], [segmen tak dikenali]] */
    private function parseFlags(?string $value, string $alternatives): array
    {
        $map = $bad = [];
        foreach (explode(',', (string) $value) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            if (preg_match('/^(.+?)\s+(' . $alternatives . ')$/iu', $part, $m)) {
                $map[mb_strtolower(trim($m[1]))] = mb_strtolower(preg_replace('/\s+/', ' ', $m[2]));
            } else {
                $bad[] = $part;
            }
        }
        return [$map, $bad];
    }

    private function normalizeHeader(string $value): string
    {
        return strtolower(trim(str_replace([' ', '.', '-', '/'], '_', $value)));
    }

    private function coordKey($x, $y): string
    {
        return number_format((float) $x, 6, '.', '') . ',' . number_format((float) $y, 6, '.', '');
    }

    private function coordinate($raw, string $label, array &$errors): ?float
    {
        if ($raw === null || $raw === '') {
            $errors[] = "{$label} kosong";
            return null;
        }
        if (!is_numeric($raw) && !preg_match('/^-?[\d.]+$/', trim((string) $raw))) {
            $errors[] = "{$label} '{$raw}' bukan angka";
            return null;
        }
        return $this->parseCoordinate($raw);
    }

    /**
     * Koordinat format Indonesia (titik sebagai pemisah ribuan):
     * "1.108.347.869" -> 108.347869, "-7.532.665.349" -> -7.532665349, "108.347869" -> 108.347869.
     */
    private function parseCoordinate($value): ?float
    {
        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);
        if (substr_count($value, '.') < 2) {
            return (float) $value;
        }

        $negative = str_starts_with($value, '-');
        $parts = explode('.', ltrim($value, '-'));

        // awalan "1." pada longitude positif adalah sisa format ribuan
        if (!$negative && $parts[0] === '1' && count($parts) >= 2) {
            array_shift($parts);
        }

        $result = (float) (array_shift($parts) . '.' . implode('', $parts));
        return $negative ? -$result : $result;
    }
}
