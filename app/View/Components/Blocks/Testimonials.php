<?php

declare(strict_types=1);

namespace App\View\Components\Blocks;

use App\Models\Testimonial;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

class Testimonials extends Component
{
    /** @var Collection<int, \App\Models\Testimonial> */
    public Collection $testimonials;

    public function __construct()
    {
        $this->testimonials = Testimonial::published()->get();
    }

    public function render(): View
    {
        return view('components.blocks.testimonials');
    }

    public function shouldRender(): bool
    {
        return $this->testimonials->isNotEmpty();
    }
}
