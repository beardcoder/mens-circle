<?php

declare(strict_types=1);

namespace App\Features\Pages\Http\Controllers;

use App\Features\Pages\Domain\Models\Page;
use App\Features\Testimonials\Domain\Models\Testimonial;
use App\Http\Controllers\Controller;
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
