<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tiang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('n_provider')->comment('Satu tiang = satu provider');
            $table->boolean('odp')->default(false)->comment('ODP ada / tidak');
            $table->string('status_izin')->nullable()->comment('berizin | tidak_berizin');
            $table->timestamps();

            $table->unique(['provider_id', 'n_provider']);
            $table->index('n_provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiang');
    }
};
