<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\Domain\Enum;

enum RegistrationStatus: string
{
    case Registered = 'registered';
    case Attended = 'attended';
    case Cancelled = 'cancelled';

    /**
     * @return list<string>
     */
    public static function activeValues(): array
    {
        return [self::Registered->value, self::Attended->value];
    }
}
