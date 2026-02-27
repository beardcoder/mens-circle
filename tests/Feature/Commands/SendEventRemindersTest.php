<?php

declare(strict_types=1);

use App\Mail\EventReminder;
use App\Models\Event;
use App\Models\EventNotificationLog;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
});

test('sends reminders for events happening tomorrow', function (): void {
    $tomorrow = now()->addDay()->startOfDay();

    $event = Event::factory()->published()->create([
        'event_date' => $tomorrow,
        'start_time' => $tomorrow->copy()->setTime(19, 0),
        'end_time' => $tomorrow->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->create(['phone' => null]);
    Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create();

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain('Processing event:')
        ->assertSuccessful();

    Mail::assertQueued(EventReminder::class);
    expect(EventNotificationLog::query()->count())->toBe(1);
});

test('sends reminders for events happening today', function (): void {
    $today = now()->startOfDay()->setTime(19, 0);

    $event = Event::factory()->published()->create([
        'event_date' => $today,
        'start_time' => $today->copy()->setTime(19, 0),
        'end_time' => $today->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->create(['phone' => null]);
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

    $participant = Participant::factory()->create(['phone' => null]);
    Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create();

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain('Processing event:')
        ->assertSuccessful();

    Mail::assertQueued(EventReminder::class);
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
        ->expectsOutputToContain('No upcoming events found')
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
        ->expectsOutputToContain('No upcoming events found')
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

test('skips already notified participants', function (): void {
    $tomorrow = now()->addDay()->startOfDay();

    $event = Event::factory()->published()->create([
        'event_date' => $tomorrow,
        'start_time' => $tomorrow->copy()->setTime(19, 0),
        'end_time' => $tomorrow->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->create(['phone' => null]);
    $registration = Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create();

    EventNotificationLog::create([
        'registration_id' => $registration->id,
        'event_id' => $event->id,
        'channel' => 'email',
        'notified_at' => now()->subHour(),
    ]);

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain('Already notified')
        ->assertSuccessful();

    Mail::assertNothingQueued();
    expect(EventNotificationLog::query()->count())->toBe(1);
});

test('sends reminder to new registration even when others already notified', function (): void {
    $tomorrow = now()->addDay()->startOfDay();

    $event = Event::factory()->published()->create([
        'event_date' => $tomorrow,
        'start_time' => $tomorrow->copy()->setTime(19, 0),
        'end_time' => $tomorrow->copy()->setTime(21, 0),
    ]);

    $existingParticipant = Participant::factory()->create(['phone' => null]);
    $existingRegistration = Registration::factory()->registered()->forEvent($event)->forParticipant($existingParticipant)->create();

    EventNotificationLog::create([
        'registration_id' => $existingRegistration->id,
        'event_id' => $event->id,
        'channel' => 'email',
        'notified_at' => now()->subHour(),
    ]);

    $newParticipant = Participant::factory()->create(['phone' => null]);
    Registration::factory()->registered()->forEvent($event)->forParticipant($newParticipant)->create();

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain("Email reminder sent to: {$newParticipant->email}")
        ->assertSuccessful();

    Mail::assertQueued(EventReminder::class, 1);
    expect(EventNotificationLog::query()->count())->toBe(2);
});

test('sends sms to participants with phone numbers and logs it', function (): void {
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

    expect(EventNotificationLog::query()->where('channel', 'email')->count())->toBe(1)
        ->and(EventNotificationLog::query()->where('channel', 'sms')->count())->toBe(1);
});

test('does not resend sms to already notified participant', function (): void {
    $tomorrow = now()->addDay()->startOfDay();

    $event = Event::factory()->published()->create([
        'event_date' => $tomorrow,
        'start_time' => $tomorrow->copy()->setTime(19, 0),
        'end_time' => $tomorrow->copy()->setTime(21, 0),
    ]);

    $participant = Participant::factory()->withPhone()->create();
    $registration = Registration::factory()->registered()->forEvent($event)->forParticipant($participant)->create();

    EventNotificationLog::create([
        'registration_id' => $registration->id,
        'event_id' => $event->id,
        'channel' => 'email',
        'notified_at' => now()->subHour(),
    ]);
    EventNotificationLog::create([
        'registration_id' => $registration->id,
        'event_id' => $event->id,
        'channel' => 'sms',
        'notified_at' => now()->subHour(),
    ]);

    $this->artisan('events:send-reminders')
        ->expectsOutputToContain('Already notified')
        ->assertSuccessful();

    Mail::assertNothingQueued();
    expect(EventNotificationLog::query()->count())->toBe(2);
});
