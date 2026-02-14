<?php

declare(strict_types=1);

function env(string $key, mixed $default = null): string|int|float|bool|null
{
    $value = getenv($key);

    if ($value === false) {
        return $default;
    }

    if ($value === '') {
        return null;
    }

    return match (true) {
        $value === 'true', $value === 'TRUE' => true,
        $value === 'false', $value === 'FALSE' => false,
        $value === 'null', $value === 'NULL' => null,
        ctype_digit($value) => (int) $value,
        $value[0] === '-' && ctype_digit(substr($value, 1)) => (int) $value,
        is_numeric($value) => (float) $value,
        default => $value,
    };
}
