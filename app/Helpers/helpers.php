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
