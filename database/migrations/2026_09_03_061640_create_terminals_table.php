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
        Schema::create('terminals', function (Blueprint $table) {
            $table->id();
            $table->string('profil', 50)->unique();
            $table->foreignId('cabang_id')->nullable()->constrained('cabangs')->nullOnDelete();
            $table->string('cabang_text', 100)->nullable();
            $table->integer('urutan_cabang')->nullable();
            $table->string('nama_lokasi', 150);
            $table->string('ip_address', 45)->nullable();
            $table->string('luno', 20)->nullable();
            $table->string('port', 20)->nullable();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('vendor_text', 100)->nullable();
            $table->string('serial_number', 50)->nullable();
            $table->string('denom', 20)->default('100');
            $table->string('tipe_mesin', 50)->nullable();
            $table->string('kategori', 20)->default('ATM');
            $table->boolean('is_hibah')->default(false);
            $table->string('rek_ia', 60)->nullable();
            $table->string('status', 30)->default('Aktif');
            $table->text('keterangan')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['cabang_id', 'status']);
            $table->index('luno');
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminals');
    }
};
