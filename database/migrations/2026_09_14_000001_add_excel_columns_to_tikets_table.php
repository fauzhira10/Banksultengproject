<?php

use App\Models\Terminal;
use App\Models\Tiket;
use Carbon\Carbon;
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
            $table->string('lokasi')->nullable()->after('kategori_problem');
            $table->string('atm_id', 50)->nullable()->after('lokasi');
            $table->string('profil', 100)->nullable()->after('atm_id');
            $table->string('tipe_mesin', 100)->nullable()->after('profil');
            $table->string('serial_number', 100)->nullable()->after('tipe_mesin');
            $table->text('status_keterangan')->nullable()->after('status');

            $table->index('atm_id');
            $table->index('profil');
        });

        // Backfill data dari data terminal yang sudah ada
        try {
            $tikets = Tiket::with('terminal')->get();
            foreach ($tikets as $tiket) {
                $terminal = $tiket->terminal;
                $updated = false;

                if ($terminal) {
                    $tiket->lokasi = $terminal->nama_lokasi;
                    $tiket->atm_id = $terminal->luno;
                    $tiket->profil = $terminal->profil;
                    $tiket->tipe_mesin = $terminal->tipe_mesin;
                    $tiket->serial_number = $terminal->serial_number;
                    $updated = true;
                }

                // Format durasi_lengkap jika belum memiliki spasi
                if ($tiket->mulai && $tiket->selesai) {
                    $start = Carbon::parse($tiket->mulai);
                    $end = Carbon::parse($tiket->selesai);

                    if ($end->greaterThanOrEqualTo($start)) {
                        $totalMinutes = (int) $start->diffInMinutes($end);
                        $totalSeconds = (int) $start->diffInSeconds($end);

                        $days = floor($totalMinutes / 1440);
                        $remHours = floor(($totalMinutes % 1440) / 60);
                        $remMins = $totalMinutes % 60;
                        $remSecs = $totalSeconds % 60;

                        $tiket->durasi_lengkap = "{$days} hari {$remHours} jam {$remMins} menit {$remSecs} detik";
                        $tiket->durasi_jam_menit = sprintf('%d:%02d', floor($totalMinutes / 60), $totalMinutes % 60);
                        $tiket->durasi_menit = $totalMinutes;
                        $updated = true;
                    }
                }

                if ($updated) {
                    $tiket->saveQuietly();
                }
            }
        } catch (Throwable) {
            // Silently ignore if table context is not fully ready during migration
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->dropIndex(['atm_id']);
            $table->dropIndex(['profil']);

            $table->dropColumn([
                'lokasi',
                'atm_id',
                'profil',
                'tipe_mesin',
                'serial_number',
                'status_keterangan',
            ]);
        });
    }
};
