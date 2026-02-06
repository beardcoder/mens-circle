<?php

declare(strict_types=1);

use App\Mail\AdminEventRegistrationNotification;
use App\Mail\EventRegistrationConfirmation;
use App\Models\Event;
use Illuminate\Support\Facades\Mail;

test('admin notification is sent when registration is created', function (): void {
    Mail::fake();

    $event = Event::factory()->create([
        'event_date' => now()->addDays(7),
        'is_published' => true,
        'max_participants' => 10,
    ]);

    $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'privacy' => true,
    ]);

    Mail::assertQueued(AdminEventRegistrationNotification::class);
});

test('admin notification has correct recipient', function (): void {
    Mail::fake();

    $event = Event::factory()->create([
        'event_date' => now()->addDays(7),
        'is_published' => true,
    ]);

    $registration = \App\Models\Registration::factory()->create([
        'event_id' => $event->id,
    ]);

    $mailable = new AdminEventRegistrationNotification($registration, $event);

    $mailable->assertTo(config('mail.admin.address'));
});

test('admin notification has correct subject', function (): void {
    Mail::fake();

    $event = Event::factory()->create([
        'title' => 'Männerkreis Test',
        'event_date' => now()->addDays(7),
        'is_published' => true,
    ]);

    $registration = \App\Models\Registration::factory()->create([
        'event_id' => $event->id,
    ]);

    $mailable = new AdminEventRegistrationNotification($registration, $event);

    $mailable->assertHasSubject('Neue Anmeldung: Männerkreis Test');
});
