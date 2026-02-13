<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Controller;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use BeardCoder\MensCircle\Domain\Enum\RegistrationStatus;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class EventBackendController extends ActionController
{
    private const EVENT_TABLE = 'tx_menscircle_domain_model_event';
    private const REGISTRATION_TABLE = 'tx_menscircle_domain_model_registration';
    private const PARTICIPANT_TABLE = 'tx_menscircle_domain_model_participant';
    private const NEWSLETTER_SUBSCRIPTION_TABLE = 'tx_menscircle_domain_model_newslettersubscription';
    private const LANGUAGE_FILE_PREFIX = 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_mod_events.xlf:';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly UriBuilder $backendUriBuilder
    ) {}

    public function indexAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $returnUrl = (string) $this->backendUriBuilder->buildUriFromRequest($this->request);
        $moduleIdentifier = (string) ($this->request->getAttribute('module')?->getIdentifier() ?? '');
        $storagePid = max(1, (int) ($this->settings['storagePid'] ?? 1));

        $events = $this->buildEventRows($returnUrl, $moduleIdentifier);

        return $moduleTemplate
            ->setFlashMessageQueue($this->getFlashMessageQueue())
            ->setTitle($this->translate('headline'))
            ->assignMultiple([
                'events' => $events,
                'eventsCount' => \count($events),
                'newEventUrl' => $this->buildNewEventUrl($storagePid, $returnUrl, $moduleIdentifier),
                'storageRecordsUrl' => $this->buildRecordsModuleUrl($storagePid),
            ])
            ->renderResponse('EventBackend/Index');
    }

    /**
     * @return list<array{
     *   uid: int,
     *   title: string,
     *   slug: string,
     *   dateLabel: string,
     *   timeLabel: string,
     *   locationLabel: string,
     *   activeRegistrations: int,
     *   maxParticipants: int,
     *   availableSpots: int,
     *   statusLabel: string,
     *   statusClass: string,
     *   participantsCount: int,
     *   participants: list<array{
     *     uid: int,
     *     name: string,
     *     email: string,
     *     phone: string,
     *     registrationStatusLabel: string,
     *     registrationStatusClass: string,
     *     registrationDateLabel: string,
     *     newsletterStatusLabel: string,
     *     newsletterStatusClass: string,
     *     newsletterDateLabel: string
     *   }>,
     *   editUrl: string,
     *   recordsUrl: string,
     *   frontendUrl: string
     * }>
     * @throws Exception
     */
    private function buildEventRows(string $returnUrl, string $moduleIdentifier): array
    {
        $events = $this->fetchEvents();
        $registrationCounts = $this->fetchActiveRegistrationCountsByEvent();
        $participantsByEvent = $this->fetchParticipantsByEvent();
        $eventRows = [];

        foreach ($events as $event) {
            $eventRow = $this->buildEventRow($event, $registrationCounts, $participantsByEvent, $returnUrl, $moduleIdentifier);
            if (\is_array($eventRow)) {
                $eventRows[] = $eventRow;
            }
        }

        return $eventRows;
    }

    /**
     * @param array<string, mixed> $event
     * @param array<int, int> $registrationCounts
     * @param array<int, list<array{
     *   uid: int,
     *   name: string,
     *   email: string,
     *   phone: string,
     *   registrationStatusLabel: string,
     *   registrationStatusClass: string,
     *   registrationDateLabel: string,
     *   newsletterStatusLabel: string,
     *   newsletterStatusClass: string,
     *   newsletterDateLabel: string
     * }>> $participantsByEvent
     * @return array{
     *   uid: int,
     *   title: string,
     *   slug: string,
     *   dateLabel: string,
     *   timeLabel: string,
     *   locationLabel: string,
     *   activeRegistrations: int,
     *   maxParticipants: int,
     *   availableSpots: int,
     *   statusLabel: string,
     *   statusClass: string,
     *   participantsCount: int,
     *   participants: list<array{
     *     uid: int,
     *     name: string,
     *     email: string,
     *     phone: string,
     *     registrationStatusLabel: string,
     *     registrationStatusClass: string,
     *     registrationDateLabel: string,
     *     newsletterStatusLabel: string,
     *     newsletterStatusClass: string,
     *     newsletterDateLabel: string
     *   }>,
     *   editUrl: string,
     *   recordsUrl: string,
     *   frontendUrl: string
     * }|null
     */
    private function buildEventRow(
        array $event,
        array $registrationCounts,
        array $participantsByEvent,
        string $returnUrl,
        string $moduleIdentifier
    ): ?array {
        $eventUid = (int) ($event['uid'] ?? 0);
        if ($eventUid <= 0) {
            return null;
        }

        $eventDate = $this->createDateTimeImmutable($event['event_date'] ?? null);
        $isPast = $eventDate instanceof \DateTimeImmutable && $eventDate->setTime(23, 59, 59) < new \DateTimeImmutable('now');

        $maxParticipants = max(0, (int) ($event['max_participants'] ?? 0));
        $activeRegistrations = $registrationCounts[$eventUid] ?? 0;
        $availableSpots = max(0, $maxParticipants - $activeRegistrations);

        $slug = trim((string) ($event['slug'] ?? ''));
        $participants = $participantsByEvent[$eventUid] ?? [];
        $status = $this->resolveStatus(
            isHidden: (int) ($event['hidden'] ?? 0) === 1,
            isPublished: (int) ($event['is_published'] ?? 0) === 1,
            isPast: $isPast
        );

        return [
            'uid' => $eventUid,
            'title' => $this->normalizeTextOrFallback($event['title'] ?? null, '-'),
            'slug' => $slug,
            'dateLabel' => $eventDate?->format('d.m.Y') ?? '-',
            'timeLabel' => $this->formatTimeRange($event['start_time'] ?? null, $event['end_time'] ?? null),
            'locationLabel' => $this->buildLocationLabel($event['location'] ?? null, $event['city'] ?? null),
            'activeRegistrations' => $activeRegistrations,
            'maxParticipants' => $maxParticipants,
            'availableSpots' => $availableSpots,
            'statusLabel' => $status['label'],
            'statusClass' => $status['class'],
            'participantsCount' => \count($participants),
            'participants' => $participants,
            'editUrl' => $this->buildEditEventUrl($eventUid, $returnUrl, $moduleIdentifier),
            'recordsUrl' => $this->buildRecordsModuleUrl((int) ($event['pid'] ?? 0)),
            'frontendUrl' => $this->buildFrontendEventUrl($slug),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     * @throws Exception
     */
    private function fetchEvents(): array
    {
        $queryBuilder = $this->createDeletedOnlyQueryBuilder(self::EVENT_TABLE);

        $rows = $queryBuilder
            ->select(
                'uid',
                'pid',
                'title',
                'slug',
                'event_date',
                'start_time',
                'end_time',
                'location',
                'city',
                'max_participants',
                'is_published',
                'hidden'
            )
            ->from(self::EVENT_TABLE)
            ->orderBy('event_date', 'DESC')
            ->addOrderBy('start_time', 'DESC')
            ->addOrderBy('uid', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();

        return \is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, int>
     */
    private function fetchActiveRegistrationCountsByEvent(): array
    {
        $queryBuilder = $this->createDeletedOnlyQueryBuilder(self::REGISTRATION_TABLE);

        $rows = $queryBuilder
            ->select('event')
            ->addSelectLiteral('COUNT(uid) AS registrations_active')
            ->from(self::REGISTRATION_TABLE)
            ->where(
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->in(
                    'status',
                    $queryBuilder->createNamedParameter(RegistrationStatus::activeValues(), ArrayParameterType::STRING)
                )
            )
            ->groupBy('event')
            ->executeQuery()
            ->fetchAllAssociative();

        if (!\is_array($rows)) {
            return [];
        }

        $countsByEvent = [];
        foreach ($rows as $row) {
            $eventUid = (int) ($row['event'] ?? 0);
            if ($eventUid <= 0) {
                continue;
            }
            $countsByEvent[$eventUid] = (int) ($row['registrations_active'] ?? 0);
        }

        return $countsByEvent;
    }

    /**
     * @return array<int, list<array{
     *   uid: int,
     *   name: string,
     *   email: string,
     *   phone: string,
     *   registrationStatusLabel: string,
     *   registrationStatusClass: string,
     *   registrationDateLabel: string,
     *   newsletterStatusLabel: string,
     *   newsletterStatusClass: string,
     *   newsletterDateLabel: string
     * }>>
     */
    private function fetchParticipantsByEvent(): array
    {
        $queryBuilder = $this->createDeletedOnlyQueryBuilder(self::REGISTRATION_TABLE);

        $rows = $queryBuilder
            ->select(
                'registration.uid',
                'registration.event',
                'registration.status',
                'registration.registered_at',
                'participant.uid AS participant_uid',
                'participant.first_name',
                'participant.last_name',
                'participant.email',
                'participant.phone'
            )
            ->from(self::REGISTRATION_TABLE, 'registration')
            ->innerJoin(
                'registration',
                self::PARTICIPANT_TABLE,
                'participant',
                $queryBuilder->expr()->eq('participant.uid', $queryBuilder->quoteIdentifier('registration.participant'))
            )
            ->where(
                $queryBuilder->expr()->eq('registration.hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('participant.hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('participant.deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->orderBy('registration.event', 'ASC')
            ->addOrderBy('registration.registered_at', 'DESC')
            ->addOrderBy('registration.uid', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();

        if (!\is_array($rows) || $rows === []) {
            return [];
        }

        $participantUids = [];
        foreach ($rows as $row) {
            $participantUid = (int) ($row['participant_uid'] ?? 0);
            if ($participantUid > 0) {
                $participantUids[] = $participantUid;
            }
        }

        $newsletterByParticipant = $this->fetchNewsletterByParticipant(array_values(array_unique($participantUids)));

        $participantsByEvent = [];
        foreach ($rows as $row) {
            $eventUid = (int) ($row['event'] ?? 0);
            $participantUid = (int) ($row['participant_uid'] ?? 0);

            if ($eventUid <= 0 || $participantUid <= 0) {
                continue;
            }

            $registrationStatus = $this->resolveRegistrationStatus((string) ($row['status'] ?? ''));
            $newsletterStatus = $this->resolveNewsletterStatus($newsletterByParticipant[$participantUid] ?? null);
            $email = trim((string) ($row['participant.email'] ?? $row['email'] ?? ''));
            $phone = trim((string) ($row['participant.phone'] ?? $row['phone'] ?? ''));

            $participantsByEvent[$eventUid][] = [
                'uid' => $participantUid,
                'name' => $this->buildParticipantName(
                    firstName: (string) ($row['participant.first_name'] ?? $row['first_name'] ?? ''),
                    lastName: (string) ($row['participant.last_name'] ?? $row['last_name'] ?? ''),
                    email: $email
                ),
                'email' => $email !== '' ? $email : '-',
                'phone' => $phone !== '' ? $phone : '-',
                'registrationStatusLabel' => $registrationStatus['label'],
                'registrationStatusClass' => $registrationStatus['class'],
                'registrationDateLabel' => $this->formatDateTimeLabel($row['registered_at'] ?? null),
                'newsletterStatusLabel' => $newsletterStatus['label'],
                'newsletterStatusClass' => $newsletterStatus['class'],
                'newsletterDateLabel' => $newsletterStatus['dateLabel'],
            ];
        }

        return $participantsByEvent;
    }

    /**
     * @param list<int> $participantUids
     * @return array<int, array{subscribed_at: mixed, confirmed_at: mixed, unsubscribed_at: mixed}>
     */
    private function fetchNewsletterByParticipant(array $participantUids): array
    {
        if ($participantUids === []) {
            return [];
        }

        $queryBuilder = $this->createDeletedOnlyQueryBuilder(self::NEWSLETTER_SUBSCRIPTION_TABLE);

        $rows = $queryBuilder
            ->select('uid', 'participant', 'subscribed_at', 'confirmed_at', 'unsubscribed_at')
            ->from(self::NEWSLETTER_SUBSCRIPTION_TABLE)
            ->where(
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->in(
                    'participant',
                    $queryBuilder->createNamedParameter($participantUids, ArrayParameterType::INTEGER)
                )
            )
            ->orderBy('participant', 'ASC')
            ->addOrderBy('uid', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();

        if (!\is_array($rows)) {
            return [];
        }

        $newsletterByParticipant = [];
        foreach ($rows as $row) {
            $participantUid = (int) ($row['participant'] ?? 0);
            if ($participantUid <= 0 || isset($newsletterByParticipant[$participantUid])) {
                continue;
            }

            $newsletterByParticipant[$participantUid] = [
                'subscribed_at' => $row['subscribed_at'] ?? null,
                'confirmed_at' => $row['confirmed_at'] ?? null,
                'unsubscribed_at' => $row['unsubscribed_at'] ?? null,
            ];
        }

        return $newsletterByParticipant;
    }

    /**
     * @return array{label: string, class: string}
     */
    private function resolveRegistrationStatus(string $status): array
    {
        $normalizedStatus = strtolower(trim($status));

        return match ($normalizedStatus) {
            RegistrationStatus::Registered->value => [
                'label' => $this->translate('registrationStatus.registered'),
                'class' => 'text-bg-success',
            ],
            RegistrationStatus::Attended->value => [
                'label' => $this->translate('registrationStatus.attended'),
                'class' => 'text-bg-primary',
            ],
            RegistrationStatus::Cancelled->value => [
                'label' => $this->translate('registrationStatus.cancelled'),
                'class' => 'text-bg-danger',
            ],
            default => [
                'label' => $normalizedStatus !== '' ? ucfirst($normalizedStatus) : '-',
                'class' => 'text-bg-secondary',
            ],
        };
    }

    /**
     * @param array{subscribed_at: mixed, confirmed_at: mixed, unsubscribed_at: mixed}|null $newsletterRow
     * @return array{label: string, class: string, dateLabel: string}
     */
    private function resolveNewsletterStatus(?array $newsletterRow): array
    {
        if ($newsletterRow === null) {
            return [
                'label' => $this->translate('newsletterStatus.none'),
                'class' => 'text-bg-secondary',
                'dateLabel' => '',
            ];
        }

        $unsubscribedAt = $this->createDateTimeImmutable($newsletterRow['unsubscribed_at'] ?? null);
        if ($unsubscribedAt instanceof \DateTimeImmutable) {
            return [
                'label' => $this->translate('newsletterStatus.unsubscribed'),
                'class' => 'text-bg-warning',
                'dateLabel' => $unsubscribedAt->format('d.m.Y H:i'),
            ];
        }

        $confirmedAt = $this->createDateTimeImmutable($newsletterRow['confirmed_at'] ?? null);
        if ($confirmedAt instanceof \DateTimeImmutable) {
            return [
                'label' => $this->translate('newsletterStatus.confirmed'),
                'class' => 'text-bg-success',
                'dateLabel' => $confirmedAt->format('d.m.Y H:i'),
            ];
        }

        $subscribedAt = $this->createDateTimeImmutable($newsletterRow['subscribed_at'] ?? null);
        return [
            'label' => $this->translate('newsletterStatus.pending'),
            'class' => 'text-bg-info',
            'dateLabel' => $subscribedAt instanceof \DateTimeImmutable ? $subscribedAt->format('d.m.Y H:i') : '',
        ];
    }

    private function buildParticipantName(string $firstName, string $lastName, string $email): string
    {
        $fullName = trim($this->normalizeText($firstName) . ' ' . $this->normalizeText($lastName));
        if ($fullName !== '') {
            return $fullName;
        }

        return $this->normalizeTextOrFallback($email, '-');
    }

    /**
     * @return array{label: string, class: string}
     */
    private function resolveStatus(bool $isHidden, bool $isPublished, bool $isPast): array
    {
        return match (true) {
            $isHidden => [
                'label' => $this->translate('status.hidden'),
                'class' => 'text-bg-secondary',
            ],
            ! $isPublished => [
                'label' => $this->translate('status.draft'),
                'class' => 'text-bg-warning',
            ],
            $isPast => [
                'label' => $this->translate('status.past'),
                'class' => 'text-bg-light',
            ],
            default => [
                'label' => $this->translate('status.live'),
                'class' => 'text-bg-success',
            ],
        };
    }

    private function formatTimeRange(mixed $startTime, mixed $endTime): string
    {
        $start = $this->createDateTimeImmutable($startTime);
        $end = $this->createDateTimeImmutable($endTime);

        if (!$start instanceof \DateTimeImmutable && !$end instanceof \DateTimeImmutable) {
            return $this->translate('label.noTime');
        }

        if ($start instanceof \DateTimeImmutable && $end instanceof \DateTimeImmutable) {
            return $start->format('H:i') . ' - ' . $end->format('H:i');
        }

        if ($start instanceof \DateTimeImmutable) {
            return $start->format('H:i');
        }

        return $end->format('H:i');
    }

    private function formatDateTimeLabel(mixed $value): string
    {
        return $this->createDateTimeImmutable($value)?->format('d.m.Y H:i') ?? '-';
    }

    private function createDateTimeImmutable(mixed $value): ?\DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return new \DateTimeImmutable($value->format('Y-m-d H:i:s'));
        }

        $stringValue = trim((string) $value);
        if ($stringValue === '' || $stringValue === '0000-00-00 00:00:00' || $stringValue === '00:00:00') {
            return null;
        }

        try {
            return new \DateTimeImmutable($stringValue);
        } catch (\Throwable) {
            return null;
        }
    }

    private function buildEditEventUrl(int $eventUid, string $returnUrl, string $moduleIdentifier): string
    {
        return $this->buildRecordEditUrl($eventUid, 'edit', $returnUrl, $moduleIdentifier);
    }

    private function buildNewEventUrl(int $storagePid, string $returnUrl, string $moduleIdentifier): string
    {
        return $this->buildRecordEditUrl($storagePid, 'new', $returnUrl, $moduleIdentifier);
    }

    private function buildRecordsModuleUrl(int $pageUid): string
    {
        if ($pageUid <= 0) {
            return '';
        }

        return (string) $this->backendUriBuilder->buildUriFromRoute('records', [
            'id' => $pageUid,
        ]);
    }

    private function buildFrontendEventUrl(string $slug): string
    {
        $baseUrl = rtrim((string) ($this->settings['baseUrl'] ?? ''), '/');
        $normalizedSlug = ltrim(trim($slug), '/');

        if ($baseUrl === '' || $normalizedSlug === '') {
            return '';
        }

        return $baseUrl . '/event/' . $normalizedSlug;
    }

    private function translate(string $key): string
    {
        $languageKey = self::LANGUAGE_FILE_PREFIX . $key;
        $translated = $GLOBALS['LANG']?->sL($languageKey);

        return \is_string($translated) && $translated !== '' ? $translated : $key;
    }

    private function buildLocationLabel(mixed $location, mixed $city): string
    {
        $locationParts = array_filter([
            $this->normalizeText($location),
            $this->normalizeText($city),
        ]);

        if ($locationParts === []) {
            return $this->translate('label.noLocation');
        }

        return implode(', ', $locationParts);
    }

    private function normalizeText(mixed $value): string
    {
        return trim((string) $value);
    }

    private function normalizeTextOrFallback(mixed $value, string $fallback): string
    {
        $normalized = $this->normalizeText($value);

        return $normalized !== '' ? $normalized : $fallback;
    }

    private function buildRecordEditUrl(
        int $targetUid,
        string $mode,
        string $returnUrl,
        string $moduleIdentifier
    ): string {
        $parameters = [
            'edit' => [
                self::EVENT_TABLE => [
                    $targetUid => $mode,
                ],
            ],
            'returnUrl' => $returnUrl,
        ];
        if ($moduleIdentifier !== '') {
            $parameters['module'] = $moduleIdentifier;
        }

        return (string) $this->backendUriBuilder->buildUriFromRoute('record_edit', $parameters);
    }

    private function createDeletedOnlyQueryBuilder(string $table): QueryBuilder
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder;
    }
}
