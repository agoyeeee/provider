<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            KategoriSeeder::class,
            SubKategoriSeeder::class,
            UmkmSeeder::class,
            ProdukSeeder::class,
            LokasiSeeder::class,
            DokumenSeeder::class,
            UlasanSeeder::class,
            CabangSeeder::class,
        ]);
    }
}
