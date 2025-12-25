<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('provinsi')->nullable();
            $table->string('kota')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('k_ref_wil')->nullable()->comment('Kode Referensi Wilayah');
            $table->string('utilitas')->nullable()->comment('Jenis Utilitas');
            $table->string('n_provider')->nullable()->comment('Nama Provider');
            $table->string('odp')->nullable()->comment('Optical Distribution Point');
            $table->string('sijali')->nullable()->comment('Sijali');
            $table->string('sijali_link')->nullable()->comment('URL detail Sijali');
            $table->decimal('x', 15, 10)->nullable()->comment('Longitude');
            $table->decimal('y', 15, 10)->nullable()->comment('Latitude');
            $table->string('foto')->nullable()->comment('Path foto');
            $table->date('tgl_survey')->nullable()->comment('Tanggal Survey');
            $table->unsignedBigInteger('id_user')->nullable()->comment('User yang input data');
            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
