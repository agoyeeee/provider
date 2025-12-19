# Dokumentasi Perubahan Struktur Database: Status Verifikasi

## 📋 Ringkasan Perubahan

Perubahan ini memindahkan `status_verifikasi` dari tabel `umkm` ke tabel `dokumen` untuk memberikan kontrol verifikasi yang lebih detail per dokumen. **Tabel `verifikasi` dihapus** karena fungsinya sudah digantikan oleh kolom-kolom di tabel `dokumen`.

## 🔄 Perubahan Database

### 1. Tabel `umkm`
**SEBELUM:**
```sql
status_verifikasi ENUM('pending', 'verified', 'rejected') DEFAULT 'pending'
```

**SESUDAH:**
```sql
status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'
```

**Penjelasan:**
- Kolom `status_verifikasi` diganti menjadi `status`
- Nilai `verified` diganti menjadi `approved` untuk konsistensi
- Status ini sekarang mewakili status **umum** UMKM

### 2. Tabel `dokumen`
**SEBELUM:**
```sql
status_dokumen ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'
```

**SESUDAH:**
```sql
status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'
catatan TEXT NULL
tanggal_verifikasi TIMESTAMP NULL
```

**Penjelasan:**
- Kolom `status_dokumen` diganti menjadi `status`
- Ditambahkan kolom `catatan` untuk alasan revisi/penolakan
- Ditambahkan kolom `tanggal_verifikasi` untuk tracking kapan dokumen diverifikasi

## 📝 File yang Diubah

### Migrations:
1. ✅ `2024_01_01_000003_create_umkm_table.php`
   - Ubah `status_verifikasi` → `status`
   - Ubah enum: `'verified'` → `'approved'`

2. ✅ `2024_01_01_000008_create_dokumen_table.php`
   - Ubah `status_dokumen` → `status`
   - Tambah `catatan TEXT NULL`
   - Tambah `tanggal_verifikasi TIMESTAMP NULL`

3. ✅ `2024_01_01_000007_create_verifikasi_table.php` (dibuat baru)

### Models:
1. ✅ `app/Models/UMKM.php`
   - Update `$fillable`: `'status_verifikasi'` → `'status'`

2. ✅ `app/Models/Dokumen.php`
   - Update `$fillable`: `'status_dokumen'` → `'status'`
   - Tambahkan: `'catatan'`, `'tanggal_verifikasi'`
   - Tambahkan `$casts` untuk `tanggal_verifikasi`

### Controllers:
1. ✅ `app/Http/Controllers/PemilikUmkmController.php`
   - `storeUmkm()`: `'status_verifikasi'` → `'status'`
   - `updateUmkm()`: `$umkm->status_verifikasi` → `$umkm->status`

2. ✅ `app/Http/Controllers/DashboardController.php`
   - Update semua query: `where('status_verifikasi', 'verified')` → `where('status', 'approved')`

3. ✅ `app/Http/Controllers/UMKMController.php`
   - `index()`: `'status_verifikasi'` → `'status'`
   - `store()`: `'status_verifikasi'` → `'status'`
   - `updateStatus()`: Validasi enum & field diupdate

4. ✅ `app/Http/Controllers/LandingController.php`
   - Update semua query: `where('status_verifikasi', 'verified')` → `where('status', 'approved')`

### Seeders:
1. ✅ `database/seeders/UMKMSeeder.php`
   - `'status_verifikasi'` → `'status'`
   - `'verified'` → `'approved'`

2. ✅ `database/seeders/DokumenSeeder.php`
   - `'status_dokumen'` → `'status'`
   - Tambahkan `'catatan'` dan `'tanggal_verifikasi'`
   - Fix: `$umkmId = DB::table('umkm')->first()->id` (bukan `->id_umkm`)

## 🎯 Status Values

### Tabel `umkm.status`:
- `pending` - UMKM baru didaftarkan, menunggu review
- `approved` - UMKM sudah disetujui dan aktif
- `rejected` - UMKM ditolak

### Tabel `dokumen.status`:
- `pending` - Dokumen belum diverifikasi
- `approved` - Dokumen valid dan diterima
- `rejected` - Dokumen ditolak (lihat kolom `catatan`)

## 🔧 Cara Menggunakan

### 1. Reset Database
```bash
php artisan migrate:fresh --seed
```

### 2. Update UMKM Status (Admin)
```php
$umkm = UMKM::find(1);
$umkm->status = 'approved';
$umkm->save();
```

### 3. Verifikasi Dokumen (Admin)
```php
$dokumen = Dokumen::find(1);
$dokumen->status = 'approved';
$dokumen->catatan = 'Dokumen lengkap dan sesuai';
$dokumen->tanggal_verifikasi = now();
$dokumen->save();
```

### 4. Reject Dokumen dengan Catatan
```php
$dokumen = Dokumen::find(2);
$dokumen->status = 'rejected';
$dokumen->catatan = 'NIK tidak sesuai dengan KTP. Mohon upload ulang dokumen yang benar.';
$dokumen->tanggal_verifikasi = now();
$dokumen->save();
```

### 5. Query UMKM yang Approved
```php
// Landing page - hanya tampilkan UMKM approved
$umkmList = UMKM::where('status', 'approved')
    ->with(['kategori', 'subKategori', 'produk'])
    ->get();
```

### 6. Check Status Dokumen UMKM
```php
$umkm = UMKM::with('dokumenUmkm')->find(1);

foreach ($umkm->dokumenUmkm as $dokumen) {
    echo "{$dokumen->jenis}: {$dokumen->status}";
    if ($dokumen->catatan) {
        echo " - Catatan: {$dokumen->catatan}";
    }
}
```

## 📊 Contoh Use Case

### Workflow Verifikasi UMKM:

1. **Pemilik UMKM** mendaftar → `umkm.status = 'pending'`
2. **Pemilik UMKM** upload dokumen → `dokumen.status = 'pending'`
3. **Admin** review dokumen:
   - Jika valid → `dokumen.status = 'approved'`, `tanggal_verifikasi = now()`
   - Jika tidak valid → `dokumen.status = 'rejected'`, `catatan = 'alasan penolakan'`
4. Jika **semua dokumen** approved → Admin bisa ubah `umkm.status = 'approved'`
5. UMKM yang `status = 'approved'` akan muncul di landing page

## ⚠️ Breaking Changes

- Frontend yang menggunakan `status_verifikasi` harus diupdate ke `status`
- Semua query yang menggunakan `'verified'` harus diupdate ke `'approved'`
- API response yang mengirim field `status_verifikasi` atau `status_dokumen` perlu disesuaikan

## ✅ Hasil Akhir

Database sekarang sudah ter-update dengan struktur baru:
- ✅ Tabel `umkm` menggunakan kolom `status`
- ✅ Tabel `dokumen` punya `status`, `catatan`, dan `tanggal_verifikasi`
- ✅ Semua controller sudah diupdate
- ✅ Semua seeder sudah diupdate
- ✅ Database sudah di-migrate dan di-seed dengan data contoh

Tanggal Perubahan: 8 Oktober 2025
