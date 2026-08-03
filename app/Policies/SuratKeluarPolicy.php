<?php

namespace App\Policies;

use App\Models\SuratKeluar;
use App\Models\User;

class SuratKeluarPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('surat-keluar.index');
    }

    public function view(User $user, SuratKeluar $suratKeluar): bool
    {
        return $user->hasPermission('surat-keluar.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('surat-keluar.create');
    }

    public function update(User $user, SuratKeluar $suratKeluar): bool
    {
        return $user->hasPermission('surat-keluar.edit');
    }

    public function delete(User $user, SuratKeluar $suratKeluar): bool
    {
        return $user->hasPermission('surat-keluar.delete');
    }
}