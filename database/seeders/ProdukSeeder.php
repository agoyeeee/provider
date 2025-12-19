<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produk;
use App\Models\Umkm;

class ProdukSeeder extends Seeder
{
	public function run()
	{
		$umkms = Umkm::take(2)->get();
		foreach ($umkms as $umkm) {
			Produk::create([
				'nama_produk' => $umkm->nama . ' - Paket A',
				'deskripsi' => 'Deskripsi produk contoh untuk ' . $umkm->nama,
				'url' => null,
				'foto' => null,
				'id_umkm' => $umkm->id,
			]);
			Produk::create([
				'nama_produk' => $umkm->nama . ' - Paket B',
				'deskripsi' => 'Alternatif produk untuk ' . $umkm->nama,
				'url' => null,
				'foto' => null,
				'id_umkm' => $umkm->id,
			]);
		}
	}
}
