<?php

use App\Support\StatusCache;

if (!function_exists('statusIds')) {
    function statusIds(): array
    {
        return StatusCache::ids();
    }
}

if (!function_exists('statusId')) {
    function statusId(string $kode): ?int
    {
        return StatusCache::ids()[$kode] ?? null;
    }
}

if (!function_exists('statusKodes')) {
    function statusKodes(): array
    {
        return StatusCache::kodes();
    }
}

if (!function_exists('statusKode')) {
    function statusKode(int $id): ?string
    {
        return StatusCache::kodes()[$id] ?? null;
    }
}

if (!function_exists('captchaQuestion')) {
    function captchaQuestion(): string
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        $op = ['+', '-', '×'][random_int(0, 2)];

        if ($op === '-' && $a < $b) {
            [$a, $b] = [$b, $a];
        }

        $result = match ($op) {
            '+' => $a + $b,
            '-' => $a - $b,
            '×' => $a * $b,
        };

        session()->put('captcha_answer', $result);

        return "{$a} {$op} {$b}";
    }
}

if (!function_exists('captchaCheck')) {
    function captchaCheck(mixed $value): bool
    {
        $answer = session()->pull('captcha_answer');

        return $answer !== null && is_numeric($value) && (int) $value === (int) $answer;
    }
}
