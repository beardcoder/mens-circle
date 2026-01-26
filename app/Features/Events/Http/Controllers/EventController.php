<?php

declare(strict_types=1);

namespace App\Features\Events\Http\Controllers;

use App\Features\Events\Domain\Models\Event;
use App\Features\Events\Domain\Services\EventRegistrationService;
use App\Features\Events\Http\Requests\EventRegistrationRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        private readonly EventRegistrationService $registrationService
    ) {
    }

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

    public function register(EventRegistrationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        /** @var Event $event */
        $event = Event::findOrFail($validated['event_id']);

        try {
            $this->registrationService->register($event, $validated);

            return response()->json([
                'success' => true,
                'message' => sprintf('Vielen Dank, %s! Deine Anmeldung war erfolgreich. Du erhältst in Kürze eine Bestätigung per E-Mail.', $validated['first_name']),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
        }
    }
}
