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
        return true;
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
}
