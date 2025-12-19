# Troubleshooting Error 403 SSO Sakti

## Analisis Error 403

Error 403 (Forbidden) pada SSO Sakti biasanya disebabkan oleh:

### 1. **Client ID/Secret Tidak Valid** ❌
- Client ID: `7bb718d2-abea-4f3e-865b-3b87b0be59b8`
- Client Secret: `VEoyEM5UCOy3ZpdCf8X3kG6piRTf7etKM68FLVkg`

**Solusi:**
- Pastikan credentials di atas masih valid dan aktif
- Hubungi admin sistem Sakti untuk verifikasi

### 2. **Redirect URI Tidak Terdaftar** ⚠️
- Current: `http://localhost:8000/auth/sso/callback`
- Alternative: `http://127.0.0.1:8000/auth/sso/callback`

**Solusi:**
- Pastikan URL callback sudah terdaftar di sistem Sakti
- Gunakan domain yang sama persis dengan yang terdaftar

### 3. **Parameter Request Tidak Sesuai** 🔍

URL yang dihasilkan:
```
https://sakti.karanganyarkab.go.id/login/oauth/authorize?
response_type=code&
client_id=7bb718d2-abea-4f3e-865b-3b87b0be59b8&
redirect_uri=http%3A%2F%2Flocalhost%3A8000%2Fauth%2Fsso%2Fcallback&
scopes=user&
state=9x4ASsjC5ZS2pNxQs1wWewWlmqzQOrma
```

## Debug Steps

### Step 1: Cek Konfigurasi
```bash
curl http://localhost:8000/debug/sso/config
```

### Step 2: Generate Test URL
```bash
curl http://localhost:8000/debug/sso/test-url
```

### Step 3: Test Manual
1. Copy URL dari step 2
2. Paste di browser
3. Cek apakah error 403 masih terjadi

## Kemungkinan Solusi

### 1. **Ubah Redirect URI ke 127.0.0.1**
```bash
SAKTI_SSO_REDIRECT_URI=http://127.0.0.1:8000/auth/sso/callback
```

### 2. **Pastikan Domain Terdaftar**
- Hubungi admin Sakti
- Daftarkan domain development

### 3. **Cek Parameter Scope**
- Mungkin perlu menggunakan `scope` bukan `scopes`
- Atau value yang berbeda

### 4. **SSL Requirement**
- Sistem produksi mungkin memerlukan HTTPS
- Test dengan ngrok untuk HTTPS local

## Langkah Selanjutnya

1. **Konfirmasi dengan Admin Sakti:**
   - Apakah client ID masih aktif?
   - Apakah redirect URI sudah terdaftar?
   - Apakah ada requirement khusus?

2. **Test dengan Postman/cURL:**
   ```bash
   curl -X GET "https://sakti.karanganyarkab.go.id/login/oauth/authorize?response_type=code&client_id=7bb718d2-abea-4f3e-865b-3b87b0be59b8&redirect_uri=http%3A%2F%2F127.0.0.1%3A8000%2Fauth%2Fsso%2Fcallback&scopes=user&state=test123"
   ```

3. **Gunakan ngrok untuk HTTPS:**
   ```bash
   ngrok http 8000
   # Update SAKTI_SSO_REDIRECT_URI dengan URL ngrok
   ```

## Status Check

- ✅ URL format sudah benar
- ✅ Parameters sesuai standar OAuth2
- ❌ Error 403 - kemungkinan masalah autorization
- ❓ Perlu konfirmasi dengan admin Sakti
