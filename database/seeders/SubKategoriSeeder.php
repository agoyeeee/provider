<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubKategoriSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sub_kategori')->insert([
            // Makanan dan Minuman (id_kategori: 1)
            [
                'nama' => 'Makanan Tradisional',
                'deskripsi' => 'Makanan khas daerah dan tradisional',
                'id_kategori' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Minuman Segar',
                'deskripsi' => 'Minuman segar dan jus buah',
                'id_kategori' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Fashion dan Aksesoris (id_kategori: 2)
            [
                'nama' => 'Pakaian Wanita',
                'deskripsi' => 'Pakaian untuk wanita',
                'id_kategori' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Tas dan Dompet',
                'deskripsi' => 'Aksesoris tas dan dompet',
                'id_kategori' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Kerajinan Tangan (id_kategori: 3)
            [
                'nama' => 'Kerajinan Kayu',
                'deskripsi' => 'Produk kerajinan dari bahan kayu',
                'id_kategori' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Kerajinan Tekstil',
                'deskripsi' => 'Produk kerajinan dari bahan tekstil',
                'id_kategori' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Jasa (id_kategori: 4)
            [
                'nama' => 'Jasa Konsultasi',
                'deskripsi' => 'Layanan konsultasi bisnis',
                'id_kategori' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Jasa Perbaikan',
                'deskripsi' => 'Layanan perbaikan barang elektronik',
                'id_kategori' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
