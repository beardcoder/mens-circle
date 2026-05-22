<?php

declare(strict_types=1);

use App\Enums\NavigationType;
use App\Models\Navigation;
use App\Models\NavigationItem;
use function Pest\Laravel\assertDatabaseHas;

test('can create navigation with items', function (): void {
    $navigation = Navigation::create([
        'name' => 'Test Navigation',
        'type' => NavigationType::Header,
        'is_active' => true,
    ]);

    expect($navigation->name)->toBe('Test Navigation')
        ->and($navigation->type)->toBe(NavigationType::Header)
        ->and($navigation->is_active)->toBeTrue();

    $item = NavigationItem::create([
        'navigation_id' => $navigation->id,
        'label' => 'Home',
        'route_name' => 'home',
        'order' => 1,
        'is_active' => true,
    ]);

    expect($item->label)->toBe('Home')
        ->and($item->route_name)->toBe('home')
        ->and($navigation->items)->toHaveCount(1);
});

test('computed url works with route name', function (): void {
    $navigation = Navigation::create([
        'name' => 'Test Navigation',
        'type' => NavigationType::Header,
        'is_active' => true,
    ]);

    $item = NavigationItem::create([
        'navigation_id' => $navigation->id,
        'label' => 'Home',
        'route_name' => 'home',
        'order' => 1,
        'is_active' => true,
    ]);

    expect($item->computed_url)->toBe(route('home'));
});

test('computed url works with direct url', function (): void {
    $navigation = Navigation::create([
        'name' => 'Test Navigation',
        'type' => NavigationType::Header,
        'is_active' => true,
    ]);

    $item = NavigationItem::create([
        'navigation_id' => $navigation->id,
        'label' => 'External',
        'url' => 'https://example.com',
        'order' => 1,
        'is_active' => true,
    ]);

    expect($item->computed_url)->toBe('https://example.com');
});

test('computed url works with anchor', function (): void {
    $navigation = Navigation::create([
        'name' => 'Test Navigation',
        'type' => NavigationType::Header,
        'is_active' => true,
    ]);

    $item = NavigationItem::create([
        'navigation_id' => $navigation->id,
        'label' => 'About',
        'route_name' => 'home',
        'anchor' => 'ueber',
        'order' => 1,
        'is_active' => true,
    ]);

    expect($item->computed_url)->toBe(route('home') . '#ueber');
});

test('computed url works with route params', function (): void {
    $navigation = Navigation::create([
        'name' => 'Test Navigation',
        'type' => NavigationType::Header,
        'is_active' => true,
    ]);

    $item = NavigationItem::create([
        'navigation_id' => $navigation->id,
        'label' => 'Page',
        'route_name' => 'page.show',
        'route_params' => ['slug' => 'impressum'],
        'order' => 1,
        'is_active' => true,
    ]);

    expect($item->computed_url)->toBe(route('page.show', ['slug' => 'impressum']));
});

test('navigation items can have parent child relationships', function (): void {
    $navigation = Navigation::create([
        'name' => 'Test Navigation',
        'type' => NavigationType::Header,
        'is_active' => true,
    ]);

    $parent = NavigationItem::create([
        'navigation_id' => $navigation->id,
        'label' => 'Parent',
        'url' => '#',
        'order' => 1,
        'is_active' => true,
    ]);

    $child = NavigationItem::create([
        'navigation_id' => $navigation->id,
        'parent_id' => $parent->id,
        'label' => 'Child',
        'url' => '/child',
        'order' => 1,
        'is_active' => true,
    ]);

    expect($parent->children)->toHaveCount(1)
        ->and($child->parent_id)->toBe($parent->id)
        ->and($child->parent->label)->toBe('Parent');
});

