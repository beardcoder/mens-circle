<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\DataProcessing;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

final class NextEventDataProcessor implements DataProcessorInterface
{
    private const EVENT_TABLE = 'tx_menscircle_domain_model_event';

    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        $variableName = trim((string) ($processorConfiguration['as'] ?? 'nextEvent'));
        if ($variableName === '') {
            $variableName = 'nextEvent';
        }

        $eventBasePath = trim((string) ($processorConfiguration['eventBasePath'] ?? '/event'));
        if ($eventBasePath === '') {
            $eventBasePath = '/event';
        }
        if (!str_starts_with($eventBasePath, '/')) {
            $eventBasePath = '/' . $eventBasePath;
        }

        $nextEvent = $this->fetchNextEvent($eventBasePath);
        $processedData[$variableName] = $nextEvent;

        return $processedData;
    }

    /**
     * @return array{uid: int, title: string, slug: string, url: string}|null
     */
    private function fetchNextEvent(string $eventBasePath): ?array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::EVENT_TABLE);

        $todayMidnight = new \DateTimeImmutable('today')->getTimestamp();

        $row = $queryBuilder
            ->select('uid', 'title', 'slug')
            ->from(self::EVENT_TABLE)
            ->where(
                $queryBuilder->expr()->eq('is_published', $queryBuilder->createNamedParameter(1, ParameterType::INTEGER)),
                $queryBuilder->expr()->gte('event_date', $queryBuilder->createNamedParameter($todayMidnight, ParameterType::INTEGER))
            )
            ->orderBy('event_date', 'ASC')
            ->addOrderBy('start_time', 'ASC')
            ->addOrderBy('uid', 'ASC')
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        if (!\is_array($row)) {
            return null;
        }

        $slug = trim((string) ($row['slug'] ?? ''));
        $url = rtrim($eventBasePath, '/');
        if ($slug !== '') {
            $url .= '/' . ltrim($slug, '/');
        }
        if ($url === '') {
            $url = '/event';
        }

        return [
            'uid' => (int) ($row['uid'] ?? 0),
            'title' => trim((string) ($row['title'] ?? '')),
            'slug' => $slug,
            'url' => $url,
        ];
    }
}
