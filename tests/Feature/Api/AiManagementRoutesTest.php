<?php

declare(strict_types=1);

use App\Models\Testimonial;
use Carbon\CarbonImmutable;
use App\Models\User;
use Illuminate\Support\Str;

afterEach(function (): void {
    config()->set('services.ai_management.token', null);
});

test('ai management routes require ai access', function (): void {
    $this->getJson(route('ai.site-context'))->assertUnauthorized();
});

test('remote mcp endpoint requires ai access', function (): void {
    $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [],
    ])->assertUnauthorized();
});

test('site context endpoint returns structured json with bearer token', function (): void {
    config()->set('services.ai_management.token', 'test-token');

    $this->withHeader('Authorization', 'Bearer test-token')
        ->getJson(route('ai.site-context'))
        ->assertSuccessful()
        ->assertJsonStructure([
            'site',
            'summary',
            'content_inventory',
            'available_endpoints',
        ]);
});


test('event planning endpoint parses month names from the prompt', function (): void {
    config()->set('services.ai_management.token', 'test-token');

    $expectedYear = CarbonImmutable::now()->month > 1 || (CarbonImmutable::now()->month === 1 && CarbonImmutable::now()->day > 15)
        ? CarbonImmutable::now()->year + 1
        : CarbonImmutable::now()->year;

    $this->withHeader('Authorization', 'Bearer test-token')
        ->postJson(route('ai.events.plan'), [
            'prompt' => 'Plane bitte ein Männerkreis-Event im Januar zum Thema Mut und Klarheit.',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.start_time', '19:00')
        ->assertJsonPath('data.event_date', sprintf('%d-01-15', $expectedYear))
        ->assertJson(fn($json) => $json
            ->where('data.title', fn(string $value): bool => str_contains($value, 'Januar'))
            ->etc());
});

test('authenticated users can manage ai pages', function (): void {
    $this->actingAs(User::factory()->create());

    $pageResponse = $this->postJson(route('ai.pages.generate'), [
        'title' => 'KI Seite',
        'prompt' => 'Erstelle eine kurze deutschsprachige Landingpage für einen Männerkreis-Abend.',
    ]);

    $pageResponse->assertCreated();
    $pageId = $pageResponse->json('data.id');

    $this->patchJson(route('ai.pages.blocks.update', ['page' => $pageId]), [
        'content_blocks' => [[
            'type' => 'text_section',
            'data' => [
                'block_id' => (string) Str::uuid(),
                'eyebrow' => 'Update',
                'title' => 'Aktualisiert',
                'content' => '<p>Aktualisierter Inhalt</p>',
            ],
        ]],
    ])->assertSuccessful();

    $this->postJson(route('ai.pages.publish', ['page' => $pageId]), [
        'confirm' => true,
        'is_published' => true,
    ])->assertSuccessful()->assertJsonPath('data.is_published', true);
});

test('newsletter endpoints generate previews and require confirmation for send', function (): void {
    $this->actingAs(User::factory()->create());

    $generate = $this->postJson(route('ai.newsletters.generate'), [
        'prompt' => 'Betone Gemeinschaft und das nächste Treffen.',
    ]);

    $generate->assertCreated();
    $newsletterId = $generate->json('data.id');

    $this->postJson(route('ai.newsletters.preview', ['newsletter' => $newsletterId]))
        ->assertSuccessful()
        ->assertJsonStructure(['data', 'recipient_count', 'can_send']);

    $this->postJson(route('ai.newsletters.send', ['newsletter' => $newsletterId]), [
        'confirm_send' => true,
    ])->assertSuccessful();
});

test('testimonial moderation endpoints expose pending items', function (): void {
    $this->actingAs(User::factory()->create());
    $testimonial = Testimonial::factory()->unpublished()->create();

    $this->getJson(route('ai.testimonials.pending'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');

    $this->postJson(route('ai.testimonials.publish', ['testimonial' => $testimonial]), [
        'confirm' => true,
    ])->assertSuccessful()->assertJsonPath('data.is_published', true);
});

test('general settings endpoint returns data for authenticated users', function (): void {
    $this->actingAs(User::factory()->create());

    $this->getJson(route('ai.settings.general.show'))
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['site_name', 'contact_email', 'location']]);
});
