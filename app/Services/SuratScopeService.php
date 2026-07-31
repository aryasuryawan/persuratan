<?php

namespace App\Services;

use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class SuratScopeService
{
    public function scopeForUser(Builder $query, User $user): Builder
    {
        $role = $user->role->kode;

        return match ($role) {
            'admin' => $query,
            'sekretaris' => $query,
            'pimpinan' => $query->whereHas('disposisi', fn($q) =>
                $q->where('atas_nama_pimpinan_id', $user->id)
            ),
            'unit_lain' => $query->whereHas('disposisi', fn($q) =>
                $q->where('kepada_user_id', $user->id)
            ),
            'cc_security' => $query->where('created_by', $user->id),
            default => $query->whereRaw('1=0'),
        };
    }

    public function listSuratMasuk(User $user, array $filters = [])
    {
        $query = SuratMasuk::with(['klasifikasi', 'status', 'pembuat', 'organisasiPengirim']);

        $query = $this->scopeForUser($query, $user);

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('nomor_agenda', 'like', "%{$s}%")
                  ->orWhere('pengirim', 'like', "%{$s}%")
                  ->orWhere('perihal', 'like', "%{$s}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->whereHas('status', fn($q) => $q->where('kode', $filters['status']));
        }

        return $query->latest('tanggal_diterima')->paginate(request('per_page', 25));
    }
}
