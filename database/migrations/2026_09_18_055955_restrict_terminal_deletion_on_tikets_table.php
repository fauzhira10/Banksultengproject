<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat tiket adalah bukti perhitungan SLA, sehingga terminal yang masih
 * memiliki tiket tidak boleh dihapus permanen (sebelumnya cascade).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->dropForeign(['terminal_id']);
            $table->foreign('terminal_id')->references('id')->on('terminals')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->dropForeign(['terminal_id']);
            $table->foreign('terminal_id')->references('id')->on('terminals')->cascadeOnDelete();
        });
    }
};
