<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'nik' => '1234567890123456',
                'kontak' => '081234567890',
                'name' => 'Admin System',
                'role' => 'super_admin',
                'email' => 'admin@umkm.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nik' => '2345678901234567',
                'kontak' => '081234567891',
                'name' => 'Pemilik UMKM 1',
                'role' => 'pemilik_umkm',
                'email' => 'pemilik1@umkm.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
