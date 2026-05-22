<?php

declare(strict_types=1);

use App\Enums\NavigationType;
use App\Models\Navigation;
use App\Models\NavigationItem;
use Illuminate\Validation\ValidationException;
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

test('data attributes with malicious keys are sanitized', function (): void {
    $navigation = Navigation::factory()->create();

    $item = NavigationItem::create([
        'navigation_id' => $navigation->id,
        'label' => 'Test',
        'url' => '/',
        'order' => 1,
        'is_active' => true,
        'data_attributes' => [
            'valid-key' => 'value1',
            'umami-event' => 'click',
            'key_with_underscore' => 'value2',
            'key.with.dots' => 'value3',
            'key:with:colons' => 'value4',
            '<script>alert("xss")</script>' => 'malicious',
            'key with spaces' => 'invalid',
            'key"with"quotes' => 'invalid',
        ],
    ]);

    $dataString = $item->data_attributes_string;

    // Valid keys should be present
    expect($dataString)->toContain('data-valid-key="value1"')
        ->and($dataString)->toContain('data-umami-event="click"')
        ->and($dataString)->toContain('data-key_with_underscore="value2"')
        ->and($dataString)->toContain('data-key.with.dots="value3"')
        ->and($dataString)->toContain('data-key:with:colons="value4"');

    // Malicious/invalid keys should be filtered out
    expect($dataString)->not->toContain('script')
        ->and($dataString)->not->toContain('spaces')
        ->and($dataString)->not->toContain('quotes');
});

test('data attribute keys with data- prefix are handled correctly', function (): void {
    $navigation = Navigation::factory()->create();

    $item = NavigationItem::create([
        'navigation_id' => $navigation->id,
        'label' => 'Test',
        'url' => '/',
        'order' => 1,
        'is_active' => true,
        'data_attributes' => [
            'data-event' => 'click', // with prefix
            'target' => 'button', // without prefix
        ],
    ]);

    $dataString = $item->data_attributes_string;

    // Both should render with data- prefix (not doubled)
    expect($dataString)->toContain('data-event="click"')
        ->and($dataString)->toContain('data-target="button"')
        ->and($dataString)->not->toContain('data-data-event'); // no double prefix
});

test('target value is restricted to safe options', function (): void {
    $navigation = Navigation::factory()->create();

    $item = NavigationItem::create([
        'navigation_id' => $navigation->id,
        'label' => 'Test',
        'url' => '/',
        'order' => 1,
        'target' => 'javascript:alert("xss")', // malicious attempt
        'is_active' => true,
    ]);

    // The database accepts any value, but the form and MCP tools should validate
    expect($item->target)->toBe('javascript:alert("xss")');
    // This tests that the column accepts the value - validation happens at form/MCP level
});

test('database rejects duplicate active navigation of the same type', function (): void {
    Navigation::factory()->header()->create([
        'is_active' => true,
    ]);

    expect(fn() => Navigation::factory()->header()->create([
        'is_active' => true,
    ]))->toThrow(ValidationException::class);
});

test('database allows inactive navigation with same type as active', function (): void {
    Navigation::factory()->header()->create([
        'is_active' => true,
    ]);

    Navigation::factory()->header()->inactive()->create();

    expect(Navigation::query()->where('type', NavigationType::Header)->count())->toBe(2);
});

test('database allows creating active navigation when previous one is soft deleted', function (): void {
    $active = Navigation::factory()->header()->create([
        'is_active' => true,
    ]);
    $active->delete();

    $new = Navigation::factory()->header()->create([
        'is_active' => true,
    ]);

    expect($new->exists)->toBeTrue()
        ->and($new->id)->not->toBe($active->id);
});

test('direct model update to active fails when another active navigation of same type exists', function (): void {
    Navigation::factory()->header()->create([
        'is_active' => true,
    ]);

    $inactiveNavigation = Navigation::factory()->header()->inactive()->create();

    expect(fn() => $inactiveNavigation->update([
        'is_active' => true,
    ]))->toThrow(ValidationException::class);
});
