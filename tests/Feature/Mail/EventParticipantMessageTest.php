<?php

declare(strict_types=1);

use App\Mail\EventParticipantMessage;
use App\Models\Event;

test('participant message has correct subject', function (): void {
    $event = Event::factory()->published()->create([
        'title' => 'Männerkreis Test',
        'event_date' => now()->addDays(7),
    ]);

    $mailable = new EventParticipantMessage(
        mailSubject: 'Einstimmung: Männerkreis Test',
        mailContent: '<p>Wir freuen uns auf dich!</p>',
        event: $event,
        participantName: 'Max',
    );

    $mailable->assertHasSubject('Einstimmung: Männerkreis Test');
});

test('participant message renders content', function (): void {
    $event = Event::factory()->published()->create([
        'title' => 'Männerkreis Test',
        'event_date' => now()->addDays(7),
    ]);

    $mailable = new EventParticipantMessage(
        mailSubject: 'Test Betreff',
        mailContent: '<p>Hallo, wir freuen uns auf dich!</p>',
        event: $event,
        participantName: 'Max',
    );

    $mailable->assertSeeInHtml('Hallo, wir freuen uns auf dich!');
    $mailable->assertSeeInHtml('Männerkreis Test');
});

test('participant message includes event title in footer', function (): void {
    $event = Event::factory()->published()->create([
        'title' => 'Besonderes Treffen',
        'event_date' => now()->addDays(7),
    ]);

    $mailable = new EventParticipantMessage(
        mailSubject: 'Test',
        mailContent: '<p>Inhalt</p>',
        event: $event,
    );

    $mailable->assertSeeInHtml('Besonderes Treffen');
});
