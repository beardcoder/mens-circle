<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

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
