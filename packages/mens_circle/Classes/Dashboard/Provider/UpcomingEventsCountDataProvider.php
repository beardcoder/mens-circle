<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Dashboard\Provider;

use BeardCoder\MensCircle\Domain\Repository\EventRepository;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;

final readonly class UpcomingEventsCountDataProvider implements NumberWithIconDataProviderInterface
{
    public function __construct(
        private EventRepository $eventRepository,
    ) {}

    public function getNumber(): int
    {
        return $this->eventRepository->findUpcomingPublished()->count();
    }
}
