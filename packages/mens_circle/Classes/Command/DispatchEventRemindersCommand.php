<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Command;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use BeardCoder\MensCircle\Domain\Enum\RegistrationStatus;
use BeardCoder\MensCircle\Message\SendEventMailMessage;
use BeardCoder\MensCircle\Message\SendEventSmsMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\ConnectionPool;

#[AsCommand(
    name: 'menscircle:events:dispatch-reminders',
    description: 'Queues reminder messages for upcoming events via TYPO3 Message Bus.'
)]
final class DispatchEventRemindersCommand extends Command
{
    private const REGISTRATION_TABLE = 'tx_menscircle_domain_model_registration';
    private const EVENT_TABLE = 'tx_menscircle_domain_model_event';
    private const PARTICIPANT_TABLE = 'tx_menscircle_domain_model_participant';
    private const CACHE_KEY_PREFIX = 'menscircle_event_reminder_';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly MessageBusInterface $messageBus,
        private readonly CacheManager $cacheManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('hours-before', null, InputOption::VALUE_REQUIRED, 'Hours before event start when reminders are sent.', '24')
            ->addOption('window-minutes', null, InputOption::VALUE_REQUIRED, 'Dispatch time window in minutes.', '120')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Validate only, do not dispatch messages.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $hoursBefore = max(1, (int) $input->getOption('hours-before'));
        $windowMinutes = max(1, (int) $input->getOption('window-minutes'));
        $dryRun = (bool) $input->getOption('dry-run');

        $now = new \DateTimeImmutable('now');
        $todayStart = $now->setTime(0, 0, 0);
        $windowStart = $now->modify('+' . $hoursBefore . ' hours');
        $windowEnd = $windowStart->modify('+' . $windowMinutes . ' minutes');
        $upperDateBound = $windowEnd->modify('+1 day');

        $settings = $this->buildNotificationSettings();
        $rows = $this->fetchReminderCandidates($todayStart, $upperDateBound);

        $cache = $this->cacheManager->getCache('hash');

        $stats = [
            'candidates' => \count($rows),
            'queuedMails' => 0,
            'queuedSms' => 0,
            'skippedOutsideWindow' => 0,
            'skippedDuplicate' => 0,
            'skippedMissingEmail' => 0,
            'skippedMissingPhone' => 0,
        ];

        foreach ($rows as $row) {
            $eventStart = $this->resolveEventStart(
                eventDate: (string) ($row['event_date'] ?? ''),
                startTime: (string) ($row['start_time'] ?? '')
            );

            if (!$eventStart instanceof \DateTimeImmutable || $eventStart < $windowStart || $eventStart > $windowEnd) {
                $stats['skippedOutsideWindow']++;
                continue;
            }

            $registrationUid = (int) ($row['uid'] ?? 0);
            if ($registrationUid <= 0) {
                $stats['skippedOutsideWindow']++;
                continue;
            }

            $cacheKey = self::CACHE_KEY_PREFIX . sha1($registrationUid . '|' . $eventStart->format('YmdHi'));
            if ($cache->has($cacheKey)) {
                $stats['skippedDuplicate']++;
                continue;
            }

            $email = strtolower(trim((string) ($row['email'] ?? '')));
            $phone = trim((string) ($row['phone'] ?? ''));

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $stats['skippedMissingEmail']++;
                continue;
            }

            if (!$dryRun) {
                $this->messageBus->dispatch(new SendEventMailMessage(
                    registrationUid: $registrationUid,
                    type: SendEventMailMessage::TYPE_REMINDER,
                    settings: $settings
                ));
            }
            $stats['queuedMails']++;

            if ($phone !== '') {
                if (!$dryRun) {
                    $this->messageBus->dispatch(new SendEventSmsMessage(
                        registrationUid: $registrationUid,
                        type: SendEventSmsMessage::TYPE_REMINDER,
                        settings: $settings
                    ));
                }
                $stats['queuedSms']++;
            } else {
                $stats['skippedMissingPhone']++;
            }

            if (!$dryRun) {
                $cache->set($cacheKey, '1', ['menscircle_event_reminder'], 172800);
            }
        }

        $io->title('Event Reminder Dispatch');
        $io->listing([
            'Jetzt: ' . $now->format('Y-m-d H:i:s'),
            'Erinnerungsfenster: ' . $windowStart->format('Y-m-d H:i:s') . ' bis ' . $windowEnd->format('Y-m-d H:i:s'),
            'Kandidaten: ' . $stats['candidates'],
            'Mail queued: ' . $stats['queuedMails'],
            'SMS queued: ' . $stats['queuedSms'],
            'Skip ausserhalb Fenster: ' . $stats['skippedOutsideWindow'],
            'Skip bereits erinnert: ' . $stats['skippedDuplicate'],
            'Skip ohne gueltige E-Mail: ' . $stats['skippedMissingEmail'],
            'Skip ohne Telefon: ' . $stats['skippedMissingPhone'],
            $dryRun ? 'Dry-Run: ja' : 'Dry-Run: nein',
        ]);

        $io->success($dryRun ? 'Dry-Run erfolgreich.' : 'Reminder-Nachrichten wurden an den Message Bus uebergeben.');

        return Command::SUCCESS;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchReminderCandidates(\DateTimeImmutable $todayStart, \DateTimeImmutable $upperDateBound): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::REGISTRATION_TABLE);
        $queryBuilder->getRestrictions()->removeAll();

        $rows = $queryBuilder
            ->select(
                'registration.uid',
                'participant.email',
                'participant.phone',
                'event.event_date',
                'event.start_time'
            )
            ->from(self::REGISTRATION_TABLE, 'registration')
            ->innerJoin(
                'registration',
                self::EVENT_TABLE,
                'event',
                $queryBuilder->expr()->eq('event.uid', $queryBuilder->quoteIdentifier('registration.event'))
            )
            ->innerJoin(
                'registration',
                self::PARTICIPANT_TABLE,
                'participant',
                $queryBuilder->expr()->eq('participant.uid', $queryBuilder->quoteIdentifier('registration.participant'))
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
                    $queryBuilder->createNamedParameter($todayStart->format('Y-m-d H:i:s'), ParameterType::STRING)
                ),
                $queryBuilder->expr()->lte(
                    'event.event_date',
                    $queryBuilder->createNamedParameter($upperDateBound->format('Y-m-d H:i:s'), ParameterType::STRING)
                ),
                $queryBuilder->expr()->eq('participant.deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('participant.hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->orderBy('event.event_date', 'ASC')
            ->addOrderBy('event.start_time', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return \is_array($rows) ? $rows : [];
    }

    private function resolveEventStart(string $eventDate, string $startTime): ?\DateTimeImmutable
    {
        $eventDate = trim($eventDate);
        if ($eventDate === '' || $eventDate === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            $start = new \DateTimeImmutable($eventDate);
        } catch (\Throwable) {
            return null;
        }

        $startTime = trim($startTime);
        if ($startTime === '' || $startTime === '00:00:00') {
            return $start;
        }

        $time = \DateTimeImmutable::createFromFormat('H:i:s', $startTime)
            ?: \DateTimeImmutable::createFromFormat('H:i', $startTime);
        if (!$time instanceof \DateTimeImmutable) {
            return $start;
        }

        return $start->setTime((int) $time->format('H'), (int) $time->format('i'), (int) $time->format('s'));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildNotificationSettings(): array
    {
        return [
            'siteName' => (string) ($GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] ?? 'Männerkreis'),
        ];
    }
}
