<?php

declare(strict_types=1);

namespace App\Traits;

trait HasEnumOptions
{
    /**
     * Get all enum options as value => label array.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case): array => [
                $case->value => $case->getLabel()
            ])
            ->toArray();
    }
}
