<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\Controller;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use MarkusSommer\MensCircle\Domain\Enum\RegistrationStatus;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class EventBackendController extends ActionController
{
    private const EVENT_TABLE = 'tx_menscircle_domain_model_event';
    private const REGISTRATION_TABLE = 'tx_menscircle_domain_model_registration';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly UriBuilder $backendUriBuilder
    ) {
    }

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
                'eventsCount' => count($events),
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
     *   editUrl: string,
     *   recordsUrl: string,
     *   frontendUrl: string
     * }>
     */
    private function buildEventRows(string $returnUrl, string $moduleIdentifier): array
    {
        $events = $this->fetchEvents();
        $registrationCounts = $this->fetchActiveRegistrationCountsByEvent();
        $eventRows = [];

        foreach ($events as $event) {
            $eventUid = (int) ($event['uid'] ?? 0);
            if ($eventUid <= 0) {
                continue;
            }

            $eventDate = $this->createDateTimeImmutable($event['event_date'] ?? null);
            $isPast = $eventDate instanceof \DateTimeImmutable
                ? $eventDate->setTime(23, 59, 59) < new \DateTimeImmutable('now')
                : false;

            $maxParticipants = max(0, (int) ($event['max_participants'] ?? 0));
            $activeRegistrations = (int) ($registrationCounts[$eventUid] ?? 0);
            $availableSpots = max(0, $maxParticipants - $activeRegistrations);

            $locationParts = array_filter([
                trim((string) ($event['location'] ?? '')),
                trim((string) ($event['city'] ?? '')),
            ]);
            $locationLabel = $locationParts === []
                ? $this->translate('label.noLocation')
                : implode(', ', $locationParts);

            $status = $this->resolveStatus(
                isHidden: (int) ($event['hidden'] ?? 0) === 1,
                isPublished: (int) ($event['is_published'] ?? 0) === 1,
                isPast: $isPast
            );

            $slug = trim((string) ($event['slug'] ?? ''));

            $eventRows[] = [
                'uid' => $eventUid,
                'title' => trim((string) ($event['title'] ?? '')) !== '' ? trim((string) ($event['title'] ?? '')) : '-',
                'slug' => $slug,
                'dateLabel' => $eventDate?->format('d.m.Y') ?? '-',
                'timeLabel' => $this->formatTimeRange($event['start_time'] ?? null, $event['end_time'] ?? null),
                'locationLabel' => $locationLabel,
                'activeRegistrations' => $activeRegistrations,
                'maxParticipants' => $maxParticipants,
                'availableSpots' => $availableSpots,
                'statusLabel' => $status['label'],
                'statusClass' => $status['class'],
                'editUrl' => $this->buildEditEventUrl($eventUid, $returnUrl, $moduleIdentifier),
                'recordsUrl' => $this->buildRecordsModuleUrl((int) ($event['pid'] ?? 0)),
                'frontendUrl' => $this->buildFrontendEventUrl($slug),
            ];
        }

        return $eventRows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchEvents(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::EVENT_TABLE);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

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
            ->orderBy('event_date', 'ASC')
            ->addOrderBy('uid', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, int>
     */
    private function fetchActiveRegistrationCountsByEvent(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::REGISTRATION_TABLE);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

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

        if (!is_array($rows)) {
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
     * @return array{label: string, class: string}
     */
    private function resolveStatus(bool $isHidden, bool $isPublished, bool $isPast): array
    {
        if ($isHidden) {
            return [
                'label' => $this->translate('status.hidden'),
                'class' => 'text-bg-secondary',
            ];
        }

        if (!$isPublished) {
            return [
                'label' => $this->translate('status.draft'),
                'class' => 'text-bg-warning',
            ];
        }

        if ($isPast) {
            return [
                'label' => $this->translate('status.past'),
                'class' => 'text-bg-light',
            ];
        }

        return [
            'label' => $this->translate('status.live'),
            'class' => 'text-bg-success',
        ];
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
        $parameters = [
            'edit' => [
                self::EVENT_TABLE => [
                    $eventUid => 'edit',
                ],
            ],
            'returnUrl' => $returnUrl,
        ];
        if ($moduleIdentifier !== '') {
            $parameters['module'] = $moduleIdentifier;
        }

        return (string) $this->backendUriBuilder->buildUriFromRoute('record_edit', $parameters);
    }

    private function buildNewEventUrl(int $storagePid, string $returnUrl, string $moduleIdentifier): string
    {
        $parameters = [
            'edit' => [
                self::EVENT_TABLE => [
                    $storagePid => 'new',
                ],
            ],
            'returnUrl' => $returnUrl,
        ];
        if ($moduleIdentifier !== '') {
            $parameters['module'] = $moduleIdentifier;
        }

        return (string) $this->backendUriBuilder->buildUriFromRoute('record_edit', $parameters);
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
        $languageKey = 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_mod_events.xlf:' . $key;
        $translated = $GLOBALS['LANG']?->sL($languageKey);

        return is_string($translated) && $translated !== '' ? $translated : $key;
    }
}
