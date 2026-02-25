<?php

declare(strict_types=1);

use App\Enums\EmailTemplate;

test('all templates have a label', function (): void {
    foreach (EmailTemplate::cases() as $template) {
        expect($template->getLabel())->toBeString()->not->toBeEmpty();
    }
});

test('all templates have a category', function (): void {
    foreach (EmailTemplate::cases() as $template) {
        expect($template->getCategory())->toBeIn(['newsletter', 'participant']);
    }
});

test('all templates have a subject with placeholders', function (): void {
    foreach (EmailTemplate::cases() as $template) {
        expect($template->getSubject())->toBeString()->not->toBeEmpty();
    }
});

test('all templates have content', function (): void {
    foreach (EmailTemplate::cases() as $template) {
        expect($template->getContent())->toBeString()->not->toBeEmpty();
    }
});

test('newsletter templates returns only newsletter category', function (): void {
    $templates = EmailTemplate::newsletterTemplates();

    expect($templates)->not->toBeEmpty();

    foreach ($templates as $template) {
        expect($template->getCategory())->toBe('newsletter');
    }
});

test('participant templates returns only participant category', function (): void {
    $templates = EmailTemplate::participantTemplates();

    expect($templates)->not->toBeEmpty();

    foreach ($templates as $template) {
        expect($template->getCategory())->toBe('participant');
    }
});

test('placeholders returns expected placeholder keys', function (): void {
    $placeholders = EmailTemplate::placeholders();

    expect($placeholders)
        ->toContain('{event_title}')
        ->toContain('{event_date}')
        ->toContain('{event_time}')
        ->toContain('{event_location}')
        ->toContain('{event_url}')
        ->toContain('{available_spots}')
        ->toContain('{cost_basis}')
        ->toContain('{site_name}');
});

test('options method returns value label pairs', function (): void {
    $options = EmailTemplate::options();

    expect($options)->toBeArray()->not->toBeEmpty();
    expect($options[EmailTemplate::NewsletterNewEvent->value])->toBe('Neues Event ankündigen');
});
