<?php

namespace App\Support;

class IndonesianTextNormalizer
{
    /**
     * Singkatan/tulisan gaul yang umum dipakai user -> bentuk baku.
     * Diterapkan per-kata sebelum pencarian agar typo tidak memutus match.
     */
    private const SLANG = [
        'utk' => 'untuk',
        'untk' => 'untuk',
        'untuk' => 'untuk',
        'utuk' => 'untuk',
        'bwt' => 'untuk',
        'buat' => 'untuk',
        'klau' => 'kalau',
        'klw' => 'kalau',
        'klao' => 'kalau',
        'kalao' => 'kalau',
        'kalo' => 'kalau',
        'klu' => 'kalau',
        'yg' => 'yang',
        'y' => 'yang',
        'udh' => 'sudah',
        'sdh' => 'sudah',
        'ydh' => 'sudah',
        'blm' => 'belum',
        'msh' => 'masih',
        'dgn' => 'dengan',
        'dng' => 'dengan',
        'dg' => 'dengan',
        'dlm' => 'dalam',
        'dri' => 'dari',
        'ttg' => 'tentang',
        'krn' => 'karena',
        'karna' => 'karena',
        'gmn' => 'bagaimana',
        'gmna' => 'bagaimana',
        'bgmn' => 'bagaimana',
        'jgn' => 'jangan',
        'sya' => 'saya',
        'aq' => 'saya',
        'kmu' => 'kamu',
        'spy' => 'supaya',
        'biar' => 'supaya',
        'bgt' => 'banget',
        'sgt' => 'sangat',
        'bkn' => 'bukan',
        'jga' => 'juga',
        'trs' => 'terus',
        'skrg' => 'sekarang',
        'skrng' => 'sekarang',
        'brp' => 'berapa',
        'dkt' => 'dekat',
        'pgn' => 'pengen',
        'pngn' => 'pengen',
        'makasih' => 'terima kasih',
    ];

    /**
     * Kata penghubung/tanya/perorangan yang tidak menambah nilai pencarian
     * metadata surat. Jika kata kunci cuma berisi kata-kata ini, pencarian
     * dikosongkan (bukan query AND yang pasti gagal).
     */
    private const STOPWORDS = [
        'untuk', 'kalau', 'kalao', 'yang', 'dengan', 'dari', 'dan', 'atau', 'pada', 'di',
        'ke', 'itu', 'ini', 'ada', 'adalah', 'yaitu', 'yakni', 'tentang', 'karena', 'dalam',
        'agar', 'supaya', 'bahwa', 'saya', 'aku', 'kamu', 'anda', 'kita', 'kami', 'mereka',
        'siapa', 'apa', 'apakah', 'berapa', 'mana', 'dimana', 'dimanakah', 'kapan', 'bagaimana',
        'kenapa', 'mengapa', 'mohon', 'tolong', 'bisa', 'dapat', 'mau', 'ingin', 'sih', 'kok',
        'deh', 'dong', 'ya', 'kah', 'lah', 'pun', 'juga', 'saja', 'semua', 'masing', 'misal',
        'misalnya', 'contoh', 'contohnya', 'dll', 'dst', 'dkk', 'cari', 'carikan', 'mencari',
        'minta', 'mohonkan', 'kak', 'mas', 'mbak', 'pak', 'bu', 'bapak', 'ibu', 'min', 'admin',
        'halo', 'hai', 'om',
    ];

    public static function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');

        $words = preg_split('/\s+/u', $text);

        $mapped = [];
        foreach ($words as $word) {
            if ($word === '') {
                continue;
            }
            $mapped[] = self::SLANG[$word] ?? $word;
        }

        return implode(' ', $mapped);
    }

    public static function normalizeTerm(string $term): string
    {
        $term = self::stripEdgePunctuation($term);

        return self::SLANG[$term] ?? $term;
    }

    /**
     * Pecah keyword jadi daftar istilah pencarian: kata baku (slang sudah
     * diterjemahkan), stopword dibuang, tanda baca tepi dibersihkan.
     */
    public static function searchTerms(string $keyword): array
    {
        $words = preg_split('/\s+/u', trim($keyword));

        $terms = [];
        foreach ($words as $word) {
            $term = self::normalizeTerm($word);
            if ($term === '' || in_array($term, self::STOPWORDS, true)) {
                continue;
            }
            $terms[] = $term;
        }

        return array_values(array_unique($terms));
    }

    /**
     * Normalisasi penuh untuk perbandingan fuzzy: huruf kecil + slang baku +
     * semua selain huruf/angka dihapus (spasi ikut hilang sehingga
     * 'kerjasama' bisa cocok dengan 'kerja sama').
     */
    public static function fuzzyNormalize(string $text): string
    {
        $text = self::normalize($text);

        return preg_replace('/[^\p{L}\p{N}]+/u', '', $text);
    }

    public static function isStopword(string $word): bool
    {
        return in_array($word, self::STOPWORDS, true);
    }

    private static function stripEdgePunctuation(string $word): string
    {
        return mb_strtolower(trim($word, " \t\n\r\0\x0B\"'\.,;:!?()[]{}"), 'UTF-8');
    }
}
