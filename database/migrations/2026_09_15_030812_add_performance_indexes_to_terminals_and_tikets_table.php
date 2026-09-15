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
            $table->index('kategori');
            $table->index('is_hibah');
            $table->index('tipe_mesin');
        });

        Schema::table('tikets', function (Blueprint $table) {
            $table->index('mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terminals', function (Blueprint $table) {
            $table->dropIndex(['kategori']);
            $table->dropIndex(['is_hibah']);
            $table->dropIndex(['tipe_mesin']);
        });

        Schema::table('tikets', function (Blueprint $table) {
            $table->dropIndex(['mulai']);
        });
    }
};
