<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Dashboard\Provider;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use BeardCoder\MensCircle\Domain\Enum\RegistrationStatus;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;

final readonly class UpcomingRegistrationsCountDataProvider implements NumberWithIconDataProviderInterface
{
    private const EVENT_TABLE = 'tx_menscircle_domain_model_event';
    private const REGISTRATION_TABLE = 'tx_menscircle_domain_model_registration';

    public function __construct(
        private ConnectionPool $connectionPool
    ) {}

    public function getNumber(): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::REGISTRATION_TABLE);
        $queryBuilder->getRestrictions()->removeAll();

        $count = $queryBuilder
            ->count('registration.uid')
            ->from(self::REGISTRATION_TABLE, 'registration')
            ->innerJoin(
                'registration',
                self::EVENT_TABLE,
                'event',
                $queryBuilder->expr()->eq('event.uid', $queryBuilder->quoteIdentifier('registration.event'))
            )
            ->where(
                $queryBuilder->expr()->eq('registration.deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('registration.hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->in(
                    'registration.status',
                    $queryBuilder->createNamedParameter(RegistrationStatus::activeValues(), ArrayParameterType::STRING)
                ),
                $queryBuilder->expr()->eq('event.deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('event.hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('event.is_published', $queryBuilder->createNamedParameter(1, ParameterType::INTEGER)),
                $queryBuilder->expr()->gte(
                    'event.event_date',
                    $queryBuilder->createNamedParameter(new \DateTimeImmutable('today')->format('Y-m-d 00:00:00'), ParameterType::STRING)
                )
            )
            ->executeQuery()
            ->fetchOne();

        return $count === false ? 0 : (int) $count;
    }
}
