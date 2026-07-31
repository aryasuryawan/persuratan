<?php

namespace App\Concerns;

use App\Models\FotoKegiatan;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait PunyaFotoKegiatan
{
    public function fotoKegiatan(): MorphMany
    {
        return $this->morphMany(FotoKegiatan::class, 'kegiatan')->orderBy('urutan');
    }
}
