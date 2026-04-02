<?php

declare(strict_types=1);

use App\Models\ContentBlock;
use App\Models\Event;
use App\Models\Page;
use App\Models\Registration;
use App\Models\Testimonial;
use Spatie\ResponseCache\Facades\ResponseCache;

beforeEach(function (): void {
    ResponseCache::spy();
    cache()->forget('next_event_data');
});

describe('Event cache invalidation', function (): void {
    it('forgets homepage, event list, and event detail URLs on create', function (): void {
        $event = Event::factory()->published()->create();

        ResponseCache::shouldHaveReceived('forget')
            ->once()
            ->withArgs(fn(array $urls): bool => in_array(url('/'), $urls, true)
                && in_array(url('/event'), $urls, true)
                && in_array(route('event.show.slug', $event->slug), $urls, true));
    });

    it('does not clear the entire cache on event update', function (): void {
        $event = Event::factory()->published()->create();

        $event->update(['title' => 'Updated Title']);

        ResponseCache::shouldNotHaveReceived('clear');
    });
});

describe('Registration cache invalidation', function (): void {
    it('forgets event list and event detail URLs on create', function (): void {
        $event = Event::factory()->published()->create();
        Registration::factory()->forEvent($event)->create();

        ResponseCache::shouldHaveReceived('forget')
            ->withArgs(fn(array $urls): bool => in_array(url('/event'), $urls, true)
                && in_array(route('event.show.slug', $event->slug), $urls, true));
    });

    it('does not clear the entire cache on registration update', function (): void {
        $event = Event::factory()->published()->create();
        $registration = Registration::factory()->forEvent($event)->create();

        $registration->cancel();

        ResponseCache::shouldNotHaveReceived('clear');
    });
});

describe('Testimonial cache invalidation', function (): void {
    it('only forgets the homepage URL on create', function (): void {
        Testimonial::factory()->create(['email' => 'test@example.com']);

        ResponseCache::shouldHaveReceived('forget')
            ->once()
            ->withArgs(fn(array $urls): bool => $urls === [url('/')]);
    });

    it('does not clear the entire cache on testimonial update', function (): void {
        $testimonial = Testimonial::factory()->create(['email' => 'test@example.com']);

        $testimonial->update(['quote' => 'Updated quote']);

        ResponseCache::shouldNotHaveReceived('clear');
    });
});

describe('Page cache invalidation', function (): void {
    it('forgets the homepage URL for the home page', function (): void {
        Page::factory()->create(['slug' => 'home']);

        ResponseCache::shouldHaveReceived('forget')
            ->withArgs(fn(array $urls): bool => $urls === [url('/')]);
    });

    it('forgets the page slug URL for non-home pages', function (): void {
        Page::factory()->published()->create(['slug' => 'ueber-uns']);

        ResponseCache::shouldHaveReceived('forget')
            ->withArgs(fn(array $urls): bool => $urls === [route('page.show', 'ueber-uns')]);
    });
});

describe('ContentBlock cache invalidation', function (): void {
    it('forgets the parent page URL on create', function (): void {
        $page = Page::factory()->published()->create(['slug' => 'ueber-uns']);
        ContentBlock::factory()->forPage($page)->create();

        ResponseCache::shouldHaveReceived('forget')
            ->withArgs(fn(array $urls): bool => in_array(route('page.show', 'ueber-uns'), $urls, true));
    });

    it('forgets the homepage URL when block belongs to the home page', function (): void {
        $homePage = Page::factory()->create(['slug' => 'home']);
        ContentBlock::factory()->forPage($homePage)->create();

        ResponseCache::shouldHaveReceived('forget')
            ->withArgs(fn(array $urls): bool => in_array(url('/'), $urls, true));
    });
});

describe('next_event_data cache key', function (): void {
    it('clears the next_event_data cache key on any model change', function (): void {
        cache()->put('next_event_data', 'some-value', 300);

        Event::factory()->published()->create();

        expect(cache()->has('next_event_data'))->toBeFalse();
    });
});
