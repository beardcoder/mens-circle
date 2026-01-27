<?php

declare(strict_types=1);

use App\Models\Participant;
use App\Models\Registration;
use App\Models\NewsletterSubscription;

test('participant can be created', function (): void {
    $participant = Participant::factory()->create([
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
        'email' => 'max@example.com',
        'phone' => '+49123456789',
    ]);

    expect($participant->first_name)->toBe('Max')
        ->and($participant->last_name)->toBe('Mustermann')
        ->and($participant->email)->toBe('max@example.com')
        ->and($participant->phone)->toBe('+49123456789');
});

test('participant can have nullable names', function (): void {
    $participant = Participant::factory()->create([
        'first_name' => '',
        'last_name' => '',
        'email' => 'test@example.com',
    ]);

    expect($participant->first_name)->toBe('')
        ->and($participant->last_name)->toBe('');
});

test('participant full name is computed correctly', function (): void {
    $participant = Participant::factory()->create([
        'first_name' => 'Max',
        'last_name' => 'Mustermann',
    ]);

    expect($participant->fullName)->toBe('Max Mustermann');
});

test('participant full name trims whitespace', function (): void {
    $participant = Participant::factory()->create([
        'first_name' => '',
        'last_name' => 'Doe',
    ]);

    expect($participant->fullName)->toBe('Doe');
});

test('participant can have multiple registrations', function (): void {
    $participant = Participant::factory()->create();
    
    Registration::factory()->count(3)->create([
        'participant_id' => $participant->id,
    ]);

    expect($participant->registrations)->toHaveCount(3);
});

test('participant can have newsletter subscription', function (): void {
    $participant = Participant::factory()->create();
    
    NewsletterSubscription::factory()->create([
        'participant_id' => $participant->id,
    ]);

    expect($participant->newsletterSubscription)->toBeInstanceOf(NewsletterSubscription::class);
});

test('participant knows if subscribed to newsletter', function (): void {
    $participant = Participant::factory()->create();
    
    NewsletterSubscription::factory()->create([
        'participant_id' => $participant->id,
        'unsubscribed_at' => null,
    ]);

    expect($participant->isSubscribedToNewsletter())->toBeTrue();
});

test('participant knows if not subscribed to newsletter', function (): void {
    $participant = Participant::factory()->create();

    expect($participant->isSubscribedToNewsletter())->toBeFalse();
});

test('participant knows if unsubscribed from newsletter', function (): void {
    $participant = Participant::factory()->create();
    
    NewsletterSubscription::factory()->create([
        'participant_id' => $participant->id,
        'unsubscribed_at' => now(),
    ]);

    expect($participant->isSubscribedToNewsletter())->toBeFalse();
});

test('participant can be found by email', function (): void {
    $participant = Participant::factory()->create([
        'email' => 'find@example.com',
    ]);

    $found = Participant::findByEmail('find@example.com');

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($participant->id);
});

test('find by email returns null when not found', function (): void {
    $found = Participant::findByEmail('nonexistent@example.com');

    expect($found)->toBeNull();
});

test('findOrCreateByEmail creates new participant', function (): void {
    $participant = Participant::findOrCreateByEmail('new@example.com', [
        'first_name' => 'New',
        'last_name' => 'User',
    ]);

    expect($participant->email)->toBe('new@example.com')
        ->and($participant->first_name)->toBe('New')
        ->and($participant->last_name)->toBe('User');
});

test('findOrCreateByEmail finds existing participant', function (): void {
    $existing = Participant::factory()->create([
        'email' => 'existing@example.com',
        'first_name' => 'Existing',
    ]);

    $found = Participant::findOrCreateByEmail('existing@example.com', [
        'first_name' => 'Different',
    ]);

    expect($found->id)->toBe($existing->id)
        ->and($found->first_name)->toBe('Existing');
});
