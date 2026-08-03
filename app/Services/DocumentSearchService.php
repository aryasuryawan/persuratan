<?php

namespace App\Services;

use App\Models\KomitePersetujuan;
use App\Models\NotulensiRapim;
use App\Models\SuratKeluar;
use App\Models\SuratKeputusan;
use App\Models\SuratMasuk;
use App\Models\User;
use App\Support\IndonesianTextNormalizer;

class DocumentSearchService
{
    private const FUZZY_CANDIDATES = 100;

    public function __construct(
        private readonly SuratScopeService $suratScope,
        private readonly ArsipAksesService $arsipAkses,
    ) {}

    public function search(User $user, string $modul, string $keyword, int $limit = 5): array
    {
        $result = match ($modul) {
            'surat_masuk' => $this->searchSuratMasuk($user, $keyword, $limit),
            'surat_keluar' => $this->searchSuratKeluar($user, $keyword, $limit),
            'surat_keputusan' => $this->searchArsip($user, SuratKeputusan::class, $keyword, $limit),
            'komite_persetujuan' => $this->searchArsip($user, KomitePersetujuan::class, $keyword, $limit),
            'notulen_rapim' => $this->searchArsip($user, NotulensiRapim::class, $keyword, $limit),
            'all' => $this->searchAll($user, $keyword, $limit),
            default => ['found' => 0, 'results' => [], 'note' => 'Modul tidak dikenal.'],
        };

        if ($result['found'] > 0) {
            return $result;
        }

        $fuzzy = $this->searchFuzzy($user, $modul, $keyword, $limit);

        return $fuzzy['found'] > 0 ? $fuzzy : $result;
    }

    private function searchAll(User $user, string $keyword, int $limit): array
    {
        $searchByModul = [
            'surat_masuk' => fn () => $this->searchSuratMasuk($user, $keyword, $limit),
            'surat_keluar' => fn () => $this->searchSuratKeluar($user, $keyword, $limit),
            'surat_keputusan' => fn () => $this->searchArsip($user, SuratKeputusan::class, $keyword, $limit),
            'komite_persetujuan' => fn () => $this->searchArsip($user, KomitePersetujuan::class, $keyword, $limit),
            'notulen_rapim' => fn () => $this->searchArsip($user, NotulensiRapim::class, $keyword, $limit),
        ];

        $results = [];
        foreach ($searchByModul as $modul => $search) {
            $res = $search();
            foreach ($res['results'] as $result) {
                $result['modul'] = $modul;
                $results[] = $result;
            }
        }

        $results = array_slice($results, 0, $limit);

        return [
            'found' => count($results),
            'results' => $results,
            'modules' => array_keys($searchByModul),
            'note' => count($results) > 0 ? null : 'Tidak ditemukan dokumen yang cocok di semua modul.',
        ];
    }

    private function keywordTerms(string $keyword): array
    {
        return IndonesianTextNormalizer::searchTerms($keyword);
    }

    private function likePattern(string $term): string
    {
        return '%' . addcslashes($term, '%_') . '%';
    }

