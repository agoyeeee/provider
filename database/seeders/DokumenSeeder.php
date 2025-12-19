<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DokumenSeeder extends Seeder
{
    public function run(): void
    {
        $umkmId = DB::table('umkm')->first()->id ?? 1;

        DB::table('dokumen')->insert([
            [
                'id_umkm' => $umkmId,
                'jenis' => 'Sertifikat Halal',
                'file_url' => 'documents/sertifikat_halal_warung_makan.pdf',
                'status' => 'approved',
                'catatan' => 'Dokumen lengkap dan sesuai',
                'tanggal_verifikasi' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_umkm' => $umkmId,
                'jenis' => 'Sertifikat Haki',
                'file_url' => 'documents/sertifikat_haki_warung_makan.pdf',
                'status' => 'pending',
                'catatan' => null,
                'tanggal_verifikasi' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
