<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use Exception;
use Illuminate\Support\Facades\Log;
use Seven\Api\Client;
use Seven\Api\Resource\Sms\SmsParams;
use Seven\Api\Resource\Sms\SmsResource;

class SmsService
{
    private ?SmsResource $smsResource = null;

    public function __construct()
    {
        $apiKey = config('sevenio.api_key');

        if (! $apiKey) {
            Log::warning('Seven.io API key not configured');

            return;
        }

        $client = new Client($apiKey);
        $this->smsResource = new SmsResource($client);
    }

    public function sendRegistrationConfirmation(EventRegistration $registration, Event $event): bool
    {
        if (! $registration->phone_number) {
            Log::info('Skipping SMS confirmation - no phone number provided', [
                'registration_id' => $registration->id,
            ]);

            return false;
        }

        $message = $this->buildRegistrationMessage($registration, $event);

        return $this->sendSms($registration->phone_number, $message, [
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'type' => 'registration_confirmation',
        ]);
    }

    public function sendEventReminder(EventRegistration $registration, Event $event): bool
    {
        if (! $registration->phone_number) {
            Log::info('Skipping SMS reminder - no phone number provided', [
                'registration_id' => $registration->id,
            ]);

            return false;
        }

        $message = $this->buildReminderMessage($registration, $event);

        return $this->sendSms($registration->phone_number, $message, [
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'type' => 'event_reminder',
        ]);
    }

    protected function buildRegistrationMessage(EventRegistration $registration, Event $event): string
    {
        $template = config('sevenio.templates.registration_confirmation');

        return strtr($template, [
            ':first_name' => $registration->first_name,
            ':event_title' => $event->title,
            ':event_date' => $event->event_date->locale('de')->isoFormat('DD.MM.YYYY'),
            ':start_time' => $event->start_time->format('H:i'),
            ':location' => $event->location,
            ':site_name' => config('app.name'),
        ]);
    }

    protected function buildReminderMessage(EventRegistration $registration, Event $event): string
    {
        $template = config('sevenio.templates.event_reminder');

        return strtr($template, [
            ':first_name' => $registration->first_name,
            ':event_title' => $event->title,
            ':event_date' => $event->event_date->locale('de')->isoFormat('DD.MM.YYYY'),
            ':start_time' => $event->start_time->format('H:i'),
            ':location' => $event->location,
            ':site_name' => config('app.name'),
        ]);
    }

    protected function sendSms(string $phoneNumber, string $message, array $context = []): bool
    {
        if (! $this->smsResource) {
            Log::warning('Cannot send SMS - Seven.io API key not configured', $context);

            return false;
        }

        try {
            $params = new SmsParams(
                text: $message,
                to: $phoneNumber,
                from: config('sevenio.from')
            );

            $response = $this->smsResource->dispatch($params);

            Log::info('SMS sent successfully', array_merge($context, [
                'phone_number' => $phoneNumber,
                'response' => $response,
            ]));

            return true;
        } catch (Exception $exception) {
            Log::error('Failed to send SMS', array_merge($context, [
                'phone_number' => $phoneNumber,
                'error' => $exception->getMessage(),
            ]));

            return false;
        }
    }
}
