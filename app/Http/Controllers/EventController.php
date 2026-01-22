<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RegisterParticipantAction;
use App\Http\Requests\EventRegistrationRequest;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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
            ->withCount('activeRegistrations')
            ->firstOrFail();

        $eventImage = $event->getFirstMedia('event_image');

        return view('event', [
            'event' => $event,
            'eventImage' => $eventImage,
        ]);
    }

    public function register(EventRegistrationRequest $request, RegisterParticipantAction $action): JsonResponse
    {
        $validated = $request->validated();
        /** @var Event $event */
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

        try {
            $action->execute($event, $validated);

            return response()->json([
                'success' => true,
                'message' => sprintf('Vielen Dank, %s! Deine Anmeldung war erfolgreich. Du erhältst in Kürze eine Bestätigung per E-Mail.', $validated['first_name']),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
        }
    }
}
