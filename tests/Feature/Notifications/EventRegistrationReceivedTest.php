<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use App\Notifications\EventRegistrationReceived;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Pushover\PushoverChannel;
use NotificationChannels\Pushover\PushoverMessage;

test('sends pushover notification on event registration', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $event = Event::factory()->published()->create();
    $participant = Participant::factory()->create();

    $user->notify(new EventRegistrationReceived($event, $participant));

    Notification::assertSentTo($user, EventRegistrationReceived::class);
});

test('sends pushover notification on waitlist registration', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $event = Event::factory()->published()->create();
    $participant = Participant::factory()->create();

    $user->notify(new EventRegistrationReceived($event, $participant, isWaitlist: true));

    Notification::assertSentTo($user, EventRegistrationReceived::class);
});

test('notification uses pushover channel', function (): void {
    $event = Event::factory()->published()->create();
    $participant = Participant::factory()->create();
    $user = User::factory()->create();

    $notification = new EventRegistrationReceived($event, $participant);

    expect($notification->via($user))->toBe([PushoverChannel::class]);
});

test('registration notification contains event details', function (): void {
    $event = Event::factory()->published()->create(['title' => 'Männerkreis April']);
    $participant = Participant::factory()->create([
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
    ]);
    $user = User::factory()->create();

    $notification = new EventRegistrationReceived($event, $participant);
    $message = $notification->toPushover($user);

    expect($message)
        ->toBeInstanceOf(PushoverMessage::class)
        ->and($message->content)->toContain('Max Mustermann')
        ->and($message->content)->toContain('Männerkreis April')
        ->and($message->title)->toBe('Neue Event-Anmeldung')
        ->and($message->url)->toContain($event->slug)
        ->and($message->format)->toBe(PushoverMessage::FORMAT_HTML);
});

test('waitlist notification contains waitlist info', function (): void {
    $event = Event::factory()->published()->create(['title' => 'Männerkreis Mai']);
    $participant = Participant::factory()->create([
        'first_name' => 'Anna',
        'last_name' => 'Schmidt',
    ]);
    $user = User::factory()->create();

    $notification = new EventRegistrationReceived($event, $participant, isWaitlist: true);
    $message = $notification->toPushover($user);

    expect($message)
        ->toBeInstanceOf(PushoverMessage::class)
        ->and($message->content)->toContain('Anna Schmidt')
        ->and($message->content)->toContain('Warteliste')
        ->and($message->content)->toContain('Männerkreis Mai')
        ->and($message->title)->toBe('Neue Wartelisten-Anmeldung');
});

test('pushover notification is sent when registering for event', function (): void {
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

    Notification::assertSentTo($user, EventRegistrationReceived::class, function ($notification) {
        return $notification->toPushover($notification)->title === 'Neue Event-Anmeldung';
    });
});

test('pushover notification is sent when registering on waitlist', function (): void {
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

    Notification::assertSentTo($user, EventRegistrationReceived::class, function ($notification) {
        return $notification->toPushover($notification)->title === 'Neue Wartelisten-Anmeldung';
    });
});
