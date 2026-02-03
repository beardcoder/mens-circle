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
    /** @phpstan-ignore-next-line property.onlyWritten - Required by RequestAwareWidgetInterface */
    private ServerRequestInterface $request;

    public function __construct(
        /** @phpstan-ignore-next-line property.onlyWritten - May be used in future enhancements */
        private readonly WidgetConfigurationInterface $configuration,
        private readonly NewsletterSubscriptionRepository $newsletterSubscriptionRepository,
        /** @phpstan-ignore-next-line property.onlyWritten - Required for potential template-based rendering */
        private readonly BackendViewFactory $backendViewFactory,
    ) {}

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function renderWidgetContent(): string
    {
        $querySettings = $this->newsletterSubscriptionRepository->createQuery()->getQuerySettings();
        $querySettings->setRespectStoragePage(false);
        $this->newsletterSubscriptionRepository->setDefaultQuerySettings($querySettings);

        $latestSubscribers = $this->newsletterSubscriptionRepository->findLatestConfirmed(5);

        $html = '<div class="widget-content"><ul class="list-unstyled">';

        if ($latestSubscribers->count() === 0) {
            $html .= '<li class="text-muted">Keine Newsletter-Anmeldungen vorhanden</li>';
        } else {
            foreach ($latestSubscribers as $subscriber) {
                $html .= sprintf(
                    '<li><strong>%s</strong><br><small>%s</small></li>',
                    htmlspecialchars($subscriber->getFirstName() ?: $subscriber->getEmail()),
                    htmlspecialchars($subscriber->getEmail())
                );
            }
        }

        $html .= '</ul></div>';

        return $html;
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
