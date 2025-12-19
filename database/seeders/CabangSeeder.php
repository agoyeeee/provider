<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lokasi;
use App\Models\Umkm;

class CabangSeeder extends Seeder
{
    public function run()
    {
        $umkms = Umkm::take(5)->get();
        if ($umkms->isEmpty()) {
            return; // tidak ada UMKM untuk dihubungkan
        }

        foreach ($umkms as $index => $umkm) {
            Lokasi::create([
                'id_umkm' => $umkm->id,
                'tipe' => Lokasi::TYPE_CABANG,
                'lokasi' => $umkm->nama . ' - Cabang ' . ($index + 1) . ' (Jl. Contoh No.' . (100 + $index) . ')',
            ]);
        }
    }
}
