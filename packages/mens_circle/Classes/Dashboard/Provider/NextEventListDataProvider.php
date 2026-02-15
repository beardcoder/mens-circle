<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Dashboard\Provider;

use BeardCoder\MensCircle\Domain\Model\Event;
use BeardCoder\MensCircle\Domain\Repository\EventRepository;
use TYPO3\CMS\Dashboard\Widgets\ListDataProviderInterface;

final readonly class NextEventListDataProvider implements ListDataProviderInterface
{
    public function __construct(
        private EventRepository $eventRepository,
    ) {}

    /**
     * @return list<string>
     */
    public function getItems(): array
    {
        $event = $this->eventRepository->findNextEvent();
        if (!$event instanceof Event) {
            return ['Kein kommendes Event vorhanden.'];
        }

        $eventDate = $event->eventDate?->format('d.m.Y') ?? 'tba';
        $eventTime = $event->startTime?->format('H:i') ?? '';
        $locationParts = array_filter([
            trim($event->location),
            trim($event->city),
        ]);
        $locationLabel = $locationParts === [] ? '-' : implode(', ', $locationParts);

        $items = [
            \sprintf(
                '%s am %s%s',
                $event->title !== '' ? $event->title : 'Termin',
                $eventDate,
                $eventTime !== '' ? ' um ' . $eventTime . ' Uhr' : '',
            ),
            'Ort: ' . $locationLabel,
            'Max. Teilnehmer: ' . $event->maxParticipants,
        ];

        $slug = trim($event->slug);
        if ($slug !== '') {
            $items[] = 'Frontend: /event/' . ltrim($slug, '/');
        }

        return $items;
    }
}
