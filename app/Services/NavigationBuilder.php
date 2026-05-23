<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NavigationCondition;
use App\Enums\NavigationLocation;
use App\Models\Event;
use App\Models\NavigationItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Route;
use Spatie\Navigation\Helpers\ActiveUrlChecker;
use Spatie\Navigation\Navigation;
use Spatie\Navigation\Section;
use Throwable;

/**
 * Builds a Spatie Navigation tree per location, resolving dynamic URLs
 * (next event) and applying conditions. The navigation is intentionally flat;
 * each Section is rendered directly without children.
 */
final class NavigationBuilder
{
    private ?string $cachedNextEventUrl = null;

    private bool $nextEventResolved = false;

    public function build(NavigationLocation $location): Navigation
    {
        $items = NavigationItem::query()->forLocation($location)->get();

        return $this->buildFromItems($items);
    }

    /**
     * @param Collection<int, NavigationItem> $items
     */
    public function buildFromItems(Collection $items): Navigation
    {
        // Spatie binds Navigation as a scoped (per-request) singleton, so
        // Navigation::make() would return the same instance for every
        // location. Instantiate manually to get a fresh tree each time.
        $navigation = new Navigation(app(ActiveUrlChecker::class));

        foreach ($items as $item) {
            $resolvedUrl = $this->resolveUrl($item);

            if ($resolvedUrl === null) {
                continue;
            }

            $navigation->add($item->label, $resolvedUrl, function (Section $section) use ($item): void {
                $section->attributes($this->buildAttributes($item));
            });
        }

        return $navigation;
    }

    /**
     * @return array<string, string|bool|int|null>
     */
    private function buildAttributes(NavigationItem $item): array
    {
        return [
            'is_cta' => $item->is_cta,
            'open_in_new_tab' => $item->open_in_new_tab,
            'umami_event' => $item->location->umamiEventName(),
            'umami_event_target' => $item->umami_event_target,
        ];
    }

    private function resolveUrl(NavigationItem $item): ?string
    {
        $base = $item->condition === NavigationCondition::NextEvent ? $this->nextEventUrl() : $this->expandPlaceholders($item->url);

        if ($base === null) {
            return null;
        }

        return $this->appendAnchor($base, $item->anchor);
    }

    private function expandPlaceholders(string $url): string
    {
        if ($url === '') {
            return '/';
        }

        // Allow #anchor as a shortcut for the home page anchor link.
        if (str_starts_with($url, '#')) {
            return $this->homeUrl() . $url;
        }

        return $url;
    }

    private function appendAnchor(string $url, ?string $anchor): string
    {
        if ($anchor === null || $anchor === '') {
            return $url;
        }

        $fragment = '#' . ltrim($anchor, '#');

        // If the URL already contains a fragment, replace it.
        $hashPosition = strpos($url, '#');
        if ($hashPosition !== false) {
            return substr($url, 0, $hashPosition) . $fragment;
        }

        return $url . $fragment;
    }

    private function homeUrl(): string
    {
        if (Route::has('home')) {
            return route('home');
        }

        return url('/');
    }

    private function nextEventUrl(): ?string
    {
        if ($this->nextEventResolved) {
            return $this->cachedNextEventUrl;
        }

        $this->nextEventResolved = true;

        try {
            $nextEvent = Event::published()->upcoming()->orderBy('event_date')->first(['slug']);
        } catch (Throwable) {
            return $this->cachedNextEventUrl = null;
        }

        if ($nextEvent === null) {
            return $this->cachedNextEventUrl = null;
        }

        return $this->cachedNextEventUrl = route('event.show.slug', $nextEvent->slug);
    }
}
