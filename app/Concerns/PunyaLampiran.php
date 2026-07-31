<?php

namespace App\Concerns;

use App\Models\Lampiran;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait PunyaLampiran
{
    public function lampiran(): MorphMany
    {
        return $this->morphMany(Lampiran::class, 'lampirkan');
    }
}
