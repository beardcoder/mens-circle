<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Model;

use BeardCoder\MensCircle\Domain\Enum\RegistrationStatus;
use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

final class Registration extends AbstractEntity
{
    public ?Event $event = null;

    public ?Participant $participant = null;

    public string $status = RegistrationStatus::Registered->value;

    public ?DateTime $registeredAt = null;

    public ?DateTime $cancelledAt = null;

    public function setStatusEnum(RegistrationStatus $status): void
    {
        $this->status = $status->value;
    }

    public function getStatusEnum(): RegistrationStatus
    {
        return RegistrationStatus::from($this->status);
    }

    public function isActive(): bool
    {
        return \in_array($this->status, RegistrationStatus::activeValues(), true);
    }
}
