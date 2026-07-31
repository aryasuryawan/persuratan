<?php

namespace App\Policies;

use App\Models\NotulensiRapim;
use App\Models\User;

class NotulensiRapimPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, NotulensiRapim $rapim): bool
    {
        if (! $rapim->is_rahasia) {
            return true;
        }

        if ($user->hasRole('admin') || $user->hasRole('sekretaris')) {
            return true;
        }

        if ($user->hasRole('cc_security')) {
            return false;
        }

        return $rapim->punyaAksesManual($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role->kode, ['admin', 'sekretaris']);
    }

    public function update(User $user, NotulensiRapim $rapim): bool
    {
        return in_array($user->role->kode, ['admin', 'sekretaris']);
    }

    public function delete(User $user, NotulensiRapim $rapim): bool
    {
        return in_array($user->role->kode, ['admin', 'sekretaris']);
    }

    public function assignAkses(User $user, NotulensiRapim $rapim): bool
    {
        return in_array($user->role->kode, ['admin', 'sekretaris']);
    }
}
