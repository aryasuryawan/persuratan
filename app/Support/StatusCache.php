<?php

namespace App\Support;

use App\Models\StatusSurat;

class StatusCache
{
    public static ?array $ids = null;

    public static ?array $kodes = null;

    public static function ids(): array
    {
        if (self::$ids === null) {
            self::$ids = StatusSurat::pluck('id', 'kode')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return self::$ids;
    }

    public static function kodes(): array
    {
        if (self::$kodes === null) {
            self::$kodes = StatusSurat::pluck('kode', 'id')->all();
        }

        return self::$kodes;
    }

    public static function flush(): void
    {
        self::$ids = null;
        self::$kodes = null;
    }
}
