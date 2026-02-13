<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Dashboard\Provider;

use DateTimeImmutable;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;

final readonly class UpcomingEventsCountDataProvider implements NumberWithIconDataProviderInterface
{
    private const EVENT_TABLE = 'tx_menscircle_domain_model_event';

    public function __construct(
        private ConnectionPool $connectionPool,
    ) {}

    public function getNumber(): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::EVENT_TABLE);
        $queryBuilder->getRestrictions()->removeAll();

        $count = $queryBuilder
            ->count('uid')
            ->from(self::EVENT_TABLE)
            ->where(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('is_published', $queryBuilder->createNamedParameter(1, ParameterType::INTEGER)),
                $queryBuilder->expr()->gte(
                    'event_date',
                    $queryBuilder->createNamedParameter((new DateTimeImmutable('today'))->format('Y-m-d 00:00:00'), ParameterType::STRING),
                ),
            )
            ->executeQuery()
            ->fetchOne();

        return $count === false ? 0 : (int)$count;
    }
}
