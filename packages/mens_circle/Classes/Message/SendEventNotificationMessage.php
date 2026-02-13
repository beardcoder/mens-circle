<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Message;

final readonly class SendEventNotificationMessage
{
    public const TYPE_REGISTRATION_CONFIRMATION = 'registration_confirmation';
    public const TYPE_REMINDER = 'reminder';

    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_SMS = 'sms';

    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        private int $registrationUid,
        private string $type,
        private string $channel,
        private array $settings = [],
    ) {}

    public function getRegistrationUid(): int
    {
        return $this->registrationUid;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
}
