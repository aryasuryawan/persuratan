<?php

namespace App\Services;

use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class SuratScopeService
{
    public function scopeForUser(Builder $query, User $user, bool $ignoreFlag = false): Builder
    {
        $role = $user->role->kode;

        if ($role === 'admin') {
            return $query;
        }

        if ($ignoreFlag) {
            return $query->where(function ($q) use ($user, $role) {
                match ($role) {
                    'sekretaris' => null,
                    'pimpinan' => $q->whereHas('disposisi', fn($q) =>
                        $q->where('atas_nama_pimpinan_id', $user->id)
                    ),
                    'unit_lain' => $q->where(function ($q) use ($user) {
                        $q->whereHas('disposisi', fn($d) =>
                            $d->where('kepada_user_id', $user->id)
                        )->orWhereHas('aksesManual', fn($a) =>
                            $a->where('user_id', $user->id)
                        );
                    }),
                    'cc_security' => $q->where('created_by', $user->id),
                    default => $q->whereRaw('1=0'),
                };
            });
        }

        return $query->where(function ($q) use ($user, $role) {
            $q->where('hanya_pembuat_pimpinan', false);

            match ($role) {
                'sekretaris' => null,
                'pimpinan' => $q->whereHas('disposisi', fn($q) =>
                    $q->where('atas_nama_pimpinan_id', $user->id)
                ),
                'unit_lain' => $q->where(function ($q) use ($user) {
                    $q->whereHas('disposisi', fn($d) =>
                        $d->where('kepada_user_id', $user->id)
                    )->orWhereHas('aksesManual', fn($a) =>
                        $a->where('user_id', $user->id)
                    );
                }),
                'cc_security' => $q->where('created_by', $user->id),
                default => $q->whereRaw('1=0'),
            };

            $q->orWhere(function ($flagged) use ($user) {
                $flagged->where('hanya_pembuat_pimpinan', true)
                    ->where(function ($f) use ($user) {
                        $f->where('created_by', $user->id)
                            ->orWhere('tujuan_pimpinan_id', $user->id)
                            ->orWhereHas('disposisi', fn($d) =>
                                $d->where('atas_nama_pimpinan_id', $user->id)
                            )->orWhereHas('aksesManual', fn($a) =>
                                $a->where('user_id', $user->id)
                            );

                        if ($user->hasRole('sekretaris')) {
                            $f->orWhere(function ($sq) use ($user) {
                                $sq->whereExists(function ($exists) use ($user) {
                                    $exists->selectRaw(1)
                                        ->from('sekretaris_pimpinan as spf')
                                        ->whereRaw('spf.pimpinan_id = surat_masuk.tujuan_pimpinan_id')
                                        ->where('spf.sekretaris_id', $user->id)
                                        ->where('spf.is_active', true);
                                })->orWhereExists(function ($exists) use ($user) {
                                    $exists->selectRaw(1)
                                        ->from('disposisi as df')
                                        ->join('sekretaris_pimpinan as spf2', 'spf2.pimpinan_id', '=', 'df.atas_nama_pimpinan_id')
                                        ->whereRaw('df.surat_masuk_id = surat_masuk.id')
                                        ->where('spf2.sekretaris_id', $user->id)
                                        ->where('spf2.is_active', true);
                                });
                            });
                        }
                    });
            });
        });
    }

    public function scopeSuratKeluarForUser(Builder $query, User $user, bool $ignoreFlag = false): Builder
    {
        if ($user->hasRole('admin')) {
            return $query;
        }

        if ($ignoreFlag) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('hanya_pembuat_pimpinan', false)
                ->orWhere(function ($flagged) use ($user) {
                    $flagged->where('hanya_pembuat_pimpinan', true)
                        ->where(function ($f) use ($user) {
                            $f->where('created_by', $user->id)
                                ->orWhere('pimpinan_pengirim_id', $user->id);

                            if ($user->hasRole('sekretaris')) {
                                $f->orWhere(function ($sq) use ($user) {
                                    $sq->whereExists(function ($exists) use ($user) {
                                        $exists->selectRaw(1)
                                            ->from('sekretaris_pimpinan as spk')
                                            ->whereRaw('spk.pimpinan_id = surat_keluar.pimpinan_pengirim_id')
                                            ->where('spk.sekretaris_id', $user->id)
                                            ->where('spk.is_active', true);
                                    });
                                });
                            }
                        });
                });
        });
    }

    public function buildSuratMasukQuery(User $user, array $filters = []): Builder
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

        if (!empty($filters['periode_dari'])) {
            $query->whereDate('tanggal_diterima', '>=', $filters['periode_dari']);
        }
        if (!empty($filters['periode_sampai'])) {
            $query->whereDate('tanggal_diterima', '<=', $filters['periode_sampai']);
        }
        if (!empty($filters['inputer_id'])) {
            $query->where('created_by', $filters['inputer_id']);
        }

        return $query->latest('tanggal_diterima');
    }

    public function listSuratMasuk(User $user, array $filters = [])
    {
        return $this->buildSuratMasukQuery($user, $filters)->paginate(request('per_page', 25));
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
                         if ($user->hasRole('sekretaris')) {
                             $ra->orWhere('created_by', $user->id);
                         }

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