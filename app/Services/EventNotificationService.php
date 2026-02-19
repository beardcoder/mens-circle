<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\AdminEventRegistrationNotification;
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
        if (!$registration->relationLoaded('participant')) {
            $registration->load('participant');
        }

        $participant = $registration->participant;

        try {
            Mail::queue(new EventRegistrationConfirmation($registration, $event));
        } catch (Exception $exception) {
            Log::error('Failed to send event registration confirmation', [
                'registration_id' => $registration->id,
                'error' => $exception->getMessage(),
            ]);
        }

        try {
            Mail::queue(new AdminEventRegistrationNotification($registration, $event));
        } catch (Exception $exception) {
            Log::error('Failed to send admin notification for new registration', [
                'registration_id' => $registration->id,
                'error' => $exception->getMessage(),
            ]);
        }

        if ($participant->phone) {
            $message = "Hallo {$participant->first_name}! Deine Anmeldung ist bestätigt. Details per E-Mail. Männerkreis";
            $this->sendSms($event, $participant->phone, $message, [
                'registration_id' => $registration->id,
                'type' => 'registration_confirmation',
            ]);
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

            $smsResource->dispatch($params);
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
