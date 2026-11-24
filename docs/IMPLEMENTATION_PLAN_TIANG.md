# Implementation Plan: Pemisahan Tiang per Provider

Status: rencana, belum diimplementasi.

## Latar belakang

Satu titik lokasi bisa memiliki beberapa tiang dari provider berbeda. Satu tiang hanya milik satu provider.

Struktur saat ini menyimpan semuanya dalam satu baris di tabel `providers`:

- `n_provider` berisi beberapa provider dalam satu string, contoh `Link Net, Telkom`.
- `odp` berisi status per provider dalam satu string, contoh `Link Net Ada, Telkom Tidak Ada`.

Akibatnya, filter dan hitung per provider harus memakai `LIKE`, dan tidak ada tempat untuk status izin per tiang.

Format data import yang baru:

```
NO | PROVINSI | KOTA | KECAMATAN | KELURAHAN | K_REF_WIL | UTILITAS | N_PROVIDER | ODP | SIJALI_21 | X | Y | FOTO | TGL_SURVEY | JML_TIANG
```

## Tujuan

1. Pisahkan titik lokasi dan tiang menjadi dua tabel (relasi satu ke banyak).
2. Tambahkan informasi status izin per tiang: berizin / tidak berizin.
3. Pindahkan data lama ke struktur baru tanpa menghapus apa pun pada tahap awal.

## Struktur data target

### `providers` (titik lokasi)

Tetap: `id`, `provinsi`, `kota`, `kecamatan`, `kelurahan`, `k_ref_wil`, `utilitas`, `sijali`, `sijali_link`, `x`, `y`, `foto`, `tgl_survey`, `id_user`, `created_at`, `updated_at`.

Kolom `n_provider` dan `odp` dipertahankan selama masa transisi, lalu dihapus di Fase 4.

`jml_tiang` tidak disimpan. Nilainya dihitung dengan `withCount('tiang')`.

`foto` tetap satu kode per titik. Semua tiang di titik yang sama memakai foto yang sama.

### `tiang` (tabel baru)

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint, PK | |
| `provider_id` | bigint, FK ke `providers.id` | `cascadeOnDelete`, di-index |
| `n_provider` | string | satu provider per tiang |
| `odp` | boolean | ODP ada atau tidak |
| `status_izin` | string, nullable | `berizin` atau `tidak_berizin` |
| `created_at`, `updated_at` | timestamp | |

## Fase

### Fase 0: Backup

- Backup tabel `providers` sebelum perubahan apa pun.
- Database yang dipakai: MySQL.
- Contoh: `mysqldump -u <user> -p <db> providers > providers_backup.sql`
- Simpan file backup di luar server.

### Fase 1: Migration struktur

- Buat migration `create_tiang_table`.
- Buat model `App\Models\Tiang`.
- Tambahkan relasi `tiang()` (`hasMany`) di `Provider`, dan `provider()` (`belongsTo`) di `Tiang`.
- Tabel `providers` tidak diubah. Data lama tidak tersentuh.

### Fase 2: Salin data lama ke `tiang`

Command artisan: `php artisan tiang:backfill`. Hanya insert, tidak menghapus atau mengubah `providers`.

Aturan:

- Proses per chunk (`chunkById(1000)`). Ada sekitar 29.165 titik.
- Split `n_provider` dengan koma, lalu trim. Satu provider menjadi satu baris `tiang`.
- Parse `odp`: cari nama provider diikuti `Ada` atau `Tidak Ada`. Jika hanya satu provider dan formatnya berbeda, pakai fallback.
- `status_izin` diisi `null` karena data lama belum punya informasi ini.
- Lewati titik yang sudah punya baris `tiang`, supaya command aman dijalankan ulang.
- Baris yang gagal di-parse dicatat di log. Tidak dipaksa masuk.

Verifikasi sebelum lanjut:

- Jumlah baris `tiang` sama dengan jumlah hasil split `n_provider`.
- Cek acak beberapa titik: isi `tiang` cocok dengan data lama.
- Tidak ada baris gagal di log, atau semuanya sudah ditangani.

### Fase 3: Aplikasi memakai `tiang`

File yang saat ini menyentuh `n_provider` atau `odp`:

- `app/Models/Provider.php`
- `app/Http/Controllers/ProviderController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/LandingController.php`
- `database/seeders/ProviderSeeder.php`
- `resources/js/Pages/Admin/ProviderView.vue`
- `resources/js/Pages/Admin/DashboardView.vue`
- `resources/js/Pages/Landing/Map.vue`
- `resources/js/Pages/Landing/Home.vue`

Perubahan:

- Query dan filter memakai `whereHas('tiang', ...)` atau join, bukan `LIKE`.
- Popup peta menampilkan daftar tiang: provider, status ODP, status izin.
- Warna pin: oranye jika ada minimal satu tiang dengan ODP "Tidak Ada" (aturan ini perlu dikonfirmasi).
- Tabel dan donut chart membaca dari `tiang`.
- Import Excel: satu baris menjadi satu titik ditambah N tiang. `JML_TIANG` dipakai untuk validasi terhadap jumlah provider hasil split. `status_izin` diisi manual atau dari kolom baru jika ada.
- Form tambah dan ubah: input daftar tiang per titik.
- Seeder disesuaikan.

Detail Fase 3 ini masih perkiraan. File di atas belum dibaca isinya.

### Fase 4: Pembersihan (terpisah, nanti)

- Dijalankan setelah aplikasi stabil memakai `tiang` selama beberapa hari.
- Buat migration yang menghapus `n_provider` dan `odp` dari `providers`.
- Ini satu-satunya langkah yang tidak bisa dibatalkan tanpa backup. Lakukan backup penuh sebelum menjalankannya.

## Pertanyaan terbuka

1. Apakah `status_izin` ada di data Excel, atau diisi manual di aplikasi? Kolomnya tidak ada di contoh import.
2. Apakah `utilitas` selalu satu nilai per titik? Jika satu titik bisa campur jenis utilitas, kolom ini pindah ke `tiang`.
3. Apakah foto satu per titik atau satu per tiang? Rencana ini mengasumsikan satu per titik.
4. Aturan warna pin di peta untuk titik dengan tiang campuran (sebagian ODP ada, sebagian tidak).
5. Apakah ada format `odp` lain di data lama selain `<provider> Ada` / `<provider> Tidak Ada`? Perlu dicek di data asli sebelum Fase 2.
