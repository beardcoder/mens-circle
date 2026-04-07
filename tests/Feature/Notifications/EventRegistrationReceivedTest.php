<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use App\Notifications\EventRegistrationReceived;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Pushover\PushoverMessage;

test('sends notification on event registration', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $event = Event::factory()->published()->create();
    $registration = Registration::factory()->forEvent($event)->registered()->create();

    $user->notify(new EventRegistrationReceived($registration, $event, $registration->participant));

    Notification::assertSentTo($user, EventRegistrationReceived::class);
});

test('sends notification on waitlist registration', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $event = Event::factory()->published()->create();
    $registration = Registration::factory()->forEvent($event)->waitlist()->create();

    $user->notify(new EventRegistrationReceived($registration, $event, $registration->participant, isWaitlist: true));

    Notification::assertSentTo($user, EventRegistrationReceived::class);
});

test('notification includes mail and pushover channels when configured', function (): void {
    config(['services.pushover.token' => 'test-token', 'services.pushover.user_key' => 'test-user']);

    $event = Event::factory()->published()->create();
    $registration = Registration::factory()->forEvent($event)->registered()->create();
    $user = User::factory()->create();

    $notification = new EventRegistrationReceived($registration, $event, $registration->participant);

    expect($notification->via($user))->toContain('mail')
        ->and($notification->via($user))->toContain(\NotificationChannels\Pushover\PushoverChannel::class);
});

test('notification only includes mail when pushover not configured', function (): void {
    config(['services.pushover.token' => null, 'services.pushover.user_key' => null]);

    $event = Event::factory()->published()->create();
    $registration = Registration::factory()->forEvent($event)->registered()->create();
    $user = User::factory()->create();

    $notification = new EventRegistrationReceived($registration, $event, $registration->participant);

    expect($notification->via($user))->toBe(['mail']);
});

test('pushover message contains event details for registration', function (): void {
    $event = Event::factory()->published()->create(['title' => 'Männerkreis April']);
    $participant = Participant::factory()->create([
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
    ]);
    $registration = Registration::factory()->forEvent($event)->forParticipant($participant)->registered()->create();
    $user = User::factory()->create();

    $notification = new EventRegistrationReceived($registration, $event, $participant);
    $message = $notification->toPushover($user);

    expect($message)
        ->toBeInstanceOf(PushoverMessage::class)
        ->and($message->content)->toContain('Max Mustermann')
        ->and($message->content)->toContain('Männerkreis April')
        ->and($message->title)->toBe('Neue Event-Anmeldung')
        ->and($message->url)->toContain($event->slug)
        ->and($message->format)->toBe(PushoverMessage::FORMAT_HTML);
});

test('pushover message contains waitlist info', function (): void {
    $event = Event::factory()->published()->create(['title' => 'Männerkreis Mai']);
    $participant = Participant::factory()->create([
        'first_name' => 'Anna',
        'last_name' => 'Schmidt',
    ]);
    $registration = Registration::factory()->forEvent($event)->forParticipant($participant)->waitlist()->create();
    $user = User::factory()->create();

    $notification = new EventRegistrationReceived($registration, $event, $participant, isWaitlist: true);
    $message = $notification->toPushover($user);

    expect($message)
        ->toBeInstanceOf(PushoverMessage::class)
        ->and($message->content)->toContain('Anna Schmidt')
        ->and($message->content)->toContain('Warteliste')
        ->and($message->content)->toContain('Männerkreis Mai')
        ->and($message->title)->toBe('Neue Wartelisten-Anmeldung');
});

test('notification is sent to admin when registering for event', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $event = Event::factory()->published()->create(['max_participants' => 10]);

    $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'privacy' => true,
    ])->assertOk();

    Notification::assertSentTo($user, EventRegistrationReceived::class, fn($notification) => $notification->toPushover($user)->title === 'Neue Event-Anmeldung');
});

test('notification is sent to admin when registering on waitlist', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $event = Event::factory()->published()->create(['max_participants' => 0]);

    $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'privacy' => true,
    ])->assertOk();

    Notification::assertSentTo($user, EventRegistrationReceived::class, fn($notification) => $notification->toPushover($user)->title === 'Neue Wartelisten-Anmeldung');
});
