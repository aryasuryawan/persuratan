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
            'unit_lain' => $query->where(function ($q) use ($user) {
                $q->whereHas('disposisi', fn($d) =>
                    $d->where('kepada_user_id', $user->id)
                )->orWhereHas('aksesManual', fn($a) =>
                    $a->where('user_id', $user->id)
                );
            }),
            'cc_security' => $query->where('created_by', $user->id),
            default => $query->whereRaw('1=0'),
        };
    }

    public function listSuratMasuk(User $user, array $filters = [])
    {
        $query = SuratMasuk::with(['klasifikasi', 'status', 'pembuat', 'organisasiPengirim', 'topiks']);

        $query = $this->scopeForUser($query, $user);

        $query = $this->scopeSuratRestricted($query, $user);

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('nomor_agenda', 'like', "%{$s}%")
                  ->orWhere('nomor_surat', 'like', "%{$s}%")
                  ->orWhere('pengirim', 'like', "%{$s}%")
                  ->orWhere('perihal', 'like', "%{$s}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->whereHas('status', fn($q) => $q->where('kode', $filters['status']));
        }

        if (!empty($filters['klasifikasi_id'])) {
            $query->where('klasifikasi_id', $filters['klasifikasi_id']);
        }

        if (!empty($filters['topik_id'])) {
            $query->whereHas('topiks', fn($q) => $q->where('topik.id', $filters['topik_id']));
        }

        return $query->latest('tanggal_diterima')->paginate(request('per_page', 25));
    }

    private function scopeSuratRestricted(Builder $query, User $user): Builder
    {
        if ($user->hasRole('admin')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->whereHas('klasifikasi', fn($kl) => $kl->where('kode', '!=', 'TERBATAS'))
              ->orWhere(function ($qr) use ($user) {
                  $qr->whereHas('klasifikasi', fn($kl) => $kl->where('kode', 'TERBATAS'))
                     ->where(function ($ra) use ($user) {
                         $ra->orWhereHas('disposisi', fn($d) =>
                             $d->where('atas_nama_pimpinan_id', $user->id)
                         );

                         if ($user->hasRole('sekretaris')) {
                             $ra->orWhere(function ($sq) use ($user) {
                                 $sq->whereExists(function ($exists) use ($user) {
                                     $exists->selectRaw(1)
                                         ->from('disposisi as d3')
                                         ->join('sekretaris_pimpinan as sp', 'sp.pimpinan_id', '=', 'd3.atas_nama_pimpinan_id')
                                         ->whereRaw('d3.surat_masuk_id = surat_masuk.id')
                                         ->where('sp.sekretaris_id', $user->id)
                                         ->where('sp.is_active', true);
                                 });
                             });
                         }

                         if (! $user->hasRole('cc_security')) {
                             $ra->orWhereHas('aksesManual', fn($a) =>
                                 $a->where('user_id', $user->id)
                             );
                         }
                     });
              });
        });
    }
}