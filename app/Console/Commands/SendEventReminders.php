<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\EventReminder;
use App\Models\Event;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';

    protected $description = 'Send reminder emails and SMS to participants for events happening in 24 hours';

    public function __construct(
        private readonly SmsService $smsService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Searching for events happening in 24 hours...');

        $startWindow = now()->addHours(23);
        $endWindow = now()->addHours(25);

        $upcomingEvents = Event::published()
            ->whereBetween('event_date', [$startWindow, $endWindow])
            ->with('confirmedRegistrations')
            ->get();

        if ($upcomingEvents->isEmpty()) {
            $this->info('No events found in the 24-hour window.');

            return self::SUCCESS;
        }

        $totalEmailsSent = 0;
        $totalSmsSent = 0;

        foreach ($upcomingEvents as $event) {
            $registrations = $event->confirmedRegistrations;

            if ($registrations->isEmpty()) {
                $this->warn(sprintf("Event '%s' has no confirmed registrations.", $event->title));

                continue;
            }

            $this->info(sprintf('Processing event: %s (%s)', $event->title, $event->event_date->format('d.m.Y H:i')));

            /** @var \App\Models\EventRegistration $registration */
            foreach ($registrations as $registration) {
                // Send email reminder
                Mail::queue(new EventReminder($registration, $event));
                $totalEmailsSent++;
                $this->line('  -> Email reminder sent to: '.$registration->email);

                // Send SMS reminder if phone number is provided
                if ($registration->phone_number) {
                    $smsSent = $this->smsService->sendEventReminder($registration, $event);
                    if ($smsSent) {
                        $totalSmsSent++;
                        $this->line('  -> SMS reminder sent to: '.$registration->phone_number);
                    }
                }
            }
        }

        $this->newLine();
        $this->info(sprintf('Successfully sent %d email(s) and %d SMS for %s event(s).', $totalEmailsSent, $totalSmsSent, $upcomingEvents->count()));

        return self::SUCCESS;
    }
}
