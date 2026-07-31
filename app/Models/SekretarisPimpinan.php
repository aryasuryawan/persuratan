<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SekretarisPimpinan extends Model
{
    protected $table = 'sekretaris_pimpinan';

    protected $fillable = ['sekretaris_id', 'pimpinan_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function sekretaris(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sekretaris_id');
    }

    public function pimpinan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pimpinan_id');
    }
}
