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

        return $user->can('master.klasifikasi.index');
    }

    public function getVisibleKlasifikasi(User $user)
    {
        $query = Klasifikasi::query();

        if (!$user->can('master.klasifikasi.index')) {
            $query->where('is_rahasia', false);
        }

        return $query->get();
    }
}