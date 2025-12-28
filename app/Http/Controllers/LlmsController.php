<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Page;
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
        $settings = settings();
        $siteName = $settings['site_name'] ?? 'Männerkreis Niederbayern';
        $siteDescription = $settings['site_description'] ?? 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.';

        $lines = [];

        $lines[] = "# {$siteName}";
        $lines[] = '';
        $lines[] = "> {$siteDescription}";
        $lines[] = '';
        $lines[] = 'Der Männerkreis Niederbayern ist eine Gemeinschaft für Männer, die sich regelmäßig zu Veranstaltungen treffen. Die Website bietet Informationen zu kommenden Events, Anmeldung zu Veranstaltungen und einen Newsletter-Service.';
        $lines[] = '';

        $lines[] = '## Veranstaltungen';
        $upcomingEvents = Event::query()
            ->where('is_published', true)
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->get();

        if ($upcomingEvents->isEmpty()) {
            $lines[] = 'Aktuell sind keine kommenden Veranstaltungen geplant.';
        } else {
            foreach ($upcomingEvents as $event) {
                $date = $event->event_date->format('d.m.Y');
                $url = route('event.show.slug', $event->slug);
                $description = $event->location ? "Veranstaltung am {$date} in {$event->location}" : "Veranstaltung am {$date}";
                $lines[] = "- [{$event->title}]({$url}): {$description}";
            }
        }
        $lines[] = '';

        $pastEvents = Event::query()
            ->where('is_published', true)
            ->where('event_date', '<', now())
            ->orderByDesc('event_date')
            ->limit(5)
            ->get();

        if ($pastEvents->isNotEmpty()) {
            $lines[] = '## Vergangene Veranstaltungen';
            foreach ($pastEvents as $event) {
                $date = $event->event_date->format('d.m.Y');
                $url = route('event.show.slug', $event->slug);
                $lines[] = "- [{$event->title}]({$url}): Stattgefunden am {$date}";
            }
            $lines[] = '';
        }

        $lines[] = '## Seiten';
        $lines[] = '- [Startseite]('.route('home').'): Hauptseite mit Überblick über den Männerkreis';

        $pages = Page::query()
            ->where('is_published', true)
            ->whereNotIn('slug', ['home', 'impressum', 'datenschutz'])
            ->orderBy('title')
            ->get();

        foreach ($pages as $page) {
            $url = route('page.show', $page->slug);
            $lines[] = "- [{$page->title}]({$url}): Informationsseite";
        }
        $lines[] = '';

        $lines[] = '## Rechtliches';
        $lines[] = '- [Impressum]('.route('page.show', 'impressum').'): Rechtliche Angaben und Anbieterkennzeichnung';
        $lines[] = '- [Datenschutz]('.route('page.show', 'datenschutz').'): Datenschutzerklärung gemäß DSGVO';
        $lines[] = '';

        $lines[] = '## Aktionen';
        $lines[] = '- Newsletter-Anmeldung: Über das Formular auf der Startseite können sich Interessierte für den Newsletter anmelden';
        $lines[] = '- Veranstaltungsanmeldung: Über die jeweilige Veranstaltungsseite können sich Teilnehmer registrieren';

        return implode("\n", $lines);
    }
}
