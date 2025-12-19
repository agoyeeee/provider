<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'nik' => '1234567890123456',
            'name' => 'Administrator',
            'email' => 'admin@provider.com',
            'password' => Hash::make('coba'),
            'role' => 'admin',
            'kontak' => '081234567890',
            'email_verified_at' => now(),
        ]);

        // Sample regular user
        User::create([
            'nik' => '9876543210987654',
            'name' => 'User Demo',
            'email' => 'user@provider.com',
            'password' => Hash::make('coba'),
            'role' => 'user',
            'kontak' => '081987654321',
            'email_verified_at' => now(),
        ]);
    }
}
