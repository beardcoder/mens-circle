<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\NavigationCondition;
use App\Enums\NavigationLocation;
use App\Models\Event;
use App\Models\NavigationItem;
use App\Services\NavigationBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Navigation\Section;
use Tests\TestCase;

class NavigationBuilderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_only_includes_visible_items(): void
    {
        NavigationItem::factory()->header()->create(['label' => 'Visible', 'url' => '/visible']);
        NavigationItem::factory()->header()->hidden()->create(['label' => 'Hidden', 'url' => '/hidden']);

        $navigation = app(NavigationBuilder::class)->build(NavigationLocation::Header);

        $titles = array_map(static fn(Section $section): string => $section->title, $navigation->children);

        self::assertSame(['Visible'], $titles);
    }

    #[Test]
    public function it_orders_items_by_sort_then_id(): void
    {
        // Inserted second (sort=10) but created first, so it has the lower id.
        $second = NavigationItem::factory()->header()->create(['label' => 'Second', 'url' => '/a', 'sort' => 10]);
        $first = NavigationItem::factory()->header()->create(['label' => 'First', 'url' => '/b', 'sort' => 5]);
        $thirdSameSort = NavigationItem::factory()->header()->create(['label' => 'Third', 'url' => '/c', 'sort' => 10]);

        $navigation = app(NavigationBuilder::class)->build(NavigationLocation::Header);

        $titles = array_map(static fn(Section $section): string => $section->title, $navigation->children);

        // sort=5 wins overall; among sort=10 entries, the smaller id ($second) comes first.
        self::assertSame(['First', 'Second', 'Third'], $titles);
        self::assertLessThan($thirdSameSort->id, $second->id);
    }

    #[Test]
    public function it_expands_anchor_only_urls_to_the_home_route(): void
    {
        NavigationItem::factory()->header()->create(['label' => 'Über', 'url' => '#ueber']);

        $navigation = app(NavigationBuilder::class)->build(NavigationLocation::Header);

        self::assertCount(1, $navigation->children);
        self::assertSame(route('home') . '#ueber', $navigation->children[0]->url);
    }

    #[Test]
    public function it_preserves_internal_paths(): void
    {
        NavigationItem::factory()->header()->create(['label' => 'Atemübung', 'url' => '/atemuebung']);

        $navigation = app(NavigationBuilder::class)->build(NavigationLocation::Header);

        self::assertSame('/atemuebung', $navigation->children[0]->url);
    }

    #[Test]
    public function it_appends_a_separate_anchor_to_the_resolved_url(): void
    {
        NavigationItem::factory()->header()->create([
            'label' => 'Über uns',
            'url' => '/atemuebung',
            'anchor' => 'ueber',
        ]);

        $navigation = app(NavigationBuilder::class)->build(NavigationLocation::Header);

        self::assertSame('/atemuebung#ueber', $navigation->children[0]->url);
    }

    #[Test]
    public function it_appends_anchor_to_home_when_url_is_empty(): void
    {
        NavigationItem::factory()->header()->create([
            'label' => 'Stimmen',
            'url' => '',
            'anchor' => 'stimmen',
        ]);

        $navigation = app(NavigationBuilder::class)->build(NavigationLocation::Header);

        self::assertSame('/#stimmen', $navigation->children[0]->url);
    }

    #[Test]
    public function it_replaces_an_existing_fragment_when_anchor_is_set(): void
    {
        NavigationItem::factory()->header()->create([
            'label' => 'Über',
            'url' => '#legacy',
            'anchor' => 'ueber',
        ]);

        $navigation = app(NavigationBuilder::class)->build(NavigationLocation::Header);

        self::assertSame(route('home') . '#ueber', $navigation->children[0]->url);
    }

    #[Test]
    public function it_treats_empty_anchor_string_as_no_anchor(): void
    {
        NavigationItem::factory()->header()->create([
            'label' => 'Atemübung',
            'url' => '/atemuebung',
            'anchor' => '',
        ]);

        $navigation = app(NavigationBuilder::class)->build(NavigationLocation::Header);

        self::assertSame('/atemuebung', $navigation->children[0]->url);
    }

    #[Test]
    public function it_preserves_absolute_urls(): void
    {
        NavigationItem::factory()->footerPrimary()->create([
            'label' => 'Externe Seite',
            'url' => 'https://example.com/page',
        ]);

        $navigation = app(NavigationBuilder::class)->build(NavigationLocation::FooterPrimary);

        self::assertSame('https://example.com/page', $navigation->children[0]->url);
    }

    #[Test]
    public function condition_next_event_resolves_to_upcoming_event_url(): void
    {
        $event = Event::create([
            'title' => 'Upcoming',
            'slug' => 'upcoming-event',
            'description' => 'desc',
            'event_date' => now()->addDays(7),
            'start_time' => '19:00',
            'end_time' => '21:00',
            'location' => 'Straubing',
            'max_participants' => 8,
            'is_published' => true,
        ]);

        NavigationItem::factory()->header()->create([
            'label' => 'Nächster Termin',
            'url' => '',
            'condition' => NavigationCondition::NextEvent,
            'is_cta' => true,
        ]);

        $navigation = app(NavigationBuilder::class)->build(NavigationLocation::Header);

        self::assertCount(1, $navigation->children);
        self::assertSame(route('event.show.slug', $event->slug), $navigation->children[0]->url);
    }

    #[Test]
    public function condition_next_event_hides_item_when_no_upcoming_event_exists(): void
    {
        Event::create([
            'title' => 'Past',
            'slug' => 'past-event',
            'description' => 'desc',
            'event_date' => now()->subDays(7),
            'start_time' => '19:00',
            'end_time' => '21:00',
            'location' => 'Straubing',
            'max_participants' => 8,
            'is_published' => true,
        ]);

        NavigationItem::factory()->header()->create([
            'label' => 'Nächster Termin',
            'url' => '',
            'condition' => NavigationCondition::NextEvent,
        ]);

        $navigation = app(NavigationBuilder::class)->build(NavigationLocation::Header);

        self::assertSame([], $navigation->children);
    }

    #[Test]
    public function condition_next_event_ignores_unpublished_events(): void
    {
        Event::create([
            'title' => 'Draft',
            'slug' => 'draft-event',
            'description' => 'desc',
            'event_date' => now()->addDays(7),
            'start_time' => '19:00',
            'end_time' => '21:00',
            'location' => 'Straubing',
            'max_participants' => 8,
            'is_published' => false,
        ]);

        NavigationItem::factory()->header()->create([
            'label' => 'Nächster Termin',
            'url' => '',
            'condition' => NavigationCondition::NextEvent,
        ]);

        $navigation = app(NavigationBuilder::class)->build(NavigationLocation::Header);

        self::assertSame([], $navigation->children);
    }

    #[Test]
    public function attributes_expose_cta_target_event_name_and_target(): void
    {
        NavigationItem::factory()->header()->cta()->create([
            'label' => 'CTA',
            'url' => '/cta',
            'open_in_new_tab' => true,
            'umami_event_target' => 'go-to-event',
        ]);

        $navigation = app(NavigationBuilder::class)->build(NavigationLocation::Header);

        $attributes = $navigation->children[0]->attributes;

        self::assertTrue($attributes['is_cta']);
        self::assertTrue($attributes['open_in_new_tab']);
        self::assertSame('nav-click', $attributes['umami_event']);
        self::assertSame('go-to-event', $attributes['umami_event_target']);
    }

    #[Test]
    public function attributes_use_footer_event_name_for_footer_locations(): void
    {
        NavigationItem::factory()->footerPrimary()->create([
            'label' => 'FAQ',
            'url' => '#faq',
            'umami_event_target' => 'faq',
        ]);

        $navigation = app(NavigationBuilder::class)->build(NavigationLocation::FooterPrimary);

        $attributes = $navigation->children[0]->attributes;

        self::assertFalse($attributes['is_cta']);
        self::assertFalse($attributes['open_in_new_tab']);
        self::assertSame('footer-link', $attributes['umami_event']);
        self::assertSame('faq', $attributes['umami_event_target']);
    }

    #[Test]
    public function build_returns_only_items_for_requested_location(): void
    {
        NavigationItem::factory()->header()->create(['label' => 'H', 'url' => '/h']);
        NavigationItem::factory()->footerPrimary()->create(['label' => 'F', 'url' => '/f']);
        NavigationItem::factory()->footerLegal()->create(['label' => 'L', 'url' => '/l']);

        $header = app(NavigationBuilder::class)->build(NavigationLocation::Header);
        $footer = app(NavigationBuilder::class)->build(NavigationLocation::FooterPrimary);
        $legal = app(NavigationBuilder::class)->build(NavigationLocation::FooterLegal);

        self::assertSame(['H'], array_map(static fn(Section $s): string => $s->title, $header->children));
        self::assertSame(['F'], array_map(static fn(Section $s): string => $s->title, $footer->children));
        self::assertSame(['L'], array_map(static fn(Section $s): string => $s->title, $legal->children));
    }
}
