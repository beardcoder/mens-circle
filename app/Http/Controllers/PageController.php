<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $page = Page::where('slug', 'home')
            ->where('is_published', true)
            ->firstOrFail();

        return view('home', ['page' => $page]);
    }

    public function show(string $slug): View
    {
        $page = Page::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return view('home', ['page' => $page]);
    }
}
