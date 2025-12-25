<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        // Check if there is a next event
        $hasNextEvent = cache()->remember('has_next_event', 600, function () {
            return Event::where('is_published', true)
                ->where('event_date', '>=', now())
                ->exists();
        });

        // If no event exists, show the no-event landing page
        if (!$hasNextEvent) {
            return view('no-event-landing');
        }

        // Otherwise, show the regular home page
        $page = cache()->remember('page.home', 3600, function () {
            return Page::where('slug', 'home')
                ->where('is_published', true)
                ->firstOrFail();
        });

        return view('home', compact('page'));
    }

    public function show(string $slug): View
    {
        $page = cache()->remember("page.{$slug}", 3600, function () use ($slug) {
            return Page::where('slug', $slug)
                ->where('is_published', true)
                ->firstOrFail();
        });

        return view('home', compact('page'));
    }
}
