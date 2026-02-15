<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Message;

final readonly class SendNewsletterMessage
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        public string $toEmail,
        public string $toName,
        public string $subject,
        public string $content,
        public string $unsubscribeUrl,
        public array $settings,
    ) {}
}
