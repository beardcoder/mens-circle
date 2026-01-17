<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\EventRegistrationRequest;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

        $eventImage = $event->getFirstMedia('event_image');

        return view('event', [
            'event' => $event,
            'eventImage' => $eventImage,
        ]);
    }

    public function register(EventRegistrationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $event = Event::findOrFail($validated['event_id']);

        // Validate event availability
        if (! $event->is_published) {
            return response()->json(['success' => false, 'message' => 'Diese Veranstaltung ist nicht verfügbar.'], 404);
        }

        if ($event->isPast) {
            return response()->json(['success' => false, 'message' => 'Diese Veranstaltung hat bereits stattgefunden. Eine Anmeldung ist nicht mehr möglich.'], 410);
        }

        if ($event->isFull) {
            return response()->json(['success' => false, 'message' => 'Diese Veranstaltung ist leider bereits ausgebucht.'], 409);
        }

        // Check for existing registration (including soft-deleted ones)
        $existingRegistration = EventRegistration::withTrashed()
            ->where('event_id', $event->id)
            ->where('email', $validated['email'])
            ->first();

        if ($existingRegistration && ! $existingRegistration->trashed()) {
            return response()->json(['success' => false, 'message' => 'Du bist bereits für diese Veranstaltung angemeldet.'], 409);
        }

        if ($existingRegistration) {
            $existingRegistration->restore();
            $existingRegistration->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone_number' => $validated['phone_number'] ?? null,
                'privacy_accepted' => true,
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);
            $registration = $existingRegistration;
        } else {
            $registration = EventRegistration::create([
                'event_id' => $validated['event_id'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'] ?? null,
                'privacy_accepted' => true,
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);
        }

        ResponseCache::clear();

        // Send confirmations
        $event->sendRegistrationConfirmation($registration);

        return response()->json([
            'success' => true,
            'message' => sprintf('Vielen Dank, %s! Deine Anmeldung war erfolgreich. Du erhältst in Kürze eine Bestätigung per E-Mail.', $validated['first_name']),
        ]);
    }
}
