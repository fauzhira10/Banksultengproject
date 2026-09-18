<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tiket extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'nomor_tiket',
        'terminal_id',
        'cabang_id',
        'cabang_text',
        'jenis_masalah_id',
        'kategori_problem',
        'permasalahan',
        'lokasi',
        'atm_id',
        'profil',
        'tipe_mesin',
        'serial_number',
        'mulai',
        'selesai',
        'durasi_menit',
        'durasi_jam_menit',
        'durasi_lengkap',
        'status',
        'status_keterangan',
        'deskripsi',
        'tindakan',
        'contact_person',
        'phone_number',
    ];

    protected $casts = [
        'mulai' => 'datetime',
        'selesai' => 'datetime',
        'durasi_menit' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Tiket $tiket) {
            // Sinkronisasi data terminal jika terminal_id diisi
            if ($tiket->terminal_id) {
                $terminal = Terminal::with('cabang')->find($tiket->terminal_id);
                if ($terminal) {
                    if (! $tiket->cabang_id) {
                        $tiket->cabang_id = $terminal->cabang_id;
                        $tiket->cabang_text = $terminal->cabang?->label_cabang ?? $terminal->cabang_text;
                    }
                    if (empty($tiket->lokasi)) {
                        $tiket->lokasi = $terminal->nama_lokasi;
                    }
                    if (empty($tiket->atm_id)) {
                        $tiket->atm_id = $terminal->luno;
                    }
                    if (empty($tiket->profil)) {
                        $tiket->profil = $terminal->profil;
                    }
                    if (empty($tiket->tipe_mesin)) {
                        $tiket->tipe_mesin = $terminal->tipe_mesin;
                    }
                    if (empty($tiket->serial_number)) {
                        $tiket->serial_number = $terminal->serial_number;
                    }
                }
            }

            // Hitung durasi otomatis bila mulai dan selesai diisi
            if ($tiket->mulai && $tiket->selesai) {
                $start = Carbon::parse($tiket->mulai);
                $end = Carbon::parse($tiket->selesai);

                if ($end->greaterThanOrEqualTo($start)) {
                    $totalMinutes = (int) $start->diffInMinutes($end);
                    $totalSeconds = (int) $start->diffInSeconds($end);

                    $tiket->durasi_menit = $totalMinutes;

                    $hours = floor($totalMinutes / 60);
                    $mins = $totalMinutes % 60;
                    $tiket->durasi_jam_menit = sprintf('%d:%02d', $hours, $mins);

                    $days = floor($totalMinutes / 1440);
                    $remHours = floor(($totalMinutes % 1440) / 60);
                    $remMins = $totalMinutes % 60;
                    $remSecs = $totalSeconds % 60;
                    $tiket->durasi_lengkap = "{$days} hari {$remHours} jam {$remMins} menit {$remSecs} detik";

                    // Bila selesai diisi dan status masih Open, otomatis tandai Closed
                    if ($tiket->status === 'Open' || empty($tiket->status)) {
                        $tiket->status = 'Closed';
                    }
                }
            } else {
                if (empty($tiket->status)) {
                    $tiket->status = 'Open';
                }
            }
        });
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function jenisMasalah(): BelongsTo
    {
        return $this->belongsTo(JenisMasalah::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'Open');
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'Closed');
    }

    public static function generateNomorTiket(): string
    {
        $prefix = 'BST'.date('ymd');
        $last = static::withTrashed()
            ->where('nomor_tiket', 'like', "{$prefix}%")
            ->orderByDesc('nomor_tiket')
            ->value('nomor_tiket');

        if ($last && preg_match('/(\d{4})$/', $last, $matches)) {
            $nextNumber = str_pad((int) $matches[1] + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        $candidate = $prefix.$nextNumber;

        while (static::withTrashed()->where('nomor_tiket', $candidate)->exists()) {
            $nextNumber = str_pad((int) $nextNumber + 1, 4, '0', STR_PAD_LEFT);
            $candidate = $prefix.$nextNumber;
        }

        return $candidate;
    }

    /**
     * Mengambil ringkasan jumlah data tiket dalam satu query agregat (memoized per-request).
     *
     * @return array{total: int, open: int, closed: int, mesin: int, jaringan_listrik: int}
     */
    public static function getCountsSummary(bool $fresh = false): array
    {
        static $cached = null;

        if ($cached !== null && ! $fresh) {
            return $cached;
        }

        $row = static::query()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) as open_count,
                SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) as closed_count,
                SUM(CASE WHEN kategori_problem = 'Mesin ATM' THEN 1 ELSE 0 END) as mesin_count,
                SUM(CASE WHEN kategori_problem IN ('Jaringan', 'Listrik', 'System') THEN 1 ELSE 0 END) as jaringan_listrik_count
            ")
            ->first();

        return $cached = [
            'total' => (int) ($row->total ?? 0),
            'open' => (int) ($row->open_count ?? 0),
            'closed' => (int) ($row->closed_count ?? 0),
            'mesin' => (int) ($row->mesin_count ?? 0),
            'jaringan_listrik' => (int) ($row->jaringan_listrik_count ?? 0),
        ];
    }
}
