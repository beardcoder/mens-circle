<?php

declare(strict_types=1);

use App\Models\NewsletterSubscription;
use App\Models\Participant;

test('can subscribe to newsletter', function (): void {
    $response = $this->postJson(route('newsletter.subscribe'), [
        'email' => 'newsletter@example.com',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);

    $participant = Participant::where('email', 'newsletter@example.com')->first();
    expect($participant)->not->toBeNull();

    $this->assertDatabaseHas('newsletter_subscriptions', [
        'participant_id' => $participant->id,
    ]);
});

test('newsletter subscription requires email', function (): void {
    $response = $this->postJson(route('newsletter.subscribe'), [
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
    ]);
});

test('newsletter subscription validates email format', function (): void {
    $response = $this->postJson(route('newsletter.subscribe'), [
        'email' => 'invalid-email',
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
    ]);
});

test('cannot subscribe to newsletter twice with same email', function (): void {
    $participant = Participant::factory()->create([
        'email' => 'existing@example.com',
    ]);

    NewsletterSubscription::factory()->create([
        'participant_id' => $participant->id,
        'unsubscribed_at' => null,
    ]);

    $response = $this->postJson(route('newsletter.subscribe'), [
        'email' => 'existing@example.com',
    ]);

    $response->assertStatus(409);
    $response->assertJson([
        'success' => false,
    ]);
});

test('can resubscribe after unsubscribing', function (): void {
    $participant = Participant::factory()->create([
        'email' => 'resubscribe@example.com',
    ]);

    $subscription = NewsletterSubscription::factory()->create([
        'participant_id' => $participant->id,
        'unsubscribed_at' => now()->subDays(7),
    ]);

    $response = $this->postJson(route('newsletter.subscribe'), [
        'email' => 'resubscribe@example.com',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);

    $subscription->refresh();
    expect($subscription->unsubscribed_at)->toBeNull();
});

test('can unsubscribe from newsletter with valid token', function (): void {
    $this->markTestSkipped('View tests require full frontend build');
    
    $participant = Participant::factory()->create();
    
    $subscription = NewsletterSubscription::factory()->create([
        'participant_id' => $participant->id,
        'token' => 'test-token-123',
        'unsubscribed_at' => null,
    ]);

    $response = $this->get(route('newsletter.unsubscribe', ['token' => 'test-token-123']));

    $response->assertStatus(200);
    $response->assertViewIs('newsletter.unsubscribed');

    $subscription->refresh();
    expect($subscription->unsubscribed_at)->not->toBeNull();
});

test('unsubscribe with invalid token returns 404', function (): void {
    $this->markTestSkipped('View tests require full frontend build');
    
    $response = $this->get(route('newsletter.unsubscribe', ['token' => 'invalid-token']));

    $response->assertStatus(404);
});

test('unsubscribe shows message when already unsubscribed', function (): void {
    $this->markTestSkipped('View tests require full frontend build');
    
    $participant = Participant::factory()->create();
    
    $subscription = NewsletterSubscription::factory()->create([
        'participant_id' => $participant->id,
        'token' => 'already-unsubscribed',
        'unsubscribed_at' => now()->subDays(1),
    ]);

    $response = $this->get(route('newsletter.unsubscribe', ['token' => 'already-unsubscribed']));

    $response->assertStatus(200);
    $response->assertViewIs('newsletter.unsubscribed');
    $response->assertSee('bereits');
});
