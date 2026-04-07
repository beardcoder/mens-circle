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

        $todayStart = now()->startOfDay();
        $tomorrowEnd = now()->addDay()->endOfDay();

        $registrations = Registration::query()
            ->active()
            ->whereNull('reminder_sent_at')
            ->whereHas(
                'event',
                fn($query) => $query
                ->where('is_published', true)
                ->whereBetween('event_date', [$todayStart, $tomorrowEnd]),
            )
            ->with(['event', 'participant'])
            ->get();

        if ($registrations->isEmpty()) {
            $this->info('No pending reminders found.');

            return self::SUCCESS;
        }

        $totalSent = 0;

        foreach ($registrations as $registration) {
            $event = $registration->event;
            $participant = $registration->participant;
            $isToday = $event->event_date->isToday();

            $participant->notify(new EventReminderNotification($registration, $event, $isToday));

            $registration->update([
                'reminder_sent_at' => now(),
                'sms_reminder_sent_at' => $participant->phone ? now() : null,
            ]);

            $totalSent++;
            $this->line("  -> Reminder sent to: {$participant->email} ({$event->title})");
        }

        $this->newLine();
        $this->info("Sent {$totalSent} reminder(s).");

        return self::SUCCESS;
    }
}
