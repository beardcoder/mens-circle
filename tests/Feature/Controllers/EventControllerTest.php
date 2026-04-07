<?php

declare(strict_types=1);

use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use App\Notifications\EventRegistrationConfirmed;
use App\Notifications\WaitlistParticipantPromoted;
use App\Notifications\WaitlistRegistrationConfirmed;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\assertModelExists;

test('can view next event page', function (): void {
    $event = Event::factory()->published()->create();

    $response = $this->get(route('event.show'));

    expect($response->status())->toBe(302)->and($response->headers->get('Location'))->toContain($event->slug);
});

test('shows no event page when no upcoming events', function (): void {
    $this->markTestSkipped('View tests require full frontend build');

    $response = $this->get(route('event.show'));

    $response->assertStatus(200);
    $response->assertViewIs('no-event');
});

test('can view specific event by slug', function (): void {
    $this->markTestSkipped('View tests require full frontend build');

    $event = Event::factory()->published()->create(['title' => 'Test Event']);

    $response = $this->get(route('event.show.slug', ['slug' => $event->slug]));

    $response->assertStatus(200);
    $response->assertViewIs('event');
    $response->assertViewHas('event');
});

test('cannot view unpublished event', function (): void {
    $this->markTestSkipped('View tests require full frontend build');

    $event = Event::factory()->unpublished()->create();

    $response = $this->get(route('event.show.slug', ['slug' => $event->slug]));

    $response->assertStatus(404);
});

test('can register for event', function (): void {
    Notification::fake();

    $event = Event::factory()->published()->create(['max_participants' => 10]);

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'phone_number' => '+49123456789',
        'privacy' => true,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $participant = Participant::where('email', 'max@example.com')->firstOrFail();
    assertModelExists($participant);

    $registration = Registration::where('event_id', $event->id)
        ->where('participant_id', $participant->id)
        ->firstOrFail();
    expect($registration->status)->toBe(RegistrationStatus::Registered);

    Notification::assertSentTo($participant, EventRegistrationConfirmed::class);
});

test('cannot register for past event', function (): void {
    $event = Event::factory()->published()->past()->create();

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'privacy' => true,
    ]);

    $response->assertStatus(410);
    $response->assertJson(['success' => false]);
});

test('registers on waitlist when event is full', function (): void {
    Notification::fake();

    $event = Event::factory()->published()->create(['max_participants' => 2]);

    Registration::factory()
        ->count(2)
        ->forEvent($event)
        ->registered()
        ->create();

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'privacy' => true,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true, 'waitlist' => true]);

    $participant = Participant::where('email', 'max@example.com')->firstOrFail();
    $registration = Registration::where('event_id', $event->id)
        ->where('participant_id', $participant->id)
        ->firstOrFail();
    expect($registration->status)->toBe(RegistrationStatus::Waitlist);

    Notification::assertSentTo($participant, WaitlistRegistrationConfirmed::class);
});

test('sends waitlist confirmation when event is full', function (): void {
    Notification::fake();

    $event = Event::factory()->published()->create(['max_participants' => 1]);

    Registration::factory()
        ->forEvent($event)
        ->registered()
        ->create();

    $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'privacy' => true,
    ]);

    $participant = Participant::where('email', 'max@example.com')->firstOrFail();
    Notification::assertSentTo($participant, WaitlistRegistrationConfirmed::class);
});

test('already on waitlist returns error', function (): void {
    $event = Event::factory()->published()->create(['max_participants' => 1]);

    $participant = Participant::factory()->create(['email' => 'max@example.com']);

    Registration::factory()->forEvent($event)->registered()->create();
    Registration::factory()->forEvent($event)->forParticipant($participant)->waitlist()->create();

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'privacy' => true,
    ]);

    $response->assertStatus(409);
    $response->assertJson(['success' => false]);
});

test('cannot register for unpublished event', function (): void {
    $event = Event::factory()->unpublished()->create();

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'privacy' => true,
    ]);

    $response->assertStatus(404);
    $response->assertJson(['success' => false]);
});

test('cannot register twice for same event', function (): void {
    $event = Event::factory()->published()->create();

    $participant = Participant::factory()->create(['email' => 'max@example.com']);

    Registration::factory()->forEvent($event)->forParticipant($participant)->registered()->create();

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'privacy' => true,
    ]);

    $response->assertStatus(409);
    $response->assertJson(['success' => false]);
});

test('registration requires all required fields', function (): void {
    $event = Event::factory()->published()->create();

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
    ]);

    $response->assertStatus(422);
    $response->assertJson(['success' => false]);
});

test('registration validates email format', function (): void {
    $event = Event::factory()->published()->create();

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'invalid-email',
        'privacy' => true,
    ]);

    $response->assertStatus(422);
    $response->assertJson(['success' => false]);
});

test('promotes from waitlist when registration is cancelled', function (): void {
    Notification::fake();

    $event = Event::factory()->published()->create(['max_participants' => 1]);

    $registered = Registration::factory()->forEvent($event)->registered()->create();
    $waitlisted = Registration::factory()->forEvent($event)->waitlist()->create();

    $registered->cancel();

    expect($waitlisted->fresh()->status)->toBe(RegistrationStatus::Registered);
});

test('sends waitlist promotion notification when registration is cancelled', function (): void {
    Notification::fake();

    $event = Event::factory()->published()->create(['max_participants' => 1]);

    $registered = Registration::factory()->forEvent($event)->registered()->create();
    $waitlisted = Registration::factory()->forEvent($event)->waitlist()->create();

    $registered->cancel();

    Notification::assertSentTo($waitlisted->participant, WaitlistParticipantPromoted::class);
});

test('does not promote when no one is on waitlist', function (): void {
    $event = Event::factory()->published()->create(['max_participants' => 1]);

    $registered = Registration::factory()->forEvent($event)->registered()->create();

    $registered->cancel();

    expect(Registration::where('event_id', $event->id)->count())->toBe(1);
});
