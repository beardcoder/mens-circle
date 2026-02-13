<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Dashboard\Provider;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Dashboard\Widgets\ListDataProviderInterface;

final readonly class NextEventListDataProvider implements ListDataProviderInterface
{
    private const EVENT_TABLE = 'tx_menscircle_domain_model_event';

    public function __construct(
        private ConnectionPool $connectionPool
    ) {}

    /**
     * @return list<string>
     */
    public function getItems(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::EVENT_TABLE);
        $queryBuilder->getRestrictions()->removeAll();

        $row = $queryBuilder
            ->select(
                'title',
                'slug',
                'event_date',
                'start_time',
                'location',
                'city',
                'max_participants'
            )
            ->from(self::EVENT_TABLE)
            ->where(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('is_published', $queryBuilder->createNamedParameter(1, ParameterType::INTEGER)),
                $queryBuilder->expr()->gte(
                    'event_date',
                    $queryBuilder->createNamedParameter(new \DateTimeImmutable('today')->format('Y-m-d 00:00:00'), ParameterType::STRING)
                )
            )
            ->orderBy('event_date', 'ASC')
            ->addOrderBy('start_time', 'ASC')
            ->addOrderBy('uid', 'ASC')
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        if (!\is_array($row)) {
            return ['Kein kommendes Event vorhanden.'];
        }

        $eventDate = $this->formatDate((string) ($row['event_date'] ?? ''));
        $eventTime = $this->formatTime((string) ($row['start_time'] ?? ''));
        $locationParts = array_filter([
            trim((string) ($row['location'] ?? '')),
            trim((string) ($row['city'] ?? '')),
        ]);
        $locationLabel = $locationParts === [] ? '-' : implode(', ', $locationParts);
        $slug = trim((string) ($row['slug'] ?? ''));

        $items = [
            \sprintf(
                '%s am %s%s',
                trim((string) ($row['title'] ?? 'Termin')),
                $eventDate !== '' ? $eventDate : 'tba',
                $eventTime !== '' ? ' um ' . $eventTime . ' Uhr' : ''
            ),
            'Ort: ' . $locationLabel,
            'Max. Teilnehmer: ' . (int) ($row['max_participants'] ?? 0),
        ];

        if ($slug !== '') {
            $items[] = 'Frontend: /event/' . ltrim($slug, '/');
        }

        return $items;
    }

    private function formatDate(string $value): string
    {
        $value = trim($value);
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return '';
        }

        try {
            return new \DateTimeImmutable($value)->format('d.m.Y');
        } catch (\Throwable) {
            return '';
        }
    }

    private function formatTime(string $value): string
    {
        $value = trim($value);
        if ($value === '' || $value === '00:00:00') {
            return '';
        }

        try {
            return new \DateTimeImmutable($value)->format('H:i');
        } catch (\Throwable) {
            return '';
        }
    }
}
