<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
        $event = Event::query()
            ->select('id', 'slug', 'event_date')
            ->where('is_published', true)
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->first();

        if (! $event) {
            return view('no-event');
        }

        return redirect()->route('event.show.slug', ['slug' => $event->slug]);
    }

    public function show(string $slug): View
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->withCount([
                'confirmedRegistrations as confirmed_registrations_count',
            ])
            ->firstOrFail();

        return view('event', ['event' => $event]);
    }

    public function register(EventRegistrationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $event = Event::query()
            ->select('id', 'event_date', 'is_published', 'max_participants')
            ->withCount(['confirmedRegistrations as confirmed_registrations_count'])
            ->findOrFail($validated['event_id']);

        if (! $event->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Diese Veranstaltung ist nicht verfügbar.',
            ], 404);
        }

        if ($event->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Diese Veranstaltung hat bereits stattgefunden. Eine Anmeldung ist nicht mehr möglich.',
            ], 410);
        }

        if ($event->isFull()) {
            return response()->json([
                'success' => false,
                'message' => 'Diese Veranstaltung ist leider bereits ausgebucht.',
            ], 409);
        }

        $hasExistingRegistration = EventRegistration::query()
            ->where('event_id', $event->id)
            ->where('email', $validated['email'])
            ->exists();

        if ($hasExistingRegistration) {
            return response()->json([
                'success' => false,
                'message' => 'Du bist bereits für diese Veranstaltung angemeldet.',
            ], 409);
        }

        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'privacy_accepted' => true,
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        // Clear response cache to update available spots on event pages
        ResponseCache::clear();

        // Send email confirmation
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

        // Send SMS confirmation if phone number provided
        if ($registration->phone_number) {
            try {
                $smsService = app(SmsService::class);
                $smsService->sendRegistrationConfirmation($registration, $event);
            } catch (Exception $exception) {
                Log::error('Failed to send SMS registration confirmation', [
                    'registration_id' => $registration->id,
                    'phone_number' => $registration->phone_number,
                    'event_id' => $event->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => sprintf('Vielen Dank, %s! Deine Anmeldung war erfolgreich. Du erhältst in Kürze eine Bestätigung per E-Mail.', $validated['first_name']),
        ]);
    }
}
