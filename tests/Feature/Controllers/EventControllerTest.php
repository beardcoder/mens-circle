<?php

declare(strict_types=1);

use App\Enums\RegistrationStatus;
use App\Mail\WaitlistConfirmation;
use App\Mail\WaitlistPromotion;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Support\Facades\Mail;

test('can view next event page', function (): void {
    $event = Event::factory()->create([
        'event_date' => now()->addDays(7),
        'is_published' => true,
    ]);

    $response = $this->get(route('event.show'));

    expect($response->status())->toBe(302)
        ->and($response->headers->get('Location'))->toContain($event->slug);
});

test('shows no event page when no upcoming events', function (): void {
    $this->markTestSkipped('View tests require full frontend build');

    $response = $this->get(route('event.show'));

    $response->assertStatus(200);
    $response->assertViewIs('no-event');
});

test('can view specific event by slug', function (): void {
    $this->markTestSkipped('View tests require full frontend build');

    $event = Event::factory()->create([
        'title' => 'Test Event',
        'event_date' => now()->addDays(7),
        'is_published' => true,
    ]);

    $response = $this->get(route('event.show.slug', ['slug' => $event->slug]));

    $response->assertStatus(200);
    $response->assertViewIs('event');
    $response->assertViewHas('event');
});

test('cannot view unpublished event', function (): void {
    $this->markTestSkipped('View tests require full frontend build');

    $event = Event::factory()->create([
        'is_published' => false,
    ]);

    $response = $this->get(route('event.show.slug', ['slug' => $event->slug]));

    $response->assertStatus(404);
});

test('can register for event', function (): void {
    $event = Event::factory()->create([
        'event_date' => now()->addDays(7),
        'is_published' => true,
        'max_participants' => 10,
    ]);

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'phone_number' => '+49123456789',
        'privacy' => true,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);

    $this->assertDatabaseHas('participants', [
        'email' => 'max@example.com',
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
    ]);

    $participant = Participant::where('email', 'max@example.com')->first();

    $this->assertDatabaseHas('registrations', [
        'event_id' => $event->id,
        'participant_id' => $participant->id,
        'status' => RegistrationStatus::Registered->value,
    ]);
});

test('cannot register for past event', function (): void {
    $event = Event::factory()->create([
        'event_date' => now()->subDays(1),
        'is_published' => true,
    ]);

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'privacy' => true,
    ]);

    $response->assertStatus(410);
    $response->assertJson([
        'success' => false,
    ]);
});

test('registers on waitlist when event is full', function (): void {
    $event = Event::factory()->create([
        'event_date' => now()->addDays(7),
        'is_published' => true,
        'max_participants' => 2,
    ]);

    Registration::factory()->count(2)->forEvent($event)->registered()->create();

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'privacy' => true,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'waitlist' => true,
    ]);

    $participant = Participant::where('email', 'max@example.com')->first();

    $this->assertDatabaseHas('registrations', [
        'event_id' => $event->id,
        'participant_id' => $participant->id,
        'status' => RegistrationStatus::Waitlist->value,
    ]);
});

test('sends waitlist confirmation email when event is full', function (): void {
    Mail::fake();

    $event = Event::factory()->create([
        'event_date' => now()->addDays(7),
        'is_published' => true,
        'max_participants' => 1,
    ]);

    Registration::factory()->forEvent($event)->registered()->create();

    $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'privacy' => true,
    ]);

    Mail::assertQueued(WaitlistConfirmation::class);
});

test('already on waitlist returns error', function (): void {
    $event = Event::factory()->create([
        'event_date' => now()->addDays(7),
        'is_published' => true,
        'max_participants' => 1,
    ]);

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
    $response->assertJson([
        'success' => false,
    ]);
});

test('cannot register for unpublished event', function (): void {
    $event = Event::factory()->create([
        'event_date' => now()->addDays(7),
        'is_published' => false,
    ]);

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'privacy' => true,
    ]);

    $response->assertStatus(404);
    $response->assertJson([
        'success' => false,
    ]);
});

test('cannot register twice for same event', function (): void {
    $event = Event::factory()->create([
        'event_date' => now()->addDays(7),
        'is_published' => true,
    ]);

    $participant = Participant::factory()->create([
        'email' => 'max@example.com',
    ]);

    Registration::factory()->create([
        'event_id' => $event->id,
        'participant_id' => $participant->id,
        'status' => RegistrationStatus::Registered,
    ]);

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'privacy' => true,
    ]);

    $response->assertStatus(409);
    $response->assertJson([
        'success' => false,
    ]);
});

test('registration requires all required fields', function (): void {
    $event = Event::factory()->create([
        'event_date' => now()->addDays(7),
        'is_published' => true,
    ]);

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
    ]);
});

test('registration validates email format', function (): void {
    $event = Event::factory()->create([
        'event_date' => now()->addDays(7),
        'is_published' => true,
    ]);

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'invalid-email',
        'privacy' => true,
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
    ]);
});

test('promotes from waitlist when registration is cancelled', function (): void {
    $event = Event::factory()->create([
        'event_date' => now()->addDays(7),
        'is_published' => true,
        'max_participants' => 1,
    ]);

    $registered = Registration::factory()->forEvent($event)->registered()->create();
    $waitlisted = Registration::factory()->forEvent($event)->waitlist()->create();

    $registered->cancel();

    expect($waitlisted->fresh()->status)->toBe(RegistrationStatus::Registered);
});

test('sends waitlist promotion email when registration is cancelled', function (): void {
    Mail::fake();

    $event = Event::factory()->create([
        'event_date' => now()->addDays(7),
        'is_published' => true,
        'max_participants' => 1,
    ]);

    $registered = Registration::factory()->forEvent($event)->registered()->create();
    Registration::factory()->forEvent($event)->waitlist()->create();

    $registered->cancel();

    Mail::assertQueued(WaitlistPromotion::class);
});

test('does not promote when no one is on waitlist', function (): void {
    $event = Event::factory()->create([
        'event_date' => now()->addDays(7),
        'is_published' => true,
        'max_participants' => 1,
    ]);

    $registered = Registration::factory()->forEvent($event)->registered()->create();

    $registered->cancel();

    expect(Registration::where('event_id', $event->id)->count())->toBe(1);
});
