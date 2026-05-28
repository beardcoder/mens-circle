<?php

declare(strict_types=1);

namespace App\Filament\Support;

use Illuminate\Support\Str;

final class Anchor
{
    /**
     * Normalise anchor input: strip whitespace and a leading "#", then
     * slugify so values stay consistent between PageResource (writer) and
     * NavigationItemResource (reader). Empty input returns null.
     */
    public static function normalise(?string $state): ?string
    {
        if ($state === null) {
            return null;
        }

        $value = ltrim(trim($state), '#');

        return $value === '' ? null : Str::slug($value);
    }
}
