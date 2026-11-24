<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Untuk query bbox peta dan filter wilayah
return new class extends Migration {
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->index(['x', 'y']);
            $table->index(['kecamatan', 'kelurahan']);
            $table->index('sijali');
        });
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropIndex(['x', 'y']);
            $table->dropIndex(['kecamatan', 'kelurahan']);
            $table->dropIndex(['sijali']);
        });
    }
};
