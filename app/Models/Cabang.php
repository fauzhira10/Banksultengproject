<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cabang extends Model
{
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
}
