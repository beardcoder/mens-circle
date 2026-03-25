<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\EventReminder;
use App\Models\Registration;
use App\Services\EventNotificationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('events:send-reminders')]
#[Description('Send reminder emails and SMS to participants for events happening today or tomorrow')]
class SendEventReminders extends Command
{
    public function __construct(private readonly EventNotificationService $notificationService)
    {
        parent::__construct();
    }

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

        $totalEmailsSent = 0;
        $totalSmsSent = 0;

        foreach ($registrations as $registration) {
            $event = $registration->event;
            $participant = $registration->participant;
            $isToday = $event->event_date->isToday();
            $timeWord = $isToday ? 'heute' : 'morgen';

            Mail::queue(new EventReminder($registration, $event, $isToday));
            $registration->update(['reminder_sent_at' => now()]);
            $totalEmailsSent++;
            $this->line("  -> Email reminder sent to: {$participant->email} ({$event->title})");

            if ($participant->phone && $registration->sms_reminder_sent_at === null) {
                $eventTime = $event->start_time->format('H:i');
                $smsMessage = "Hallo {$participant->first_name}, {$timeWord} ist Maennerkreis! {$event->title} um {$eventTime} Uhr in {$event->location}. Bis bald!";

                if ($this->notificationService->sendSms($participant->phone, $smsMessage, [
                    'registration_id' => $registration->id,
                    'event_id' => $event->id,
                    'type' => 'event_reminder',
                ])) {
                    $registration->update(['sms_reminder_sent_at' => now()]);
                    $totalSmsSent++;
                    $this->line("  -> SMS reminder sent to: {$participant->phone}");
                }
            }
        }

        $this->newLine();
        $this->info("Sent {$totalEmailsSent} email(s) and {$totalSmsSent} SMS.");

        return self::SUCCESS;
    }

}
