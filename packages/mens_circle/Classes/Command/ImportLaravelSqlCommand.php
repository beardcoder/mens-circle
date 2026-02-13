<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Command;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'menscircle:import:laravel-sql',
    description: 'Importiert Daten aus einem Laravel SQL-Dump in die TYPO3 Tabellen von EXT:mens_circle.'
)]
final class ImportLaravelSqlCommand extends Command
{
    /**
     * @var list<string>
     */
    private const TARGET_TABLES = [
        'tx_menscircle_domain_model_registration',
        'tx_menscircle_domain_model_newslettersubscription',
        'tx_menscircle_domain_model_testimonial',
        'tx_menscircle_domain_model_event',
        'tx_menscircle_domain_model_participant',
    ];

    /**
     * @var array<string, string>
     */
    private const SOURCE_CONTENT_TYPE_TO_CTYPE = [
        'hero' => 'menscircle_hero',
        'intro' => 'menscircle_intro',
        'text_section' => 'menscircle_text_section',
        'text-section' => 'menscircle_text_section',
        'value_items' => 'menscircle_value_items',
        'value-items' => 'menscircle_value_items',
        'moderator' => 'menscircle_moderator',
        'journey_steps' => 'menscircle_journey_steps',
        'journey-steps' => 'menscircle_journey_steps',
        'testimonials' => 'menscircle_testimonials',
        'faq' => 'menscircle_faq',
        'newsletter' => 'menscircle_newsletter_section',
        'cta' => 'menscircle_cta',
        'whatsapp_community' => 'menscircle_whatsapp_community',
        'whatsapp-community' => 'menscircle_whatsapp_community',
    ];

