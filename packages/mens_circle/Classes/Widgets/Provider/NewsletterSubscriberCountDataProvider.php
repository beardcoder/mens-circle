<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Widgets\Provider;

use BeardCoder\MensCircle\Domain\Repository\NewsletterSubscriptionRepository;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;

final class NewsletterSubscriberCountDataProvider implements NumberWithIconDataProviderInterface
{
    public function __construct(
        private readonly NewsletterSubscriptionRepository $newsletterSubscriptionRepository,
    ) {}

    public function getNumber(): int
    {
        $querySettings = $this->newsletterSubscriptionRepository->createQuery()->getQuerySettings();
        $querySettings->setRespectStoragePage(false);
        $this->newsletterSubscriptionRepository->setDefaultQuerySettings($querySettings);

        return $this->newsletterSubscriptionRepository->countByIsConfirmed(true);
    }
}
