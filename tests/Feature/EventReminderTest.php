<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\EventReminder;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EventReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_is_sent_for_events_happening_in_24_hours(): void
    {
        Mail::fake();

        $event = Event::factory()
            ->published()
            ->onDate(now()->addHours(24))
            ->create();

        EventRegistration::factory()
            ->count(3)
            ->confirmed()
            ->forEvent($event)
            ->create();

        Artisan::call('events:send-reminders');

        Mail::assertQueuedCount(3);
        Mail::assertQueued(EventReminder::class, 3);
    }

    public function test_reminder_is_not_sent_for_unpublished_events(): void
    {
        Mail::fake();

        $event = Event::factory()
            ->unpublished()
            ->onDate(now()->addHours(24))
            ->create();

        EventRegistration::factory()
            ->confirmed()
            ->forEvent($event)
            ->create();

        Artisan::call('events:send-reminders');

        Mail::assertNothingQueued();
    }

    public function test_reminder_is_not_sent_for_events_too_far_in_future(): void
    {
        Mail::fake();

        $event = Event::factory()
            ->published()
            ->onDate(now()->addHours(48))
            ->create();

        EventRegistration::factory()
            ->confirmed()
            ->forEvent($event)
            ->create();

        Artisan::call('events:send-reminders');

        Mail::assertNothingQueued();
    }

    public function test_reminder_is_not_sent_for_events_in_the_past(): void
    {
        Mail::fake();

        $event = Event::factory()
            ->published()
            ->onDate(now()->subDay())
            ->create();

        EventRegistration::factory()
            ->confirmed()
            ->forEvent($event)
            ->create();

        Artisan::call('events:send-reminders');

        Mail::assertNothingQueued();
    }

    public function test_reminder_is_not_sent_to_pending_registrations(): void
    {
        Mail::fake();

        $event = Event::factory()
            ->published()
            ->onDate(now()->addHours(24))
            ->create();

        EventRegistration::factory()
            ->confirmed()
            ->forEvent($event)
            ->create();

        EventRegistration::factory()
            ->pending()
            ->forEvent($event)
            ->create();

        Artisan::call('events:send-reminders');

        Mail::assertQueued(EventReminder::class, 1);
    }

    public function test_reminder_handles_events_with_no_registrations(): void
    {
        Mail::fake();

        Event::factory()
            ->published()
            ->onDate(now()->addHours(24))
            ->create();

        Artisan::call('events:send-reminders');

        Mail::assertNothingQueued();
    }

    public function test_reminder_email_contains_correct_information(): void
    {
        Mail::fake();

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
                'email' => 'max@example.com',
            ]);

        Artisan::call('events:send-reminders');

        Mail::assertQueued(EventReminder::class, function ($mail) use ($event, $registration): bool {
            return $mail->event->id === $event->id
                && $mail->registration->id === $registration->id
                && $mail->hasTo($registration->email);
        });
    }

    public function test_reminder_is_sent_for_multiple_events_in_24_hour_window(): void
    {
        Mail::fake();

        $event1 = Event::factory()
            ->published()
            ->onDate(now()->addHours(23))
            ->create();

        $event2 = Event::factory()
            ->published()
            ->onDate(now()->addHours(24))
            ->create();

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

        Artisan::call('events:send-reminders');

        Mail::assertQueued(EventReminder::class, 4);
    }
}
