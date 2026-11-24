<?php

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    /** kecamatan => [pusat lng, pusat lat, kelurahan[]] */
    private const WILAYAH = [
        'Banjarsari' => [110.8200, -7.5450, ['Banjarsari', 'Kadipiro', 'Keprabon', 'Manahan', 'Mangkubumen', 'Nusukan', 'Punggawan', 'Setabelan', 'Sumber', 'Timuran', 'Gilingan', 'Kestalan', 'Ketelan', 'Joglo', 'Banyuanyar']],
        'Jebres' => [110.8500, -7.5600, ['Gandekan', 'Jagalan', 'Jebres', 'Kepatihan Kulon', 'Kepatihan Wetan', 'Mojosongo', 'Pucangsawit', 'Purwodiningratan', 'Sewu', 'Sudiroprajan', 'Tegalharjo']],
        'Laweyan' => [110.7950, -7.5680, ['Bumi', 'Jajar', 'Karangasem', 'Kerten', 'Laweyan', 'Pajang', 'Panularan', 'Penumping', 'Purwosari', 'Sondakan', 'Sriwedari']],
        'Pasar Kliwon' => [110.8300, -7.5850, ['Baluwarti', 'Gajahan', 'Joyosuran', 'Kampung Baru', 'Kauman', 'Kedung Lumbu', 'Mojo', 'Pasar Kliwon', 'Sangkrah', 'Semanggi']],
        'Serengan' => [110.8150, -7.5850, ['Danukusuman', 'Jayengan', 'Joyotakan', 'Kemlayan', 'Kratonan', 'Serengan', 'Tipes']],
    ];

    private const JALAN = ['Slamet Riyadi', 'Dr. Rajiman', 'Kemasan 3', 'Ronggowarsito', 'Adi Sucipto', 'Veteran', 'Yos Sudarso', 'Kapten Mulyadi', 'Honggowongso', 'Hasanudin', 'Monginsidi', 'Jend. Urip Sumoharjo', 'Gatot Subroto', 'Brigjen Slamet Riyadi', 'Bhayangkara', 'Samanhudi', 'Gading', 'Kebangkitan Nasional', 'Sutan Syahrir', 'Mojosongo Raya'];

    private const PROVIDER = ['Telkom', 'Link Net', 'Biznet', 'MyRepublic', 'First Media', 'Indosat', 'XL Axiata', 'PowerTel', 'Iconnet', 'Oxygen'];

    /** ~400 titik contoh Surakarta + tiang per titik (deterministik, aman dijalankan ulang). */
    public function run(): void
    {
        mt_srand(42);

        foreach (self::WILAYAH as $kecamatan => [$lng, $lat, $kelurahanList]) {
            foreach ($kelurahanList as $kelurahan) {
                // pusat kelurahan digeser kecil dari pusat kecamatan
                $cx = $lng + (mt_rand(-150, 150) / 10000);
                $cy = $lat + (mt_rand(-100, 100) / 10000);

                for ($i = 0; $i < 8; $i++) {
                    $x = round($cx + mt_rand(-40, 40) / 10000, 7);
                    $y = round($cy + mt_rand(-40, 40) / 10000, 7);

                    $provider = Provider::firstOrCreate(['x' => $x, 'y' => $y], [
                        'provinsi' => 'Jawa Tengah',
                        'kota' => 'Surakarta',
                        'kecamatan' => $kecamatan,
                        'kelurahan' => $kelurahan,
                        'k_ref_wil' => (string) mt_rand(3372000000, 3372999999),
                        'utilitas' => 'Tiang Provider',
                        'sijali' => self::JALAN[mt_rand(0, count(self::JALAN) - 1)],
                        'tgl_survey' => date('Y-m-d', strtotime('2026-10-01 +' . mt_rand(0, 50) . ' days')),
                    ]);

                    $names = self::PROVIDER;
                    shuffle($names);
                    foreach (array_slice($names, 0, mt_rand(1, 3)) as $name) {
                        $provider->tiang()->firstOrCreate(['n_provider' => $name], [
                            'odp' => (bool) mt_rand(0, 1),
                            'status_izin' => mt_rand(0, 3) ? 'berizin' : 'tidak_berizin',
                        ]);
                    }
                }
            }
        }

        // Titik lama (kolom n_provider) yang belum punya tiang: salin jadi 1 tiang.
        Provider::doesntHave('tiang')->whereNotNull('n_provider')->each(
            fn(Provider $p) => $p->tiang()->create(['n_provider' => ucwords(strtolower($p->n_provider)), 'odp' => $p->sijali === 'Y'])
        );
    }
}
