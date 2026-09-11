<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jenis_masalahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_masalah_id')->nullable()->constrained('kategori_masalahs')->nullOnDelete();
            $table->string('nama_jenis', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_masalahs');
    }
};
