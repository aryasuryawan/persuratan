<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganisasiPengirim extends Model
{
    protected $fillable = ['nama', 'alamat', 'kontak', 'aktif'];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function suratMasuk(): HasMany
    {
        return $this->hasMany(SuratMasuk::class, 'pengirim_id');
    }
}
