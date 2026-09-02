<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Topik extends Model
{
    protected $table = 'topik';

    protected $fillable = ['uuid', 'kode', 'nama', 'deskripsi', 'color'];

    protected static function booted(): void
    {
        static::creating(function (Topik $t) {
            if (empty($t->uuid)) {
                $t->uuid = (string) Str::uuid();
            }
        });
    }
}
