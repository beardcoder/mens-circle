<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Service;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;

final readonly class RegistrationNotificationDataService
{
    private const REGISTRATION_TABLE = 'tx_menscircle_domain_model_registration';
    private const EVENT_TABLE = 'tx_menscircle_domain_model_event';
    private const PARTICIPANT_TABLE = 'tx_menscircle_domain_model_participant';

    public function __construct(
        private ConnectionPool $connectionPool,
    ) {}

    /**
     * @return array{
     *   registrationUid: int,
     *   status: string,
     *   participantEmail: string,
     *   participantFirstName: string,
     *   participantLastName: string,
     *   participantPhone: string,
     *   eventTitle: string,
     *   eventSlug: string,
     *   eventDate: string,
     *   eventStartTime: string,
     *   eventEndTime: string,
     *   eventLocation: string,
     *   eventCity: string
     * }|null
     */
    public function findByRegistrationUid(int $registrationUid): ?array
    {
        if ($registrationUid <= 0) {
            return null;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::REGISTRATION_TABLE);
        $queryBuilder->getRestrictions()->removeAll();

        $row = $queryBuilder
            ->select(
                'registration.uid',
                'registration.status',
                'participant.email',
                'participant.first_name',
                'participant.last_name',
                'participant.phone',
                'event.title',
                'event.slug',
                'event.event_date',
                'event.start_time',
                'event.end_time',
                'event.location',
                'event.city',
            )
            ->from(self::REGISTRATION_TABLE, 'registration')
            ->innerJoin(
                'registration',
                self::PARTICIPANT_TABLE,
                'participant',
                $queryBuilder->expr()->eq(
                    'participant.uid',
                    $queryBuilder->quoteIdentifier('registration.participant'),
                ),
            )
            ->innerJoin(
                'registration',
                self::EVENT_TABLE,
                'event',
                $queryBuilder->expr()->eq(
                    'event.uid',
                    $queryBuilder->quoteIdentifier('registration.event'),
                ),
            )
            ->where(
                $queryBuilder->expr()->eq(
                    'registration.uid',
                    $queryBuilder->createNamedParameter($registrationUid, ParameterType::INTEGER),
                ),
                $queryBuilder->expr()->eq('registration.deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('registration.hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('participant.deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('participant.hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('event.deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('event.hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        if (!\is_array($row)) {
            return null;
        }

        return [
            'registrationUid' => (int)($row['uid'] ?? 0),
            'status' => trim((string)($row['status'] ?? '')),
            'participantEmail' => strtolower(trim((string)($row['email'] ?? ''))),
            'participantFirstName' => trim((string)($row['first_name'] ?? '')),
            'participantLastName' => trim((string)($row['last_name'] ?? '')),
            'participantPhone' => trim((string)($row['phone'] ?? '')),
            'eventTitle' => trim((string)($row['title'] ?? '')),
            'eventSlug' => trim((string)($row['slug'] ?? '')),
            'eventDate' => trim((string)($row['event_date'] ?? '')),
            'eventStartTime' => trim((string)($row['start_time'] ?? '')),
            'eventEndTime' => trim((string)($row['end_time'] ?? '')),
            'eventLocation' => trim((string)($row['location'] ?? '')),
            'eventCity' => trim((string)($row['city'] ?? '')),
        ];
    }
}
