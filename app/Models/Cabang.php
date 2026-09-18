<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cabang extends Model
{
    use Auditable;

    protected $fillable = [
        'kode_cabang',
        'nama_cabang',
        'label_cabang',
        'urutan',
    ];

    public function terminals(): HasMany
    {
        return $this->hasMany(Terminal::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Cabang $cabang) {
            if (empty($cabang->label_cabang) && $cabang->kode_cabang && $cabang->nama_cabang) {
                $cabang->label_cabang = "{$cabang->kode_cabang}-{$cabang->nama_cabang}";
            }
        });
    }
}
