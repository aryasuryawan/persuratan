<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Klasifikasi extends Model
{
    protected $table = 'klasifikasi';

    protected $fillable = ['uuid', 'kode', 'nama', 'deskripsi', 'color', 'is_rahasia'];

    protected function casts(): array
    {
        return ['is_rahasia' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (Klasifikasi $k) {
            if (empty($k->uuid)) {
                $k->uuid = (string) Str::uuid();
            }
        });
    }

    public function suratMasuk(): HasMany
    {
        return $this->hasMany(SuratMasuk::class);
    }

    public function suratKeluar(): HasMany
    {
        return $this->hasMany(SuratKeluar::class);
    }
}
