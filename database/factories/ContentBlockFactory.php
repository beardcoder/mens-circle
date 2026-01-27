<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ContentBlock;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ContentBlock>
 */
class ContentBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'type' => 'text',
            'data' => [
                'content' => \fake()->paragraphs(2, true),
            ],
            'block_id' => Str::uuid()->toString(),
            'order' => 0,
        ];
    }

    public function forPage(Page $page): static
    {
        return $this->state(fn (array $attributes): array => [
            'page_id' => $page->id,
        ]);
    }

    public function hero(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'hero',
            'data' => [
                'title' => \fake()->sentence(),
                'subtitle' => \fake()->sentence(),
            ],
        ]);
    }

    public function text(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'text',
            'data' => [
                'content' => \fake()->paragraphs(3, true),
            ],
        ]);
    }
}
