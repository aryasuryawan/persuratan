<?php

namespace App\Services;

use App\Models\Klasifikasi;
use App\Models\User;

class AksesKlasifikasiService
{
    public function canAccess(User $user, Klasifikasi $klasifikasi): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (!$klasifikasi->is_rahasia) {
            return true;
        }

        return in_array($user->role->kode, ['sekretaris', 'pimpinan']);
    }

    public function getVisibleKlasifikasi(User $user)
    {
        $query = Klasifikasi::query();

        if (!$user->hasRole('admin') && !$user->hasRole('sekretaris') && !$user->hasRole('pimpinan')) {
            $query->where('is_rahasia', false);
        }

        return $query->get();
    }
}
