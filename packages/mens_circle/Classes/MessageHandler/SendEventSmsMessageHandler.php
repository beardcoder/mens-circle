<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\MessageHandler;

use MarkusSommer\MensCircle\Domain\Enum\RegistrationStatus;
use MarkusSommer\MensCircle\Message\SendEventSmsMessage;
use MarkusSommer\MensCircle\Service\RegistrationNotificationDataService;
use MarkusSommer\MensCircle\Service\SmsService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendEventSmsMessageHandler
{
    public function __construct(
        private RegistrationNotificationDataService $notificationDataService,
        private SmsService $smsService
    ) {
    }

    public function __invoke(SendEventSmsMessage $message): void
    {
        $notificationData = $this->notificationDataService->findByRegistrationUid($message->registrationUid);
        if (!is_array($notificationData)) {
            return;
        }

        if ($message->type === SendEventSmsMessage::TYPE_REMINDER) {
            if (!in_array((string) ($notificationData['status'] ?? ''), RegistrationStatus::activeValues(), true)) {
                return;
            }

            $this->smsService->sendReminder($notificationData, $message->settings);

            return;
        }

        $this->smsService->sendRegistrationConfirmation($notificationData, $message->settings);
    }
}
