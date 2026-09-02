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

        if (!$klasifikasi->isRahasia()) {
            return true;
        }

        return $user->hasPermission('master.klasifikasi.index');
    }

    public function getVisibleKlasifikasi(User $user)
    {
        $query = Klasifikasi::query();

        if (!$user->hasPermission('master.klasifikasi.index')) {
            $query->where('is_rahasia', false)->orWhere('kode', 'UMUM');
        }

        return $query->get();
    }
}