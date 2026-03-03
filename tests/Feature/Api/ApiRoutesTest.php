<?php

declare(strict_types=1);

use App\Models\Event;

test('api routes are accessible without csrf token', function (): void {
    $event = Event::factory()->published()->tomorrow()->create([
        'max_participants' => 10,
    ]);

    $response = $this->postJson(route('event.register'), [
        'event_id' => $event->id,
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'api-test@example.com',
        'phone_number' => null,
        'privacy' => 1,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
});

test('api event register route has api prefix', function (): void {
    expect(route('event.register'))->toContain('/api/');
});

test('api newsletter subscribe route has api prefix', function (): void {
    expect(route('newsletter.subscribe'))->toContain('/api/');
});

test('api testimonial submit route has api prefix', function (): void {
    expect(route('testimonial.submit'))->toContain('/api/');
});

