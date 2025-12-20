<?php

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'fid' => 'FID001',
                'provinsi' => 'JAWA BARAT',
                'kota' => 'BANDUNG',
                'kecamatan' => 'COBLONG',
                'kelurahan' => 'DAGO',
                'k_ref_wil' => '3273010001',
                'utilitas' => 'TELEKOMUNIKASI',
                'n_provider' => 'TELKOM',
                'odp' => 'ODP-CBL-001',
                'sijali' => 'Y',
                'sijali_link' => 'https://sijali.surakarta.go.id/',
                'x' => 107.6191,
                'y' => -6.8876,
                'tgl_survey' => '2025-01-15',
            ],
            [
                'fid' => 'FID002',
                'provinsi' => 'JAWA BARAT',
                'kota' => 'BANDUNG',
                'kecamatan' => 'SUKAJADI',
                'kelurahan' => 'PASTEUR',
                'k_ref_wil' => '3273020002',
                'utilitas' => 'TELEKOMUNIKASI',
                'n_provider' => 'INDOSAT',
                'odp' => 'ODP-SKJ-002',
                'sijali' => 'Y',
                'sijali_link' => 'https://sijali.surakarta.go.id/',
                'x' => 107.5888,
                'y' => -6.8856,
                'tgl_survey' => '2025-01-16',
            ],
            [
                'fid' => 'FID003',
                'provinsi' => 'DKI JAKARTA',
                'kota' => 'JAKARTA SELATAN',
                'kecamatan' => 'KEBAYORAN BARU',
                'kelurahan' => 'SENAYAN',
                'k_ref_wil' => '3174010001',
                'utilitas' => 'TELEKOMUNIKASI',
                'n_provider' => 'XL AXIATA',
                'odp' => 'ODP-KBY-003',
                'sijali' => 'N',
                'sijali_link' => 'https://sijali.surakarta.go.id/',
                'x' => 106.8006,
                'y' => -6.2297,
                'tgl_survey' => '2025-01-17',
            ],
            [
                'fid' => 'FID004',
                'provinsi' => 'DKI JAKARTA',
                'kota' => 'JAKARTA PUSAT',
                'kecamatan' => 'MENTENG',
                'kelurahan' => 'CIKINI',
                'k_ref_wil' => '3173020002',
                'utilitas' => 'TELEKOMUNIKASI',
                'n_provider' => 'TELKOM',
                'odp' => 'ODP-MTG-004',
                'sijali' => 'Y',
                'sijali_link' => 'https://sijali.surakarta.go.id/',
                'x' => 106.8456,
                'y' => -6.1967,
                'tgl_survey' => '2025-01-18',
            ],
            [
                'fid' => 'FID005',
                'provinsi' => 'JAWA TIMUR',
                'kota' => 'SURABAYA',
                'kecamatan' => 'GUBENG',
                'kelurahan' => 'AIRLANGGA',
                'k_ref_wil' => '3578010001',
                'utilitas' => 'TELEKOMUNIKASI',
                'n_provider' => 'BIZNET',
                'odp' => 'ODP-GBG-005',
                'sijali' => 'N',
                'sijali_link' => 'https://sijali.surakarta.go.id/',
                'x' => 112.7568,
                'y' => -7.2756,
                'tgl_survey' => '2025-01-19',
            ],
        ];

        foreach ($providers as $provider) {
            Provider::create($provider);
        }
    }
}
