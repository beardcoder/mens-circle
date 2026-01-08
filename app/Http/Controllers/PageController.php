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
        $query = Page::where('slug', 'home')
            ->where('is_published', true)
            ->select('id', 'slug', 'title', 'meta');

        // Nur contentBlocks laden wenn die Tabelle korrekt strukturiert ist
        if (Schema::hasTable('content_blocks') && Schema::hasColumn('content_blocks', 'page_id')) {
            $query->with('contentBlocks');
        }

        $page = $query->firstOrFail();

        return view('home', ['page' => $page]);
    }

    public function show(string $slug): View
    {
        $query = Page::where('slug', $slug)
            ->where('is_published', true)
            ->select('id', 'slug', 'title', 'meta');

        // Nur contentBlocks laden wenn die Tabelle korrekt strukturiert ist
        if (Schema::hasTable('content_blocks') && Schema::hasColumn('content_blocks', 'page_id')) {
            $query->with('contentBlocks');
        }

        $page = $query->firstOrFail();

        return view('home', ['page' => $page]);
    }
}
