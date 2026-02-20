<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Http\Requests\EventRegistrationRequest;
use App\Mail\AdminEventRegistrationNotification;
use App\Mail\EventRegistrationConfirmation;
use App\Mail\WaitlistConfirmation;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use RuntimeException;
use Seven\Api\Client;
use Seven\Api\Resource\Sms\SmsParams;
use Seven\Api\Resource\Sms\SmsResource;

class EventController
{
    public function showNext(): View|RedirectResponse
    {
        $event = Event::published()
            ->upcoming()
            ->select('id', 'slug', 'event_date')
            ->orderBy('event_date')
            ->first();

        return $event ? redirect()->route('event.show.slug', ['slug' => $event->slug]) : view('no-event');
    }

    public function show(string $slug): View
    {
        $event = Event::published()
            ->where('slug', $slug)
            ->with(['media'])
            ->withCount('activeRegistrations')
            ->firstOrFail();

        $eventImage = $event->getFirstMedia('event_image');

        return view('event', [
            'event' => $event,
            'eventImage' => $eventImage,
        ]);
    }

    public function register(EventRegistrationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        /** @var Event $event */
        $event = Event::query()->findOrFail($validated['event_id']);

        $error = match (true) {
            !$event->is_published => ['Diese Veranstaltung ist nicht verfügbar.', 404],
            $event->isPast => [
                'Diese Veranstaltung hat bereits stattgefunden. Eine Anmeldung ist nicht mehr möglich.',
                410,
            ],
            default => null,
        };

        if ($error) {
            [$message, $status] = $error;

            return response()->json(['success' => false, 'message' => $message], $status);
        }

        $isWaitlist = $event->isFull;

        try {
            $participant = Participant::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'phone' => $validated['phone_number'] ?? null,
                ],
            );

            $existingRegistration = Registration::withTrashed()
                ->where('event_id', $event->id)
                ->where('participant_id', $participant->id)
                ->first();

            if ($existingRegistration && !$existingRegistration->trashed()) {
                $alreadyOnWaitlist = $existingRegistration->status === RegistrationStatus::Waitlist;
                $message = $alreadyOnWaitlist
                    ? 'Du bist bereits auf der Warteliste für diese Veranstaltung.'
                    : 'Du bist bereits für diese Veranstaltung angemeldet.';

                throw new RuntimeException($message);
            }

            $status = $isWaitlist ? RegistrationStatus::Waitlist : RegistrationStatus::Registered;

            if ($existingRegistration) {
                $existingRegistration->restore();
                $existingRegistration->update([
                    'status' => $status,
                    'registered_at' => now(),
                    'cancelled_at' => null,
                ]);
                $registration = $existingRegistration;
            } else {
                $registration = Registration::create([
                    'participant_id' => $participant->id,
                    'event_id' => $event->id,
                    'status' => $status,
                    'registered_at' => now(),
                ]);
            }

            $registration->setRelation('participant', $participant);

            /** @var string $firstName */
            $firstName = $validated['first_name'];

            if ($isWaitlist) {
                try {
                    Mail::queue(new WaitlistConfirmation($registration, $event));
                } catch (Exception $e) {
                    Log::error('Failed to send waitlist confirmation email', [
                        'registration_id' => $registration->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'waitlist' => true,
                    'message' => "Du wurdest auf die Warteliste eingetragen, {$firstName}. Wir benachrichtigen dich per E-Mail, sobald ein Platz frei wird.",
                ]);
            }

            try {
                Mail::queue(new EventRegistrationConfirmation($registration, $event));
            } catch (Exception $e) {
                Log::error('Failed to send event registration confirmation', [
                    'registration_id' => $registration->id,
                    'error' => $e->getMessage(),
                ]);
            }

            try {
                Mail::queue(new AdminEventRegistrationNotification($registration, $event));
            } catch (Exception $e) {
                Log::error('Failed to send admin notification for new registration', [
                    'registration_id' => $registration->id,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($participant->phone) {
                $eventDate = $event->event_date->format('d.m.Y');
                $eventTime = $event->start_time->format('H:i');
                $smsMessage = "Hallo {$participant->first_name}! Deine Anmeldung fuer den Maennerkreis am {$eventDate} um {$eventTime} Uhr ist bestaetigt. Wir freuen uns auf dich!";
                $this->sendSms($participant->phone, $smsMessage, [
                    'registration_id' => $registration->id,
                    'type' => 'registration_confirmation',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Vielen Dank, {$firstName}! Deine Anmeldung war erfolgreich. Du erhältst in Kürze eine Bestätigung per E-Mail.",
            ]);
        } catch (RuntimeException $runtimeException) {
            return response()->json([
                'success' => false,
                'message' => $runtimeException->getMessage(),
            ], 409);
        }
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
