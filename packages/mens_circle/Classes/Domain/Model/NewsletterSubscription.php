<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

final class NewsletterSubscription extends AbstractEntity
{
    protected ?Participant $participant = null;

    protected string $token = '';

    protected ?DateTime $subscribedAt = null;

    protected ?DateTime $confirmedAt = null;

    protected ?DateTime $unsubscribedAt = null;

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(?Participant $participant): void
    {
        $this->participant = $participant;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getSubscribedAt(): ?DateTime
    {
        return $this->subscribedAt;
    }

    public function setSubscribedAt(?DateTime $subscribedAt): void
    {
        $this->subscribedAt = $subscribedAt;
    }

    public function getConfirmedAt(): ?DateTime
    {
        return $this->confirmedAt;
    }

    public function setConfirmedAt(?DateTime $confirmedAt): void
    {
        $this->confirmedAt = $confirmedAt;
    }

    public function getUnsubscribedAt(): ?DateTime
    {
        return $this->unsubscribedAt;
    }

    public function setUnsubscribedAt(?DateTime $unsubscribedAt): void
    {
        $this->unsubscribedAt = $unsubscribedAt;
    }

    public function isActive(): bool
    {
        return $this->unsubscribedAt === null;
    }
}
