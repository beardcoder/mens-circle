<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Testimonial;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return $this->show('home');
    }

    public function show(string $slug): View
    {
        $page = Page::published()
            ->where('slug', $slug)
            ->select('id', 'slug', 'title', 'meta')
            ->with(['contentBlocks.media'])
            ->firstOrFail();

        // Load testimonials if needed for this page
        $testimonials = $page->contentBlocks->contains('type', 'testimonials')
            ? Testimonial::published()->get()
            : collect();

        // Use specific view if it exists, otherwise fall back to generic 'page' view
        /** @var view-string $viewName */
        $viewName = ViewFacade::exists($slug) ? $slug : 'page';

        return view($viewName, [
            'page' => $page,
            'testimonials' => $testimonials,
        ]);
    }
}
