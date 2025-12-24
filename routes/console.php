<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Event Reminder Notifications
// Läuft täglich um 10:00 Uhr und sendet Erinnerungen für Events in 24 Stunden
Schedule::command('events:send-reminders')
    ->dailyAt('10:00')
    ->timezone('Europe/Berlin')
    ->onSuccess(function () {
        info('Event reminders sent successfully');
    })
    ->onFailure(function () {
        error('Event reminders failed to send');
    });

Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('01:30');