<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\Domain\Model;

use MarkusSommer\MensCircle\Domain\Enum\RegistrationStatus;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

final class Registration extends AbstractEntity
{
    protected ?Event $event = null;

    protected ?Participant $participant = null;

    protected string $status = RegistrationStatus::Registered->value;

    protected ?\DateTime $registeredAt = null;

    protected ?\DateTime $cancelledAt = null;

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): void
    {
        $this->event = $event;
    }

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(?Participant $participant): void
    {
        $this->participant = $participant;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string|RegistrationStatus $status): void
    {
        $this->status = $status instanceof RegistrationStatus ? $status->value : $status;
    }

    public function getRegisteredAt(): ?\DateTime
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(?\DateTime $registeredAt): void
    {
        $this->registeredAt = $registeredAt;
    }

    public function getCancelledAt(): ?\DateTime
    {
        return $this->cancelledAt;
    }

    public function setCancelledAt(?\DateTime $cancelledAt): void
    {
        $this->cancelledAt = $cancelledAt;
    }

    public function isActive(): bool
    {
        return in_array($this->status, RegistrationStatus::activeValues(), true);
    }
}
