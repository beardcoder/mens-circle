<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\MessageHandler;

use MarkusSommer\MensCircle\Message\SendNewsletterMessage;
use MarkusSommer\MensCircle\Service\MailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendNewsletterMessageHandler
{
    public function __construct(
        private MailService $mailService
    ) {
    }

    public function __invoke(SendNewsletterMessage $message): void
    {
        $this->mailService->sendNewsletterBroadcast(
            $message->getToEmail(),
            $message->getToName(),
            $message->getSubject(),
            $message->getContent(),
            $message->getUnsubscribeUrl(),
            $message->getSettings()
        );
    }
}
