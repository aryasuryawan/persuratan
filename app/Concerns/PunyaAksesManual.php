<?php

namespace App\Concerns;

use App\Models\SuratAkses;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait PunyaAksesManual
{
    public function aksesManual(): MorphMany
    {
        return $this->morphMany(SuratAkses::class, 'aksesable');
    }

    public function punyaAksesManual(User $user): bool
    {
        return $this->aksesManual()->where('user_id', $user->id)->exists();
    }
}
