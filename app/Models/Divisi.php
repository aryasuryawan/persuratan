<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Divisi extends Model
{
    protected $fillable = ['direktorat_id', 'nama', 'kode', 'aktif'];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function direktorat(): BelongsTo
    {
        return $this->belongsTo(Direktorat::class);
    }

    public function departemens(): HasMany
    {
        return $this->hasMany(Departemen::class);
    }
}