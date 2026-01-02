<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Spatie\Health\Commands\DispatchQueueCheckJobsCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Health Check Heartbeat Commands
// These commands run every minute to update timestamps that health checks monitor
Schedule::command(ScheduleCheckHeartbeatCommand::class)->everyMinute();
Schedule::command(DispatchQueueCheckJobsCommand::class)->everyMinute();

// Event Reminder Notifications
// Läuft täglich um 10:00 Uhr und sendet Erinnerungen für Events in 24 Stunden
Schedule::command('events:send-reminders')
    ->dailyAt('10:00')
    ->timezone('Europe/Berlin')
    ->onSuccess(function (): void {
        info('Event reminders sent successfully');
    })
    ->onFailure(function (): void {
        error('Event reminders failed to send');
    });

Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('01:30');

// Health Checks
// Runs health checks every hour to monitor application health
Schedule::command('health:check')
    ->hourly()
    ->onSuccess(function (): void {
        info('Health checks completed successfully');
    })
    ->onFailure(function (): void {
        error('Health checks failed to complete');
    });

// Sitemap Generation
// Regenerates the sitemap daily at 02:00
Schedule::command('sitemap:generate')
    ->dailyAt('02:00')
    ->timezone('Europe/Berlin')
    ->onSuccess(function (): void {
        info('Sitemap generated successfully');
    })
    ->onFailure(function (): void {
        error('Sitemap generation failed');
    });
