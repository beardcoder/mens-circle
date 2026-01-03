<?php

declare(strict_types=1);

use App\Console\Commands\GenerateSitemap;
use App\Console\Commands\SendEventReminders;
use Illuminate\Support\Facades\Schedule;
use Spatie\Backup\Commands\BackupCommand;
use Spatie\Backup\Commands\CleanupCommand;
use Spatie\Health\Commands\DispatchQueueCheckJobsCommand;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

Schedule::command(RunHealthChecksCommand::class)->everyMinute();
Schedule::command(ScheduleCheckHeartbeatCommand::class)->everyMinute();
Schedule::command(DispatchQueueCheckJobsCommand::class)->everyMinute();

Schedule::command(SendEventReminders::class)->daily()->at('10:00');
Schedule::command(CleanupCommand::class)->daily()->at('01:00');
Schedule::command(BackupCommand::class)->daily()->at('01:30');
Schedule::command(GenerateSitemap::class)->daily()->at('02:00');
