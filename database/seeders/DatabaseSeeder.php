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
        // ProviderSeeder (data dummy) sengaja tidak dipanggil.
        $this->call([
            UserSeeder::class,
        ]);
    }
}
