<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Testimonial;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListTestimonialsTool extends Tool
{
    protected string $description = 'List testimonials. Optionally filter by published status.';

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'filter' => $schema->string()->description('Filter: "published", "unpublished", or leave empty for all.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $query = Testimonial::query()
            ->withoutGlobalScope('order')
            ->orderBy('created_at', 'desc');

        $filter = $request->get('filter');

        if ($filter === 'published') {
            $query->published();
        } elseif ($filter === 'unpublished') {
            $query->where('is_published', false);
        }

        $testimonials = $query->get(['id', 'quote', 'author_name', 'role', 'is_published', 'sort_order', 'created_at']);

        return Response::json(
            $testimonials->map(static fn(Testimonial $t): array => [
                'id' => $t->id,
                'quote' => $t->quote,
                'author_name' => $t->author_name,
                'role' => $t->role,
                'is_published' => $t->is_published,
                'sort_order' => $t->sort_order,
                'created_at' => $t->created_at?->toISOString(),
            ])->all(),
        );
    }
}
