<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\EventReminder;
use App\Models\Event;
use App\Models\EventNotificationLog;
use App\Models\Registration;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Seven\Api\Client;
use Seven\Api\Resource\Sms\SmsParams;
use Seven\Api\Resource\Sms\SmsResource;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';

    protected $description = 'Send reminder emails and SMS to participants for events happening today or tomorrow';

    public function handle(): int
    {
        $this->info('Searching for events happening today or tomorrow...');

        $todayStart = now()->startOfDay();
        $tomorrowEnd = now()->addDay()->endOfDay();

        $upcomingEvents = Event::published()
            ->whereBetween('event_date', [$todayStart, $tomorrowEnd])
            ->with(['activeRegistrations.participant'])
            ->get();

        if ($upcomingEvents->isEmpty()) {
            $this->info('No upcoming events found for today or tomorrow.');

            return self::SUCCESS;
        }

        $totalEmailsSent = 0;
        $totalSmsSent = 0;
        $totalSkipped = 0;

        foreach ($upcomingEvents as $event) {
            $registrations = $event->activeRegistrations;

            if ($registrations->isEmpty()) {
                $this->warn("Event '{$event->title}' has no active registrations.");

                continue;
            }

            $eventDate = $event->event_date->format('d.m.Y');
            $this->info("Processing event: {$event->title} ({$eventDate})");

            $isToday = $event->event_date->isToday();
            $timeWord = $isToday ? 'heute' : 'morgen';

            $existingLogs = EventNotificationLog::query()
                ->where('event_id', $event->id)
                ->whereIn('registration_id', $registrations->pluck('id'))
                ->get()
                ->groupBy('registration_id');

            $registrations->each(function (Registration $registration) use ($event, $isToday, $timeWord, $existingLogs, &$totalEmailsSent, &$totalSmsSent, &$totalSkipped): void {
                $participant = $registration->participant;
                $logs = $existingLogs->get($registration->id, new Collection());

                if ($logs->contains('channel', 'email')) {
                    $totalSkipped++;
                    $this->line("  -> Already notified: {$participant->email} (skipped)");
                } else {
                    Mail::queue(new EventReminder($registration, $event, $isToday));

                    EventNotificationLog::create([
                        'registration_id' => $registration->id,
                        'event_id' => $event->id,
                        'channel' => 'email',
                        'notified_at' => now(),
                    ]);

                    $totalEmailsSent++;
                    $this->line("  -> Email reminder sent to: {$participant->email}");
                }

                if ($participant->phone && !$logs->contains('channel', 'sms')) {
                    $eventTime = $event->start_time->format('H:i');
                    $smsMessage = "Hallo {$participant->first_name}, {$timeWord} ist Maennerkreis! {$event->title} um {$eventTime} Uhr in {$event->location}. Bis bald!";

                    if ($this->sendSms($participant->phone, $smsMessage, [
                        'registration_id' => $registration->id,
                        'event_id' => $event->id,
                        'type' => 'event_reminder',
                    ])) {
                        EventNotificationLog::create([
                            'registration_id' => $registration->id,
                            'event_id' => $event->id,
                            'channel' => 'sms',
                            'notified_at' => now(),
                        ]);

                        $totalSmsSent++;
                        $this->line("  -> SMS reminder sent to: {$participant->phone}");
                    }
                }
            });
        }

        $this->newLine();
        $eventCount = $upcomingEvents->count();
        $this->info("Sent {$totalEmailsSent} email(s) and {$totalSmsSent} SMS for {$eventCount} event(s). Skipped {$totalSkipped} already notified.");

        return self::SUCCESS;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function sendSms(string $phoneNumber, string $message, array $context = []): bool
    {
        /** @var string|null $apiKey */
        $apiKey = config('sevenio.api_key');

        if (!$apiKey) {
            Log::warning('Cannot send SMS - Seven.io API key not configured', $context);

            return false;
        }

        try {
            $client = new Client($apiKey);
            $smsResource = new SmsResource($client);
            /** @var string|null $from */
            $from = config('sevenio.from');
            $params = new SmsParams(text: $message, to: $phoneNumber, from: $from ?? '');
            $smsResource->dispatch($params);

            return true;
        } catch (Exception $exception) {
            Log::error('Failed to send SMS', [
                ...$context,
                'phone_number' => $phoneNumber,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
