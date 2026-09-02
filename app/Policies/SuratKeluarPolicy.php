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
        if (! $user->hasPermission('surat-keluar.show')) {
            return false;
        }

        if ($suratKeluar->hanya_pembuat_pimpinan) {
            if ($user->hasRole('admin') || $suratKeluar->created_by === $user->id) {
                return true;
            }

            if ($suratKeluar->pimpinan_pengirim_id === $user->id) {
                return true;
            }

            return $user->hasRole('sekretaris') && $user->servesPimpinan($suratKeluar->pimpinan_pengirim_id);
        }

        return true;
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
        return $user->isCreatorOrAdmin($suratKeluar->created_by);
    }
}