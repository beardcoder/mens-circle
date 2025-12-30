<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\EventReminder;
use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';

    protected $description = 'Send reminder emails to participants for events happening in 24 hours';

    public function handle(): int
    {
        $this->info('Searching for events happening in 24 hours...');

        $startWindow = now()->addHours(23);
        $endWindow = now()->addHours(25);

        $upcomingEvents = Event::query()
            ->where('is_published', true)
            ->whereBetween('event_date', [$startWindow, $endWindow])
            ->with('confirmedRegistrations')
            ->get();

        if ($upcomingEvents->isEmpty()) {
            $this->info('No events found in the 24-hour window.');

            return self::SUCCESS;
        }

        $totalRemindersSent = 0;

        foreach ($upcomingEvents as $event) {
            $registrations = $event->confirmedRegistrations;

            if ($registrations->isEmpty()) {
                $this->warn(sprintf("Event '%s' has no confirmed registrations.", $event->title));

                continue;
            }

            $this->info(sprintf('Processing event: %s (%s)', $event->title, $event->event_date->format('d.m.Y H:i')));

            foreach ($registrations as $registration) {
                Mail::queue(new EventReminder($registration, $event));
                $totalRemindersSent++;
                $this->line('  -> Reminder sent to: '.$registration->email);
            }
        }

        $this->newLine();
        $this->info(sprintf('Successfully sent %d reminder(s) for %s event(s).', $totalRemindersSent, $upcomingEvents->count()));

        return self::SUCCESS;
    }
}
