<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RegisterParticipantAction;
use App\Http\Requests\EventRegistrationRequest;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class EventController extends Controller
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

    public function register(
        EventRegistrationRequest $request,
        RegisterParticipantAction $action
    ): JsonResponse {
        $validated = $request->validated();
        /** @var Event $event */
        $event = Event::query()->findOrFail($validated['event_id']);

        $error = match (true) {
            !$event->is_published => ['Diese Veranstaltung ist nicht verfügbar.', 404],
            $event->isPast => ['Diese Veranstaltung hat bereits stattgefunden. Eine Anmeldung ist nicht mehr möglich.', 410],
            $event->isFull => ['Diese Veranstaltung ist leider bereits ausgebucht.', 409],
            default => null,
        };

        if ($error) {
            [$message, $status] = $error;
            return response()->json(['success' => false, 'message' => $message], $status);
        }

        try {
            $action->execute($event, $validated);

            return response()->json([
                'success' => true,
                'message' => sprintf('Vielen Dank, %s! Deine Anmeldung war erfolgreich. Du erhältst in Kürze eine Bestätigung per E-Mail.', $validated['first_name']),
            ]);
        } catch (RuntimeException $runtimeException) {
            return response()->json([
                'success' => false,
                'message' => $runtimeException->getMessage(),
            ], 409);
        }
    }

}
