<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

final class NewsletterSubscription extends AbstractEntity
{
    public ?Participant $participant = null;

    public string $token = '';

    public ?DateTime $subscribedAt = null;

    public ?DateTime $confirmedAt = null;

    public ?DateTime $unsubscribedAt = null;

    public function isActive(): bool
    {
        return $this->unsubscribedAt === null;
    }
}
