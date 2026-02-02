<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Widgets;

use BeardCoder\MensCircle\Domain\Repository\NewsletterSubscriptionRepository;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Dashboard\Widgets\RequestAwareWidgetInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;

final class LatestNewsletterSubscriberWidget implements WidgetInterface, RequestAwareWidgetInterface
{
    private ServerRequestInterface $request;

    public function __construct(
        private readonly WidgetConfigurationInterface $configuration,
        private readonly NewsletterSubscriptionRepository $newsletterSubscriptionRepository,
        private readonly BackendViewFactory $backendViewFactory,
    ) {}

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function renderWidgetContent(): string
    {
        $view = $this->backendViewFactory->create($this->request);
        $view->setTemplate('Widget/LatestNewsletterSubscriber');

        $querySettings = $this->newsletterSubscriptionRepository->createQuery()->getQuerySettings();
        $querySettings->setRespectStoragePage(false);
        $this->newsletterSubscriptionRepository->setDefaultQuerySettings($querySettings);

        $latestSubscribers = $this->newsletterSubscriptionRepository->findLatestConfirmed(5);

        $view->assignMultiple([
            'subscribers' => $latestSubscribers,
            'configuration' => $this->configuration,
        ]);

        return $view->render();
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
        return [];
    }
}
