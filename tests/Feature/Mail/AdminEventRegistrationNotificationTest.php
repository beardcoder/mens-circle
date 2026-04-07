<?php

declare(strict_types=1);

use App\Mail\AdminEventRegistrationNotification;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use App\Notifications\EventRegistrationReceived;
use Illuminate\Support\Facades\Notification;

test('admin notification is sent when registration is created', function (): void {
    Notification::fake();

    $user = User::factory()->create();
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

    Notification::assertSentTo($user, EventRegistrationReceived::class);
});

test('admin notification has correct subject', function (): void {
    $event = Event::factory()->create([
        'title' => 'Männerkreis Test',
        'event_date' => now()->addDays(7),
        'is_published' => true,
    ]);

    $registration = Registration::factory()->create([
        'event_id' => $event->id,
    ]);

    $mailable = new AdminEventRegistrationNotification($registration, $event);

    $mailable->assertHasSubject('Neue Anmeldung: Männerkreis Test');
});
