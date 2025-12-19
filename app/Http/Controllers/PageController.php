<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
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

        return view('page', compact('page'));
    }

    public function impressum(): View
    {
        return view('impressum');
    }

    public function datenschutz(): View
    {
        return view('datenschutz');
    }
}
