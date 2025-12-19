<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LokasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first UMKM ID
        $umkmId = DB::table('umkm')->first()->id_umkm ?? 1;

        DB::table('lokasi')->insert([
            [
                'id_umkm' => $umkmId,
                'kecamatan' => 'Lokasi utama warung makan',
                'desa' => 'Kuta Utara, Badung, Bali',
                'maps_link' => 'https://goo.gl/maps/your-main-location', // Example link
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_umkm' => $umkmId,
                'kecamatan' => 'Cabang di pusat kota',
                'desa' => 'Pemecutan Kelod, Denpasar Barat',
                'maps_link' => 'https://goo.gl/maps/your-branch-location', // Example link
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_umkm' => $umkmId,
                'kecamatan' => 'Outlet wisata',
                'desa' => 'Ubud, Gianyar',
                'maps_link' => 'https://goo.gl/maps/your-tourist-location', // Example link
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
