<?php

declare(strict_types=1);

use App\Mail\EventReminder;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
});

test('sends reminders for events happening tomorrow', function (): void {
    $tomorrow = now()->addDay()->startOfDay()->setTime(19, 0);

    $event = Event::factory()->published()->create([
        'event_date' => $tomorrow,
        'start_time' => $tomorrow->copy()->setTime(19, 0),
        'end_time' => $tomorrow->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->create();
    Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create();

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain('Processing event:')
        ->assertSuccessful();

    Mail::assertQueued(EventReminder::class);
});

test('sends reminders when event_date is stored as midnight', function (): void {
    $tomorrow = now()->addDay()->startOfDay();

    $event = Event::factory()->published()->create([
        'event_date' => $tomorrow,
        'start_time' => $tomorrow->copy()->setTime(19, 0),
        'end_time' => $tomorrow->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->create();
    Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create();

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain('Processing event:')
        ->assertSuccessful();

    Mail::assertQueued(EventReminder::class);
});

test('does not send reminders for events happening today', function (): void {
    $today = now()->startOfDay()->setTime(19, 0);

    $event = Event::factory()->published()->create([
        'event_date' => $today,
        'start_time' => $today->copy()->setTime(19, 0),
        'end_time' => $today->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->create();
    Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create();

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain('No events found')
        ->assertSuccessful();

    Mail::assertNothingQueued();
});

test('does not send reminders for events in two days', function (): void {
    $twoDaysLater = now()->addDays(2)->startOfDay()->setTime(19, 0);

    $event = Event::factory()->published()->create([
        'event_date' => $twoDaysLater,
        'start_time' => $twoDaysLater->copy()->setTime(19, 0),
        'end_time' => $twoDaysLater->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->create();
    Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create();

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain('No events found')
        ->assertSuccessful();

    Mail::assertNothingQueued();
});

test('does not send reminders for unpublished events', function (): void {
    $tomorrow = now()->addDay()->startOfDay();

    $event = Event::factory()->unpublished()->create([
        'event_date' => $tomorrow,
        'start_time' => $tomorrow->copy()->setTime(19, 0),
        'end_time' => $tomorrow->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->create();
    Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create();

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain('No events found')
        ->assertSuccessful();

    Mail::assertNothingQueued();
});

test('does not send reminders for cancelled registrations', function (): void {
    $tomorrow = now()->addDay()->startOfDay();

    $event = Event::factory()->published()->create([
        'event_date' => $tomorrow,
        'start_time' => $tomorrow->copy()->setTime(19, 0),
        'end_time' => $tomorrow->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->create();
    Registration::factory()->cancelled()->forEvent($event)->forParticipant($participant)->create();

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain('no active registrations')
        ->assertSuccessful();

    Mail::assertNothingQueued();
});

test('sends sms to participants with phone numbers', function (): void {
    $tomorrow = now()->addDay()->startOfDay();

    $event = Event::factory()->published()->create([
        'event_date' => $tomorrow,
        'start_time' => $tomorrow->copy()->setTime(19, 0),
        'end_time' => $tomorrow->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->withPhone()->create();
    Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create();

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain('SMS reminder sent to:')
        ->assertSuccessful();
});
