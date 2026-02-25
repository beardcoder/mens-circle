<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Page;

test('home alias redirects permanently to root url', function (): void {
    $response = $this->get('/home');

    $response->assertRedirect('/');
    expect($response->status())->toBe(301);
});

test('sitemap excludes duplicate home page slug and includes canonical urls', function (): void {
    Page::factory()
        ->published()
        ->create([
            'title' => 'Home',
            'slug' => 'home',
        ]);

    $page = Page::factory()
        ->published()
        ->create([
            'title' => 'Ueber uns',
            'slug' => 'ueber-uns',
        ]);

    $event = Event::factory()->create([
        'is_published' => true,
        'event_date' => now()->addDays(14),
    ]);

    $this->artisan('sitemap:generate')->assertSuccessful();

    $sitemap = file_get_contents(public_path('sitemap.xml'));

    expect($sitemap)->toBeString()->not->toBeEmpty();
    expect($sitemap)
        ->toContain('<loc>' . route('home') . '</loc>')
        ->toContain('<loc>' . route('event.show.slug', $event->slug) . '</loc>')
        ->toContain('<loc>' . route('page.show', $page->slug) . '</loc>')
        ->not->toContain('<loc>' . route('page.show', 'home') . '</loc>');
});
