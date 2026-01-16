<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Page;
use App\Settings\GeneralSettings;
use Illuminate\Http\Response;

class LlmsController extends Controller
{
    public function show(): Response
    {
        return response($this->generateLlmsTxt(), 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }

    private function generateLlmsTxt(): string
    {
        $settings = app(GeneralSettings::class);
        $siteName = $settings->site_name ?? 'Männerkreis Niederbayern';
        $siteDescription = $settings->site_description ?? 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.';

        $lines = [];

        $lines[] = '# ' . $siteName;
        $lines[] = '';
        $lines[] = '> ' . $siteDescription;
        $lines[] = '';

        $lines[] = '## Structured Data API';
        $lines[] = '';
        $lines[] = 'This site provides structured JSON endpoints for AI systems to reliably discover and understand content.';
        $lines[] = '';
        $lines[] = '### Site Structure';
        $lines[] = 'GET ' . route('ai.site');
        $lines[] = 'Returns: site metadata, contact info, content types, capabilities';
        $lines[] = '';
        $lines[] = '### All Pages';
        $lines[] = 'GET ' . route('ai.pages');
        $lines[] = 'Returns: all published pages with structured content blocks (hero, intro, text, FAQ, testimonials, etc.)';
        $lines[] = '';
        $lines[] = '### All Events';
        $lines[] = 'GET ' . route('ai.events');
        $lines[] = 'Returns: upcoming and past events with dates, location, registration status, available spots';
        $lines[] = '';

        $lines[] = '## How to Interpret This Site';
        $lines[] = '';
        $lines[] = '### Content Types';
        $lines[] = '- **Pages**: Dynamic content with flexible blocks (text, FAQ, calls-to-action, testimonials)';
        $lines[] = '- **Events**: Community gatherings with date, time, location, and registration management';
        $lines[] = '- **Language**: All content is in German (de)';
        $lines[] = '';
        $lines[] = '### User Actions';
        $lines[] = '- Event registration: requires first_name, last_name, email, phone_number';
        $lines[] = '- Newsletter subscription: requires email';
        $lines[] = '- Both actions available via web forms on respective pages';
        $lines[] = '';

        $lines[] = '## Content Overview';
        $lines[] = '';
        $lines[] = '### Upcoming Events';
        $upcomingEvents = Event::published()
            ->upcoming()
            ->withCount('confirmedRegistrations as confirmed_registrations_count')
            ->orderBy('event_date')
            ->get();

        if ($upcomingEvents->isEmpty()) {
            $lines[] = 'No upcoming events scheduled';
        } else {
            foreach ($upcomingEvents as $event) {
                $date = $event->event_date->format('Y-m-d');
                $time = $event->start_time->format('H:i');
                $url = route('event.show.slug', $event->slug);
                $spots = $event->max_participants - $event->confirmedRegistrationsCount;
                $status = $spots > 0 ? "{$spots} spots available" : 'fully booked';
                $lines[] = sprintf('- [%s](%s): %s at %s in %s (%s)', $event->title, $url, $date, $time, $event->location, $status);
            }
        }

        $lines[] = '';

        $lines[] = '### Pages';
        $pages = Page::published()
            ->orderBy('title')
            ->get();

        foreach ($pages as $page) {
            $url = $page->slug === 'home' ? route('home') : route('page.show', $page->slug);
            $lines[] = sprintf('- [%s](%s)', $page->title, $url);
        }

        $lines[] = '';

        $lines[] = '## Data Freshness';
        $lines[] = '- All endpoints use response cache that automatically invalidates on content changes';
        $lines[] = '- Cache is cleared when pages, events, or settings are updated';
        $lines[] = '- Content reflects published status in real-time after cache invalidation';

        return implode("\n", $lines);
    }
}
