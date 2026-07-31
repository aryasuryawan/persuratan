<?php

namespace App\Policies;

use App\Models\KomitePersetujuan;
use App\Models\User;

class KomitePersetujuanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, KomitePersetujuan $komite): bool
    {
        if (! $komite->is_rahasia) {
            return true;
        }

        if ($user->hasRole('admin') || $user->hasRole('sekretaris')) {
            return true;
        }

        if ($user->hasRole('cc_security')) {
            return false;
        }

        return $komite->punyaAksesManual($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role->kode, ['admin', 'sekretaris']);
    }

    public function update(User $user, KomitePersetujuan $komite): bool
    {
        return in_array($user->role->kode, ['admin', 'sekretaris']);
    }

    public function delete(User $user, KomitePersetujuan $komite): bool
    {
        return in_array($user->role->kode, ['admin', 'sekretaris']);
    }

    public function assignAkses(User $user, KomitePersetujuan $komite): bool
    {
        return in_array($user->role->kode, ['admin', 'sekretaris']);
    }
}
