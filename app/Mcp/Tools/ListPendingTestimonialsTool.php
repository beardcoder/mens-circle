<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Testimonial;
use App\Services\Ai\AiDataFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

final class ListPendingTestimonialsTool extends Tool
{
    protected string $name = 'list_pending_testimonials';

    protected string $description = 'Listet unveröffentlichte Testimonials zur Moderation auf.';

    public function __construct(
        private readonly AiDataFormatter $formatter,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $testimonials = Testimonial::query()->where('is_published', false)->orderByDesc('created_at')->get();

        return Response::structured([
            'data' => $this->formatter->testimonials($testimonials),
        ]);
    }
}
