<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Message;

final readonly class SendNewsletterMessage
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        private string $toEmail,
        private string $toName,
        private string $subject,
        private string $content,
        private string $unsubscribeUrl,
        private array $settings,
    ) {}

    public function getToEmail(): string
    {
        return $this->toEmail;
    }

    public function getToName(): string
    {
        return $this->toName;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getUnsubscribeUrl(): string
    {
        return $this->unsubscribeUrl;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
}
