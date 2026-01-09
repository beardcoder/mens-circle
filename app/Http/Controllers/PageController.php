<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $page = Page::published()
            ->where('slug', 'home')
            ->select('id', 'slug', 'title', 'meta')
            ->with(['contentBlocks.media'])
            ->firstOrFail();

        return view('home', ['page' => $page]);
    }

    public function show(string $slug): View
    {
        $page = Page::published()
            ->where('slug', $slug)
            ->select('id', 'slug', 'title', 'meta')
            ->with(['contentBlocks.media'])
            ->firstOrFail();

        return view('home', ['page' => $page]);
    }
}
