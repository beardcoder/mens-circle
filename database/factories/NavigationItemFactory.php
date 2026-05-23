<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NavigationLocation;
use App\Models\NavigationItem;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<NavigationItem>
 */
#[UseModel(NavigationItem::class)]
class NavigationItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function definition(): array
    {
        $faker = FakerFactory::create();

        return [
            'location' => NavigationLocation::Header,
            'label' => $faker->words(2, true),
            'url' => '/' . $faker->slug(),
            'anchor' => null,
            'condition' => null,
            'open_in_new_tab' => false,
            'is_cta' => false,
            'is_visible' => true,
            'umami_event_target' => null,
            'sort' => 0,
        ];
    }

    public function header(): static
    {
        return $this->state(static fn(): array => ['location' => NavigationLocation::Header]);
    }

    public function footerPrimary(): static
    {
        return $this->state(static fn(): array => ['location' => NavigationLocation::FooterPrimary]);
    }

    public function footerContact(): static
    {
        return $this->state(static fn(): array => ['location' => NavigationLocation::FooterContact]);
    }

    public function footerLegal(): static
    {
        return $this->state(static fn(): array => ['location' => NavigationLocation::FooterLegal]);
    }

    public function cta(): static
    {
        return $this->state(static fn(): array => ['is_cta' => true]);
    }

    public function hidden(): static
    {
        return $this->state(static fn(): array => ['is_visible' => false]);
    }
}
