<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NavigationType;
use App\Models\Navigation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Navigation>
 */
class NavigationFactory extends Factory
{
    protected $model = Navigation::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true) . ' Navigation',
            'type' => fake()->randomElement(NavigationType::cases()),
            'is_active' => true,
        ];
    }

    public function header(): self
    {
        return $this->state(fn(array $attributes): array => [
            'type' => NavigationType::Header,
        ]);
    }

    public function footer(): self
    {
        return $this->state(fn(array $attributes): array => [
            'type' => NavigationType::Footer,
        ]);
    }

    public function legal(): self
    {
        return $this->state(fn(array $attributes): array => [
            'type' => NavigationType::Legal,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn(array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
