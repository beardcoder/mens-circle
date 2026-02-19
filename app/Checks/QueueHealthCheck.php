<?php

declare(strict_types=1);

namespace App\Checks;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Override;
use RuntimeException;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Throwable;

class QueueHealthCheck extends Check
{
    #[Override]
    public function run(): Result
    {
        $result = Result::make();

        /** @var string $connection */
        $connection = Config::get('queue.default', 'sync');

        // Sync queue is only for local development
        if ($connection === 'sync') {
            return $result
                ->warning("Queue-Treiber ist 'sync' (nicht für Produktion)")
                ->shortSummary($connection);
        }

        // Null queue means no jobs are processed
        if ($connection === 'null') {
            return $result
                ->warning("Queue-Treiber ist 'null' (Jobs werden nicht verarbeitet)")
                ->shortSummary($connection);
        }

        try {
            // For database queue, check the jobs table exists
            if ($connection === 'database') {
                $this->checkDatabaseQueue();
            }

            // For redis queue, check the connection
            if ($connection === 'redis') {
                $this->checkRedisQueue();
            }

            // Try to get queue size (works for most drivers)
            $size = Queue::size($connection);

            return $result
                ->ok()
                ->shortSummary("{$connection} ({$size} jobs)")
                ->meta([
                    'connection' => $connection,
                    'jobs_in_queue' => $size,
                ]);
        } catch (Throwable $throwable) {
            return $result
                ->failed('Queue-Verbindungsfehler: ' . $throwable->getMessage())
                ->shortSummary('Verbindungsfehler')
                ->meta([
                    'error' => $throwable->getMessage(),
                ]);
        }
    }

    /**
     * Check database queue (works with PostgreSQL, MySQL, SQLite, etc.)
     */
    private function checkDatabaseQueue(): void
    {
        // Check if jobs table exists
        if (!DB::getSchemaBuilder()->hasTable('jobs')) {
            throw new RuntimeException('Queue-Tabelle "jobs" existiert nicht');
        }

        // Check if failed_jobs table exists
        if (!DB::getSchemaBuilder()->hasTable('failed_jobs')) {
            throw new RuntimeException('Queue-Tabelle "failed_jobs" existiert nicht');
        }
    }

    private function checkRedisQueue(): void
    {
        /** @var string $redisConnection */
        $redisConnection = Config::get('queue.connections.redis.connection', 'default');
        // Try to ping redis
        $redis = app('redis')
            ->connection($redisConnection);
        $redis->ping();
    }
}
