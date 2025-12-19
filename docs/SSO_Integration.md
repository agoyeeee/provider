# Integrasi SSO Sakti Karanganyar

Dokumentasi ini menjelaskan cara menggunakan fitur Single Sign-On (SSO) dengan sistem Sakti Karanganyar di aplikasi Laravel.

## Konfigurasi

### 1. Environment Variables

Tambahkan konfigurasi berikut ke file `.env`:

```bash
# Sakti SSO Configuration
SAKTI_SSO_CLIENT_ID=7bb718d2-abea-4f3e-865b-3b87b0be59b8
SAKTI_SSO_CLIENT_SECRET=VEoyEM5UCOy3ZpdCf8X3kG6piRTf7etKM68FLVkg
SAKTI_SSO_AUTHORIZE_URL=https://sakti.karanganyarkab.go.id/login/oauth/authorize
SAKTI_SSO_TOKEN_URL=https://sakti.karanganyarkab.go.id/login/oauth/access_token
SAKTI_SSO_API_URL_BASE=https://sakti.karanganyarkab.go.id/api/
SAKTI_SSO_REDIRECT_URI=http://localhost:8000/auth/sso/callback
```

**Catatan:** Pastikan `SAKTI_SSO_REDIRECT_URI` sesuai dengan URL aplikasi Anda.

### 2. Database Migration

Migration untuk menambahkan kolom `sso_id` ke tabel users sudah tersedia:

```bash
php artisan migrate
```

## Fitur yang Tersedia

### 1. Register dengan SSO
- Di halaman register (`/register`), terdapat tombol "Register with Sakti SSO"
- Tombol ini akan mengarahkan user ke sistem SSO Sakti
- Setelah berhasil login di Sakti, user akan otomatis didaftarkan di sistem

### 2. Login dengan SSO
- Di halaman login (`/login`), terdapat tombol "Login with Sakti SSO"
- User yang sudah terdaftar melalui SSO dapat login langsung tanpa password

## Alur Kerja SSO

### Proses Registrasi/Login:
1. User klik tombol SSO di halaman register/login
2. User diarahkan ke halaman login Sakti (`/auth/sso/redirect`)
3. User login di sistem Sakti
4. Sakti mengarahkan kembali ke aplikasi (`/auth/sso/callback`)
5. Sistem mengambil informasi user dari API Sakti
6. Jika user belum ada, sistem membuat akun baru
7. User otomatis login ke sistem

### Data User yang Diterima dari SSO:
- ID SSO
- Nama lengkap
- Email
- Data lain sesuai response API Sakti

## File yang Dimodifikasi/Ditambahkan

### Controllers:
- `app/Http/Controllers/Auth/SsoController.php` - Handler SSO

### Routes:
- `routes/auth.php` - Tambahan route SSO:
  - `GET /auth/sso/redirect` - Redirect ke SSO
  - `GET /auth/sso/callback` - Callback dari SSO

### Views:
- `resources/js/Pages/Auth/Register.vue` - Tambahan tombol SSO
- `resources/js/Pages/Auth/Login.vue` - Tambahan tombol SSO

### Configuration:
- `config/services.php` - Konfigurasi SSO
- `.env.example` - Contoh environment variables

### Database:
- Migration untuk kolom `sso_id` di tabel users
- Model User diupdate untuk mendukung `sso_id`

## Keamanan

### CSRF Protection:
- Menggunakan state parameter untuk mencegah CSRF attacks
- State disimpan di session dan diverifikasi saat callback

### SSL Verification:
- SSL verification dinonaktifkan untuk kompatibilitas dengan server Sakti
- Dapat diaktifkan kembali jika diperlukan

### Error Handling:
- Semua error dicatat di log Laravel
- User mendapat pesan error yang user-friendly

## Testing

Untuk testing SSO:

1. Pastikan konfigurasi environment sudah benar
2. Akses halaman register atau login
3. Klik tombol SSO
4. Login dengan kredensial Sakti yang valid
5. Verifikasi user berhasil login/register

## Troubleshooting

### Masalah Umum:

1. **Error "Invalid state parameter"**
   - Pastikan session berjalan dengan baik
   - Clear browser cache/cookies

2. **Error "Failed to get access token"**
   - Periksa client_id dan client_secret
   - Periksa URL token endpoint

3. **Error "Failed to get user data"**
   - Periksa API URL base
   - Periksa apakah access token valid

4. **CORS Error saat redirect ke SSO** ✅ **SUDAH DIPERBAIKI**
   ```
   Access to XMLHttpRequest at 'https://sakti.karanganyarkab.go.id/...' has been blocked by CORS policy
   ```
   **Solusi:**
   - SSO Button sekarang menggunakan `window.location.href` untuk full page redirect
   - Routes SSO dipindahkan keluar dari guest middleware
   - Tidak ada lagi masalah CORS karena bukan AJAX request

### Implementasi Anti-CORS:

#### 1. SsoButton Component
```vue
// Menggunakan window.location.href daripada Inertia Link
const handleSsoRedirect = () => {
  window.location.href = route('sso.redirect');
};
```

#### 2. Routes Configuration
```php
// SSO Routes di luar guest middleware untuk menghindari konflik
Route::get('auth/sso/redirect', [SsoController::class, 'redirectToProvider'])
    ->middleware('web')
    ->name('sso.redirect');
```

#### 3. Controller Headers
```php
// Menambahkan header X-Inertia-Location untuk redirect yang tepat
$response = redirect($authUrl);
$response->headers->set('X-Inertia-Location', $authUrl);
```

### Log Files:
Error SSO dicatat di `storage/logs/laravel.log` dengan prefix "SSO:"

## Kustomisasi

### Mengubah Data User yang Disimpan:
Edit method `findOrCreateUser()` di `SsoController.php`

### Mengubah Tampilan Tombol SSO:
Edit file Vue di `resources/js/Pages/Auth/`

### Menambah Logout SSO:
Uncomment dan konfigurasi logout URL di `SsoController.php`
