<?php

namespace App\Policies;

use App\Models\SuratMasuk;
use App\Models\User;

class SuratMasukPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('surat-masuk.index');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('surat-masuk.create');
    }

    public function update(User $user, SuratMasuk $suratMasuk): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('sekretaris')) {
            return true;
        }

        if ($user->hasRole('sekretaris')) {
            return $suratMasuk->disposisi()->whereNotNull('atas_nama_pimpinan_id')->whereExists(function ($sub) use ($user) {
                $sub->selectRaw(1)
                    ->from('sekretaris_pimpinan as sp')
                    ->whereRaw('sp.pimpinan_id = disposisi.atas_nama_pimpinan_id')
                    ->where('sp.sekretaris_id', $user->id)
                    ->where('sp.is_active', true);
            })->exists();
        }

        return $user->id === $suratMasuk->created_by;
    }

    public function view(User $user, SuratMasuk $suratMasuk): bool
    {
        if (! $user->hasPermission('surat-masuk.show')) {
            return false;
        }

        if ($suratMasuk->klasifikasi->kode === 'restricted') {
            if ($user->hasRole('admin')) {
                return true;
            }

            if ($suratMasuk->disposisi()->where('atas_nama_pimpinan_id', $user->id)->exists()) {
                return true;
            }

            if ($user->hasRole('sekretaris')) {
                if ($suratMasuk->disposisi()->whereNotNull('atas_nama_pimpinan_id')->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw(1)
                        ->from('disposisi as d3')
                        ->join('sekretaris_pimpinan as sp', 'sp.pimpinan_id', '=', 'd3.atas_nama_pimpinan_id')
                        ->whereRaw('d3.surat_masuk_id = disposisi.surat_masuk_id')
                        ->where('sp.sekretaris_id', $user->id)
                        ->where('sp.is_active', true);
                })->exists()) {
                    return true;
                }
            }

            if ($suratMasuk->punyaAksesManual($user)) {
                return true;
            }

            return false;
        }

        if ($suratMasuk->klasifikasi->is_rahasia && ! $user->hasRole('pimpinan') && ! $user->hasRole('admin') && ! $user->hasRole('sekretaris')) {
            return false;
        }

        return true;
    }

    public function verify(User $user): bool
    {
        return $user->hasPermission('surat-masuk.verify');
    }

    public function dispose(User $user): bool
    {
        return $user->hasPermission('disposisi.create');
    }

    public function assignAkses(User $user, SuratMasuk $suratMasuk): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($suratMasuk->disposisi()->where('atas_nama_pimpinan_id', $user->id)->exists()) {
            return true;
        }

        if ($user->hasRole('sekretaris')) {
            return $suratMasuk->disposisi()->whereNotNull('atas_nama_pimpinan_id')->where(function ($q) use ($user) {
                $q->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw(1)
                        ->from('sekretaris_pimpinan as sp')
                        ->whereRaw('sp.pimpinan_id = disposisi.atas_nama_pimpinan_id')
                        ->where('sp.sekretaris_id', $user->id)
                        ->where('sp.is_active', true);
                });
            })->exists();
        }

        return false;
    }
}