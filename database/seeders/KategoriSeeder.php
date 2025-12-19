<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('kategori')->insert([
            [
                'nama' => 'Makanan dan Minuman',
                'deskripsi' => 'Usaha yang bergerak di bidang kuliner termasuk makanan dan minuman',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Fashion dan Aksesoris',
                'deskripsi' => 'Usaha yang bergerak di bidang fashion, pakaian, dan aksesoris',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Kerajinan Tangan',
                'deskripsi' => 'Usaha yang bergerak di bidang kerajinan dan produk handmade',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Jasa',
                'deskripsi' => 'Usaha yang bergerak di bidang penyediaan jasa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
