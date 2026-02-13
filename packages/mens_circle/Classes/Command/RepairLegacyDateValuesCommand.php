<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Command;

use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\SmallIntType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

#[AsCommand(
    name: 'menscircle:repair:legacy-datetime',
    description: 'Repairs legacy imported datetime and time values in mens_circle tables.'
)]
final class RepairLegacyDateValuesCommand extends Command
{
    /**
     * @var array<string, list<string>>
     */
    private const TIMESTAMP_FIELDS = [
        'tx_menscircle_domain_model_event' => ['event_date'],
        'tx_menscircle_domain_model_registration' => ['registered_at', 'cancelled_at'],
        'tx_menscircle_domain_model_newslettersubscription' => ['subscribed_at', 'confirmed_at', 'unsubscribed_at'],
        'tx_menscircle_domain_model_testimonial' => ['published_at'],
    ];

    /**
     * @var array<string, list<string>>
     */
    private const TIME_FIELDS = [
        'tx_menscircle_domain_model_event' => ['start_time', 'end_time'],
    ];

    /**
     * @var array<string, array<string, bool>>
     */
    private array $integerColumnCache = [];

    public function __construct(private readonly ConnectionPool $connectionPool)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('mens_circle legacy datetime repair');

        $connection = $this->connectionPool->getConnectionForTable('tx_menscircle_domain_model_event');

        try {
            $connection->beginTransaction();

            $changes = 0;

            foreach (self::TIMESTAMP_FIELDS as $table => $fields) {
                foreach ($fields as $field) {
                    if (!$this->isIntegerColumn($connection, $table, $field)) {
                        continue;
                    }

                    $changes += $this->repairTimestampField($connection, $table, $field, $field === 'event_date');
                }
            }

            foreach (self::TIME_FIELDS as $table => $fields) {
                foreach ($fields as $field) {
                    if (!$this->isIntegerColumn($connection, $table, $field)) {
                        continue;
                    }

                    $changes += $this->repairTimeField($connection, $table, $field);
                }
            }

            $connection->commit();

            $io->success(\sprintf('Repair completed. Updated values: %d', $changes));

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

    private function repairTimestampField(Connection $connection, string $table, string $field, bool $dateOnly): int
    {
        $changes = 0;
        $quotedField = $connection->quoteIdentifier($field);
        $quotedTable = $connection->quoteIdentifier($table);

        $timestampExpression = $dateOnly
            ? \sprintf('UNIX_TIMESTAMP(DATE(STR_TO_DATE(CAST(%1$s AS CHAR), "%%Y%%m%%d%%H%%i%%s")))', $quotedField)
            : \sprintf('UNIX_TIMESTAMP(STR_TO_DATE(CAST(%1$s AS CHAR), "%%Y%%m%%d%%H%%i%%s"))', $quotedField);

        $changes += $connection->executeStatement(
            \sprintf(
                'UPDATE %1$s SET %2$s = %3$s WHERE %2$s IS NOT NULL AND %2$s > 4102444800 AND CHAR_LENGTH(CAST(%2$s AS CHAR)) = 14',
                $quotedTable,
                $quotedField,
                $timestampExpression
            )
        );

        $changes += $connection->executeStatement(
            \sprintf(
                'UPDATE %1$s SET %2$s = FLOOR(%2$s / 1000) WHERE %2$s IS NOT NULL AND %2$s > 4102444800 AND CHAR_LENGTH(CAST(%2$s AS CHAR)) = 13',
                $quotedTable,
                $quotedField
            )
        );

        if ($dateOnly) {
            $changes += $connection->executeStatement(
                \sprintf(
                    'UPDATE %1$s SET %2$s = UNIX_TIMESTAMP(STR_TO_DATE(CAST(%2$s AS CHAR), "%%Y%%m%%d")) WHERE %2$s IS NOT NULL AND %2$s > 4102444800 AND CHAR_LENGTH(CAST(%2$s AS CHAR)) = 8',
                    $quotedTable,
                    $quotedField
                )
            );

            $changes += $connection->executeStatement(
                \sprintf(
                    'UPDATE %1$s SET %2$s = UNIX_TIMESTAMP(DATE(FROM_UNIXTIME(%2$s))) WHERE %2$s IS NOT NULL AND %2$s > 0 AND %2$s <= 4102444800 AND %2$s <> UNIX_TIMESTAMP(DATE(FROM_UNIXTIME(%2$s)))',
                    $quotedTable,
                    $quotedField
                )
            );
        }

        return $changes;
    }

    private function repairTimeField(Connection $connection, string $table, string $field): int
    {
        $changes = 0;
        $quotedField = $connection->quoteIdentifier($field);
        $quotedTable = $connection->quoteIdentifier($table);

        $changes += $connection->executeStatement(
            \sprintf(
                'UPDATE %1$s SET %2$s = TIME_TO_SEC(TIME(STR_TO_DATE(CAST(%2$s AS CHAR), "%%Y%%m%%d%%H%%i%%s"))) WHERE %2$s IS NOT NULL AND %2$s > 4102444800 AND CHAR_LENGTH(CAST(%2$s AS CHAR)) = 14',
                $quotedTable,
                $quotedField
            )
        );

        $changes += $connection->executeStatement(
            \sprintf(
                'UPDATE %1$s SET %2$s = ((%2$s DIV 10000) * 3600) + (((%2$s DIV 100) %% 100) * 60) + (%2$s %% 100) WHERE %2$s IS NOT NULL AND %2$s > 86400 AND %2$s <= 235959',
                $quotedTable,
                $quotedField
            )
        );

        return $changes;
    }

    private function isIntegerColumn(Connection $connection, string $table, string $field): bool
    {
        $columnTypes = $this->getIntegerColumnMap($connection, $table);
        $normalizedField = strtolower($field);

        if (!isset($columnTypes[$normalizedField])) {
            return false;
        }

        return $columnTypes[$normalizedField];
    }

    /**
     * @return array<string, bool>
     */
    private function getIntegerColumnMap(Connection $connection, string $table): array
    {
        if (isset($this->integerColumnCache[$table])) {
            return $this->integerColumnCache[$table];
        }

        $columnTypes = [];
        foreach ($connection->createSchemaManager()->listTableColumns($table) as $columnName => $columnDefinition) {
            $type = $columnDefinition->getType();
            $columnTypes[strtolower((string) $columnName)] = $type instanceof IntegerType
                || $type instanceof BigIntType
                || $type instanceof SmallIntType;
        }

        $this->integerColumnCache[$table] = $columnTypes;

        return $columnTypes;
    }
}
