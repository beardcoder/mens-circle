<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RegisterForEvent;
use App\Http\Requests\EventRegistrationRequest;
use App\Models\Event;
use App\Seo\Data\BreadcrumbItem;
use App\Seo\Schemas\BreadcrumbSchema;
use App\Seo\Schemas\EventSchema;
use App\Settings\GeneralSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

final class EventController
{
    public function __construct(
        private readonly GeneralSettings $settings,
    ) {}

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

        return view('event', [
            'event' => $event,
            'eventImage' => $event->getFirstMedia('event_image'),
            'eventSchema' => new EventSchema($event, $this->settings),
            'breadcrumbSchema' => new BreadcrumbSchema([
                new BreadcrumbItem('Startseite', route('home')),
                new BreadcrumbItem('Veranstaltungen', route('event.show')),
                new BreadcrumbItem($event->title, route('event.show.slug', $event->slug)),
            ]),
        ]);
    }

    public function register(EventRegistrationRequest $request, RegisterForEvent $action): JsonResponse
    {
        $data = $request->registrationData();

        $event = Event::query()->withCount('activeRegistrations')->findOrFail($data['event_id']);

        if (!$event->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Diese Veranstaltung ist nicht verfügbar.',
            ], 404);
        }

        if ($event->isPast) {
            return response()->json([
                'success' => false,
                'message' => 'Diese Veranstaltung hat bereits stattgefunden. Eine Anmeldung ist nicht mehr möglich.',
            ], 410);
        }

        try {
            $action->execute($event, $data);
        } catch (RuntimeException $runtimeException) {
            return response()->json([
                'success' => false,
                'message' => $runtimeException->getMessage(),
            ], 409);
        }

        $firstName = $data['first_name'];

        if ($event->isFull) {
            return response()->json([
                'success' => true,
                'waitlist' => true,
                'message' => "Du wurdest auf die Warteliste eingetragen, {$firstName}. Wir benachrichtigen dich per E-Mail, sobald ein Platz frei wird.",
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Vielen Dank, {$firstName}! Deine Anmeldung war erfolgreich. Du erhältst in Kürze eine Bestätigung per E-Mail.",
        ]);
    }
}
