<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\DataProcessing;

use BeardCoder\MensCircle\Domain\Model\Event;
use BeardCoder\MensCircle\Domain\Repository\EventRepository;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

final readonly class NextEventDataProcessor implements DataProcessorInterface
{
    public function __construct(
        private EventRepository $eventRepository,
    ) {}

    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData,
    ): array {
        $variableName = trim((string)($processorConfiguration['as'] ?? 'nextEvent'));
        if ($variableName === '') {
            $variableName = 'nextEvent';
        }

        $eventBasePath = trim((string)($processorConfiguration['eventBasePath'] ?? '/event'));
        if ($eventBasePath === '') {
            $eventBasePath = '/event';
        }
        if (!str_starts_with($eventBasePath, '/')) {
            $eventBasePath = '/' . $eventBasePath;
        }

        $event = $this->eventRepository->findNextEvent();
        $processedData[$variableName] = $event instanceof Event
            ? [
                'uid' => (int)$event->getUid(),
                'title' => $event->title,
                'slug' => $event->slug,
                'url' => $this->buildEventUrl($eventBasePath, $event->slug),
            ]
            : null;

        return $processedData;
    }

    private function buildEventUrl(string $eventBasePath, string $slug): string
    {
        $url = rtrim($eventBasePath, '/');
        $slug = trim($slug);
        if ($slug !== '') {
            $url .= '/' . ltrim($slug, '/');
        }

        return $url !== '' ? $url : '/event';
    }
}
