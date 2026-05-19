<?php

declare(strict_types=1);

namespace App\Support;

final readonly class CmsButtonLink
{
    public function __construct(
        public string $href,
        public bool $shouldShow,
    ) {}

    /**
     * Resolve a CMS-configured button link. If the link points at the
     * generic event route, redirect it to the next upcoming event — and
     * hide the button entirely when no upcoming event exists.
     */
    public static function resolve(string $link, bool $hasNextEvent, string $nextEventUrl): self
    {
        $isEventLink = str_contains($link, route('event.show')) || str_contains($link, '/event');

        return new self(href: $isEventLink ? $nextEventUrl : $link, shouldShow: !$isEventLink || $hasNextEvent);
    }
}
