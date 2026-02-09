<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\MessageHandler;

use MarkusSommer\MensCircle\Domain\Enum\RegistrationStatus;
use MarkusSommer\MensCircle\Message\SendEventMailMessage;
use MarkusSommer\MensCircle\Service\MailService;
use MarkusSommer\MensCircle\Service\RegistrationNotificationDataService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendEventMailMessageHandler
{
    public function __construct(
        private RegistrationNotificationDataService $notificationDataService,
        private MailService $mailService
    ) {
    }

    public function __invoke(SendEventMailMessage $message): void
    {
        $notificationData = $this->notificationDataService->findByRegistrationUid($message->getRegistrationUid());
        if (!is_array($notificationData)) {
            return;
        }

        if ($message->getType() === SendEventMailMessage::TYPE_REMINDER) {
            if (!in_array((string) ($notificationData['status'] ?? ''), RegistrationStatus::activeValues(), true)) {
                return;
            }

            $this->mailService->sendEventReminderFromData($notificationData, $message->getSettings());

            return;
        }

        $this->mailService->sendEventRegistrationConfirmationFromData($notificationData, $message->getSettings());
    }
}
