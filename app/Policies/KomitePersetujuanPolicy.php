<?php

namespace App\Policies;

use App\Models\KomitePersetujuan;
use App\Models\User;

class KomitePersetujuanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('komite-persetujuan.index');
    }

    public function view(User $user, KomitePersetujuan $komite): bool
    {
        if (! $komite->is_rahasia) {
            return $user->hasPermission('komite-persetujuan.show');
        }

        if ($user->hasRole('admin') || $user->hasRole('sekretaris')) {
            return true;
        }

        return $komite->punyaAksesManual($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('komite-persetujuan.create');
    }

    public function update(User $user, KomitePersetujuan $komite): bool
    {
        return $user->hasPermission('komite-persetujuan.edit');
    }

    public function delete(User $user, KomitePersetujuan $komite): bool
    {
        return $user->hasPermission('komite-persetujuan.delete');
    }

    public function assignAkses(User $user, KomitePersetujuan $komite): bool
    {
        return $user->hasPermission('komite-persetujuan.assign-akses');
    }
}