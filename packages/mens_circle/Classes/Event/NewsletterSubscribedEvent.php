<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Event;

use BeardCoder\MensCircle\Domain\Model\NewsletterSubscription;

final class NewsletterSubscribedEvent
{
    public function __construct(
        private readonly NewsletterSubscription $subscription,
    ) {}

    public function getSubscription(): NewsletterSubscription
    {
        return $this->subscription;
    }
}
