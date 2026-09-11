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
        Schema::create('tikets', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tiket', 50)->unique()->nullable();
            $table->foreignId('terminal_id')->constrained('terminals')->cascadeOnDelete();
            $table->foreignId('jenis_masalah_id')->nullable()->constrained('jenis_masalahs')->nullOnDelete();
            $table->dateTime('mulai')->nullable();
            $table->dateTime('selesai')->nullable();
            $table->integer('durasi_menit')->nullable();
            $table->string('status', 30)->default('Open');
            $table->text('deskripsi')->nullable();
            $table->text('tindakan')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['terminal_id', 'mulai', 'selesai']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tikets');
    }
};
