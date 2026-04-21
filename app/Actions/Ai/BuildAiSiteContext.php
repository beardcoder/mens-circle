<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Models\Event;
use App\Models\NewsletterSubscription;
use App\Models\Page;
use App\Models\Testimonial;
use App\Services\Ai\AiDataFormatter;
use App\Settings\GeneralSettings;

final readonly class BuildAiSiteContext
{
    public function __construct(
        private AiDataFormatter $formatter,
        private GeneralSettings $settings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $nextEvent = Event::query()
            ->published()
            ->upcoming()
            ->withCount('activeRegistrations')
            ->orderBy('event_date')
            ->first();

        return [
            'site' => $this->formatter->settings($this->settings),
            'summary' => [
                'published_events' => Event::query()->published()->count(),
                'upcoming_events' => Event::query()->published()->upcoming()->count(),
                'published_pages' => Page::query()->published()->count(),
                'pending_testimonials' => Testimonial::query()->where('is_published', false)->count(),
                'active_newsletter_subscribers' => NewsletterSubscription::activeCount(),
            ],
            'next_event' => $nextEvent ? $this->formatter->event($nextEvent) : null,
            'content_inventory' => [
                'pages' => Page::query()->published()->orderBy('title')->pluck('slug')->all(),
                'block_types' => Page::query()
                    ->with('contentBlocks')
                    ->get()
                    ->flatMap(static fn(Page $page) => $page->contentBlocks->pluck('type'))
                    ->unique()
                    ->values()
                    ->all(),
            ],
            'available_endpoints' => [
                'site_context' => route('ai.site-context'),
                'events' => route('ai.events.index'),
                'pages' => route('ai.pages.index'),
                'settings' => route('ai.settings.general.show'),
                'pending_testimonials' => route('ai.testimonials.pending'),
            ],
        ];
    }
}
