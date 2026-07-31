<?php

namespace App\Policies;

use App\Models\SuratKeputusan;
use App\Models\User;

class SuratKeputusanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SuratKeputusan $suratKeputusan): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role->kode, ['admin', 'sekretaris']);
    }

    public function update(User $user, SuratKeputusan $suratKeputusan): bool
    {
        return in_array($user->role->kode, ['admin', 'sekretaris']);
    }

    public function delete(User $user, SuratKeputusan $suratKeputusan): bool
    {
        return in_array($user->role->kode, ['admin', 'sekretaris']);
    }
}
