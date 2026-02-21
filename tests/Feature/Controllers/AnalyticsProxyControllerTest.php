<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    Config::set('analytics.umami.enabled', true);
    Config::set('analytics.umami.website_id', 'test-website-id');
    Config::set('analytics.umami.script_url', 'https://cloud.umami.is/script.js');
});

test('script endpoint proxies umami script', function (): void {
    Http::fake([
        'https://cloud.umami.is/script.js' => Http::response('(function(){console.log("umami")})();', 200),
    ]);

    $response = $this->get('/va/script.js');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/javascript');
    expect($response->getContent())->toContain('umami');
});

test('script endpoint caches the response', function (): void {
    Http::fake([
        'https://cloud.umami.is/script.js' => Http::response('(function(){})();', 200),
    ]);

    $this->get('/va/script.js')->assertOk();
    $this->get('/va/script.js')->assertOk();

    Http::assertSentCount(1);
});

test('collect endpoint proxies events to umami', function (): void {
    Http::fake([
        'https://cloud.umami.is/api/send' => Http::response('{"ok":true}', 200),
    ]);

    $payload = [
        'type' => 'event',
        'payload' => [
            'website' => 'test-website-id',
            'name' => 'test-event',
        ],
    ];

    $response = $this->postJson('/va/api/send', $payload);

    $response->assertOk();

    Http::assertSent(fn ($request): bool => $request->url() === 'https://cloud.umami.is/api/send'
        && $request['type'] === 'event');
});
