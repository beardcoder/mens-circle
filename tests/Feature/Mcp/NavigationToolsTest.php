<?php

declare(strict_types=1);

use App\Mcp\Tools\CreateNavigationItem;
use App\Mcp\Tools\GetNavigation;
use App\Mcp\Tools\ReorderNavigationItems;
use App\Mcp\Tools\UpdateNavigation;
use App\Models\Navigation;
use App\Models\NavigationItem;
use Illuminate\Validation\ValidationException;

test('CreateNavigationItem rejects parent from different navigation', function (): void {
    $nav1 = Navigation::factory()->create(['name' => 'Navigation 1']);
    $nav2 = Navigation::factory()->create(['name' => 'Navigation 2']);

    $parentInNav2 = NavigationItem::factory()->forNavigation($nav2)->create([
        'label' => 'Parent in Nav 2',
    ]);

    $tool = new CreateNavigationItem();
    expect(fn() => $tool([
        'navigation_id' => $nav1->id,
        'label' => 'Child trying to use wrong parent',
        'parent_id' => $parentInNav2->id,
    ]))->toThrow(ValidationException::class);
});

test('CreateNavigationItem rejects non-existent parent', function (): void {
    $navigation = Navigation::factory()->create();

    $tool = new CreateNavigationItem();
    expect(fn() => $tool([
        'navigation_id' => $navigation->id,
        'label' => 'Child with fake parent',
        'parent_id' => '00000000-0000-0000-0000-000000000000',
    ]))->toThrow(ValidationException::class);
});

test('CreateNavigationItem validates target value', function (): void {
    $navigation = Navigation::factory()->create();

    $tool = new CreateNavigationItem();
    expect(fn() => $tool([
        'navigation_id' => $navigation->id,
        'label' => 'Test Item',
        'url' => '/test',
        'target' => 'javascript:alert("xss")',
    ]))->toThrow(ValidationException::class);
});

test('ReorderNavigationItems rejects missing item IDs', function (): void {
    $navigation = Navigation::factory()->create();
    $item1 = NavigationItem::factory()->forNavigation($navigation)->create();

    $tool = new ReorderNavigationItems();
    expect(fn() => $tool([
        'navigation_id' => $navigation->id,
        'item_ids' => [
            $item1->id,
            '00000000-0000-0000-0000-000000000000',
        ],
    ]))->toThrow(ValidationException::class);
});

test('ReorderNavigationItems rejects items from different navigation', function (): void {
    $nav1 = Navigation::factory()->create();
    $nav2 = Navigation::factory()->create();

    $item1 = NavigationItem::factory()->forNavigation($nav1)->create();
    $item2 = NavigationItem::factory()->forNavigation($nav2)->create();

    $tool = new ReorderNavigationItems();
    expect(fn() => $tool([
        'navigation_id' => $nav1->id,
        'item_ids' => [$item1->id, $item2->id],
    ]))->toThrow(ValidationException::class);
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
    expect(fn() => $tool([
        'navigation_id' => $navigation->id,
        'items' => [
            [
                'label' => 'Test Item',
                'url' => '/test',
                'target' => 'javascript:void(0)',
            ],
        ],
    ]))->toThrow(ValidationException::class);
});

test('get-navigation output can be passed to update-navigation and preserves nesting', function (): void {
    $navigation = Navigation::factory()->create();
    $parent = NavigationItem::factory()->forNavigation($navigation)->create([
        'label' => 'Parent',
        'parent_id' => null,
    ]);
    NavigationItem::factory()->forNavigation($navigation)->create([
        'label' => 'Child',
        'parent_id' => $parent->id,
    ]);

    $getTool = new GetNavigation();
    $payload = $getTool(['navigation_id' => $navigation->id]);

    $updateTool = new UpdateNavigation();
    $result = $updateTool([
        'navigation_id' => $navigation->id,
        'items' => $payload['items'],
    ]);

    expect($result['success'])->toBeTrue();

    $navigation->refresh();
    $items = $navigation->items;
    $newParent = $items->firstWhere('label', 'Parent');
    $newChild = $items->firstWhere('label', 'Child');

    expect($newParent)->not->toBeNull()
        ->and($newChild)->not->toBeNull()
        ->and($newChild->parent_id)->toBe($newParent->id);
});

test('UpdateNavigation throws for invalid parent reference and rolls back changes', function (): void {
    $navigation = Navigation::factory()->create();
    $existingItem = NavigationItem::factory()->forNavigation($navigation)->create([
        'label' => 'Existing',
    ]);

    $tool = new UpdateNavigation();

    expect(fn() => $tool([
        'navigation_id' => $navigation->id,
        'items' => [
            [
                'id' => $existingItem->id,
                'label' => 'Updated Existing',
            ],
            [
                'label' => 'Broken Child',
                'parent_id' => '00000000-0000-0000-0000-000000000000',
            ],
        ],
    ]))->toThrow(ValidationException::class);

    $navigation->refresh();
    expect($navigation->items()->count())->toBe(1)
        ->and($navigation->items()->first()->label)->toBe('Existing');
});

test('UpdateNavigation throws for unknown parent_temp_id', function (): void {
    $navigation = Navigation::factory()->create();

    $tool = new UpdateNavigation();

    expect(fn() => $tool([
        'navigation_id' => $navigation->id,
        'items' => [
            [
                'temp_id' => 'item-1',
                'label' => 'Item 1',
            ],
            [
                'temp_id' => 'item-2',
                'label' => 'Item 2',
                'parent_temp_id' => 'missing-parent',
            ],
        ],
    ]))->toThrow(ValidationException::class);
});

test('UpdateNavigation prefers parent_temp_id over parent_id when both are provided', function (): void {
    $navigation = Navigation::factory()->create();

    $tool = new UpdateNavigation();
    $result = $tool([
        'navigation_id' => $navigation->id,
        'items' => [
            [
                'temp_id' => 'parent-temp',
                'label' => 'Parent Item',
                'url' => '/parent',
            ],
            [
                'label' => 'Child Item',
                'parent_id' => '00000000-0000-0000-0000-000000000000',
                'parent_temp_id' => 'parent-temp',
                'url' => '/child',
            ],
        ],
    ]);

    expect($result['success'])->toBeTrue();

    $navigation->refresh();
    $items = $navigation->items;
    $parent = $items->firstWhere('label', 'Parent Item');
    $child = $items->firstWhere('label', 'Child Item');

    expect($child->parent_id)->toBe($parent->id);
});
