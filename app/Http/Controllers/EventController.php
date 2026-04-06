<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Throwable;
use App\Enums\RegistrationStatus;
use App\Http\Requests\EventRegistrationRequest;
use App\Mail\AdminEventRegistrationNotification;
use App\Mail\EventRegistrationConfirmation;
use App\Mail\WaitlistConfirmation;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use App\Seo\Data\BreadcrumbItem;
use App\Seo\Schemas\BreadcrumbSchema;
use App\Seo\Schemas\EventSchema;
use App\Services\EventNotificationService;
use App\Services\PushoverService;
use App\Settings\GeneralSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use RuntimeException;

use function Illuminate\Support\defer;

final class EventController
{
    public function __construct(
        private readonly GeneralSettings $settings,
        private readonly EventNotificationService $notificationService,
        private readonly PushoverService $pushoverService,
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
                defer(function () use ($registration, $event): void {
                    try {
                        Mail::queue(new WaitlistConfirmation($registration, $event));
                    } catch (Throwable $throwable) {
                        Log::error('Failed to send waitlist confirmation email', [
                            'registration_id' => $registration->id,
                            'error' => $throwable->getMessage(),
                        ]);
                    }
                });

                defer(function () use ($participant, $event): void {
                    $this->pushoverService->send(
                        "<b>{$participant->first_name} {$participant->last_name}</b> hat sich auf die <b>Warteliste</b> für <b>{$event->title}</b> eingetragen.",
                        [
                            'title' => 'Neue Wartelisten-Anmeldung',
                            'url' => route('event.show.slug', $event->slug),
                            'url_title' => $event->title,
                            'html' => 1,
                        ],
                    );
                });

                return response()->json([
                    'success' => true,
                    'waitlist' => true,
                    'message' => "Du wurdest auf die Warteliste eingetragen, {$firstName}. Wir benachrichtigen dich per E-Mail, sobald ein Platz frei wird.",
                ]);
            }

            defer(function () use ($registration, $event): void {
                try {
                    Mail::queue(new EventRegistrationConfirmation($registration, $event));
                } catch (Throwable $throwable) {
                    Log::error('Failed to send event registration confirmation', [
                        'registration_id' => $registration->id,
                        'error' => $throwable->getMessage(),
                    ]);
                }
            });

            defer(function () use ($registration, $event): void {
                try {
                    Mail::queue(new AdminEventRegistrationNotification($registration, $event));
                } catch (Throwable $throwable) {
                    Log::error('Failed to send admin notification for new registration', [
                        'registration_id' => $registration->id,
                        'error' => $throwable->getMessage(),
                    ]);
                }
            });

            defer(function () use ($participant, $event): void {
                $eventDate = $event->event_date->format('d.m.Y');
                $eventTime = $event->start_time->format('H:i');
                $this->pushoverService->send(
                    "<b>{$participant->first_name} {$participant->last_name}</b> hat sich für <b>{$event->title}</b> am {$eventDate} um {$eventTime} Uhr angemeldet.",
                    [
                        'title' => 'Neue Event-Anmeldung',
                        'url' => route('event.show.slug', $event->slug),
                        'url_title' => $event->title,
                        'html' => 1,
                    ],
                );
            });

            if ($participant->phone) {
                defer(function () use ($participant, $event, $registration): void {
                    $eventDate = $event->event_date->format('d.m.Y');
                    $eventTime = $event->start_time->format('H:i');
                    $smsMessage = "Hallo {$participant->first_name}! Deine Anmeldung fuer den Maennerkreis am {$eventDate} um {$eventTime} Uhr ist bestaetigt. Wir freuen uns auf dich!";
                    $this->notificationService->sendSms($participant->phone, $smsMessage, [
                        'registration_id' => $registration->id,
                        'type' => 'registration_confirmation',
                    ]);
                });
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

}
