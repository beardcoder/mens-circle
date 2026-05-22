<?php

declare(strict_types=1);

use App\Enums\NavigationType;
use App\Models\Navigation;
use App\Models\NavigationItem;
use Database\Seeders\NavigationSeeder;

test('navigation seeder is idempotent', function (): void {
    $seeder = new NavigationSeeder();
    $seeder->run();
    $seeder->run();

    expect(Navigation::query()->where('type', NavigationType::Header)->count())->toBe(1)
        ->and(Navigation::query()->where('type', NavigationType::Footer)->count())->toBe(1)
        ->and(Navigation::query()->where('type', NavigationType::Legal)->count())->toBe(1)
        ->and(NavigationItem::query()->count())->toBe(11);

    $headerNavigation = Navigation::query()->where('type', NavigationType::Header)->firstOrFail();
    $headerItems = $headerNavigation->items()->get();

    expect($headerItems->pluck('label')->all())->toBe(['Über', 'Die Reise', 'Fragen', 'Atemübung'])
        ->and($headerItems->pluck('order')->all())->toBe([1, 2, 3, 4])
        ->and($headerItems->firstWhere('label', 'Über')?->data_attributes)->toBe([
            'umami-event' => 'nav-click',
            'umami-event-target' => 'ueber',
        ]);
});
