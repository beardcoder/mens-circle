<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\EventRegistrationConfirmation;
use App\Models\Event;
use App\Models\Registration;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Seven\Api\Client;
use Seven\Api\Resource\Sms\SmsParams;
use Seven\Api\Resource\Sms\SmsResource;

class EventNotificationService
{
    public function sendRegistrationConfirmation(Event $event, Registration $registration): void
    {
        // Ensure participant relationship is loaded
        if (!$registration->relationLoaded('participant')) {
            $registration->load('participant');
        }

        $participant = $registration->participant;

        // Send email
        try {
            Mail::queue(new EventRegistrationConfirmation($registration, $event));
            Log::info('Event registration confirmation sent', [
                'registration_id' => $registration->id,
                'email' => $participant->email,
                'event_id' => $event->id,
            ]);
        } catch (Exception $exception) {
            Log::error('Failed to send event registration confirmation', [
                'registration_id' => $registration->id,
                'error' => $exception->getMessage(),
            ]);
        }

        // Send SMS if phone number provided
        if ($participant->phone) {
            $this->sendRegistrationSms($event, $registration);
        }
    }

    public function sendEventReminder(Event $event, Registration $registration): void
    {
        $participant = $registration->participant;

        if (!$participant->phone) {
            return;
        }

        $message = 'Erinnerung: Männerkreis findet morgen statt. Details per E-Mail. Bis bald!';
        $this->sendSms($event, $participant->phone, $message, [
            'registration_id' => $registration->id,
            'type' => 'event_reminder',
        ]);
    }

    private function sendRegistrationSms(Event $event, Registration $registration): void
    {
        $participant = $registration->participant;

        if (!$participant->phone) {
            return;
        }

        $message = \sprintf('Hallo %s! Deine Anmeldung ist bestätigt. Details per E-Mail. Männerkreis', $participant->first_name);

        $this->sendSms($event, $participant->phone, $message, [
            'registration_id' => $registration->id,
            'type' => 'registration_confirmation',
        ]);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function sendSms(Event $event, string $phoneNumber, string $message, array $context = []): void
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

            $response = $smsResource->dispatch($params);

            Log::info('SMS sent successfully', [
                ...$context,
                'phone_number' => $phoneNumber,
                'event_id' => $event->id,
            ]);
        } catch (Exception $exception) {
            Log::error('Failed to send SMS', [
                ...$context,
                'phone_number' => $phoneNumber,
                'event_id' => $event->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
