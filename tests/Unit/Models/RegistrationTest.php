<?php

declare(strict_types=1);

use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;

test('registration can be created', function (): void {
    $event = Event::factory()->create();
    $participant = Participant::factory()->create();

    $registration = Registration::factory()->create([
        'event_id' => $event->id,
        'participant_id' => $participant->id,
        'status' => RegistrationStatus::Registered,
    ]);

    expect($registration->event_id)
        ->toBe($event->id)
        ->and($registration->participant_id)
        ->toBe($participant->id)
        ->and($registration->status)
        ->toBe(RegistrationStatus::Registered);
});

test('registration can be cancelled', function (): void {
    $registration = Registration::factory()->create([
        'status' => RegistrationStatus::Registered,
    ]);

    $registration->cancel();

    expect($registration->status)->toBe(RegistrationStatus::Cancelled)->and($registration->cancelled_at)->not->toBeNull();
});

test('registration can be marked as attended', function (): void {
    $registration = Registration::factory()->create([
        'status' => RegistrationStatus::Registered,
    ]);

    $registration->markAsAttended();

    expect($registration->status)->toBe(RegistrationStatus::Attended);
});

test('active scope includes registered and attended', function (): void {
    Registration::factory()->create(['status' => RegistrationStatus::Registered]);
    Registration::factory()->create(['status' => RegistrationStatus::Attended]);
    Registration::factory()->create(['status' => RegistrationStatus::Cancelled]);

    $activeRegistrations = Registration::active()->get();

    expect($activeRegistrations)->toHaveCount(2);
});

test('registered scope only includes registered status', function (): void {
    Registration::factory()->create(['status' => RegistrationStatus::Registered]);
    Registration::factory()->create(['status' => RegistrationStatus::Attended]);
    Registration::factory()->create(['status' => RegistrationStatus::Cancelled]);

    $registeredOnly = Registration::registered()->get();

    expect($registeredOnly)->toHaveCount(1)->and($registeredOnly->first()->status)->toBe(RegistrationStatus::Registered);
});

test('cancelled scope only includes cancelled status', function (): void {
    Registration::factory()->create(['status' => RegistrationStatus::Registered]);
    Registration::factory()->create(['status' => RegistrationStatus::Cancelled]);

    $cancelledOnly = Registration::cancelled()->get();

    expect($cancelledOnly)->toHaveCount(1)->and($cancelledOnly->first()->status)->toBe(RegistrationStatus::Cancelled);
});

test('registration belongs to event', function (): void {
    $event = Event::factory()->create();
    $registration = Registration::factory()->create(['event_id' => $event->id]);

    expect($registration->event)->toBeInstanceOf(Event::class)->and($registration->event->id)->toBe($event->id);
});

test('registration belongs to participant', function (): void {
    $participant = Participant::factory()->create();
    $registration = Registration::factory()->create(['participant_id' => $participant->id]);

    expect($registration->participant)->toBeInstanceOf(Participant::class)->and($registration->participant->id)->toBe($participant->id);
});

test('registration has registered_at timestamp', function (): void {
    $registration = Registration::factory()->create([
        'registered_at' => now()->subHours(2),
    ]);

    expect($registration->registered_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});
