<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        if (!User::where('email', 'admin@umkm.com')->exists()) {
            User::create([
                'nik' => '0000000000000001',
                'kontak' => '081234567800',
                'name' => 'Admin UMKM',
                'email' => 'admin@umkm.com',
                'sso_id' => '',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]);
        }

        // Check if pemilik umkm user already exists
        if (!User::where('email', 'pemilik@umkm.com')->exists()) {
            User::create([
                'nik' => '0000000000000002',
                'kontak' => '081234567801',
                'name' => 'Pemilik UMKM Contoh',
                'email' => 'pemilik@umkm.com',
                'sso_id' => '',
                'password' => Hash::make('password'),
                'role' => 'pemilik_umkm',
                'email_verified_at' => now(),
            ]);
        }
    }
}
