<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Direktorat extends Model
{
    protected $fillable = ['nama', 'kode', 'aktif'];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function divisis(): HasMany
    {
        return $this->hasMany(Divisi::class);
    }
}