    /**
     * Fallback-Spaltenreihenfolge für SQL-Dumps ohne explizite Spaltenliste
     * (z. B. klassisches mysqldump mit INSERT ... VALUES (...)).
     *
     * @var array<string, list<string>>
     */
    private const SOURCE_DEFAULT_COLUMNS = [
        'participants' => [
            'id',
            'first_name',
            'last_name',
            'email',
            'phone',
            'created_at',
            'updated_at',
        ],
        'events' => [
            'id',
            'title',
            'slug',
            'description',
            'event_date',
            'start_time',
            'end_time',
            'location',
            'location_details',
            'max_participants',
            'cost_basis',
            'is_published',
            'created_at',
            'updated_at',
            'deleted_at',
            'image',
            'street',
            'postal_code',
            'city',
        ],
        'registrations' => [
            'id',
            'participant_id',
            'event_id',
            'status',
            'registered_at',
            'cancelled_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ],
        'newsletter_subscriptions' => [
            'id',
            'participant_id',
            'token',
            'subscribed_at',
            'confirmed_at',
            'unsubscribed_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ],
        'testimonials' => [
            'id',
            'quote',
            'author_name',
            'email',
            'role',
            'is_published',
            'published_at',
            'sort_order',
            'created_at',
            'updated_at',
            'deleted_at',
        ],
        'pages' => [
            'id',
            'title',
            'slug',
            'meta',
            'is_published',
            'published_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ],
        'content_blocks' => [
            'id',
            'type',
            'data',
            'block_id',
            'order',
            'created_at',
            'updated_at',
            'page_id',
        ],
    ];

    /**
     * @var array<string, array<string, string>>
     */
    private array $tableColumnCache = [];

    public function __construct(private readonly ConnectionPool $connectionPool)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('sql-file', InputArgument::REQUIRED, 'Pfad zur Laravel SQL-Datei (Dump mit INSERT Statements)')
            ->addOption('pid', null, InputOption::VALUE_REQUIRED, 'Storage-PID für importierte Datensätze', '1')
            ->addOption('content-pid', null, InputOption::VALUE_REQUIRED, 'Seiten-UID für importierte Content-Elemente (tt_content)', '1')
            ->addOption('source-page-slug', null, InputOption::VALUE_REQUIRED, 'Laravel Page-Slug, dessen content_blocks importiert werden', 'home')
            ->addOption('truncate', null, InputOption::VALUE_NONE, 'Leert die TYPO3-Zieltabellen vor dem Import')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Parst und prüft den Dump ohne zu schreiben');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $sqlFile = (string) $input->getArgument('sql-file');
        $realPath = realpath($sqlFile);
        if ($realPath === false || !is_file($realPath)) {
            $this->writeError($io, \sprintf('SQL-Datei nicht gefunden: %s', $sqlFile));

            return Command::FAILURE;
        }

        $pid = (int) $input->getOption('pid');
        if ($pid <= 0) {
            $this->writeError($io, '--pid muss größer als 0 sein.');

            return Command::FAILURE;
        }

        $contentPid = (int) $input->getOption('content-pid');
        if ($contentPid <= 0) {
            $this->writeError($io, '--content-pid muss größer als 0 sein.');

            return Command::FAILURE;
        }

        $sourcePageSlug = trim((string) $input->getOption('source-page-slug'));
        if ($sourcePageSlug === '') {
            $sourcePageSlug = 'home';
        }

        $io->title('Laravel -> TYPO3 Import (EXT:mens_circle)');
        $io->text(\sprintf('SQL: %s', $realPath));
        $io->text(\sprintf('Storage PID: %d', $pid));
        $io->text(\sprintf('Content PID: %d', $contentPid));
        $io->text(\sprintf('Source Page Slug: %s', $sourcePageSlug));

        $sqlContent = (string) file_get_contents($realPath);
        if ($sqlContent === '') {
            $this->writeError($io, 'Die SQL-Datei ist leer oder konnte nicht gelesen werden.');

            return Command::FAILURE;
        }

        $sourceData = $this->parseInsertStatements($sqlContent);
        $sourceData['registrations'] ??= $sourceData['event_registrations'] ?? [];

        $participants = $sourceData['participants'] ?? [];
        $events = $sourceData['events'] ?? [];
        $registrations = $sourceData['registrations'] ?? [];
        $newsletterSubscriptions = $sourceData['newsletter_subscriptions'] ?? [];
        $testimonials = $sourceData['testimonials'] ?? [];
        $pages = $sourceData['pages'] ?? [];
        $contentBlocks = $sourceData['content_blocks'] ?? [];
        $selectedPageId = $this->resolveSourcePageId($pages, $sourcePageSlug);

        if ($contentBlocks !== [] && $pages !== [] && $selectedPageId <= 0) {
            $this->writeError($io, \sprintf('Der angegebene Source-Page-Slug "%s" wurde in der Tabelle "pages" nicht gefunden.', $sourcePageSlug));

            return Command::FAILURE;
        }

        if (
            $participants === []
            && $events === []
            && $registrations === []
            && $newsletterSubscriptions === []
            && $testimonials === []
            && $pages === []
            && $contentBlocks === []
        ) {
            $this->writeError($io, 'Im SQL-Dump wurden keine unterstützten Quelldaten gefunden.');

            return Command::FAILURE;
        }

        $io->section('Quell-Daten');
        $sourceStats = [
            \sprintf('participants: %d', \count($participants)),
            \sprintf('events: %d', \count($events)),
            \sprintf('registrations: %d', \count($registrations)),
            \sprintf('newsletter_subscriptions: %d', \count($newsletterSubscriptions)),
            \sprintf('testimonials: %d', \count($testimonials)),
            \sprintf('pages: %d', \count($pages)),
            \sprintf('content_blocks: %d', \count($contentBlocks)),
        ];
        if ($selectedPageId > 0) {
            $sourceStats[] = \sprintf('selected page id (%s): %d', $sourcePageSlug, $selectedPageId);
        } else {
            $sourceStats[] = \sprintf('selected page id (%s): not found', $sourcePageSlug);
        }
        $io->listing($sourceStats);

        if ((bool) $input->getOption('dry-run')) {
            $io->success('Dry-Run erfolgreich. Keine Daten wurden geschrieben.');

            return Command::SUCCESS;
        }

        $connection = $this->connectionPool->getConnectionForTable('tx_menscircle_domain_model_event');

        try {
            $connection->beginTransaction();

            $hasRows = $this->targetTablesContainRows($connection);
            $hasContentRows = $this->contentElementsContainRows($connection);
            $truncate = (bool) $input->getOption('truncate');

            if (($hasRows || $hasContentRows) && !$truncate) {
                throw new \RuntimeException('Zieltabellen oder Content-Elemente sind nicht leer. Starte mit --truncate oder leere die Datensätze manuell.');
            }

            if ($truncate) {
                $this->truncateTargetTables($connection);
                $this->truncateImportedContentElements($connection);
            }

            $importStats = $this->importAll(
                connection: $connection,
                pid: $pid,
                contentPid: $contentPid,
                selectedSourcePageId: $selectedPageId,
                participants: $participants,
                events: $events,
                registrations: $registrations,
                newsletterSubscriptions: $newsletterSubscriptions,
                testimonials: $testimonials,
                pages: $pages,
                contentBlocks: $contentBlocks
            );

            $connection->commit();

            $io->section('Import Ergebnis');
            $io->listing([
                \sprintf('participants importiert: %d', $importStats['participantsImported']),
                \sprintf('events importiert: %d', $importStats['eventsImported']),
                \sprintf('registrations importiert: %d', $importStats['registrationsImported']),
                \sprintf('registrations übersprungen: %d', $importStats['registrationsSkipped']),
                \sprintf('newsletter_subscriptions importiert: %d', $importStats['newsletterImported']),
                \sprintf('newsletter_subscriptions übersprungen: %d', $importStats['newsletterSkipped']),
                \sprintf('testimonials importiert: %d', $importStats['testimonialsImported']),
                \sprintf('pages erstellt: %d', $importStats['pagesCreated']),
                \sprintf('pages aktualisiert: %d', $importStats['pagesUpdated']),
                \sprintf('content_blocks importiert: %d', $importStats['contentBlocksImported']),
                \sprintf('content_blocks übersprungen: %d', $importStats['contentBlocksSkipped']),
            ]);

            $io->success('Import abgeschlossen.');

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            $this->writeError($io, $exception->getMessage());

            return Command::FAILURE;
        }
    }

    private function writeError(SymfonyStyle $io, string $message): void
    {
        $io->writeln('<error>' . $message . '</error>');
    }

    private function importAll(
        Connection $connection,
        int $pid,
        int $contentPid,
        int $selectedSourcePageId,
        array $participants,
        array $events,
        array $registrations,
        array $newsletterSubscriptions,
        array $testimonials,
        array $pages,
        array $contentBlocks
    ): array {
        $stats = [
            'participantsImported' => 0,
            'eventsImported' => 0,
            'registrationsImported' => 0,
            'registrationsSkipped' => 0,
            'newsletterImported' => 0,
            'newsletterSkipped' => 0,
            'testimonialsImported' => 0,
            'pagesCreated' => 0,
            'pagesUpdated' => 0,
            'contentBlocksImported' => 0,
            'contentBlocksSkipped' => 0,
        ];

        $pageImportResult = $this->importPages(
            connection: $connection,
            rootPageUid: $contentPid,
            sourcePages: $pages,
            selectedSourcePageId: $selectedSourcePageId
        );
        $sourcePageToTargetPageMap = $pageImportResult['pageMap'];
        $stats['pagesCreated'] = $pageImportResult['pagesCreated'];
        $stats['pagesUpdated'] = $pageImportResult['pagesUpdated'];

        $eventPageResult = $this->ensureEventPageAndPlugin(
            connection: $connection,
            rootPageUid: $contentPid
        );
        $stats['pagesCreated'] += $eventPageResult['pagesCreated'];
        $stats['pagesUpdated'] += $eventPageResult['pagesUpdated'];

        $participantIds = [];
        foreach ($participants as $row) {
            $uid = (int) ($row['id'] ?? 0);
            if ($uid <= 0) {
                continue;
            }

            $this->insertRow($connection, 'tx_menscircle_domain_model_participant', [
                'uid' => $uid,
                'pid' => $pid,
                'tstamp' => $this->toUnixTimestamp($row['updated_at'] ?? null),
                'crdate' => $this->toUnixTimestamp($row['created_at'] ?? null),
                'cruser_id' => 0,
                'deleted' => $this->toDeletedFlag($row['deleted_at'] ?? null),
                'hidden' => 0,
                'first_name' => $this->toString($row['first_name'] ?? ''),
                'last_name' => $this->toString($row['last_name'] ?? ''),
                'email' => strtolower($this->toString($row['email'] ?? '')),
                'phone' => $this->toString($row['phone'] ?? ''),
            ]);

            $participantIds[$uid] = true;
            $stats['participantsImported']++;
        }

        $eventIds = [];
        foreach ($events as $row) {
            $uid = (int) ($row['id'] ?? 0);
            if ($uid <= 0) {
                continue;
            }

            $this->insertRow($connection, 'tx_menscircle_domain_model_event', [
                'uid' => $uid,
                'pid' => $pid,
                'tstamp' => $this->toUnixTimestamp($row['updated_at'] ?? null),
                'crdate' => $this->toUnixTimestamp($row['created_at'] ?? null),
                'cruser_id' => 0,
                'deleted' => $this->toDeletedFlag($row['deleted_at'] ?? null),
                'hidden' => 0,
                'starttime' => 0,
                'endtime' => 0,
                'sorting' => $uid * 256,
                'sys_language_uid' => 0,
                'l10n_parent' => 0,
                'l10n_diffsource' => null,
                'title' => $this->toString($row['title'] ?? ''),
                'slug' => $this->toString($row['slug'] ?? ''),
                'teaser' => $this->toString($row['teaser'] ?? ''),
                'description' => $this->toString($row['description'] ?? ''),
                'event_date' => $this->toDateTimestamp($row['event_date'] ?? null),
                'start_time' => $this->toTimeTimestampNullable($row['start_time'] ?? null),
                'end_time' => $this->toTimeTimestampNullable($row['end_time'] ?? null),
                'location' => $this->toString($row['location'] ?? ''),
                'street' => $this->toString($row['street'] ?? ''),
                'postal_code' => $this->toString($row['postal_code'] ?? ''),
                'city' => $this->toString($row['city'] ?? ''),
                'location_details' => $this->toString($row['location_details'] ?? ''),
                'max_participants' => $this->toInt($row['max_participants'] ?? 20, 20),
                'cost_basis' => $this->toString($row['cost_basis'] ?? ''),
                'is_published' => $this->toBoolInt($row['is_published'] ?? 0),
            ]);

            $eventIds[$uid] = true;
            $stats['eventsImported']++;
        }

        foreach ($registrations as $row) {
            $uid = (int) ($row['id'] ?? 0);
            $eventUid = (int) ($row['event_id'] ?? 0);
            $participantUid = (int) ($row['participant_id'] ?? 0);

            if ($uid <= 0 || !isset($eventIds[$eventUid]) || !isset($participantIds[$participantUid])) {
                $stats['registrationsSkipped']++;
                continue;
            }

            $status = strtolower($this->toString($row['status'] ?? 'registered'));
            if ($status === 'confirmed') {
                $status = 'registered';
            }
            if (!\in_array($status, ['registered', 'attended', 'cancelled'], true)) {
                $status = 'registered';
            }

            $this->insertRow($connection, 'tx_menscircle_domain_model_registration', [
                'uid' => $uid,
                'pid' => $pid,
                'tstamp' => $this->toUnixTimestamp($row['updated_at'] ?? null),
                'crdate' => $this->toUnixTimestamp($row['created_at'] ?? null),
                'cruser_id' => 0,
                'deleted' => $this->toDeletedFlag($row['deleted_at'] ?? null),
                'hidden' => 0,
                'event' => $eventUid,
                'participant' => $participantUid,
                'status' => $status,
                'registered_at' => $this->toUnixTimestampNullable($row['registered_at'] ?? $row['confirmed_at'] ?? $row['created_at'] ?? null) ?? time(),
                'cancelled_at' => $this->toUnixTimestampNullable($row['cancelled_at'] ?? null),
            ]);

            $stats['registrationsImported']++;
        }

        $participantByEmail = [];
        foreach ($participants as $row) {
            $email = strtolower(trim($this->toString($row['email'] ?? '')));
            if ($email !== '' && isset($row['id'])) {
                $participantByEmail[$email] = (int) $row['id'];
            }
        }

        foreach ($newsletterSubscriptions as $row) {
            $uid = (int) ($row['id'] ?? 0);
            if ($uid <= 0) {
                $stats['newsletterSkipped']++;
                continue;
            }

            $participantUid = (int) ($row['participant_id'] ?? 0);
            if ($participantUid <= 0 && isset($row['email'])) {
                $email = strtolower(trim($this->toString($row['email'])));
                $participantUid = $participantByEmail[$email] ?? 0;
            }

            if ($participantUid <= 0 || !isset($participantIds[$participantUid])) {
                $stats['newsletterSkipped']++;
                continue;
            }

            $token = trim($this->toString($row['token'] ?? ''));
            if ($token === '') {
                $token = bin2hex(random_bytes(32));
            }

            $this->insertRow($connection, 'tx_menscircle_domain_model_newslettersubscription', [
                'uid' => $uid,
                'pid' => $pid,
                'tstamp' => $this->toUnixTimestamp($row['updated_at'] ?? null),
                'crdate' => $this->toUnixTimestamp($row['created_at'] ?? null),
                'cruser_id' => 0,
                'deleted' => $this->toDeletedFlag($row['deleted_at'] ?? null),
                'hidden' => 0,
                'participant' => $participantUid,
                'token' => $token,
                'subscribed_at' => $this->toUnixTimestampNullable($row['subscribed_at'] ?? $row['created_at'] ?? null) ?? time(),
                'confirmed_at' => $this->toUnixTimestampNullable($row['confirmed_at'] ?? null),
                'unsubscribed_at' => $this->toUnixTimestampNullable($row['unsubscribed_at'] ?? null),
            ]);

            $stats['newsletterImported']++;
        }

        foreach ($testimonials as $row) {
            $uid = (int) ($row['id'] ?? 0);
            if ($uid <= 0) {
                continue;
            }

            $this->insertRow($connection, 'tx_menscircle_domain_model_testimonial', [
                'uid' => $uid,
                'pid' => $pid,
                'tstamp' => $this->toUnixTimestamp($row['updated_at'] ?? null),
                'crdate' => $this->toUnixTimestamp($row['created_at'] ?? null),
                'cruser_id' => 0,
                'deleted' => $this->toDeletedFlag($row['deleted_at'] ?? null),
                'hidden' => 0,
                'quote' => $this->toString($row['quote'] ?? ''),
                'author_name' => $this->toString($row['author_name'] ?? ''),
                'email' => strtolower($this->toString($row['email'] ?? '')),
                'role' => $this->toString($row['role'] ?? ''),
                'is_published' => $this->toBoolInt($row['is_published'] ?? 0),
                'published_at' => $this->toUnixTimestampNullable($row['published_at'] ?? null),
                'sort_order' => $this->toInt($row['sort_order'] ?? 0, 0),
            ]);

            $stats['testimonialsImported']++;
        }

        if ($contentBlocks !== []) {
            usort(
                $contentBlocks,
                static function (array $left, array $right): int {
                    $leftOrder = (int) ($left['order'] ?? 0);
                    $rightOrder = (int) ($right['order'] ?? 0);
                    if ($leftOrder === $rightOrder) {
                        return (int) ($left['id'] ?? 0) <=> (int) ($right['id'] ?? 0);
                    }

                    return $leftOrder <=> $rightOrder;
                }
            );
        }

        $pagePositions = [];
        foreach ($contentBlocks as $row) {
            $sourcePageId = (int) ($row['page_id'] ?? 0);
            $targetPageUid = $sourcePageToTargetPageMap[$sourcePageId] ?? $contentPid;
            $position = $pagePositions[$targetPageUid] ?? 0;

            $mappedRecord = $this->mapContentBlockToTtContentRecord($row, $targetPageUid, $position);
            if ($mappedRecord === null) {
                $stats['contentBlocksSkipped']++;
                continue;
            }

            $this->insertRow($connection, 'tt_content', $mappedRecord);
            $stats['contentBlocksImported']++;
            $pagePositions[$targetPageUid] = $position + 1;
        }

        return $stats;
    }

    /**
     * @param list<array<string, mixed>> $sourcePages
     *
     * @return array{pageMap: array<int, int>, pagesCreated: int, pagesUpdated: int}
     */
    private function importPages(
        Connection $connection,
        int $rootPageUid,
        array $sourcePages,
        int $selectedSourcePageId
    ): array {
        $pageMap = [];
        $pagesCreated = 0;
        $pagesUpdated = 0;

        if ($sourcePages === []) {
            return [
                'pageMap' => $pageMap,
                'pagesCreated' => $pagesCreated,
                'pagesUpdated' => $pagesUpdated,
            ];
        }

        usort(
            $sourcePages,
            static fn(array $left, array $right): int => (int) ($left['id'] ?? 0) <=> (int) ($right['id'] ?? 0)
        );

        if ($selectedSourcePageId <= 0) {
            foreach ($sourcePages as $sourcePage) {
                $slug = strtolower($this->toString($sourcePage['slug'] ?? ''));
                if ($slug === 'home') {
                    $selectedSourcePageId = (int) ($sourcePage['id'] ?? 0);
                    break;
                }
            }
        }

        if ($selectedSourcePageId <= 0) {
            $selectedSourcePageId = (int) ($sourcePages[0]['id'] ?? 0);
        }

        $sortingByPid = [];
        foreach ($sourcePages as $sourcePage) {
            $sourcePageId = (int) ($sourcePage['id'] ?? 0);
            if ($sourcePageId <= 0) {
                continue;
            }

            $sourceSlug = $this->toString($sourcePage['slug'] ?? '');
            $targetTitle = $this->toString($sourcePage['title'] ?? '');
            if ($targetTitle === '') {
                $targetTitle = 'Seite ' . $sourcePageId;
            }

            $meta = $this->decodeJsonAssociative($sourcePage['meta'] ?? null);
            $robotsFlags = $this->extractRobotsFlags($meta);
            $targetSlug = $this->normalizePageSlug($sourceSlug, $sourcePageId);
            $targetHidden = $this->toBoolInt($sourcePage['is_published'] ?? 0) === 1 ? 0 : 1;
            $tstamp = $this->toUnixTimestamp($sourcePage['updated_at'] ?? null);
            $crdate = $this->toUnixTimestamp($sourcePage['created_at'] ?? null);
            $isDeleted = $this->toDeletedFlag($sourcePage['deleted_at'] ?? null);

            if ($sourcePageId === $selectedSourcePageId) {
                $pageMap[$sourcePageId] = $rootPageUid;

                $this->updateRow($connection, 'pages', [
                    'tstamp' => $tstamp,
                    'title' => $targetTitle,
                    'hidden' => $targetHidden,
                    'description' => $this->toString($meta['description'] ?? ''),
                    'no_index' => $robotsFlags['noIndex'],
                    'no_follow' => $robotsFlags['noFollow'],
                ], [
                    'uid' => $rootPageUid,
                ]);

                $pagesUpdated++;
                continue;
            }

            if ($targetSlug === '/') {
                $targetSlug = '/seite-' . $sourcePageId;
            }

            $targetPid = $rootPageUid;
            $existingPageUid = $this->findPageUidBySlug($connection, $targetPid, $targetSlug);
            if ($existingPageUid > 0) {
                $pageMap[$sourcePageId] = $existingPageUid;

                $this->updateRow($connection, 'pages', [
                    'tstamp' => $tstamp,
                    'title' => $targetTitle,
                    'hidden' => $targetHidden,
                    'deleted' => $isDeleted,
                    'description' => $this->toString($meta['description'] ?? ''),
                    'no_index' => $robotsFlags['noIndex'],
                    'no_follow' => $robotsFlags['noFollow'],
                    'slug' => $targetSlug,
                ], [
                    'uid' => $existingPageUid,
                ]);

                $pagesUpdated++;
                continue;
            }

            $sorting = $sortingByPid[$targetPid] ?? 256;
            $this->insertRow($connection, 'pages', [
                'pid' => $targetPid,
                'tstamp' => $tstamp,
                'crdate' => $crdate,
                'cruser_id' => 0,
                'deleted' => $isDeleted,
                'hidden' => $targetHidden,
                'starttime' => 0,
                'endtime' => 0,
                'doktype' => 1,
                'sorting' => $sorting,
                'title' => $targetTitle,
                'slug' => $targetSlug,
                'description' => $this->toString($meta['description'] ?? ''),
                'no_index' => $robotsFlags['noIndex'],
                'no_follow' => $robotsFlags['noFollow'],
            ]);
            $sortingByPid[$targetPid] = $sorting + 256;

            $newPageUid = (int) $connection->lastInsertId();
            if ($newPageUid <= 0) {
                $newPageUid = $this->findPageUidBySlug($connection, $targetPid, $targetSlug);
            }
            if ($newPageUid <= 0) {
                throw new \RuntimeException(\sprintf('Import fehlgeschlagen: Konnte Zielseite für Source-Page %d nicht ermitteln.', $sourcePageId));
            }

            $pageMap[$sourcePageId] = $newPageUid;
            $pagesCreated++;
        }

        return [
            'pageMap' => $pageMap,
            'pagesCreated' => $pagesCreated,
            'pagesUpdated' => $pagesUpdated,
        ];
    }

    /**
     * @return array{pagesCreated: int, pagesUpdated: int}
     */
    private function ensureEventPageAndPlugin(Connection $connection, int $rootPageUid): array
    {
        $pagesCreated = 0;
        $pagesUpdated = 0;

        $eventPageUid = $this->findPageUidBySlug($connection, $rootPageUid, '/event');
        if ($eventPageUid <= 0) {
            $sorting = $this->getNextSortingValueByPid($connection, 'pages', $rootPageUid);
            $now = time();

            $this->insertRow($connection, 'pages', [
                'pid' => $rootPageUid,
                'tstamp' => $now,
                'crdate' => $now,
                'cruser_id' => 0,
                'deleted' => 0,
                'hidden' => 0,
                'starttime' => 0,
                'endtime' => 0,
                'doktype' => 1,
                'sorting' => $sorting,
                'title' => 'Termine',
                'slug' => '/event',
                'description' => 'Aktuelle und kommende Termine vom Männerkreis.',
            ]);

            $eventPageUid = (int) $connection->lastInsertId();
            if ($eventPageUid <= 0) {
                $eventPageUid = $this->findPageUidBySlug($connection, $rootPageUid, '/event');
            }
            if ($eventPageUid <= 0) {
                throw new \RuntimeException('Import fehlgeschlagen: Event-Seite (/event) konnte nicht erstellt werden.');
            }

            $pagesCreated++;
        }

        $eventDetailPluginUid = $this->findContentElementUidByType($connection, $eventPageUid, 'menscircle_eventdetail');
        if ($eventDetailPluginUid <= 0) {
            $sorting = $this->getNextSortingValueByPid($connection, 'tt_content', $eventPageUid);
            $now = time();

            $this->insertRow($connection, 'tt_content', [
                'pid' => $eventPageUid,
                'tstamp' => $now,
                'crdate' => $now,
                'cruser_id' => 0,
                'deleted' => 0,
                'hidden' => 0,
                'starttime' => 0,
                'endtime' => 0,
                'sys_language_uid' => 0,
                'l18n_parent' => 0,
                'l10n_source' => 0,
                'colPos' => 0,
                'CType' => 'menscircle_eventdetail',
                'header' => 'Nächster Termin',
                'sorting' => $sorting,
            ]);
        }

        return [
            'pagesCreated' => $pagesCreated,
            'pagesUpdated' => $pagesUpdated,
        ];
    }

    private function targetTablesContainRows(Connection $connection): bool
    {
        foreach (self::TARGET_TABLES as $table) {
            $count = (int) $connection->count('*', $table, []);
            if ($count > 0) {
                return true;
            }
        }

        return false;
    }

    private function truncateTargetTables(Connection $connection): void
    {
        foreach (self::TARGET_TABLES as $table) {
            $connection->delete($table, []);
        }
    }

    private function contentElementsContainRows(Connection $connection): bool
    {
        $contentTypes = array_values(array_unique(array_values(self::SOURCE_CONTENT_TYPE_TO_CTYPE)));
        if ($contentTypes === []) {
            return false;
        }

        $quotedTypes = array_map(
            static fn(string $type): string => $connection->quote($type),
            $contentTypes
        );
        $inList = implode(',', $quotedTypes);

        $queryBuilder = $connection->createQueryBuilder();
        $count = (int) $queryBuilder
            ->count('*')
            ->from('tt_content')
            ->where(
                \sprintf('(CType IN (%s) OR CType = :emptyType OR CType IS NULL)', $inList)
            )
            ->setParameter('emptyType', '', ParameterType::STRING)
            ->executeQuery()
            ->fetchOne();

        return $count > 0;
    }

    private function truncateImportedContentElements(Connection $connection): void
    {
        $contentTypes = array_values(array_unique(array_values(self::SOURCE_CONTENT_TYPE_TO_CTYPE)));
        if ($contentTypes === []) {
            return;
        }

        $quotedTypes = array_map(
            static fn(string $type): string => $connection->quote($type),
            $contentTypes
        );
        $inList = implode(',', $quotedTypes);

        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder
            ->delete('tt_content')
            ->where(
                \sprintf('(CType IN (%s) OR CType = :emptyType OR CType IS NULL)', $inList)
            )
            ->setParameter('emptyType', '', ParameterType::STRING)
            ->executeStatement();
    }

    /**
     * @param array<string, mixed> $row
     */
    private function insertRow(Connection $connection, string $table, array $row): void
    {
        $filteredRow = $this->filterRowForExistingColumns($connection, $table, $row);
        if ($filteredRow === []) {
            throw new \RuntimeException(\sprintf('Import fehlgeschlagen: Keine passenden Spalten für Tabelle "%s".', $table));
        }
        if ($table === 'tt_content') {
            $recordTypeValue = null;
            foreach ($filteredRow as $column => $value) {
                if (strtolower((string) $column) === 'ctype') {
                    $recordTypeValue = trim((string) $value);
                    break;
                }
            }
            if ($recordTypeValue === null || $recordTypeValue === '') {
                throw new \RuntimeException('Import fehlgeschlagen: Für tt_content fehlt das Pflichtfeld "CType".');
            }
        }

        $connection->insert($table, $filteredRow);
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed> $criteria
     */
    private function updateRow(Connection $connection, string $table, array $row, array $criteria): void
    {
        $filteredRow = $this->filterRowForExistingColumns($connection, $table, $row);
        if ($filteredRow === []) {
            return;
        }

        $filteredCriteria = $this->filterRowForExistingColumns($connection, $table, $criteria);
        if ($filteredCriteria === []) {
            throw new \RuntimeException(\sprintf('Import fehlgeschlagen: Keine gültigen Update-Kriterien für Tabelle "%s".', $table));
        }

        $connection->update($table, $filteredRow, $filteredCriteria);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function filterRowForExistingColumns(Connection $connection, string $table, array $row): array
    {
        $availableColumns = $this->getAvailableColumns($connection, $table);
        $filteredRow = [];
        foreach ($row as $column => $value) {
            $normalizedColumnName = strtolower((string) $column);
            if (isset($availableColumns[$normalizedColumnName])) {
                $filteredRow[$availableColumns[$normalizedColumnName]] = $value;
            }
        }

        return $filteredRow;
    }

    /**
     * @return array<string, string>
     */
    private function getAvailableColumns(Connection $connection, string $table): array
    {
        if (isset($this->tableColumnCache[$table])) {
            return $this->tableColumnCache[$table];
        }

        $columns = [];
        foreach (array_keys($connection->createSchemaManager()->listTableColumns($table)) as $columnName) {
            $columns[strtolower((string) $columnName)] = (string) $columnName;
        }

        $this->tableColumnCache[$table] = $columns;

        return $columns;
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private function parseInsertStatements(string $sql): array
    {
        $parsed = [];

        $pattern = '/INSERT\s+INTO\s+(?:[`"]?[^`".]+[`"]?\.)?[`"]?([a-zA-Z0-9_]+)[`"]?\s*(?:\((.*?)\)\s*)?VALUES\s*(.*?);/is';
        preg_match_all($pattern, $sql, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $table = strtolower((string) $match[1]);
            $columnsPart = isset($match[2]) ? trim((string) $match[2]) : '';

            if ($columnsPart !== '') {
                $columns = array_map(
                    static fn(string $column): string => trim($column, " \t\n\r\0\x0B`\""),
                    explode(',', $columnsPart)
                );
            } else {
                $columns = self::SOURCE_DEFAULT_COLUMNS[$table] ?? [];
            }

            if ($columns === []) {
                continue;
            }

            $tuples = $this->splitSqlTuples((string) $match[3]);
            foreach ($tuples as $tuple) {
                $values = $this->splitSqlFields($tuple);
                if (\count($values) !== \count($columns)) {
                    continue;
                }

                $row = [];
                foreach ($columns as $index => $column) {
                    $row[$column] = $this->decodeSqlValue($values[$index]);
                }

                $parsed[$table][] = $row;
            }
        }

        return $parsed;
    }

    /**
     * @return list<string>
     */
    private function splitSqlTuples(string $valuesPart): array
    {
        $tuples = [];
        $buffer = '';
        $depth = 0;
        $inString = false;
        $escaped = false;
        $length = \strlen($valuesPart);

        for ($i = 0; $i < $length; $i++) {
            $char = $valuesPart[$i];

            if ($inString) {
                $buffer .= $char;

                if ($escaped) {
                    $escaped = false;
                    continue;
                }

                if ($char === '\\') {
                    $escaped = true;
                    continue;
                }

                if ($char === "'") {
                    $inString = false;
                }

                continue;
            }

            if ($char === "'") {
                $inString = true;
                $buffer .= $char;
                continue;
            }

            if ($char === '(') {
                if ($depth > 0) {
                    $buffer .= $char;
                }
                $depth++;
                continue;
            }

            if ($char === ')') {
                $depth--;
                if ($depth === 0) {
                    $tuples[] = $buffer;
                    $buffer = '';
                    continue;
                }
                $buffer .= $char;
                continue;
            }

            if ($depth > 0) {
                $buffer .= $char;
            }
        }

        return $tuples;
    }

    /**
     * @return list<string>
     */
    private function splitSqlFields(string $tuple): array
    {
        $fields = [];
        $buffer = '';
        $inString = false;
        $escaped = false;
        $length = \strlen($tuple);

        for ($i = 0; $i < $length; $i++) {
            $char = $tuple[$i];

            if ($inString) {
                $buffer .= $char;

                if ($escaped) {
                    $escaped = false;
                    continue;
                }

                if ($char === '\\') {
                    $escaped = true;
                    continue;
                }

                if ($char === "'") {
                    $inString = false;
                }

                continue;
            }

            if ($char === "'") {
                $inString = true;
                $buffer .= $char;
                continue;
            }

            if ($char === ',') {
                $fields[] = trim($buffer);
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $fields[] = trim($buffer);

        return $fields;
    }

    private function decodeSqlValue(string $value): mixed
    {
        $trimmed = trim($value);

        if (preg_match('/^(.*?)(::[a-zA-Z0-9_ \[\]]+)$/', $trimmed, $matches) === 1) {
            $trimmed = trim((string) $matches[1]);
        }

        if (strtoupper($trimmed) === 'NULL') {
            return null;
        }

        if ($trimmed === '') {
            return '';
        }

        $lowerValue = strtolower($trimmed);
        if (\in_array($lowerValue, ['true', 'false', 't', 'f'], true)) {
            return \in_array($lowerValue, ['true', 't'], true);
        }

        if (preg_match('/^-?\d+$/', $trimmed) === 1) {
            return (int) $trimmed;
        }

        if (preg_match('/^-?\d+\.\d+$/', $trimmed) === 1) {
            return (float) $trimmed;
        }

        if ($trimmed[0] === "'" && str_ends_with($trimmed, "'")) {
            $inner = substr($trimmed, 1, -1);
            $inner = str_replace("''", "'", $inner);
            $inner = str_replace("\\'", "'", $inner);

            return $inner;
        }

        return $trimmed;
    }

    /**
     * @param list<array<string, mixed>> $pages
     */
    private function resolveSourcePageId(array $pages, string $sourcePageSlug): int
    {
        $slug = strtolower(trim($sourcePageSlug));
        if ($slug === '') {
            return 0;
        }

        foreach ($pages as $page) {
            $pageSlug = strtolower(trim($this->toString($page['slug'] ?? '')));
            if ($pageSlug === $slug) {
                return (int) ($page['id'] ?? 0);
            }
        }

        return 0;
    }

    /**
     * @param list<array<string, mixed>> $contentBlocks
     *
     * @return list<array<string, mixed>>
     */
    private function filterContentBlocksForPage(array $contentBlocks, int $sourcePageId): array
    {
        if ($sourcePageId <= 0) {
            return $contentBlocks;
        }

        return array_values(
            array_filter(
                $contentBlocks,
                static fn(array $row): bool => (int) ($row['page_id'] ?? 0) === $sourcePageId
            )
        );
    }

    private function findPageUidBySlug(Connection $connection, int $pid, string $slug): int
    {
        if ($pid <= 0 || $slug === '') {
            return 0;
        }

        $queryBuilder = $connection->createQueryBuilder();
        $uid = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', ':pid'),
                $queryBuilder->expr()->eq('slug', ':slug')
            )
            ->setParameter('pid', $pid, ParameterType::INTEGER)
            ->setParameter('slug', $slug, ParameterType::STRING)
            ->orderBy('uid', 'ASC')
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        return $uid === false ? 0 : (int) $uid;
    }

    private function findContentElementUidByType(Connection $connection, int $pid, string $cType): int
    {
        if ($pid <= 0 || $cType === '') {
            return 0;
        }

        $queryBuilder = $connection->createQueryBuilder();
        $uid = $queryBuilder
            ->select('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', ':pid'),
                $queryBuilder->expr()->eq('CType', ':ctype'),
                $queryBuilder->expr()->eq('deleted', ':deleted')
            )
            ->setParameter('pid', $pid, ParameterType::INTEGER)
            ->setParameter('ctype', $cType, ParameterType::STRING)
            ->setParameter('deleted', 0, ParameterType::INTEGER)
            ->orderBy('uid', 'ASC')
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        return $uid === false ? 0 : (int) $uid;
    }

    private function getNextSortingValueByPid(Connection $connection, string $table, int $pid): int
    {
        $queryBuilder = $connection->createQueryBuilder();
        $maxSorting = $queryBuilder
            ->selectLiteral('MAX(sorting)')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('pid', ':pid')
            )
            ->setParameter('pid', $pid, ParameterType::INTEGER)
            ->executeQuery()
            ->fetchOne();

        $nextSorting = (int) $maxSorting + 256;
        if ($nextSorting <= 0) {
            return 256;
        }

        return $nextSorting;
    }

    /**
     * @param array<string, mixed> $meta
     *
     * @return array{noIndex: int, noFollow: int}
     */
    private function extractRobotsFlags(array $meta): array
    {
        $robots = strtolower($this->toString($meta['robots'] ?? ''));

        return [
            'noIndex' => str_contains($robots, 'noindex') ? 1 : 0,
            'noFollow' => str_contains($robots, 'nofollow') ? 1 : 0,
        ];
    }

    private function normalizePageSlug(string $sourceSlug, int $fallbackId): string
    {
        $slug = strtolower(trim($sourceSlug));
        if ($slug === '' || $slug === 'home') {
            return '/';
        }

        $slug = str_replace('\\', '/', $slug);
        $slug = preg_replace('#[^a-z0-9/_-]+#', '-', $slug) ?? '';
        $slug = preg_replace('#/{2,}#', '/', $slug) ?? '';
        $slug = trim($slug, '/');

        if ($slug === '') {
            $slug = 'seite-' . $fallbackId;
        }

        return '/' . $slug;
    }

    /**
     * @param array<string, mixed> $sourceRow
     *
     * @return array<string, mixed>|null
     */
    private function mapContentBlockToTtContentRecord(array $sourceRow, int $contentPid, int $position): ?array
    {
        $sourceType = strtolower(trim($this->toString($sourceRow['type'] ?? '')));
        if ($sourceType === '') {
            return null;
        }

        $cType = self::SOURCE_CONTENT_TYPE_TO_CTYPE[$sourceType] ?? '';
        if ($cType === '') {
            return null;
        }

        $data = $this->decodeJsonAssociative($sourceRow['data'] ?? null);
        $header = '';
        $subheader = '';
        $bodytext = '';
        $headerLink = '';
        $flexFormValues = [];

        switch ($cType) {
            case 'menscircle_hero':
                $subheader = $this->toString($data['label'] ?? $data['eyebrow'] ?? '');
                $header = $this->toString($data['title'] ?? '');
                $bodytext = $this->toString($data['description'] ?? '');
                $headerLink = $this->toString($data['button_link'] ?? '');
                $flexFormValues['settings.buttonLabel'] = $this->toString($data['button_text'] ?? '');
                break;

            case 'menscircle_intro':
                $subheader = $this->toString($data['eyebrow'] ?? '');
                $header = $this->toString($data['title'] ?? '');
                $bodytext = $this->toString($data['text'] ?? '');
                $flexFormValues['settings.quote'] = $this->toString($data['quote'] ?? '');

                $values = $this->toArray($data['values'] ?? []);
                for ($index = 1; $index <= 4; $index++) {
                    $valueRow = $this->toArray($values[$index - 1] ?? []);
                    $flexFormValues[\sprintf('settings.value%dNumber', $index)] = $this->toString($valueRow['number'] ?? '');
                    $flexFormValues[\sprintf('settings.value%dTitle', $index)] = $this->toString($valueRow['title'] ?? '');
                    $flexFormValues[\sprintf('settings.value%dDescription', $index)] = $this->toString($valueRow['description'] ?? '');
                }
                break;

            case 'menscircle_text_section':
                $subheader = $this->toString($data['eyebrow'] ?? '');
                $header = $this->toString($data['title'] ?? '');
                $bodytext = $this->toString($data['content'] ?? $data['text'] ?? '');
                break;

            case 'menscircle_value_items':
                $subheader = $this->toString($data['eyebrow'] ?? '');
                $header = $this->toString($data['title'] ?? '');
                $bodytext = $this->toString($data['text'] ?? '');

                $items = $this->toArray($data['items'] ?? []);
                for ($index = 1; $index <= 6; $index++) {
                    $itemRow = $this->toArray($items[$index - 1] ?? []);
                    $flexFormValues[\sprintf('settings.item%dNumber', $index)] = $this->toString($itemRow['number'] ?? '');
                    $flexFormValues[\sprintf('settings.item%dTitle', $index)] = $this->toString($itemRow['title'] ?? '');
                    $flexFormValues[\sprintf('settings.item%dDescription', $index)] = $this->toString($itemRow['description'] ?? '');
                }
                break;

            case 'menscircle_moderator':
                $subheader = $this->toString($data['eyebrow'] ?? '');
                $header = $this->toString($data['name'] ?? $data['title'] ?? '');
                $bodytext = $this->toString($data['bio'] ?? $data['text'] ?? '');
                $flexFormValues['settings.quote'] = $this->toString($data['quote'] ?? '');
                break;

            case 'menscircle_journey_steps':
                $subheader = $this->toString($data['eyebrow'] ?? '');
                $header = $this->toString($data['title'] ?? '');
                $bodytext = $this->toString($data['subtitle'] ?? '');
                $flexFormValues['settings.subtitle'] = $this->toString($data['subtitle'] ?? '');

                $steps = $this->toArray($data['steps'] ?? []);
                for ($index = 1; $index <= 6; $index++) {
                    $stepRow = $this->toArray($steps[$index - 1] ?? []);
                    $flexFormValues[\sprintf('settings.step%dNumber', $index)] = $this->toString($stepRow['number'] ?? '');
                    $flexFormValues[\sprintf('settings.step%dTitle', $index)] = $this->toString($stepRow['title'] ?? '');
                    $flexFormValues[\sprintf('settings.step%dDescription', $index)] = $this->toString($stepRow['description'] ?? '');
                }
                break;

            case 'menscircle_testimonials':
                $subheader = $this->toString($data['eyebrow'] ?? 'Community Stimmen');
                $header = $this->toString($data['title'] ?? '');
                $bodytext = $this->toString($data['subtitle'] ?? $data['text'] ?? '');
                $flexFormValues['settings.limit'] = $this->toString($data['limit'] ?? '');
                $flexFormValues['settings.emptyMessage'] = $this->toString($data['empty_message'] ?? '');
                break;

            case 'menscircle_faq':
                $subheader = $this->toString($data['eyebrow'] ?? '');
                $header = $this->toString($data['title'] ?? '');
                $bodytext = $this->toString($data['intro'] ?? '');

                $items = $this->toArray($data['items'] ?? []);
                for ($index = 1; $index <= 6; $index++) {
                    $itemRow = $this->toArray($items[$index - 1] ?? []);
                    $flexFormValues[\sprintf('settings.item%dQuestion', $index)] = $this->toString($itemRow['question'] ?? '');
                    $flexFormValues[\sprintf('settings.item%dAnswer', $index)] = $this->toString($itemRow['answer'] ?? '');
                }
                break;

            case 'menscircle_newsletter_section':
                $subheader = $this->toString($data['eyebrow'] ?? 'Newsletter');
                $header = $this->toString($data['title'] ?? '');
                $bodytext = $this->toString($data['text'] ?? '');
                $headerLink = $this->toString($data['button_link'] ?? '');
                $flexFormValues['settings.buttonLabel'] = $this->toString($data['button_text'] ?? '');
                $flexFormValues['settings.privacyLabel'] = $this->toString($data['privacy_label'] ?? $data['privacy_text'] ?? '');
                break;

            case 'menscircle_cta':
                $subheader = $this->toString($data['eyebrow'] ?? '');
                $header = $this->toString($data['title'] ?? '');
                $bodytext = $this->toString($data['text'] ?? '');
                $headerLink = $this->toString($data['button_link'] ?? '');
                $flexFormValues['settings.buttonLabel'] = $this->toString($data['button_text'] ?? '');
                break;

            case 'menscircle_whatsapp_community':
                $subheader = $this->toString($data['eyebrow'] ?? 'Community');
                $header = $this->toString($data['title'] ?? '');
                $bodytext = $this->toString($data['text'] ?? '');
                $headerLink = $this->toString($data['button_link'] ?? $data['whatsapp_link'] ?? '');
                $flexFormValues['settings.buttonLabel'] = $this->toString($data['button_text'] ?? '');
                break;

            default:
                return null;
        }

        $flexFormXml = $this->buildFlexFormXml($flexFormValues);

        return [
            'pid' => $contentPid,
            'tstamp' => $this->toUnixTimestamp($sourceRow['updated_at'] ?? null),
            'crdate' => $this->toUnixTimestamp($sourceRow['created_at'] ?? null),
            'cruser_id' => 0,
            'deleted' => $this->toDeletedFlag($sourceRow['deleted_at'] ?? null),
            'hidden' => 0,
            'starttime' => 0,
            'endtime' => 0,
            'sys_language_uid' => 0,
            'l10n_parent' => 0,
            'l10n_diffsource' => null,
            'colPos' => 0,
            'CType' => $cType,
            'header' => $header,
            'subheader' => $subheader,
            'bodytext' => $bodytext,
            'header_link' => $headerLink,
            'pi_flexform' => $flexFormXml,
            'sorting' => ($position + 1) * 256,
        ];
    }

    /**
     * @param array<string, string> $values
     */
    private function buildFlexFormXml(array $values): string
    {
        $normalizedValues = [];
        foreach ($values as $fieldName => $fieldValue) {
            $value = $this->toString($fieldValue);
            if ($value === '') {
                continue;
            }
            $normalizedValues[$fieldName] = $value;
        }

        if ($normalizedValues === []) {
            return '';
        }

        $xml = new \SimpleXMLElement('<T3FlexForms><data><sheet index="sDEF"><language index="lDEF"></language></sheet></data></T3FlexForms>');
        $languageNodes = $xml->xpath('/T3FlexForms/data/sheet/language');
        if (!\is_array($languageNodes) || !isset($languageNodes[0]) || !$languageNodes[0] instanceof \SimpleXMLElement) {
            return '';
        }
        $languageNode = $languageNodes[0];

        foreach ($normalizedValues as $fieldName => $fieldValue) {
            $fieldNode = $languageNode->addChild('field');
            $fieldNode->addAttribute('index', $fieldName);
            $valueNode = $fieldNode->addChild('value');
            $valueNode->addAttribute('index', 'vDEF');
            $valueNode[0] = $fieldValue;
        }

        $xmlString = $xml->asXML();

        return $xmlString === false ? '' : $xmlString;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonAssociative(mixed $value): array
    {
        if (\is_array($value)) {
            return $value;
        }

        if (!\is_string($value)) {
            return [];
        }

        $json = trim($value);
        if ($json === '' || strtolower($json) === 'null') {
            return [];
        }

        $decoded = json_decode($json, true);
        if (\is_array($decoded)) {
            return $decoded;
        }

        return [];
    }

    /**
     * @return array<int|string, mixed>
     */
    private function toArray(mixed $value): array
    {
        return \is_array($value) ? $value : [];
    }

    private function toUnixTimestamp(mixed $value): int
    {
        $dateTime = $this->createDateTimeImmutable($value);

        return $dateTime?->getTimestamp() ?? time();
    }

    private function toUnixTimestampNullable(mixed $value): ?int
    {
        $dateTime = $this->createDateTimeImmutable($value);

        return $dateTime?->getTimestamp();
    }

    private function toDateTimestamp(mixed $value): int
    {
        $dateTime = $this->createDateTimeImmutable($value);
        if (!$dateTime instanceof \DateTimeImmutable) {
            return strtotime('today') ?: time();
        }

        return $dateTime->setTime(0, 0, 0)->getTimestamp();
    }

    private function toDateTimeString(mixed $value): ?string
    {
        $dateTime = $this->createDateTimeImmutable($value);

        return $dateTime?->format('Y-m-d H:i:s');
    }

    private function toTimeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);
        if ($string === '' || $string === '0000-00-00 00:00:00') {
            return null;
        }

        if (preg_match('/^\d{2}:\d{2}$/', $string) === 1) {
            return $string . ':00';
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $string) === 1) {
            return $string;
        }

        $dateTime = $this->createDateTimeImmutable($value);

        return $dateTime?->format('H:i:s');
    }

    private function toTimeTimestampNullable(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);
        if ($string === '' || $string === '0000-00-00 00:00:00') {
            return null;
        }

        if (preg_match('/^(\d{2}):(\d{2})(?::(\d{2}))?$/', $string, $matches) === 1) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            $seconds = isset($matches[3]) ? (int) $matches[3] : 0;

            if ($hours > 23 || $minutes > 59 || $seconds > 59) {
                return null;
            }

            return $hours * 3600 + $minutes * 60 + $seconds;
        }

        if (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $string, $matches) === 1) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            $seconds = (int) $matches[3];

