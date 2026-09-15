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
        Schema::table('terminals', function (Blueprint $table) {
            // First add an index for cabang_id so the foreign key constraint remains satisfied
            $table->index('cabang_id', 'terminals_cabang_id_index');
        });

        Schema::table('terminals', function (Blueprint $table) {
            // Now safe to drop the composite index and status column
            try {
                $table->dropIndex('terminals_cabang_id_status_index');
            } catch (Throwable) {
                // Ignore if already dropped
            }

            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terminals', function (Blueprint $table) {
            $table->string('status', 30)->default('Aktif');
            $table->index(['cabang_id', 'status'], 'terminals_cabang_id_status_index');
            $table->dropIndex('terminals_cabang_id_index');
        });
    }
};
