<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Http\Requests\EventRegistrationRequest;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use App\Notifications\EventRegistrationConfirmed;
use App\Notifications\EventRegistrationReceived;
use App\Notifications\WaitlistRegistrationConfirmed;
use App\Seo\Data\BreadcrumbItem;
use App\Seo\Schemas\BreadcrumbSchema;
use App\Seo\Schemas\EventSchema;
use App\Settings\GeneralSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
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

        $eventImage = $event->getFirstMedia('event_image');

        return view('event', [
            'event' => $event,
            'eventImage' => $eventImage,
            'eventSchema' => new EventSchema($event, $this->settings),
            'breadcrumbSchema' => new BreadcrumbSchema([
                new BreadcrumbItem('Startseite', route('home')),
                new BreadcrumbItem('Veranstaltungen', route('event.show')),
                new BreadcrumbItem($event->title, route('event.show.slug', $event->slug)),
            ]),
        ]);
    }

    public function register(EventRegistrationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        /** @var Event $event */
        $event = Event::query()->withCount('activeRegistrations')->findOrFail($validated['event_id']);

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
            $participant = Participant::updateOrCreate(['email' => $validated['email']], [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone_number'] ?? null,
            ]);

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
                $participant->notify(new WaitlistRegistrationConfirmed($registration, $event));
                Notification::send(User::all(), new EventRegistrationReceived($registration, $event, $participant, isWaitlist: true));

                return response()->json([
                    'success' => true,
                    'waitlist' => true,
                    'message' => "Du wurdest auf die Warteliste eingetragen, {$firstName}. Wir benachrichtigen dich per E-Mail, sobald ein Platz frei wird.",
                ]);
            }

            $participant->notify(new EventRegistrationConfirmed($registration, $event));
            Notification::send(User::all(), new EventRegistrationReceived($registration, $event, $participant));

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
}
