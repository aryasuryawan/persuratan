<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusSurat extends Model
{
    protected $table = 'status_surat';

    protected $fillable = ['kode', 'label', 'urutan'];

    public function suratMasuk(): HasMany
    {
        return $this->hasMany(SuratMasuk::class, 'status_id');
    }
}
