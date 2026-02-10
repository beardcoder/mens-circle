<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\EventReminder;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    private const REMINDER_WINDOW_START_HOURS = 23;

    private const REMINDER_WINDOW_END_HOURS = 25;

    protected $signature = 'events:send-reminders';

    protected $description = 'Send reminder emails and SMS to participants for events happening in 24 hours';

    public function handle(): int
    {
        $this->info('Searching for events happening in 24 hours...');

        $startWindow = now()
->addHours(self::REMINDER_WINDOW_START_HOURS);
        $endWindow = now()
->addHours(self::REMINDER_WINDOW_END_HOURS);

        $upcomingEvents = Event::published()
            ->whereBetween('event_date', [$startWindow, $endWindow])
            ->with(['activeRegistrations.participant'])
            ->get();

        if ($upcomingEvents->isEmpty()) {
            $this->info('No events found in the 24-hour window.');

            return self::SUCCESS;
        }

        $totalEmailsSent = 0;
        $totalSmsSent = 0;

        foreach ($upcomingEvents as $event) {
            $registrations = $event->activeRegistrations;

            if ($registrations->isEmpty()) {
                $this->warn("Event '{$event->title}' has no active registrations.");

                continue;
            }

            $eventDate = $event->event_date->format('d.m.Y H:i');
            $this->info("Processing event: {$event->title} ({$eventDate})");

            $registrations->each(function (Registration $registration) use (
                $event,
                &$totalEmailsSent,
                &$totalSmsSent
            ): void {
                $participant = $registration->participant;

                // Send email reminder
                Mail::queue(new EventReminder($registration, $event));
                $totalEmailsSent++;
                $this->line("  -> Email reminder sent to: {$participant->email}");

                // Send SMS reminder if phone number is provided
                if ($participant->phone) {
                    $event->sendEventReminder($registration);
                    $totalSmsSent++;
                    $this->line("  -> SMS reminder sent to: {$participant->phone}");
                }
            });
        }

        $this->newLine();
        $eventCount = $upcomingEvents->count();
        $this->info(
            "Successfully sent {$totalEmailsSent} email(s) and {$totalSmsSent} SMS for {$eventCount} event(s)."
        );

        return self::SUCCESS;
    }
}
