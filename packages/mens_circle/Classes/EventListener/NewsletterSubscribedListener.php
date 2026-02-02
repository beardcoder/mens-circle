<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\EventListener;

use BeardCoder\MensCircle\Event\NewsletterSubscribedEvent;
use BeardCoder\MensCircle\Service\EmailService;
use TYPO3\CMS\Core\Attribute\AsEventListener;

#[AsEventListener(identifier: 'mens-circle/newsletter-subscribed')]
final class NewsletterSubscribedListener
{
    public function __construct(
        private readonly EmailService $emailService,
    ) {
    }

    public function __invoke(NewsletterSubscribedEvent $event): void
    {
        $subscription = $event->getSubscription();

        $this->emailService->sendNewsletterConfirmation($subscription);
    }
}
