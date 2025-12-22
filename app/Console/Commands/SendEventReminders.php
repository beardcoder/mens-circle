<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails to participants for events happening in 24 hours';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Searching for events happening in 24 hours...');

        // Finde alle Events, die in 23-25 Stunden stattfinden
        // Das gibt uns eine 2-Stunden-Toleranz für die tägliche Ausführung
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

        $totalNotificationsSent = 0;

        foreach ($upcomingEvents as $event) {
            $registrations = $event->confirmedRegistrations;

            if ($registrations->isEmpty()) {
                $this->warn("Event '{$event->title}' has no confirmed registrations.");

                continue;
            }

            $this->info("Processing event: {$event->title} ({$event->event_date->format('d.m.Y H:i')})");

            foreach ($registrations as $registration) {
                // Sende Notification an den Teilnehmer
                Notification::route('mail', $registration->email)
                    ->notify(new EventReminderNotification($registration, $event));

                $totalNotificationsSent++;
                $this->line("  → Reminder sent to: {$registration->email}");
            }
        }

        $this->newLine();
        $this->info("✓ Successfully sent {$totalNotificationsSent} reminder(s) for {$upcomingEvents->count()} event(s).");

        return self::SUCCESS;
    }
}
