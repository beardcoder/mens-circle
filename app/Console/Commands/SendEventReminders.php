<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\EventReminder;
use App\Models\Event;
use App\Models\Registration;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Seven\Api\Client;
use Seven\Api\Resource\Sms\SmsParams;
use Seven\Api\Resource\Sms\SmsResource;

class SendEventReminders extends Command
{
    private const REMINDER_WINDOW_START_HOURS = 23;

    private const REMINDER_WINDOW_END_HOURS = 25;

    protected $signature = 'events:send-reminders';

    protected $description = 'Send reminder emails and SMS to participants for events happening in 24 hours';

    public function handle(): int
    {
        $this->info('Searching for events happening in 24 hours...');

        $startWindow = now()->addHours(self::REMINDER_WINDOW_START_HOURS);
        $endWindow = now()->addHours(self::REMINDER_WINDOW_END_HOURS);

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

                Mail::queue(new EventReminder($registration, $event));
                $totalEmailsSent++;
                $this->line("  -> Email reminder sent to: {$participant->email}");

                if ($participant->phone) {
                    $this->sendSms($participant->phone, 'Erinnerung: Männerkreis findet morgen statt. Details per E-Mail. Bis bald!', [
                        'registration_id' => $registration->id,
                        'type' => 'event_reminder',
                    ]);
                    $totalSmsSent++;
                    $this->line("  -> SMS reminder sent to: {$participant->phone}");
                }
            });
        }

        $this->newLine();
        $eventCount = $upcomingEvents->count();
        $this->info(
            "Successfully sent {$totalEmailsSent} email(s) and {$totalSmsSent} SMS for {$eventCount} event(s).",
        );

        return self::SUCCESS;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function sendSms(string $phoneNumber, string $message, array $context = []): void
    {
        /** @var string|null $apiKey */
        $apiKey = config('sevenio.api_key');

        if (!$apiKey) {
            Log::warning('Cannot send SMS - Seven.io API key not configured', $context);

            return;
        }

        try {
            $client = new Client($apiKey);
            $smsResource = new SmsResource($client);
            /** @var string|null $from */
            $from = config('sevenio.from');
            $params = new SmsParams(text: $message, to: $phoneNumber, from: $from ?? '');
            $smsResource->dispatch($params);
        } catch (Exception $exception) {
            Log::error('Failed to send SMS', [
                ...$context,
                'phone_number' => $phoneNumber,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
