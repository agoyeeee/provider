<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Umkm;
use App\Models\JamOperasional;

class UmkmSeeder extends Seeder
{
	public function run()
	{
		$u1 = Umkm::create([
			'nama' => 'Warung Makan Sari',
			'deskripsi' => 'Warung makan tradisional dengan menu nusantara.',
			'logo' => null,
			'no_telepon' => '081234567890',
			'fasilitas' => 'Parkir, WiFi',
			'sosmed' => ['instagram' => '@warungsari', 'facebook' => 'warungsari'],
			'id_user' => 1,
			'id_kategori' => 1,
			'id_sub_kategori' => null,
		]);

		JamOperasional::create([
			'id_umkm' => $u1->id,
			'hari' => 'Senin-Jumat',
			'jam_buka' => '08:00:00',
			'jam_tutup' => '20:00:00',
			'keterangan' => 'Operasional normal',
		]);
		JamOperasional::create([
			'id_umkm' => $u1->id,
			'hari' => 'Sabtu-Minggu',
			'jam_buka' => '09:00:00',
			'jam_tutup' => '17:00:00',
			'keterangan' => 'Jam akhir pekan',
		]);

		$u2 = Umkm::create([
			'nama' => 'Kerajinan Tangan Kita',
			'deskripsi' => 'Produk kerajinan lokal berkualitas.',
			'logo' => null,
			'no_telepon' => '082345678901',
			'fasilitas' => 'Kartu kredit diterima',
			'sosmed' => ['instagram' => '@kerajinankita'],
			'id_user' => 2,
			'id_kategori' => 2,
			'id_sub_kategori' => null,
		]);

		JamOperasional::create([
			'id_umkm' => $u2->id,
			'hari' => 'Senin-Jumat',
			'jam_buka' => '09:00:00',
			'jam_tutup' => '17:00:00',
			'keterangan' => 'Jam kerja toko',
		]);
	}
}
