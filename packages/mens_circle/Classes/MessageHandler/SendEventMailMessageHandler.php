<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\MessageHandler;

use BeardCoder\MensCircle\Domain\Enum\RegistrationStatus;
use BeardCoder\MensCircle\Message\SendEventMailMessage;
use BeardCoder\MensCircle\Service\MailService;
use BeardCoder\MensCircle\Service\RegistrationNotificationDataService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendEventMailMessageHandler
{
    public function __construct(
        private RegistrationNotificationDataService $notificationDataService,
        private MailService $mailService,
    ) {}

    public function __invoke(SendEventMailMessage $message): void
    {
        $notificationData = $this->notificationDataService->findByRegistrationUid($message->getRegistrationUid());
        if (!\is_array($notificationData)) {
            return;
        }

        if ($message->getType() === SendEventMailMessage::TYPE_REMINDER) {
            if (!\in_array((string)($notificationData['status'] ?? ''), RegistrationStatus::activeValues(), true)) {
                return;
            }

            $this->mailService->sendEventReminderFromData($notificationData, $message->getSettings());

            return;
        }

        $this->mailService->sendEventRegistrationConfirmationFromData($notificationData, $message->getSettings());
    }
}
