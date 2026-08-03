<?php

namespace App\Services;

use App\Models\KomitePersetujuan;
use App\Models\NotulensiRapim;
use App\Models\SuratKeluar;
use App\Models\SuratKeputusan;
use App\Models\SuratMasuk;
use App\Models\User;

class DocumentSearchService
{
    public function __construct(
        private readonly SuratScopeService $suratScope,
        private readonly ArsipAksesService $arsipAkses,
    ) {}

    public function search(User $user, string $modul, string $keyword, int $limit = 5): array
    {
        return match ($modul) {
            'surat_masuk' => $this->searchSuratMasuk($user, $keyword, $limit),
            'surat_keluar' => $this->searchSuratKeluar($user, $keyword, $limit),
            'surat_keputusan' => $this->searchArsip($user, SuratKeputusan::class, $keyword, $limit),
            'komite_persetujuan' => $this->searchArsip($user, KomitePersetujuan::class, $keyword, $limit),
            'notulen_rapim' => $this->searchArsip($user, NotulensiRapim::class, $keyword, $limit),
            default => ['found' => 0, 'results' => [], 'note' => 'Modul tidak dikenal.'],
        };
    }

    private function searchSuratMasuk(User $user, string $keyword, int $limit): array
    {
        if (! $user->hasPermission('surat-masuk.index')) {
            return $this->noAccess();
        }

        $query = SuratMasuk::with('klasifikasi');
        $query = $this->suratScope->scopeForUser($query, $user);
        $query = $this->applyScopeSuratRestricted($query, $user);

        $query->where(function ($q) use ($keyword) {
            $q->where('nomor_agenda', 'like', "%{$keyword}%")
              ->orWhere('nomor_surat', 'like', "%{$keyword}%")
              ->orWhere('pengirim', 'like', "%{$keyword}%")
              ->orWhere('perihal', 'like', "%{$keyword}%");
        });

        $rows = $query->latest('tanggal_diterima')->limit($limit)->get();

        return $this->format(
            $rows,
            fn (SuratMasuk $s) => [
                'label' => $s->nomor_agenda,
                'judul' => $s->perihal,
                'tanggal' => $s->tanggal_surat?->toDateString(),
                'url' => route('penerimaan-surat.show', $s->uuid),
                'snippet' => $this->snippet("{$s->nomor_agenda} - {$s->perihal} - Pengirim: {$s->pengirim}"),
            ]
        );
    }

    private function searchSuratKeluar(User $user, string $keyword, int $limit): array
    {
        if (! $user->hasPermission('surat-keluar.index')) {
            return $this->noAccess();
        }

        $rows = SuratKeluar::where(function ($q) use ($keyword) {
            $q->where('nomor_surat', 'like', "%{$keyword}%")
              ->orWhere('perihal', 'like', "%{$keyword}%")
              ->orWhere('tujuan_surat', 'like', "%{$keyword}%");
        })
            ->latest('tanggal_surat')
            ->limit($limit)
            ->get();

        return $this->format(
            $rows,
            fn (SuratKeluar $s) => [
                'label' => $s->nomor_surat,
                'judul' => $s->perihal,
                'tanggal' => $s->tanggal_surat?->toDateString(),
                'url' => route('surat-keluar.show', $s->uuid),
                'snippet' => $this->snippet("{$s->nomor_surat} - {$s->perihal} - Tujuan: {$s->tujuan_surat}"),
            ]
        );
    }

    private function searchArsip(User $user, string $model, string $keyword, int $limit): array
    {
        $config = $this->arsipConfig($model);

        if (! $user->hasPermission($config['permission'])) {
            return $this->noAccess();
        }

        $query = $this->arsipAkses->scopeUntukUser($model::query(), $user);

        $query->where(function ($q) use ($keyword, $config) {
            foreach ($config['search_cols'] as $col) {
                $q->orWhere($col, 'like', "%{$keyword}%");
            }
        });

        $rows = $query->latest($config['date_col'])->limit($limit)->get();

        return $this->format(
            $rows,
            fn ($row) => [
                'label' => $row->{$config['label_col']},
                'judul' => $row->{$config['judul_col']},
                'tanggal' => $row->{$config['date_col']}?->toDateString(),
                'url' => route($config['route'], $row->uuid),
                'snippet' => $this->snippet($config['snippet_cols'] ? implode(' - ', array_map(
                    fn ($col) => (string) $row->{$col},
                    $config['snippet_cols']
                )) : ''),
            ]
        );
    }

    private function arsipConfig(string $model): array
    {
        return match ($model) {
            SuratKeputusan::class => [
                'permission' => 'surat-keputusan.index',
                'search_cols' => ['nomor_sk', 'perihal', 'kategori'],
                'label_col' => 'nomor_sk',
                'judul_col' => 'perihal',
                'date_col' => 'tanggal_sk',
                'route' => 'surat-keputusan.show',
                'snippet_cols' => ['nomor_sk', 'perihal', 'kategori'],
            ],
            KomitePersetujuan::class => [
                'permission' => 'komite-persetujuan.index',
                'search_cols' => ['judul', 'jenis', 'hasil'],
                'label_col' => 'judul',
                'judul_col' => 'judul',
                'date_col' => 'tanggal_rapat',
                'route' => 'komite-persetujuan.show',
                'snippet_cols' => ['judul', 'jenis', 'hasil'],
            ],
            NotulensiRapim::class => [
                'permission' => 'notulensi-rapim.index',
                'search_cols' => ['judul_rapat', 'ringkasan_pembahasan'],
                'label_col' => 'judul_rapat',
                'judul_col' => 'judul_rapat',
                'date_col' => 'tanggal',
                'route' => 'notulensi-rapim.show',
                'snippet_cols' => ['judul_rapat', 'ringkasan_pembahasan'],
            ],
            default => throw new \InvalidArgumentException("Modul arsip tidak didukung: {$model}"),
        };
    }

    private function applyScopeSuratRestricted($query, User $user)
    {
        if ($user->hasRole('admin')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->whereHas('klasifikasi', fn ($kl) => $kl->where('kode', '!=', 'restricted'))
              ->orWhere(function ($qr) use ($user) {
                  $qr->whereHas('klasifikasi', fn ($kl) => $kl->where('kode', 'restricted'))
                     ->where(function ($ra) use ($user) {
                         $ra->orWhereHas('disposisi', fn ($d) =>
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
                             $ra->orWhereHas('aksesManual', fn ($a) =>
                                 $a->where('user_id', $user->id)
                             );
                         }
                     });
              });
        });
    }

    private function format($rows, callable $mapper): array
    {
        $results = $rows->map($mapper)->values()->all();

        return [
            'found' => count($results),
            'results' => $results,
            'note' => count($results) > 0 ? null : 'Tidak ditemukan dokumen yang cocok.',
        ];
    }

    private function noAccess(): array
    {
        return [
            'found' => 0,
            'results' => [],
            'note' => 'Anda tidak memiliki akses ke modul ini.',
        ];
    }

    private function snippet(string $text, int $max = 150): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $max)) . '...';
    }
}
