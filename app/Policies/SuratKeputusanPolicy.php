<?php

namespace App\Policies;

use App\Models\SuratKeputusan;
use App\Models\User;

class SuratKeputusanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('surat-keputusan.index');
    }

    public function view(User $user, SuratKeputusan $suratKeputusan): bool
    {
        if (! $suratKeputusan->is_rahasia) {
            return $user->hasPermission('surat-keputusan.show');
        }

        if ($user->hasRole('admin') || $user->hasRole('sekretaris')) {
            return true;
        }

        return $suratKeputusan->punyaAksesManual($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('surat-keputusan.create');
    }

    public function update(User $user, SuratKeputusan $suratKeputusan): bool
    {
        return $user->hasPermission('surat-keputusan.edit');
    }

    public function delete(User $user, SuratKeputusan $suratKeputusan): bool
    {
        return $user->isCreatorOrAdmin($suratKeputusan->dicatat_oleh_id);
    }

    public function assignAkses(User $user, SuratKeputusan $suratKeputusan): bool
    {
        return $user->hasPermission('surat-keputusan.assign-akses');
    }
}