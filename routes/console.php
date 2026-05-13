<?php

declare(strict_types=1);

use App\Console\Commands\GenerateSitemap;
use App\Console\Commands\SendEventReminders;
use Illuminate\Support\Facades\Schedule;
use Spatie\Health\Commands\DispatchQueueCheckJobsCommand;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

// Event Management
Schedule::command(SendEventReminders::class)->everyFifteenMinutes()->withoutOverlapping()->onOneServer();

// SEO
Schedule::command(GenerateSitemap::class)->daily()->at('02:00')->withoutOverlapping()->onOneServer()->runInBackground();

// Health Checks
Schedule::command(RunHealthChecksCommand::class)->everyMinute()->runInBackground();
Schedule::command(DispatchQueueCheckJobsCommand::class)->everyMinute()->runInBackground();
Schedule::command(ScheduleCheckHeartbeatCommand::class)->everyMinute()->runInBackground();
