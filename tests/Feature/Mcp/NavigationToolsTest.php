<?php

declare(strict_types=1);

use App\Enums\NavigationType;
use App\Mcp\Tools\CreateNavigationItem;
use App\Mcp\Tools\ReorderNavigationItems;
use App\Mcp\Tools\UpdateNavigation;
use App\Models\Navigation;
use App\Models\NavigationItem;

test('CreateNavigationItem rejects parent from different navigation', function (): void {
    $nav1 = Navigation::factory()->create(['name' => 'Navigation 1']);
    $nav2 = Navigation::factory()->create(['name' => 'Navigation 2']);

    $parentInNav2 = NavigationItem::factory()->forNavigation($nav2)->create([
        'label' => 'Parent in Nav 2',
    ]);

    $tool = new CreateNavigationItem();
    $result = $tool([
        'navigation_id' => $nav1->id,
        'label' => 'Child trying to use wrong parent',
        'parent_id' => $parentInNav2->id,
    ]);

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toContain('different navigation');
});

test('CreateNavigationItem rejects non-existent parent', function (): void {
    $navigation = Navigation::factory()->create();

    $tool = new CreateNavigationItem();
    $result = $tool([
        'navigation_id' => $navigation->id,
        'label' => 'Child with fake parent',
        'parent_id' => '00000000-0000-0000-0000-000000000000', // Non-existent UUID
    ]);

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toContain('not found');
});

test('CreateNavigationItem validates target value', function (): void {
    $navigation = Navigation::factory()->create();

    $tool = new CreateNavigationItem();
    $result = $tool([
        'navigation_id' => $navigation->id,
        'label' => 'Test Item',
        'url' => '/test',
        'target' => 'javascript:alert("xss")', // Invalid target
    ]);

    expect($result['success'])->toBeTrue(); // Should succeed but sanitize target

    $item = NavigationItem::find($result['item']['id']);
    expect($item->target)->toBe('_self'); // Should default to _self
});

test('ReorderNavigationItems rejects missing item IDs', function (): void {
    $navigation = Navigation::factory()->create();
    $item1 = NavigationItem::factory()->forNavigation($navigation)->create();

    $tool = new ReorderNavigationItems();
    $result = $tool([
        'navigation_id' => $navigation->id,
        'item_ids' => [
            $item1->id,
            '00000000-0000-0000-0000-000000000000', // Non-existent
        ],
    ]);

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toContain('not found');
});

test('ReorderNavigationItems rejects items from different navigation', function (): void {
    $nav1 = Navigation::factory()->create();
    $nav2 = Navigation::factory()->create();

    $item1 = NavigationItem::factory()->forNavigation($nav1)->create();
    $item2 = NavigationItem::factory()->forNavigation($nav2)->create();

    $tool = new ReorderNavigationItems();
    $result = $tool([
        'navigation_id' => $nav1->id,
        'item_ids' => [$item1->id, $item2->id],
    ]);

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toContain('different navigation');
});

test('UpdateNavigation preserves nested parent-child relationships with temp IDs', function (): void {
    $navigation = Navigation::factory()->create();

    $tool = new UpdateNavigation();
    $result = $tool([
        'navigation_id' => $navigation->id,
        'items' => [
            [
                'temp_id' => 'parent-1',
                'label' => 'Parent Item',
                'url' => '/parent',
            ],
            [
                'temp_id' => 'child-1',
                'parent_temp_id' => 'parent-1',
                'label' => 'Child Item',
                'url' => '/child',
            ],
        ],
    ]);

    expect($result['success'])->toBeTrue()
        ->and($result['items_count'])->toBe(2);

    $navigation->refresh();
    $items = $navigation->items;

    $parent = $items->firstWhere('label', 'Parent Item');
    $child = $items->firstWhere('label', 'Child Item');

    expect($parent)->not->toBeNull()
        ->and($child)->not->toBeNull()
        ->and($child->parent_id)->toBe($parent->id)
        ->and($child->parent_id)->not->toBeNull(); // Should not be null or stale
});

test('UpdateNavigation handles multiple levels of nesting', function (): void {
    $navigation = Navigation::factory()->create();

    $tool = new UpdateNavigation();
    $result = $tool([
        'navigation_id' => $navigation->id,
        'items' => [
            [
                'temp_id' => 'level-1',
                'label' => 'Level 1',
                'url' => '/l1',
            ],
            [
                'temp_id' => 'level-2',
                'parent_temp_id' => 'level-1',
                'label' => 'Level 2',
                'url' => '/l2',
            ],
            [
                'temp_id' => 'level-3',
                'parent_temp_id' => 'level-2',
                'label' => 'Level 3',
                'url' => '/l3',
            ],
        ],
    ]);

    expect($result['success'])->toBeTrue();

    $navigation->refresh();
    $items = $navigation->items;

    $level1 = $items->firstWhere('label', 'Level 1');
    $level2 = $items->firstWhere('label', 'Level 2');
    $level3 = $items->firstWhere('label', 'Level 3');

    expect($level1->parent_id)->toBeNull()
        ->and($level2->parent_id)->toBe($level1->id)
        ->and($level3->parent_id)->toBe($level2->id);
});

test('UpdateNavigation validates target values', function (): void {
    $navigation = Navigation::factory()->create();

    $tool = new UpdateNavigation();
    $result = $tool([
        'navigation_id' => $navigation->id,
        'items' => [
            [
                'label' => 'Test Item',
                'url' => '/test',
                'target' => 'javascript:void(0)', // Invalid
            ],
        ],
    ]);

    expect($result['success'])->toBeTrue();

    $navigation->refresh();
    $item = $navigation->items->first();
    expect($item->target)->toBe('_self'); // Should be sanitized
});
