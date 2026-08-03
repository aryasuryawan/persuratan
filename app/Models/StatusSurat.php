<?php

namespace App\Models;

use App\Support\StatusCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusSurat extends Model
{
    protected $table = 'status_surat';

    protected $fillable = ['kode', 'label', 'urutan'];

    protected static function booted(): void
    {
        static::saved(fn () => StatusCache::flush());
        static::deleted(fn () => StatusCache::flush());
    }

    public function suratMasuk(): HasMany
    {
        return $this->hasMany(SuratMasuk::class, 'status_id');
    }
}
