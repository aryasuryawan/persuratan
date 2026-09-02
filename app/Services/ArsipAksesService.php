<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ArsipAksesService
{
    public function scopeUntukUser(Builder $query, User $user): Builder
    {
        if ($user->hasRole('admin') || $user->hasRole('sekretaris')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->whereHas('klasifikasi', fn ($kl) => $kl->where('is_rahasia', false));

            if (! $user->hasRole('cc_security')) {
                $q->orWhereHas('aksesManual', fn ($a) => $a->where('user_id', $user->id));
            }
        });
    }
}
