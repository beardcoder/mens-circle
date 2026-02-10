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
        return array_column(
            array_map(
                static fn (self $case): array => [
'value' => $case->value,
'label' => $case->getLabel()
],
                self::cases(),
            ),
            'label',
            'value',
        );
    }
}
