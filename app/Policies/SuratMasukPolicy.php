<?php

namespace App\Policies;

use App\Models\SuratMasuk;
use App\Models\User;

class SuratMasukPolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role->kode, ['sekretaris', 'admin', 'cc_security', 'unit_lain']);
    }

    public function view(User $user, SuratMasuk $suratMasuk): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('sekretaris')) {
            return true;
        }

        if ($suratMasuk->klasifikasi->is_rahasia && !$user->hasRole('pimpinan')) {
            return false;
        }

        return true;
    }

    public function verify(User $user): bool
    {
        return in_array($user->role->kode, ['sekretaris', 'admin']);
    }

    public function dispose(User $user): bool
    {
        return in_array($user->role->kode, ['sekretaris', 'pimpinan', 'admin']);
    }
}
