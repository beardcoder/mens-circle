<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Notifications\EventReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EventReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_is_sent_for_events_happening_in_24_hours(): void
    {
        Notification::fake();

        // Create an event happening in exactly 24 hours
        $event = Event::factory()
            ->published()
            ->onDate(now()->addHours(24))
            ->create();

        // Create confirmed registrations
        $registrations = EventRegistration::factory()
            ->count(3)
            ->confirmed()
            ->forEvent($event)
            ->create();

        // Run the command
        Artisan::call('events:send-reminders');

        // Assert that notifications were sent to all registrations
        Notification::assertSentTimes(EventReminderNotification::class, 3);

        foreach ($registrations as $registration) {
            Notification::assertSentOnDemand(
                EventReminderNotification::class,
                function ($notification, $channels, $notifiable) use ($registration, $event) {
                    return $notifiable->routes['mail'] === $registration->email
                        && $notification->event->id === $event->id
                        && $notification->registration->id === $registration->id;
                }
            );
        }
    }

    public function test_reminder_is_not_sent_for_unpublished_events(): void
    {
        Notification::fake();

        // Create an unpublished event happening in 24 hours
        $event = Event::factory()
            ->unpublished()
            ->onDate(now()->addHours(24))
            ->create();

        EventRegistration::factory()
            ->confirmed()
            ->forEvent($event)
            ->create();

        // Run the command
        Artisan::call('events:send-reminders');

        // Assert no notifications were sent
        Notification::assertNothingSent();
    }

    public function test_reminder_is_not_sent_for_events_too_far_in_future(): void
    {
        Notification::fake();

        // Create an event happening in 48 hours (too far)
        $event = Event::factory()
            ->published()
            ->onDate(now()->addHours(48))
            ->create();

        EventRegistration::factory()
            ->confirmed()
            ->forEvent($event)
            ->create();

        // Run the command
        Artisan::call('events:send-reminders');

        // Assert no notifications were sent
        Notification::assertNothingSent();
    }

    public function test_reminder_is_not_sent_for_events_in_the_past(): void
    {
        Notification::fake();

        // Create an event that already happened
        $event = Event::factory()
            ->published()
            ->onDate(now()->subDay())
            ->create();

        EventRegistration::factory()
            ->confirmed()
            ->forEvent($event)
            ->create();

        // Run the command
        Artisan::call('events:send-reminders');

        // Assert no notifications were sent
        Notification::assertNothingSent();
    }

    public function test_reminder_is_not_sent_to_pending_registrations(): void
    {
        Notification::fake();

        // Create an event happening in 24 hours
        $event = Event::factory()
            ->published()
            ->onDate(now()->addHours(24))
            ->create();

        // Create one confirmed and one pending registration
        EventRegistration::factory()
            ->confirmed()
            ->forEvent($event)
            ->create();

        EventRegistration::factory()
            ->pending()
            ->forEvent($event)
            ->create();

        // Run the command
        Artisan::call('events:send-reminders');

        // Assert only one notification was sent (to confirmed registration)
        Notification::assertSentTimes(EventReminderNotification::class, 1);
    }

    public function test_reminder_handles_events_with_no_registrations(): void
    {
        Notification::fake();

        // Create an event with no registrations
        Event::factory()
            ->published()
            ->onDate(now()->addHours(24))
            ->create();

        // Run the command
        Artisan::call('events:send-reminders');

        // Assert no notifications were sent
        Notification::assertNothingSent();
    }

    public function test_reminder_email_contains_correct_information(): void
    {
        Notification::fake();

        $event = Event::factory()
            ->published()
            ->onDate(now()->addHours(24))
            ->create([
                'title' => 'Männerkreis Test Event',
                'location' => 'Test Location',
            ]);

        $registration = EventRegistration::factory()
            ->confirmed()
            ->forEvent($event)
            ->create([
                'first_name' => 'Max',
                'email' => '[email protected]',
            ]);

        // Run the command
        Artisan::call('events:send-reminders');

        // Assert notification was sent with correct data
        Notification::assertSentOnDemand(
            EventReminderNotification::class,
            function ($notification, $channels, $notifiable) {
                $mailMessage = $notification->toMail($notifiable);

                return $notifiable->routes['mail'] === '[email protected]'
                    && $notification->event->title === 'Männerkreis Test Event'
                    && $notification->registration->first_name === 'Max'
                    && $mailMessage->subject === 'Erinnerung: Männerkreis Test Event ist morgen!';
            }
        );
    }

    public function test_reminder_is_sent_for_multiple_events_in_24_hour_window(): void
    {
        Notification::fake();

        // Create two events in the 24-hour window
        $event1 = Event::factory()
            ->published()
            ->onDate(now()->addHours(23))
            ->create();

        $event2 = Event::factory()
            ->published()
            ->onDate(now()->addHours(24))
            ->create();

        // Create registrations for both events
        EventRegistration::factory()
            ->count(2)
            ->confirmed()
            ->forEvent($event1)
            ->create();

        EventRegistration::factory()
            ->count(2)
            ->confirmed()
            ->forEvent($event2)
            ->create();

        // Run the command
        Artisan::call('events:send-reminders');

        // Assert 4 notifications were sent (2 per event)
        Notification::assertSentTimes(EventReminderNotification::class, 4);
    }
}
