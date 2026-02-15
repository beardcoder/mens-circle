<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\MessageHandler;

use BeardCoder\MensCircle\Domain\Enum\NotificationChannel;
use BeardCoder\MensCircle\Domain\Enum\NotificationType;
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
        $notificationData = $this->notificationDataService->findByRegistrationUid($message->registrationUid);
        if (!\is_array($notificationData)) {
            return;
        }

        if ($message->type === NotificationType::Reminder) {
            if (!\in_array($notificationData['status'], RegistrationStatus::activeValues(), true)) {
                return;
            }

            match ($message->channel) {
                NotificationChannel::Email => $this->mailService->sendEventReminderFromData($notificationData, $message->settings),
                NotificationChannel::Sms => $this->smsService->sendReminder($notificationData, $message->settings),
            };

            return;
        }

        match ($message->channel) {
            NotificationChannel::Email => $this->mailService->sendEventRegistrationConfirmationFromData($notificationData, $message->settings),
            NotificationChannel::Sms => $this->smsService->sendRegistrationConfirmation($notificationData, $message->settings),
        };
    }
}
