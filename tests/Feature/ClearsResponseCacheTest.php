<?php

declare(strict_types=1);

use App\Models\ContentBlock;
use App\Models\Event;
use App\Models\Page;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\Testimonial;
use Spatie\ResponseCache\Facades\ResponseCache;

beforeEach(function (): void {
    ResponseCache::spy();
});

describe('Response cache clearing on model changes', function (): void {
    it('clears the response cache when an event is created', function (): void {
        Event::factory()->published()->create();

        ResponseCache::shouldHaveReceived('clear');
    });

    it('clears the response cache when an event is updated', function (): void {
        $event = Event::factory()->published()->create();

        $event->update(['title' => 'Updated Title']);

        ResponseCache::shouldHaveReceived('clear')->atLeast()->twice();
    });

    it('clears the response cache when an event is deleted', function (): void {
        $event = Event::factory()->published()->create();

        $event->delete();

        ResponseCache::shouldHaveReceived('clear')->atLeast()->twice();
    });

    it('clears the response cache when a registration changes', function (): void {
        $event = Event::factory()->published()->create();

        Registration::factory()->forEvent($event)->create();

        ResponseCache::shouldHaveReceived('clear');
    });

    it('clears the response cache when a participant changes', function (): void {
        Participant::factory()->create();

        ResponseCache::shouldHaveReceived('clear');
    });

    it('clears the response cache when a testimonial changes', function (): void {
        Testimonial::factory()->create(['email' => 'test@example.com']);

        ResponseCache::shouldHaveReceived('clear');
    });

    it('clears the response cache when a page changes', function (): void {
        Page::factory()->create(['slug' => 'ueber-uns']);

        ResponseCache::shouldHaveReceived('clear');
    });

    it('clears the response cache when a content block changes', function (): void {
        $page = Page::factory()->published()->create(['slug' => 'ueber-uns']);

        ContentBlock::factory()->forPage($page)->create();

        ResponseCache::shouldHaveReceived('clear');
    });
});
