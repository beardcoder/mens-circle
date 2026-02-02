<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class NewsletterSubscription extends AbstractEntity
{
    protected ?Participant $participant = null;
    protected string $email = '';
    protected string $firstName = '';
    protected bool $isConfirmed = false;
    protected string $confirmationToken = '';
    protected string $unsubscribeToken = '';
    protected ?\DateTimeInterface $confirmedAt = null;
    protected ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->confirmationToken = bin2hex(\random_bytes(32));
        $this->unsubscribeToken = bin2hex(\random_bytes(32));
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

    public function getEmail(): string
    {
        // Prefer participant email if available
        return $this->participant?->getEmail() ?? $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): string
    {
        // Prefer participant firstName if available
        return $this->participant?->getFirstName() ?? $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function isConfirmed(): bool
    {
        return $this->isConfirmed;
    }

    public function confirm(): void
    {
        $this->isConfirmed = true;
        $this->confirmedAt = new \DateTime();
    }

    public function getConfirmationToken(): string
    {
        return $this->confirmationToken;
    }

    public function getUnsubscribeToken(): string
    {
        return $this->unsubscribeToken;
    }

    public function getConfirmedAt(): ?\DateTimeInterface
    {
        return $this->confirmedAt;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
}
