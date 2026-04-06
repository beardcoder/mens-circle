<?php

declare(strict_types=1);

use App\Services\PushoverService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

test('sends pushover notification successfully', function (): void {
    config(['pushover.token' => 'test-token', 'pushover.user_key' => 'test-user']);

    Http::fake(['https://api.pushover.net/*' => Http::response(['status' => 1], 200)]);

    $service = new PushoverService();
    $result = $service->send('Test message', ['title' => 'Test Title']);

    expect($result)->toBeTrue();

    Http::assertSent(fn($request): bool => $request['token'] === 'test-token'
        && $request['user'] === 'test-user'
        && $request['message'] === 'Test message'
        && $request['title'] === 'Test Title');
});

test('returns false when token is not configured', function (): void {
    config(['pushover.token' => null, 'pushover.user_key' => 'test-user']);

    Log::shouldReceive('warning')
        ->once()
        ->with('Cannot send Pushover notification - token or user key not configured');

    $service = new PushoverService();
    $result = $service->send('Test message');

    expect($result)->toBeFalse();
});

test('returns false when user key is not configured', function (): void {
    config(['pushover.token' => 'test-token', 'pushover.user_key' => null]);

    Log::shouldReceive('warning')
        ->once()
        ->with('Cannot send Pushover notification - token or user key not configured');

    $service = new PushoverService();
    $result = $service->send('Test message');

    expect($result)->toBeFalse();
});

test('returns false and logs error on api failure', function (): void {
    config(['pushover.token' => 'test-token', 'pushover.user_key' => 'test-user']);

    Http::fake(['https://api.pushover.net/*' => Http::response(['status' => 0, 'errors' => ['invalid token']], 400)]);

    $service = new PushoverService();
    $result = $service->send('Test message');

    expect($result)->toBeFalse();
});

test('passes optional parameters to pushover api', function (): void {
    config(['pushover.token' => 'test-token', 'pushover.user_key' => 'test-user']);

    Http::fake(['https://api.pushover.net/*' => Http::response(['status' => 1], 200)]);

    $service = new PushoverService();
    $service->send('HTML message', [
        'title' => 'Event',
        'url' => 'https://example.com/event/test',
        'url_title' => 'View Event',
        'html' => 1,
        'priority' => 0,
        'sound' => 'pushover',
    ]);

    Http::assertSent(fn($request): bool => $request['html'] === 1
        && $request['url'] === 'https://example.com/event/test'
        && $request['url_title'] === 'View Event'
        && $request['priority'] === 0
        && $request['sound'] === 'pushover');
});
