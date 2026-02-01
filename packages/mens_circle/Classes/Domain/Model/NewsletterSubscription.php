<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class NewsletterSubscription extends AbstractEntity
{
    protected string $email = '';
    protected string $firstName = '';
    protected bool $isConfirmed = false;
    protected string $confirmationToken = '';
    protected string $unsubscribeToken = '';
    protected ?\DateTimeInterface $confirmedAt = null;

    public function __construct()
    {
        $this->confirmationToken = bin2hex(\random_bytes(32));
        $this->unsubscribeToken = bin2hex(\random_bytes(32));
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
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
}
