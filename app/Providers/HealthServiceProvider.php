<?php

declare(strict_types=1);

namespace App\Providers;

use App\HealthChecks\PerformanceHealthCheck;
use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\QueueCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;

class HealthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Health::checks([
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
            DatabaseCheck::new(),
            QueueCheck::new()
                ->useCacheStore('health_checks'),
            ScheduleCheck::new()
                ->useCacheStore('health_checks'),
            OptimizedAppCheck::new(),
            PerformanceHealthCheck::new()
                ->warningThreshold(1000)
                ->failureThreshold(2000),
            UsedDiskSpaceCheck::new()
                ->warnWhenUsedSpaceIsAbovePercentage(90)
                ->failWhenUsedSpaceIsAbovePercentage(95),
        ]);
    }
}
