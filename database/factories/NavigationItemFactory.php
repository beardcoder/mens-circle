<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Navigation;
use App\Models\NavigationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NavigationItem>
 */
class NavigationItemFactory extends Factory
{
    protected $model = NavigationItem::class;

    public function definition(): array
    {
        return [
            'navigation_id' => Navigation::factory(),
            'parent_id' => null,
            'label' => fake()->words(2, true),
            'url' => fake()->url(),
            'route_name' => null,
            'route_params' => null,
            'anchor' => null,
            'target' => '_self',
            'order' => 0,
            'is_active' => true,
            'icon' => null,
            'css_class' => null,
            'data_attributes' => null,
        ];
    }

    public function forNavigation(Navigation $navigation): self
    {
        return $this->state(fn(array $attributes): array => [
            'navigation_id' => $navigation->id,
        ]);
    }

    public function withRoute(string $routeName, ?array $params = null): self
    {
        return $this->state(fn(array $attributes): array => [
            'route_name' => $routeName,
            'route_params' => $params,
            'url' => null,
        ]);
    }

    public function withAnchor(string $anchor): self
    {
        return $this->state(fn(array $attributes): array => [
            'anchor' => $anchor,
        ]);
    }

    public function withParent(NavigationItem $parent): self
    {
        return $this->state(fn(array $attributes): array => [
            'parent_id' => $parent->id,
            'navigation_id' => $parent->navigation_id,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn(array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function withAnalytics(string $event, string $target): self
    {
        return $this->state(fn(array $attributes): array => [
            'data_attributes' => [
                'umami-event' => $event,
                'umami-event-target' => $target,
            ],
        ]);
    }

    public function atOrder(int $order): self
    {
        return $this->state(fn(array $attributes): array => [
            'order' => $order,
        ]);
    }
}
