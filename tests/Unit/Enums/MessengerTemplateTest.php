<?php

declare(strict_types=1);

use App\Enums\MessengerTemplate;
use App\Models\Event;
use App\Services\EmailTemplateService;

test('every variant has a label and a content body', function (MessengerTemplate $template): void {
    expect($template->getLabel())->not->toBeEmpty()
        ->and($template->getContent())->not->toBeEmpty();
})->with(MessengerTemplate::cases());

test('every variant renders cleanly for a real event', function (MessengerTemplate $template): void {
    $event = Event::factory()
        ->published()
        ->create([
            'title' => 'Männerkreis Mai',
            'event_date' => now()->addDays(7),
            'start_time' => now()->setTime(19, 0),
            'end_time' => now()->setTime(21, 0),
            'location' => 'Straubing',
            'cost_basis' => 'Auf Spendenbasis',
        ]);

    $text = (new EmailTemplateService())->renderForMessenger($template->getContent(), $event);

    expect($text)
        ->not->toContain('{')
        ->not->toContain('—')
        ->not->toMatch('/\n{3,}/')
        ->toContain('Männerkreis Mai');
})->with(MessengerTemplate::cases());

test('reminder variant is hidden when no spots are available', function (): void {
    $available = MessengerTemplate::availableForSpots(0);

    expect($available)
        ->not->toContain(MessengerTemplate::Reminder)
        ->toContain(MessengerTemplate::Short)
        ->toContain(MessengerTemplate::Personal);
});

test('reminder variant is shown when spots are available', function (): void {
    expect(MessengerTemplate::availableForSpots(3))
        ->toContain(MessengerTemplate::Reminder);
});
