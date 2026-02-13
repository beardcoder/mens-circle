<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\MessageHandler;

use BeardCoder\MensCircle\Domain\Enum\RegistrationStatus;
use BeardCoder\MensCircle\Message\SendEventSmsMessage;
use BeardCoder\MensCircle\Service\RegistrationNotificationDataService;
use BeardCoder\MensCircle\Service\SmsService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendEventSmsMessageHandler
{
    public function __construct(
        private RegistrationNotificationDataService $notificationDataService,
        private SmsService $smsService,
    ) {}

    public function __invoke(SendEventSmsMessage $message): void
    {
        $notificationData = $this->notificationDataService->findByRegistrationUid($message->getRegistrationUid());
        if (!\is_array($notificationData)) {
            return;
        }

        if ($message->getType() === SendEventSmsMessage::TYPE_REMINDER) {
            if (!\in_array((string)($notificationData['status'] ?? ''), RegistrationStatus::activeValues(), true)) {
                return;
            }

            $this->smsService->sendReminder($notificationData, $message->getSettings());

            return;
        }

        $this->smsService->sendRegistrationConfirmation($notificationData, $message->getSettings());
    }
}
