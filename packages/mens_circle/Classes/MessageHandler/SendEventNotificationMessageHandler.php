<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\MessageHandler;

use BeardCoder\MensCircle\Domain\Enum\RegistrationStatus;
use BeardCoder\MensCircle\Message\SendEventNotificationMessage;
use BeardCoder\MensCircle\Service\MailService;
use BeardCoder\MensCircle\Service\RegistrationNotificationDataService;
use BeardCoder\MensCircle\Service\SmsService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendEventNotificationMessageHandler
{
    public function __construct(
        private RegistrationNotificationDataService $notificationDataService,
        private MailService $mailService,
        private SmsService $smsService,
    ) {}

    public function __invoke(SendEventNotificationMessage $message): void
    {
        $notificationData = $this->notificationDataService->findByRegistrationUid($message->getRegistrationUid());
        if (!\is_array($notificationData)) {
            return;
        }

        if ($message->getType() === SendEventNotificationMessage::TYPE_REMINDER) {
            if (!\in_array($notificationData['status'], RegistrationStatus::activeValues(), true)) {
                return;
            }

            match ($message->getChannel()) {
                SendEventNotificationMessage::CHANNEL_EMAIL => $this->mailService->sendEventReminderFromData($notificationData, $message->getSettings()),
                SendEventNotificationMessage::CHANNEL_SMS => $this->smsService->sendReminder($notificationData, $message->getSettings()),
                default => null,
            };

            return;
        }

        match ($message->getChannel()) {
            SendEventNotificationMessage::CHANNEL_EMAIL => $this->mailService->sendEventRegistrationConfirmationFromData($notificationData, $message->getSettings()),
            SendEventNotificationMessage::CHANNEL_SMS => $this->smsService->sendRegistrationConfirmation($notificationData, $message->getSettings()),
            default => null,
        };
    }
}
