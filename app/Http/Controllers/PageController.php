<?php

namespace App\Http\Controllers;

use App\Models\Page;
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

        return view('home', ['page' => $page]);
    }

    public function show(string $slug): View
    {
        $page = cache()->remember('page.'.$slug, 3600, function () use ($slug) {
            return Page::where('slug', $slug)
                ->where('is_published', true)
                ->firstOrFail();
        });

        return view('home', ['page' => $page]);
    }
}
