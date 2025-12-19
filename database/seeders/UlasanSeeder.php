<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ulasan;
use App\Models\Umkm;

class UlasanSeeder extends Seeder
{
	public function run()
	{
		$umkm = Umkm::first();
		if ($umkm) {
			Ulasan::create([
				'id_umkm' => $umkm->id,
				'ulasan' => 'Pelayanan cepat dan ramah. Makanan enak.',
			]);
			Ulasan::create([
				'id_umkm' => $umkm->id,
				'ulasan' => 'Harga terjangkau, porsi besar.',
			]);
		}
	}
}
