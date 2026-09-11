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
        Schema::table('tikets', function (Blueprint $table) {
            $table->foreignId('cabang_id')->nullable()->after('terminal_id')->constrained('cabangs')->nullOnDelete();
            $table->string('cabang_text', 100)->nullable()->after('cabang_id');
            $table->string('kategori_problem', 50)->nullable()->after('jenis_masalah_id');
            $table->string('permasalahan', 150)->nullable()->after('kategori_problem');
            $table->string('durasi_jam_menit', 30)->nullable()->after('durasi_menit');
            $table->string('durasi_lengkap', 100)->nullable()->after('durasi_jam_menit');
            $table->string('contact_person', 100)->nullable()->after('tindakan');
            $table->string('phone_number', 50)->nullable()->after('contact_person');

            $table->index('cabang_id');
            $table->index('kategori_problem');
            $table->index('permasalahan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->dropForeign(['cabang_id']);
            $table->dropIndex(['cabang_id']);
            $table->dropIndex(['kategori_problem']);
            $table->dropIndex(['permasalahan']);

            $table->dropColumn([
                'cabang_id',
                'cabang_text',
                'kategori_problem',
                'permasalahan',
                'durasi_jam_menit',
                'durasi_lengkap',
                'contact_person',
                'phone_number',
            ]);
        });
    }
};
