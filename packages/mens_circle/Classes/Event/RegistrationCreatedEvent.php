<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Event;

use BeardCoder\MensCircle\Domain\Model\Registration;

final class RegistrationCreatedEvent
{
    public function __construct(
        private readonly Registration $registration,
        private readonly bool $sendSms = false,
    ) {
    }

    public function getRegistration(): Registration
    {
        return $this->registration;
    }

    public function shouldSendSms(): bool
    {
        return $this->sendSms;
    }
}
