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
        'rek_ia',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'is_hibah' => 'boolean',
        'urutan_cabang' => 'integer',
    ];

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
}
