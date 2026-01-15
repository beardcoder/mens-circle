<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\EventRegistrationData;
use App\Http\Requests\EventRegistrationRequest;
use App\Mail\EventRegistrationConfirmation;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\SmsService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Spatie\ResponseCache\Facades\ResponseCache;

class EventController extends Controller
{
    public function showNext(): View|RedirectResponse
    {
        $event = Event::published()
            ->upcoming()
            ->select('id', 'slug', 'event_date')
            ->orderBy('event_date')
            ->first();

        return $event
            ? redirect()->route('event.show.slug', ['slug' => $event->slug])
            : view('no-event');
    }

    public function show(string $slug): View
    {
        $event = Event::published()
            ->where('slug', $slug)
            ->with(['media'])
            ->withCount('confirmedRegistrations')
            ->firstOrFail();

        return view('event', ['event' => $event]);
    }

    public function register(EventRegistrationRequest $request): JsonResponse
    {
        $data = EventRegistrationData::fromRequest($request->validated());
        $event = $this->loadEventForRegistration($data->eventId);

        if (($error = $this->validateEventAvailability($event, $data->email)) instanceof JsonResponse) {
            return $error;
        }

        $registration = EventRegistration::create($data->toArray());
        ResponseCache::clear();

        $this->sendConfirmations($registration, $event);

        return response()->json([
            'success' => true,
            'message' => sprintf('Vielen Dank, %s! Deine Anmeldung war erfolgreich. Du erhältst in Kürze eine Bestätigung per E-Mail.', $data->firstName),
        ]);
    }

    private function loadEventForRegistration(int $eventId): Event
    {
        return Event::select('id', 'title', 'event_date', 'is_published', 'max_participants', 'start_time', 'end_time', 'location', 'street', 'postal_code', 'city')
            ->withCount('confirmedRegistrations')
            ->findOrFail($eventId);
    }

    private function validateEventAvailability(Event $event, string $email): ?JsonResponse
    {
        return match (true) {
            ! $event->is_published => response()->json([
                'success' => false,
                'message' => 'Diese Veranstaltung ist nicht verfügbar.',
            ], 404),
            $event->isPast => response()->json([
                'success' => false,
                'message' => 'Diese Veranstaltung hat bereits stattgefunden. Eine Anmeldung ist nicht mehr möglich.',
            ], 410),
            $event->isFull => response()->json([
                'success' => false,
                'message' => 'Diese Veranstaltung ist leider bereits ausgebucht.',
            ], 409),
            $this->hasExistingRegistration($event->id, $email) => response()->json([
                'success' => false,
                'message' => 'Du bist bereits für diese Veranstaltung angemeldet.',
            ], 409),
            default => null,
        };
    }

    private function hasExistingRegistration(int $eventId, string $email): bool
    {
        return EventRegistration::query()
            ->where('event_id', $eventId)
            ->where('email', $email)
            ->exists();
    }

    private function sendConfirmations(EventRegistration $registration, Event $event): void
    {
        $this->sendEmailConfirmation($registration, $event);

        if ($registration->phone_number) {
            $this->sendSmsConfirmation($registration, $event);
        }
    }

    private function sendEmailConfirmation(EventRegistration $registration, Event $event): void
    {
        try {
            Mail::queue(new EventRegistrationConfirmation($registration, $event));

            Log::info('Event registration confirmation sent', [
                'registration_id' => $registration->id,
                'email' => $registration->email,
                'event_id' => $event->id,
            ]);
        } catch (Exception $exception) {
            Log::error('Failed to send event registration confirmation', [
                'registration_id' => $registration->id,
                'email' => $registration->email,
                'event_id' => $event->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendSmsConfirmation(EventRegistration $registration, Event $event): void
    {
        try {
            app(SmsService::class)->sendRegistrationConfirmation($registration, $event);
        } catch (Exception $exception) {
            Log::error('Failed to send SMS registration confirmation', [
                'registration_id' => $registration->id,
                'phone_number' => $registration->phone_number,
                'event_id' => $event->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
