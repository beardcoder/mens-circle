<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Registration;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('events:send-reminders')]
#[Description('Send reminder emails and SMS to participants for events happening today or tomorrow')]
class SendEventReminders extends Command
{
    public function handle(): int
    {
        $this->info('Searching for events happening today or tomorrow...');

        $registrations = Registration::query()
            ->active()
            ->whereNull('reminder_sent_at')
            ->whereRelation('event', static fn($query) => $query
                ->where('is_published', true)
                ->whereBetween('event_date', [now()->startOfDay(), now()->addDay()->endOfDay()]))
            ->with(['event', 'participant'])
            ->get();

        if ($registrations->isEmpty()) {
            $this->info('No pending reminders found.');

            return self::SUCCESS;
        }

        foreach ($registrations as $registration) {
            $participant = $registration->participant;
            $event = $registration->event;

            $participant->notify(new EventReminderNotification($registration, $event, $event->event_date->isToday()));

            $registration->update([
                'reminder_sent_at' => now(),
                'sms_reminder_sent_at' => $participant->phone ? now() : null,
            ]);

            $this->line("  -> Reminder sent to: {$participant->email} ({$event->title})");
        }

        $this->newLine();
        $this->info("Sent {$registrations->count()} reminder(s).");

        return self::SUCCESS;
    }
}
