<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Registration represents a link between a Participant and an Event
 */
class Registration extends AbstractEntity
{
    protected ?Event $event = null;
    protected ?Participant $participant = null;
    protected bool $isConfirmed = false;
    protected string $confirmationToken = '';
    protected string $notes = '';
    protected ?\DateTimeInterface $createdAt = null;
    protected ?\DateTimeInterface $confirmedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->confirmationToken = bin2hex(\random_bytes(32));
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(?Participant $participant): self
    {
        $this->participant = $participant;

        return $this;
    }

    // Convenience methods to access participant data
    public function getFirstName(): string
    {
        return $this->participant?->getFirstName() ?? '';
    }

    public function getLastName(): string
    {
        return $this->participant?->getLastName() ?? '';
    }

    public function getFullName(): string
    {
        return $this->participant?->getFullName() ?? '';
    }

    public function getEmail(): string
    {
        return $this->participant?->getEmail() ?? '';
    }

    public function getPhone(): string
    {
        return $this->participant?->getPhone() ?? '';
    }

    public function isConfirmed(): bool
    {
        return $this->isConfirmed;
    }

    public function setIsConfirmed(bool $isConfirmed): self
    {
        $this->isConfirmed = $isConfirmed;
        if ($isConfirmed && $this->confirmedAt === null) {
            $this->confirmedAt = new \DateTime();
        }

        return $this;
    }

    public function confirm(): void
    {
        $this->setIsConfirmed(true);
    }

    public function getConfirmationToken(): string
    {
        return $this->confirmationToken;
    }

    public function getNotes(): string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getConfirmedAt(): ?\DateTimeInterface
    {
        return $this->confirmedAt;
    }
}
