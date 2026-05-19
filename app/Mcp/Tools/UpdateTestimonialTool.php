<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Testimonial;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Carbon;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class UpdateTestimonialTool extends Tool
{
    protected string $description = 'Update a testimonial. Publish, unpublish, or edit its content.';

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->required()->description('The numeric testimonial ID to update.'),
            'quote' => $schema->string()->description('Updated testimonial quote text.'),
            'author_name' => $schema->string()->nullable()->description('Updated author name.'),
            'role' => $schema->string()->nullable()->description('Updated author role or description.'),
            'is_published' => $schema->boolean()->description('Whether the testimonial should be published.'),
            'sort_order' => $schema->integer()->description('Display order (lower = earlier).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'id' => ['required', 'integer'],
            'quote' => ['sometimes', 'string'],
            'author_name' => ['sometimes', 'string', 'max:255', 'nullable'],
            'role' => ['sometimes', 'string', 'max:255', 'nullable'],
            'is_published' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer'],
        ]);

        $testimonial = Testimonial::query()->find($data['id']);

        if ($testimonial === null) {
            return Response::error("Testimonial [{$data['id']}] not found.");
        }

        $fields = ['quote', 'author_name', 'role', 'is_published', 'sort_order'];

        $updates = array_filter(
            array_intersect_key($data, array_flip($fields)),
            static fn($v): bool => $v !== null,
        );

        if (isset($updates['is_published']) && $updates['is_published'] && $testimonial->published_at === null) {
            $updates['published_at'] = Carbon::now();
        }

        $testimonial->update($updates);

        $label = $testimonial->author_name ?? "Testimonial #{$testimonial->id}";

        return Response::text("{$label} updated successfully.");
    }
}
