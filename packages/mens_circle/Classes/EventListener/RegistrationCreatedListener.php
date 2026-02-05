<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\EventListener;

use BeardCoder\MensCircle\Event\RegistrationCreatedEvent;
use BeardCoder\MensCircle\Service\EmailService;
use BeardCoder\MensCircle\Service\SmsService;
use TYPO3\CMS\Core\Attribute\AsEventListener;

#[AsEventListener(identifier: 'mens-circle/registration-created')]
final class RegistrationCreatedListener
{
    public function __construct(
        private readonly EmailService $emailService,
        private readonly SmsService $smsService,
    ) {}

    public function __invoke(RegistrationCreatedEvent $event): void
    {
        $registration = $event->getRegistration();

        $this->emailService->sendRegistrationConfirmation($registration);

        if ($event->shouldSendSms()) {
            $this->smsService->sendRegistrationConfirmation($registration);
        }
    }
}
