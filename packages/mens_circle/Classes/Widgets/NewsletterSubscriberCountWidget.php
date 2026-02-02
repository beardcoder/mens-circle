<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Widgets;

use BeardCoder\MensCircle\Domain\Repository\NewsletterSubscriptionRepository;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;

final class NewsletterSubscriberCountWidget implements WidgetInterface
{
    public function __construct(
        private readonly WidgetConfigurationInterface $configuration,
        private readonly NewsletterSubscriptionRepository $newsletterSubscriptionRepository,
        private readonly BackendViewFactory $backendViewFactory,
    ) {}

    public function renderWidgetContent(): string
    {
        $view = $this->backendViewFactory->create($this->configuration->getServiceName());

        $querySettings = $this->newsletterSubscriptionRepository->createQuery()->getQuerySettings();
        $querySettings->setRespectStoragePage(false);
        $this->newsletterSubscriptionRepository->setDefaultQuerySettings($querySettings);

        $count = $this->newsletterSubscriptionRepository->countByIsConfirmed(true);

        $view->assignMultiple([
            'count' => $count,
            'configuration' => $this->configuration,
        ]);

        return $view->render('Widget/NewsletterSubscriberCount');
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getCssFiles(): array
    {
        return [];
    }

    public function getJavaScriptFiles(): array
    {
        return [];
    }

    public function getEventData(): array
    {
        return [
            'count' => $this->getSubscriberCount(),
        ];
    }

    private function getSubscriberCount(): int
    {
        $querySettings = $this->newsletterSubscriptionRepository->createQuery()->getQuerySettings();
        $querySettings->setRespectStoragePage(false);
        $this->newsletterSubscriptionRepository->setDefaultQuerySettings($querySettings);

        return $this->newsletterSubscriptionRepository->countByIsConfirmed(true);
    }
}