            if ($hours > 23 || $minutes > 59 || $seconds > 59) {
                return null;
            }

            return $hours * 3600 + $minutes * 60 + $seconds;
        }

        $dateTime = $this->createDateTimeImmutable($value);
        if (!$dateTime instanceof \DateTimeImmutable) {
            return null;
        }

        return ((int) $dateTime->format('H') * 3600)
            + ((int) $dateTime->format('i') * 60)
            + (int) $dateTime->format('s');
    }

    private function createDateTimeImmutable(mixed $value): ?\DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        if (\is_int($value) || \is_float($value)) {
            $numeric = (int) $value;
            if ($numeric <= 0) {
                return null;
            }

            $numericString = (string) $numeric;
            if (preg_match('/^\d{14}$/', $numericString) === 1) {
                $dateTime = \DateTimeImmutable::createFromFormat('YmdHis', $numericString);

                return $dateTime instanceof \DateTimeImmutable ? $dateTime : null;
            }

            if (preg_match('/^\d{8}$/', $numericString) === 1) {
                $dateTime = \DateTimeImmutable::createFromFormat('Ymd', $numericString);

                return $dateTime instanceof \DateTimeImmutable ? $dateTime : null;
            }

            if ($numeric <= 4102444800) {
                return new \DateTimeImmutable()->setTimestamp($numeric);
            }

            return null;
        }

        $string = trim((string) $value);
        if ($string === '' || $string === '0000-00-00 00:00:00') {
            return null;
        }

        if (preg_match('/^\d+$/', $string) === 1) {
            if (preg_match('/^\d{14}$/', $string) === 1) {
                $dateTime = \DateTimeImmutable::createFromFormat('YmdHis', $string);

                return $dateTime instanceof \DateTimeImmutable ? $dateTime : null;
            }

            if (preg_match('/^\d{8}$/', $string) === 1) {
                $dateTime = \DateTimeImmutable::createFromFormat('Ymd', $string);

                return $dateTime instanceof \DateTimeImmutable ? $dateTime : null;
            }

            $numeric = (int) $string;
            if ($numeric > 0 && $numeric <= 4102444800) {
                return new \DateTimeImmutable()->setTimestamp($numeric);
            }

            return null;
        }

        try {
            return new \DateTimeImmutable($string);
        } catch (\Throwable) {
            return null;
        }
    }

    private function toDeletedFlag(mixed $deletedAt): int
    {
        return $this->createDateTimeImmutable($deletedAt) instanceof \DateTimeImmutable ? 1 : 0;
    }

    private function toString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    private function toInt(mixed $value, int $fallback = 0): int
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        return (int) $value;
    }

    private function toBoolInt(mixed $value): int
    {
        if (\is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (\is_int($value)) {
            return $value > 0 ? 1 : 0;
        }

        $string = strtolower(trim((string) $value));

        return \in_array($string, ['1', 'true', 'yes', 't'], true) ? 1 : 0;
    }
}