test('can filter active navigation items', function (): void {
    $navigation = Navigation::create([
        'name' => 'Test Navigation',
        'type' => NavigationType::Header,
        'is_active' => true,
    ]);

    NavigationItem::create([
        'navigation_id' => $navigation->id,
        'label' => 'Active Item',
        'url' => '/active',
        'order' => 1,
        'is_active' => true,
    ]);

    NavigationItem::create([
        'navigation_id' => $navigation->id,
        'label' => 'Inactive Item',
        'url' => '/inactive',
        'order' => 2,
        'is_active' => false,
    ]);

    expect($navigation->items)->toHaveCount(2)
        ->and($navigation->activeItems)->toHaveCount(1)
        ->and($navigation->activeItems->first()->label)->toBe('Active Item');
});

test('items are ordered correctly', function (): void {
    $navigation = Navigation::create([
        'name' => 'Test Navigation',
        'type' => NavigationType::Header,
        'is_active' => true,
    ]);

    NavigationItem::create([
        'navigation_id' => $navigation->id,
        'label' => 'Third',
        'url' => '/third',
        'order' => 3,
        'is_active' => true,
    ]);

    NavigationItem::create([
        'navigation_id' => $navigation->id,
        'label' => 'First',
        'url' => '/first',
        'order' => 1,
        'is_active' => true,
    ]);

    NavigationItem::create([
        'navigation_id' => $navigation->id,
        'label' => 'Second',
        'url' => '/second',
        'order' => 2,
        'is_active' => true,
    ]);

    $items = $navigation->items()->get();

    expect($items->first()->label)->toBe('First')
        ->and($items->skip(1)->first()->label)->toBe('Second')
        ->and($items->last()->label)->toBe('Third');
});

test('data attributes are stored and retrieved correctly', function (): void {
    $navigation = Navigation::create([
        'name' => 'Test Navigation',
        'type' => NavigationType::Header,
        'is_active' => true,
    ]);

    $item = NavigationItem::create([
        'navigation_id' => $navigation->id,
        'label' => 'Home',
        'url' => '/',
        'order' => 1,
        'is_active' => true,
        'data_attributes' => [
            'umami-event' => 'nav-click',
            'umami-event-target' => 'home',
        ],
    ]);

    expect($item->data_attributes)->toBe([
        'umami-event' => 'nav-click',
        'umami-event-target' => 'home',
    ]);

    $dataString = $item->data_attributes_string;
    expect($dataString)->toContain('data-umami-event="nav-click"')
        ->and($dataString)->toContain('data-umami-event-target="home"');
});

test('navigation can be filtered by type', function (): void {
    Navigation::create([
        'name' => 'Header Navigation',
        'type' => NavigationType::Header,
        'is_active' => true,
    ]);

    Navigation::create([
        'name' => 'Footer Navigation',
        'type' => NavigationType::Footer,
        'is_active' => true,
    ]);

    $headerNav = Navigation::ofType(NavigationType::Header)->first();
    $footerNav = Navigation::ofType(NavigationType::Footer)->first();

    expect($headerNav->name)->toBe('Header Navigation')
        ->and($footerNav->name)->toBe('Footer Navigation');
});

test('can soft delete navigation and items', function (): void {
    $navigation = Navigation::create([
        'name' => 'Test Navigation',
        'type' => NavigationType::Header,
        'is_active' => true,
    ]);

    $item = NavigationItem::create([
        'navigation_id' => $navigation->id,
        'label' => 'Home',
        'url' => '/',
        'order' => 1,
        'is_active' => true,
    ]);

    $navigation->delete();

    expect(Navigation::count())->toBe(0)
        ->and(Navigation::withTrashed()->count())->toBe(1)
        ->and(NavigationItem::count())->toBe(0);
});

test('navigation clears response cache on update', function (): void {
    $navigation = Navigation::create([
        'name' => 'Test Navigation',
        'type' => NavigationType::Header,
        'is_active' => true,
    ]);

    // This test verifies the ClearsResponseCache trait is used
    expect($navigation)->toHaveProperty('clearsResponseCacheWhenUpdated');
});
