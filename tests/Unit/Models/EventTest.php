<?php

declare(strict_types=1);

use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;

test('event can be created with required fields', function (): void {
    $event = Event::factory()->create([
        'title' => 'Test Event',
        'event_date' => now()->addDays(7),
        'start_time' => now()->setTime(18, 0),
        'end_time' => now()->setTime(20, 0),
        'max_participants' => 20,
        'is_published' => true,
    ]);

    expect($event->title)->toBe('Test Event')->and($event->max_participants)->toBe(20)->and($event->is_published)->toBeTrue();
});

test('event slug is generated automatically', function (): void {
    $eventDate = now()->addDays(7);
    $event = Event::factory()->create([
        'event_date' => $eventDate,
    ]);

    expect($event->slug)->toBe($eventDate->format('Y-m-d'));
});

test('event calculates available spots correctly', function (): void {
    $event = Event::factory()->create([
        'max_participants' => 10,
    ]);

    $participant = Participant::factory()->create();

    Registration::factory()
        ->count(3)
        ->create([
            'event_id' => $event->id,
            'participant_id' => Participant::factory(),
            'status' => RegistrationStatus::Registered,
        ]);

    $event->refresh();

    expect($event->availableSpots)->toBe(7);
});

test('event knows when it is full', function (): void {
    $event = Event::factory()->create([
        'max_participants' => 2,
    ]);

    Registration::factory()
        ->count(2)
        ->create([
            'event_id' => $event->id,
            'participant_id' => Participant::factory(),
            'status' => RegistrationStatus::Registered,
        ]);

    $event->refresh();

    expect($event->isFull)->toBeTrue();
});

test('event knows when it is not full', function (): void {
    $event = Event::factory()->create([
        'max_participants' => 10,
    ]);

    Registration::factory()
        ->count(5)
        ->create([
            'event_id' => $event->id,
            'participant_id' => Participant::factory(),
            'status' => RegistrationStatus::Registered,
        ]);

    $event->refresh();

    expect($event->isFull)->toBeFalse();
});

test('event knows when it is past', function (): void {
    $pastEvent = Event::factory()->create([
        'event_date' => now()->subDays(1),
    ]);

    expect($pastEvent->isPast)->toBeTrue();
});

test('event knows when it is upcoming', function (): void {
    $futureEvent = Event::factory()->create([
        'event_date' => now()->addDays(7),
    ]);

    expect($futureEvent->isPast)->toBeFalse();
});

test('event generates full address correctly', function (): void {
    $event = Event::factory()->create([
        'street' => 'Hauptstraße 123',
        'postal_code' => '94315',
        'city' => 'Straubing',
    ]);

    expect($event->fullAddress)->toBe('Hauptstraße 123, 94315 Straubing');
});

test('event full address is null when street is missing', function (): void {
    $event = Event::factory()->create([
        'street' => null,
        'city' => 'Straubing',
    ]);

    expect($event->fullAddress)->toBeNull();
});

test('published scope filters published events', function (): void {
    Event::factory()->create(['is_published' => true]);
    Event::factory()->create(['is_published' => false]);

    $publishedEvents = Event::published()->get();

    expect($publishedEvents)->toHaveCount(1)->and($publishedEvents->first()->is_published)->toBeTrue();
});

test('upcoming scope filters future events', function (): void {
    Event::factory()->create(['event_date' => now()->addDays(7)]);
    Event::factory()->create(['event_date' => now()->subDays(1)]);

    $upcomingEvents = Event::upcoming()->get();

    expect($upcomingEvents)->toHaveCount(1);
});

test('nextEvent returns next published upcoming event', function (): void {
    $soonestEvent = Event::factory()->create([
        'event_date' => now()->addDays(1),
        'is_published' => true,
    ]);

    Event::factory()->create([
        'event_date' => now()->addDays(7),
        'is_published' => true,
    ]);

    Event::factory()->create([
        'event_date' => now()->subDays(1),
        'is_published' => true,
    ]);

    $nextEvent = Event::nextEvent();

    expect($nextEvent)->toBeInstanceOf(Event::class)->and($nextEvent->id)->toBe($soonestEvent->id);
});

test('event generates valid ical content', function (): void {
    $event = Event::factory()->create([
        'title' => 'Test Event',
        'description' => 'Test Description',
        'event_date' => now()->addDays(7),
        'start_time' => now()->setTime(18, 0),
        'end_time' => now()->setTime(20, 0),
        'street' => 'Test Street 1',
        'city' => 'Test City',
    ]);

    $ical = $event->generateICalContent();

    expect($ical)
        ->toContain('BEGIN:VCALENDAR')
        ->toContain('BEGIN:VEVENT')
        ->toContain('SUMMARY:Test Event')
        ->toContain('DTSTART;TZID=Europe/Berlin:')
        ->toContain('DTEND;TZID=Europe/Berlin:')
        ->toContain('END:VEVENT')
        ->toContain('END:VCALENDAR');
});

test('cancelled registrations do not count as active', function (): void {
    $event = Event::factory()->create([
        'max_participants' => 10,
    ]);

    Registration::factory()
        ->count(3)
        ->create([
            'event_id' => $event->id,
            'participant_id' => Participant::factory(),
            'status' => RegistrationStatus::Registered,
        ]);

    Registration::factory()
        ->count(2)
        ->create([
            'event_id' => $event->id,
            'participant_id' => Participant::factory(),
            'status' => RegistrationStatus::Cancelled,
        ]);

    $event->refresh();

    expect($event->activeRegistrationsCount)->toBe(3)->and($event->availableSpots)->toBe(7);
});
