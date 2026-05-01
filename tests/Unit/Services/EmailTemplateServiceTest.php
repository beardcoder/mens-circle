<?php

declare(strict_types=1);

use App\Enums\EmailTemplate;
use App\Models\Event;
use App\Services\EmailTemplateService;

test('resolve replaces placeholders with event data', function (): void {
    $event = Event::factory()
        ->published()
        ->create([
            'title' => 'Männerkreis Januar',
            'event_date' => now()->addDays(7),
            'start_time' => now()->setTime(19, 0),
            'end_time' => now()->setTime(21, 0),
            'location' => 'Straubing',
            'max_participants' => 8,
            'cost_basis' => 'Auf Spendenbasis',
        ]);

    $service = new EmailTemplateService();
    $result = $service->resolve(EmailTemplate::NewsletterNewEvent, $event);

    expect($result['subject'])
        ->toContain('Männerkreis Januar')
        ->and($result['content'])
        ->toContain('Männerkreis Januar')
        ->and($result['content'])
        ->toContain('Straubing')
        ->and($result['content'])
        ->toContain('19:00')
        ->and($result['content'])
        ->toContain('Auf Spendenbasis')
        ->and($result['content'])
        ->toContain('8 Plätze');
});

test('resolve uses next event when no event provided', function (): void {
    $event = Event::factory()
        ->published()
        ->create([
            'title' => 'Nächstes Treffen',
            'event_date' => now()->addDays(3),
            'start_time' => now()->setTime(19, 0),
            'end_time' => now()->setTime(21, 0),
            'location' => 'München',
            'max_participants' => 10,
        ]);

    $service = new EmailTemplateService();
    $result = $service->resolve(EmailTemplate::NewsletterEventReminder);

    expect($result['subject'])->toContain('Nächstes Treffen')->and($result['content'])->toContain('München');
});

test('resolve uses dash placeholders when no event exists', function (): void {
    $service = new EmailTemplateService();
    $result = $service->resolve(EmailTemplate::NewsletterNewEvent);

    expect($result['subject'])->toContain('—')->and($result['content'])->toContain('—');
});

test('resolve includes event url', function (): void {
    $event = Event::factory()
        ->published()
        ->create([
            'title' => 'Test Event',
            'event_date' => now()->addDays(5),
            'start_time' => now()->setTime(19, 0),
            'end_time' => now()->setTime(21, 0),
        ]);

    $service = new EmailTemplateService();
    $result = $service->resolve(EmailTemplate::NewsletterNewEvent, $event);

    expect($result['content'])->toContain(route('event.show.slug', ['slug' => $event->slug]));
});

test('replace placeholders works with custom strings', function (): void {
    $event = Event::factory()
        ->published()
        ->create([
            'title' => 'Custom Event',
            'event_date' => now()->addDays(5),
            'start_time' => now()->setTime(18, 30),
            'end_time' => now()->setTime(20, 30),
            'location' => 'Regensburg',
        ]);

    $service = new EmailTemplateService();
    $result = $service->replacePlaceholders('Einladung: {event_title}', '<p>Treffen in {event_location} am {event_date}</p>', $event);

    expect($result['subject'])->toContain('Custom Event')->and($result['content'])->toContain('Regensburg');
});

test('renderForMessenger replaces placeholders with event values', function (): void {
    $event = Event::factory()
        ->published()
        ->create([
            'title' => 'Männerkreis Mai',
            'event_date' => now()->addDays(7),
            'start_time' => now()->setTime(19, 30),
            'end_time' => now()->setTime(21, 30),
            'location' => 'Straubing',
            'cost_basis' => 'Auf Spendenbasis',
        ]);

    $service = new EmailTemplateService();
    $text = $service->renderForMessenger(
        "{event_title}\n{event_time} Uhr\n{event_location}\nTeilnahme: {cost_basis}\nAnmeldung:\n{event_url}",
        $event,
    );

    expect($text)
        ->toContain('Männerkreis Mai')
        ->toContain('19:30 Uhr')
        ->toContain('Straubing')
        ->toContain('Teilnahme: Auf Spendenbasis')
        ->toContain(route('event.show.slug', ['slug' => $event->slug]))
        ->not->toContain('—')
        ->not->toContain('{');
});

test('renderForMessenger removes label lines that lost their value', function (): void {
    $event = Event::factory()
        ->published()
        ->create([
            'title' => 'Männerkreis',
            'event_date' => now()->addDays(7),
            'start_time' => now()->setTime(19, 0),
            'end_time' => now()->setTime(21, 0),
            'location' => 'Straubing',
            'cost_basis' => '',
        ]);

    $service = new EmailTemplateService();
    $text = $service->renderForMessenger(
        "{event_title}\nTeilnahme: {cost_basis}\nAnmeldung:\n{event_url}",
        $event,
    );

    expect($text)
        ->not->toContain('Teilnahme:')
        ->not->toContain('—')
        ->toContain('Männerkreis')
        ->toContain('Anmeldung:')
        ->toContain(route('event.show.slug', ['slug' => $event->slug]));
});

test('renderForMessenger collapses multiple blank lines', function (): void {
    $event = Event::factory()
        ->published()
        ->create([
            'title' => 'Test',
            'event_date' => now()->addDays(2),
            'start_time' => now()->setTime(19, 0),
            'end_time' => now()->setTime(21, 0),
            'cost_basis' => '',
        ]);

    $service = new EmailTemplateService();
    $text = $service->renderForMessenger(
        "Zeile A\n\n\n\nTeilnahme: {cost_basis}\n\n\nZeile B",
        $event,
    );

    expect($text)->not->toMatch('/\n{3,}/');
});

test('renderForMessenger returns empty placeholders cleanly when no event exists', function (): void {
    $service = new EmailTemplateService();
    $text = $service->renderForMessenger("Teilnahme: {cost_basis}\nTitel: {event_title}");

    expect($text)
        ->not->toContain('—')
        ->not->toContain('{')
        ->not->toContain('Teilnahme:')
        ->not->toContain('Titel:');
});

test('resolve calculates available spots correctly', function (): void {
    $event = Event::factory()
        ->published()
        ->create([
            'title' => 'Spots Test',
            'event_date' => now()->addDays(5),
            'start_time' => now()->setTime(19, 0),
            'end_time' => now()->setTime(21, 0),
            'max_participants' => 8,
        ]);

    \App\Models\Registration::factory()
        ->count(3)
        ->create([
            'event_id' => $event->id,
            'participant_id' => \App\Models\Participant::factory(),
            'status' => \App\Enums\RegistrationStatus::Registered,
        ]);

    $event->refresh();
    $event->loadCount('activeRegistrations');

    $service = new EmailTemplateService();
    $result = $service->resolve(EmailTemplate::NewsletterNewEvent, $event);

    expect($result['content'])->toContain('5 Plätze');
});
