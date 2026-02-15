<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\MessageHandler;

use BeardCoder\MensCircle\Message\SendNewsletterMessage;
use BeardCoder\MensCircle\Service\MailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendNewsletterMessageHandler
{
    public function __construct(
        private MailService $mailService,
    ) {}

    public function __invoke(SendNewsletterMessage $message): void
    {
        $this->mailService->sendNewsletterBroadcast(
            $message->toEmail,
            $message->toName,
            $message->subject,
            $message->content,
            $message->unsubscribeUrl,
            $message->settings,
        );
    }
}
