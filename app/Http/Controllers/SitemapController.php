<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Page;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $pages = Page::where('is_published', true)->get();
        $events = Event::where('is_published', true)
            ->where('event_date', '>=', now())
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Homepage
        $xml .= '<url>';
        $xml .= '<loc>' . route('home') . '</loc>';
        $xml .= '<lastmod>' . now()->toW3cString() . '</lastmod>';
        $xml .= '<changefreq>weekly</changefreq>';
        $xml .= '<priority>1.0</priority>';
        $xml .= '</url>';

        // Events
        $xml .= '<url>';
        $xml .= '<loc>' . route('event.show') . '</loc>';
        $xml .= '<lastmod>' . ($events->first()?->updated_at ?? now())->toW3cString() . '</lastmod>';
        $xml .= '<changefreq>daily</changefreq>';
        $xml .= '<priority>0.9</priority>';
        $xml .= '</url>';

        // Pages
        foreach ($pages as $page) {
            if ($page->slug === 'home') {
                continue; // Already added
            }

            $xml .= '<url>';
            $xml .= '<loc>' . route('page.show', $page->slug) . '</loc>';
            $xml .= '<lastmod>' . $page->updated_at->toW3cString() . '</lastmod>';
            $xml .= '<changefreq>monthly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        // Legal pages
        $xml .= '<url>';
        $xml .= '<loc>' . route('impressum') . '</loc>';
        $xml .= '<lastmod>' . now()->toW3cString() . '</lastmod>';
        $xml .= '<changefreq>yearly</changefreq>';
        $xml .= '<priority>0.3</priority>';
        $xml .= '</url>';

        $xml .= '<url>';
        $xml .= '<loc>' . route('datenschutz') . '</loc>';
        $xml .= '<lastmod>' . now()->toW3cString() . '</lastmod>';
        $xml .= '<changefreq>yearly</changefreq>';
        $xml .= '<priority>0.3</priority>';
        $xml .= '</url>';

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }
}
