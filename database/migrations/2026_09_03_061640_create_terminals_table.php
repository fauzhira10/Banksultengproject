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
            $table->timestamps();
            $table->string('profil', 50)->unique();
            $table->string('serial_number', 50)->unique()->nullable();
            $table->string('ip_adress', 45)->unique()->nullable();

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
