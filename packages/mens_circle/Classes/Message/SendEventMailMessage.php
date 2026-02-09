<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\Message;

final readonly class SendEventMailMessage
{
    public const TYPE_REGISTRATION_CONFIRMATION = 'registration_confirmation';
    public const TYPE_REMINDER = 'reminder';

    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        public int $registrationUid,
        public string $type,
        public array $settings = []
    ) {
    }
}
