<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public static function clearCache(): void
    {
        Cache::forget('settings_all');

        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget('setting.'.$setting->key);
        }
    }

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }
}
