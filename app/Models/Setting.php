<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = ['key', 'label', 'value', 'type'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            return Setting::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function getArray(string $key, ?array $default = null): ?array
    {
        $value = self::get($key);
        if ($value === null) {
            return $default;
        }
        try {
            return json_decode($value, true) ?? $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    public static function set(string $key, ?string $value): void
    {
        Setting::where('key', $key)->update(['value' => $value]);
        Cache::forget("setting_{$key}");
    }
}
