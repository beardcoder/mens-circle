<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Message;

final readonly class SendEventSmsMessage
{
    public const TYPE_REGISTRATION_CONFIRMATION = 'registration_confirmation';
    public const TYPE_REMINDER = 'reminder';

    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        private int $registrationUid,
        private string $type,
        private array $settings = []
    ) {}

    public function getRegistrationUid(): int
    {
        return $this->registrationUid;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
}
