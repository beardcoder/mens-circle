<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\CpuLoadHealthCheck\CpuLoadCheck;
use Spatie\Health\Checks\Checks\BackupsCheck;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DatabaseConnectionCountCheck;
use Spatie\Health\Checks\Checks\DatabaseTableSizeCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\QueueCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;
use Spatie\SecurityAdvisoriesHealthCheck\SecurityAdvisoriesCheck;

class HealthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Health::checks([
            // Environment & Configuration Checks
            EnvironmentCheck::new(),
            DebugModeCheck::new(),
            OptimizedAppCheck::new(),

            // Infrastructure & System Checks
            CacheCheck::new(),
            DatabaseCheck::new(),
            DatabaseConnectionCountCheck::new()
                ->warnWhenMoreConnectionsThan(50)
                ->failWhenMoreConnectionsThan(100),
            UsedDiskSpaceCheck::new()
                ->warnWhenUsedSpaceIsAbovePercentage(80)
                ->failWhenUsedSpaceIsAbovePercentage(90),
            CpuLoadCheck::new()
                ->failWhenLoadIsHigherInTheLast5Minutes(2.0)
                ->failWhenLoadIsHigherInTheLast15Minutes(1.5),

            // Job & Scheduling Checks
            QueueCheck::new()
                ->useCacheStore('health_checks'),
            ScheduleCheck::new()
                ->useCacheStore('health_checks'),

            // Database Table Size Monitoring
            DatabaseTableSizeCheck::new()
                ->table('users', maxSizeInMb: 100)
                ->table('events', maxSizeInMb: 50)
                ->table('event_registrations', maxSizeInMb: 100)
                ->table('pages', maxSizeInMb: 50)
                ->table('newsletters', maxSizeInMb: 100)
                ->table('newsletter_subscriptions', maxSizeInMb: 100)
                ->table('media', maxSizeInMb: 500),

            // Security Checks
            SecurityAdvisoriesCheck::new()
                ->cacheResultsForMinutes(60 * 24), // Cache for 24 hours

            // Backup Monitoring
            BackupsCheck::new()
                ->locatedAt(storage_path('app/backups/*')),
        ]);
    }
}
