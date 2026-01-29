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
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }
}
