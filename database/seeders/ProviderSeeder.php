<?php

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    /**
     * Satu titik + daftar tiang (provider => ODP ada?).
     */
    public function run(): void
    {
        $titik = [
            [
                'provinsi' => 'Jawa Tengah',
                'kota' => 'Surakarta',
                'kecamatan' => 'Jebres',
                'kelurahan' => 'Kepatihan Kulon',
                'k_ref_wil' => '3372041001',
                'utilitas' => 'Tiang Provider',
                'sijali' => 'Kemasan 3',
                'x' => 110.8298474,
                'y' => -7.5617317968,
                'tgl_survey' => '2026-10-12',
                'tiang' => ['Link Net' => true, 'Telkom' => false],
            ],
            [
                'provinsi' => 'Jawa Tengah',
                'kota' => 'Surakarta',
                'kecamatan' => 'Laweyan',
                'kelurahan' => 'Sriwedari',
                'k_ref_wil' => '3372021001',
                'utilitas' => 'Tiang Provider',
                'sijali' => 'Slamet Riyadi',
                'x' => 110.8101,
                'y' => -7.5685,
                'tgl_survey' => '2026-10-13',
                'tiang' => ['Telkom' => true],
            ],
        ];

        foreach ($titik as $row) {
            $tiang = $row['tiang'];
            unset($row['tiang']);

            $provider = Provider::firstOrCreate(['x' => $row['x'], 'y' => $row['y']], $row);
            foreach ($tiang as $name => $odp) {
                $provider->tiang()->firstOrCreate(['n_provider' => $name], ['odp' => $odp]);
            }
        }
    }
}
