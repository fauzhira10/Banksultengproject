<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = [
        'nama_vendor',
        'kontak',
        'keterangan',
    ];

    public function terminals(): HasMany
    {
        return $this->hasMany(Terminal::class);
    }
}
