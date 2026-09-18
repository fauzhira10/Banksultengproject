<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use Auditable;

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
