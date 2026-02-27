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
        ->expectsOutputToContain('Email reminder sent to:')
        ->assertSuccessful();

    Mail::assertQueued(EventReminder::class);
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

    Mail::assertQueued(EventReminder::class);
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
        ->expectsOutputToContain('No pending reminders found')
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
        ->expectsOutputToContain('No pending reminders found')
        ->assertSuccessful();

    Mail::assertNothingQueued();
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

    Mail::assertNothingQueued();
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
        ->expectsOutputToContain("Email reminder sent to: {$newParticipant->email}")
        ->assertSuccessful();

    Mail::assertQueued(EventReminder::class, 1);
    expect($newRegistration->fresh()->reminder_sent_at)->not->toBeNull();
});

test('does not set sms_reminder_sent_at when api key is not configured', function (): void {
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
    Mail::assertQueued(EventReminder::class, 1);

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain('No pending reminders found')
        ->assertSuccessful();

    Mail::assertQueued(EventReminder::class, 1);
});
