<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Terminal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'profil',
        'cabang_id',
        'cabang_text',
        'urutan_cabang',
        'nama_lokasi',
        'ip_address',
        'luno',
        'port',
        'vendor_id',
        'vendor_text',
        'serial_number',
        'denom',
        'tipe_mesin',
        'kategori',
        'is_hibah',
        'keterangan',
    ];

    protected $casts = [
        'is_hibah' => 'boolean',
        'urutan_cabang' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Terminal $terminal): void {
            $rawVendor = strtoupper(trim((string) ($terminal->vendor_text ?? '')));
            $isHibahVendor = str_contains($rawVendor, 'HIBAH');

            if (! $isHibahVendor && $terminal->vendor_id) {
                $vendorObj = Vendor::find($terminal->vendor_id);
                if ($vendorObj && str_contains(strtoupper($vendorObj->nama_vendor), 'HIBAH')) {
                    $isHibahVendor = true;
                }
            }

            if ($terminal->is_hibah || $isHibahVendor) {
                $terminal->is_hibah = true;
                $terminal->vendor_text = 'KOPERASI BANK SULTENG';

                $koperasi = Vendor::firstOrCreate(['nama_vendor' => 'KOPERASI BANK SULTENG']);
                $terminal->vendor_id = $koperasi->id;
            }
        });
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function tikets(): HasMany
    {
        return $this->hasMany(Tiket::class);
    }

    public function scopeCrm(Builder $query): Builder
    {
        return $query->where('kategori', 'CRM');
    }

    public function scopeAtm(Builder $query): Builder
    {
        return $query->where('kategori', 'ATM');
    }

    public function scopeHibah(Builder $query): Builder
    {
        return $query->where('is_hibah', true);
    }

    /**
     * Mengambil ringkasan jumlah data terminal dalam satu query agregat (memoized per-request).
     *
     * @return array{total: int, crm: int, atm: int, hibah: int}
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
                SUM(CASE WHEN kategori = 'CRM' THEN 1 ELSE 0 END) as crm,
                SUM(CASE WHEN kategori = 'ATM' THEN 1 ELSE 0 END) as atm,
                SUM(CASE WHEN is_hibah = 1 THEN 1 ELSE 0 END) as hibah
            ")
            ->first();

        return $cached = [
            'total' => (int) ($row->total ?? 0),
            'crm' => (int) ($row->crm ?? 0),
            'atm' => (int) ($row->atm ?? 0),
            'hibah' => (int) ($row->hibah ?? 0),
        ];
    }
}
