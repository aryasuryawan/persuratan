<?php

use App\Models\StatusSurat;

if (!function_exists('statusId')) {
    function statusId(string $kode): ?int
    {
        return StatusSurat::where('kode', $kode)->value('id');
    }
}

if (!function_exists('statusKode')) {
    function statusKode(int $id): ?string
    {
        return StatusSurat::where('id', $id)->value('kode');
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