    private function keywordWhere(array $columns, string $keyword)
    {
        $terms = $this->keywordTerms($keyword);

        return function ($q) use ($columns, $terms) {
            if (empty($terms)) {
                $q->whereRaw('1 = 0');
                return;
            }

            foreach ($terms as $term) {
                $q->where(function ($w) use ($columns, $term) {
                    foreach ($columns as $col) {
                        $w->orWhere($col, 'like', $this->likePattern($term));
                    }
                });
            }
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

        $query->where($this->keywordWhere(
            ['nomor_agenda', 'nomor_surat', 'pengirim', 'perihal'],
            $keyword
        ));

        $rows = $query->latest('tanggal_diterima')->limit($limit)->get();

        return $this->format($rows, fn (SuratMasuk $s) => $this->formatSuratMasukRow($s));
    }

    private function searchSuratKeluar(User $user, string $keyword, int $limit): array
    {
        if (! $user->hasPermission('surat-keluar.index')) {
            return $this->noAccess();
        }

        $rows = SuratKeluar::where($this->keywordWhere(
            ['nomor_surat', 'perihal', 'tujuan_surat'],
            $keyword
        ))
            ->latest('tanggal_surat')
            ->limit($limit)
            ->get();

        return $this->format($rows, fn (SuratKeluar $s) => $this->formatSuratKeluarRow($s));
    }

    private function searchArsip(User $user, string $model, string $keyword, int $limit): array
    {
        $config = $this->arsipConfig($model);

        if (! $user->hasPermission($config['permission'])) {
            return $this->noAccess();
        }

        $query = $this->arsipAkses->scopeUntukUser($model::query(), $user);

        $query->where($this->keywordWhere($config['search_cols'], $keyword));

        $rows = $query->latest($config['date_col'])->limit($limit)->get();

        return $this->format($rows, fn ($row) => $this->formatArsipRow($row, $config));
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
                'konten_cols' => ['Ringkasan' => 'ringkasan', 'Status' => 'status', 'Kategori' => 'kategori'],
            ],
            KomitePersetujuan::class => [
                'permission' => 'komite-persetujuan.index',
                'search_cols' => ['judul', 'jenis', 'hasil'],
                'label_col' => 'judul',
                'judul_col' => 'judul',
                'date_col' => 'tanggal_rapat',
                'route' => 'komite-persetujuan.show',
                'snippet_cols' => ['judul', 'jenis', 'hasil'],
                'konten_cols' => ['Hasil' => 'hasil', 'Jenis' => 'jenis', 'Catatan Keputusan' => 'catatan_keputusan'],
            ],
            NotulensiRapim::class => [
                'permission' => 'notulensi-rapim.index',
                'search_cols' => ['judul_rapat', 'ringkasan_pembahasan'],
                'label_col' => 'judul_rapat',
                'judul_col' => 'judul_rapat',
                'date_col' => 'tanggal',
                'route' => 'notulensi-rapim.show',
                'snippet_cols' => ['judul_rapat', 'ringkasan_pembahasan'],
                'konten_cols' => ['Ringkasan Pembahasan' => 'ringkasan_pembahasan', 'Keputusan/Arahan' => 'keputusan_arahan'],
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

    private function formatSuratMasukRow(SuratMasuk $s): array
    {
        $konten = trim('Perihal: ' . $s->perihal . ' | Pengirim: ' . $s->pengirim
            . ($s->notes ? ' | Catatan: ' . $s->notes : ''));

        return [
            'label' => $s->nomor_agenda,
            'judul' => $s->perihal,
            'tanggal' => $s->tanggal_surat?->toDateString(),
            'url' => route('penerimaan-surat.show', $s->uuid),
            'snippet' => $this->snippet("{$s->nomor_agenda} - {$s->perihal} - Pengirim: {$s->pengirim}"),
            'konten' => $this->plainText($konten, 400),
        ];
    }

    private function formatSuratKeluarRow(SuratKeluar $s): array
    {
        $konten = trim('Perihal: ' . $s->perihal . ' | Tujuan: ' . $s->tujuan_surat);

        return [
            'label' => $s->nomor_surat,
            'judul' => $s->perihal,
            'tanggal' => $s->tanggal_surat?->toDateString(),
            'url' => route('surat-keluar.show', $s->uuid),
            'snippet' => $this->snippet("{$s->nomor_surat} - {$s->perihal} - Tujuan: {$s->tujuan_surat}"),
            'konten' => $this->plainText($konten, 400),
        ];
    }

    private function formatArsipRow($row, array $config): array
    {
        return [
            'label' => $row->{$config['label_col']},
            'judul' => $row->{$config['judul_col']},
            'tanggal' => $row->{$config['date_col']}?->toDateString(),
            'url' => route($config['route'], $row->uuid),
            'snippet' => $this->snippet($config['snippet_cols'] ? implode(' - ', array_map(
                fn ($col) => (string) $row->{$col},
                $config['snippet_cols']
            )) : ''),
            'konten' => $this->plainText($this->kontenFromConfig($row, $config), 400),
        ];
    }

    private function kontenFromConfig($row, array $config): string
    {
        $parts = [];
        foreach ($config['konten_cols'] ?? [] as $label => $col) {
            $value = $row->{$col};
            if ($value === null || $value === '') {
                continue;
            }
            $parts[] = $label . ': ' . (string) $value;
        }

        return implode(' | ', $parts);
    }

    private function searchFuzzy(User $user, string $modul, string $keyword, int $limit): array
    {
        if ($modul === 'all') {
            return $this->searchFuzzyAll($user, $keyword, $limit);
        }

        $config = $this->fuzzyConfig($modul);
        if ($config === null || ! $user->hasPermission($config['permission'])) {
            return $this->noAccess();
        }

        $terms = array_values(array_filter(
            IndonesianTextNormalizer::searchTerms($keyword),
            fn (string $t) => strlen($t) >= 4 || preg_match('/\d/', $t)
        ));

        if (empty($terms)) {
            return ['found' => 0, 'results' => [], 'note' => 'Tidak ditemukan dokumen yang cocok.'];
        }

        $rows = $config['scope']($user)
            ->latest($config['date'])
            ->limit(self::FUZZY_CANDIDATES)
            ->get();

        $matched = [];
        foreach ($rows as $row) {
            $haystack = implode(' ', array_map(fn ($c) => (string) $row->{$c}, $config['columns']));
            if (! $this->fuzzyMatch($haystack, $terms)) {
                continue;
            }
            $matched[] = $row;
            if (count($matched) >= $limit) {
                break;
            }
        }

        return $this->format($matched, $config['map']);
    }

    private function searchFuzzyAll(User $user, string $keyword, int $limit): array
    {
        $modules = ['surat_masuk', 'surat_keluar', 'surat_keputusan', 'komite_persetujuan', 'notulen_rapim'];

        $results = [];
        foreach ($modules as $modul) {
            $res = $this->searchFuzzy($user, $modul, $keyword, $limit);
            foreach ($res['results'] as $result) {
                $result['modul'] = $modul;
                $results[] = $result;
            }
        }

        $results = array_slice($results, 0, $limit);

        return [
            'found' => count($results),
            'results' => $results,
            'modules' => $modules,
            'note' => count($results) > 0 ? null : 'Tidak ditemukan dokumen yang cocok di semua modul.',
        ];
    }

    private function fuzzyConfig(string $modul): ?array
    {
        return match ($modul) {
            'surat_masuk' => [
                'permission' => 'surat-masuk.index',
                'scope' => fn (User $user) => $this->applyScopeSuratRestricted(
                    $this->suratScope->scopeForUser(SuratMasuk::query(), $user),
                    $user
                ),
                'columns' => ['nomor_agenda', 'nomor_surat', 'pengirim', 'perihal'],
                'date' => 'tanggal_diterima',
                'map' => fn ($row) => $this->formatSuratMasukRow($row),
            ],
            'surat_keluar' => [
                'permission' => 'surat-keluar.index',
                'scope' => fn (User $user) => SuratKeluar::query(),
                'columns' => ['nomor_surat', 'perihal', 'tujuan_surat'],
                'date' => 'tanggal_surat',
                'map' => fn ($row) => $this->formatSuratKeluarRow($row),
            ],
            'surat_keputusan' => $this->arsipFuzzyConfig(SuratKeputusan::class),
            'komite_persetujuan' => $this->arsipFuzzyConfig(KomitePersetujuan::class),
            'notulen_rapim' => $this->arsipFuzzyConfig(NotulensiRapim::class),
            default => null,
        };
    }

    private function arsipFuzzyConfig(string $model): array
    {
        $config = $this->arsipConfig($model);

        return [
            'permission' => $config['permission'],
            'scope' => fn (User $user) => $this->arsipAkses->scopeUntukUser($model::query(), $user),
            'columns' => $config['search_cols'],
            'date' => $config['date_col'],
            'map' => fn ($row) => $this->formatArsipRow($row, $config),
        ];
    }

    private function fuzzyMatch(string $haystack, array $terms): bool
    {
        $norm = IndonesianTextNormalizer::fuzzyNormalize($haystack);

        if (empty($terms)) {
            return false;
        }

        $allTermsMatched = true;

        foreach ($terms as $term) {
            $t = IndonesianTextNormalizer::fuzzyNormalize($term);
            if ($t === '' || str_contains($norm, $t)) {
                continue;
            }

            if (strlen($t) < 4 && !preg_match('/\d/', $t)) {
                $allTermsMatched = false;
                continue;
            }

            $len = strlen($t);
            $allowed = max(1, intdiv($len, 3));
            $found = false;

            for ($i = 0, $max = strlen($norm) - $len; $i <= $max; $i++) {
                if (levenshtein($t, substr($norm, $i, $len)) <= $allowed) {
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $allTermsMatched = false;
            }
        }

        if (!$allTermsMatched && count($terms) > 1) {
            $firstTerm = IndonesianTextNormalizer::fuzzyNormalize($terms[0]);
            if (strlen($firstTerm) >= 6 && strlen($firstTerm) <= 30) {
                foreach ($terms as $term) {
                    $t = IndonesianTextNormalizer::fuzzyNormalize($term);
                    if ($t !== $firstTerm && strlen($t) >= 4 && levenshtein($firstTerm, $t) <= 3) {
                        return true;
                    }
                }
            }
        }

        return $allTermsMatched;
    }

    private function format($rows, callable $mapper): array
    {
        $results = collect($rows)->map($mapper)->values()->all();

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

    private function plainText(string $text, int $max = 400): string
    {
        return $this->snippet(strip_tags($text), $max);
    }
}
