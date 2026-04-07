<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use App\Notifications\EventReminderNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    Notification::fake();
});

test('sends reminders for events happening tomorrow and sets reminder_sent_at', function (): void {
    $tomorrow = now()->addDay()->startOfDay();

    $event = Event::factory()->published()->create([
        'event_date' => $tomorrow,
        'start_time' => $tomorrow->copy()->setTime(19, 0),
        'end_time' => $tomorrow->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->create(['phone' => null]);
    $registration = Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create();

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain('Reminder sent to:')
        ->assertSuccessful();

    Notification::assertSentTo($participant, EventReminderNotification::class);
    expect($registration->fresh()->reminder_sent_at)->not->toBeNull();
});

test('sends reminders for events happening today', function (): void {
    $today = now()->startOfDay()->setTime(19, 0);

    $event = Event::factory()->published()->create([
        'event_date' => $today,
        'start_time' => $today->copy()->setTime(19, 0),
        'end_time' => $today->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->create(['phone' => null]);
    $registration = Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create();

    $this->artisan('events:send-reminders')
        ->assertSuccessful();

    Notification::assertSentTo($participant, EventReminderNotification::class);
    expect($registration->fresh()->reminder_sent_at)->not->toBeNull();
});

test('does not send reminders for events in two days', function (): void {
    $twoDaysLater = now()->addDays(2)->startOfDay();

    $event = Event::factory()->published()->create([
        'event_date' => $twoDaysLater,
        'start_time' => $twoDaysLater->copy()->setTime(19, 0),
        'end_time' => $twoDaysLater->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->create();
    Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create();

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain('No pending reminders found')
        ->assertSuccessful();

    Notification::assertNothingSent();
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
        ->expectsOutputToContain('No pending reminders found')
        ->assertSuccessful();

    Notification::assertNothingSent();
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
        ->expectsOutputToContain('No pending reminders found')
        ->assertSuccessful();

    Notification::assertNothingSent();
});

test('skips already notified registrations', function (): void {
    $tomorrow = now()->addDay()->startOfDay();

    $event = Event::factory()->published()->create([
        'event_date' => $tomorrow,
        'start_time' => $tomorrow->copy()->setTime(19, 0),
        'end_time' => $tomorrow->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->create(['phone' => null]);
    Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create([
        'reminder_sent_at' => now()->subHour(),
    ]);

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain('No pending reminders found')
        ->assertSuccessful();

    Notification::assertNothingSent();
});

test('sends reminder to new registration even when others already notified', function (): void {
    $tomorrow = now()->addDay()->startOfDay();

    $event = Event::factory()->published()->create([
        'event_date' => $tomorrow,
        'start_time' => $tomorrow->copy()->setTime(19, 0),
        'end_time' => $tomorrow->copy()->setTime(21, 0),
    ]);

    $existingParticipant = Participant::factory()->create(['phone' => null]);
    Registration::factory()->registered()->forEvent($event)->forParticipant($existingParticipant)->create([
        'reminder_sent_at' => now()->subHour(),
    ]);

    $newParticipant = Participant::factory()->create(['phone' => null]);
    $newRegistration = Registration::factory()->registered()->forEvent($event)->forParticipant($newParticipant)->create();

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain("Reminder sent to: {$newParticipant->email}")
        ->assertSuccessful();

    Notification::assertSentTo($newParticipant, EventReminderNotification::class);
    Notification::assertNotSentTo($existingParticipant, EventReminderNotification::class);
    expect($newRegistration->fresh()->reminder_sent_at)->not->toBeNull();
});

test('sets sms_reminder_sent_at when participant has phone', function (): void {
    $tomorrow = now()->addDay()->startOfDay();

    $event = Event::factory()->published()->create([
        'event_date' => $tomorrow,
        'start_time' => $tomorrow->copy()->setTime(19, 0),
        'end_time' => $tomorrow->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->withPhone()->create();
    $registration = Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create();

    $this->artisan('events:send-reminders')
        ->assertSuccessful();

    $registration->refresh();
    expect($registration->reminder_sent_at)->not->toBeNull()
        ->and($registration->sms_reminder_sent_at)->not->toBeNull();
});

test('does not set sms_reminder_sent_at when participant has no phone', function (): void {
    $tomorrow = now()->addDay()->startOfDay();

    $event = Event::factory()->published()->create([
        'event_date' => $tomorrow,
        'start_time' => $tomorrow->copy()->setTime(19, 0),
        'end_time' => $tomorrow->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->create(['phone' => null]);
    $registration = Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create();

    $this->artisan('events:send-reminders')
        ->assertSuccessful();

    $registration->refresh();
    expect($registration->reminder_sent_at)->not->toBeNull()
        ->and($registration->sms_reminder_sent_at)->toBeNull();
});

test('running command twice does not send duplicate reminders', function (): void {
    $tomorrow = now()->addDay()->startOfDay();

    $event = Event::factory()->published()->create([
        'event_date' => $tomorrow,
        'start_time' => $tomorrow->copy()->setTime(19, 0),
        'end_time' => $tomorrow->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->create(['phone' => null]);
    Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create();

    $this->artisan('events:send-reminders')->assertSuccessful();
    Notification::assertSentTo($participant, EventReminderNotification::class);

    Notification::fake();

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain('No pending reminders found')
        ->assertSuccessful();

    Notification::assertNothingSent();
});
