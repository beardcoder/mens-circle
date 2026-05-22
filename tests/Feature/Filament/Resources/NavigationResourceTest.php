<?php

declare(strict_types=1);

use App\Enums\NavigationType;
use App\Filament\Resources\NavigationResource;
use App\Filament\Resources\NavigationResource\Pages\CreateNavigation;
use App\Filament\Resources\NavigationResource\Pages\EditNavigation;
use App\Filament\Resources\NavigationResource\Pages\ListNavigations;
use App\Models\Navigation;
use App\Models\User;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

test('can render navigation list page', function (): void {
    livewire(ListNavigations::class)
        ->assertSuccessful();
});

test('can list navigations', function (): void {
    $navigations = Navigation::factory()->count(3)->create();

    livewire(ListNavigations::class)
        ->assertCanSeeTableRecords($navigations);
});

test('can create navigation', function (): void {
    livewire(CreateNavigation::class)
        ->fillForm([
            'name' => 'Test Navigation',
            'type' => NavigationType::Header->value,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    assertDatabaseHas(Navigation::class, [
        'name' => 'Test Navigation',
        'type' => NavigationType::Header->value,
        'is_active' => true,
    ]);
});

test('can create navigation with items', function (): void {
    livewire(CreateNavigation::class)
        ->fillForm([
            'name' => 'Test Navigation',
            'type' => NavigationType::Header->value,
            'is_active' => true,
            'items' => [
                [
                    'label' => 'Home',
                    'route_name' => 'home',
                    'is_active' => true,
                ],
                [
                    'label' => 'About',
                    'route_name' => 'home',
                    'anchor' => 'ueber',
                    'is_active' => true,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $navigation = Navigation::where('name', 'Test Navigation')->first();
    expect($navigation)->not->toBeNull()
        ->and($navigation->items)->toHaveCount(2);
});

test('can edit navigation', function (): void {
    $navigation = Navigation::factory()->create([
        'name' => 'Original Name',
    ]);

    livewire(EditNavigation::class, ['record' => $navigation->id])
        ->fillForm([
            'name' => 'Updated Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $navigation->refresh();
    expect($navigation->name)->toBe('Updated Name');
});

test('can delete navigation', function (): void {
    $navigation = Navigation::factory()->create();

    livewire(EditNavigation::class, ['record' => $navigation->id])
        ->callAction('delete');

    expect(Navigation::count())->toBe(0);
});

test('can validate required fields', function (): void {
    livewire(CreateNavigation::class)
        ->fillForm([
            'name' => null,
            'type' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'type' => 'required',
        ]);
});

test('can add navigation items in form', function (): void {
    $navigation = Navigation::factory()->create();

    livewire(EditNavigation::class, ['record' => $navigation->id])
        ->fillForm([
            'items' => [
                [
                    'label' => 'New Item',
                    'url' => '/new-item',
                    'is_active' => true,
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $navigation->refresh();
    expect($navigation->items)->toHaveCount(1)
        ->and($navigation->items->first()->label)->toBe('New Item');
});

test('can edit navigation item with anchor', function (): void {
    $navigation = Navigation::factory()->create();

    livewire(EditNavigation::class, ['record' => $navigation->id])
        ->fillForm([
            'items' => [
                [
                    'label' => 'FAQ',
                    'route_name' => 'home',
                    'anchor' => 'faq',
                    'data_attributes' => [
                        'umami-event' => 'nav-click',
                        'umami-event-target' => 'faq',
                    ],
                    'is_active' => true,
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $navigation->refresh();
    $item = $navigation->items->first();

    expect($item->anchor)->toBe('faq')
        ->and($item->computed_url)->toContain('#faq')
        ->and($item->data_attributes)->toBe([
            'umami-event' => 'nav-click',
            'umami-event-target' => 'faq',
        ]);
});

test('can filter navigations by type', function (): void {
    Navigation::factory()->create(['type' => NavigationType::Header]);
    Navigation::factory()->create(['type' => NavigationType::Footer]);
    Navigation::factory()->create(['type' => NavigationType::Legal]);

    livewire(ListNavigations::class)
        ->filterTable('type', NavigationType::Header->value)
        ->assertCountTableRecords(1);
});

test('can search navigations by name', function (): void {
    Navigation::factory()->create(['name' => 'Main Navigation']);
    Navigation::factory()->create(['name' => 'Footer Links']);

    livewire(ListNavigations::class)
        ->searchTable('Main')
        ->assertCanSeeTableRecords(Navigation::where('name', 'Main Navigation')->get())
        ->assertCanNotSeeTableRecords(Navigation::where('name', 'Footer Links')->get());
});

test('cannot create duplicate active navigation of same type', function (): void {
    $existing = Navigation::factory()->create([
        'name' => 'Existing Header',
        'type' => NavigationType::Header,
        'is_active' => true,
    ]);

    livewire(CreateNavigation::class)
        ->fillForm([
            'name' => 'New Header',
            'type' => NavigationType::Header->value,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['type']);
});

test('can create navigation of same type if existing is inactive', function (): void {
    $existing = Navigation::factory()->create([
        'name' => 'Inactive Header',
        'type' => NavigationType::Header,
        'is_active' => false,
    ]);

    livewire(CreateNavigation::class)
        ->fillForm([
            'name' => 'New Active Header',
            'type' => NavigationType::Header->value,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    assertDatabaseHas(Navigation::class, [
        'name' => 'New Active Header',
        'type' => NavigationType::Header->value,
        'is_active' => true,
    ]);
});

test('can create navigation of same type if existing is soft deleted', function (): void {
    $existing = Navigation::factory()->create([
        'name' => 'Deleted Header',
        'type' => NavigationType::Header,
        'is_active' => true,
    ]);
    $existing->delete(); // Soft delete

    livewire(CreateNavigation::class)
        ->fillForm([
            'name' => 'New Header',
            'type' => NavigationType::Header->value,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    assertDatabaseHas(Navigation::class, [
        'name' => 'New Header',
        'type' => NavigationType::Header->value,
        'is_active' => true,
        'deleted_at' => null,
    ]);
});

test('can edit navigation to different type if no conflict', function (): void {
    $navigation = Navigation::factory()->create([
        'name' => 'Header Nav',
        'type' => NavigationType::Header,
    ]);

    livewire(EditNavigation::class, ['record' => $navigation->id])
        ->fillForm([
            'type' => NavigationType::Footer->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $navigation->refresh();
    expect($navigation->type)->toBe(NavigationType::Footer);
});

test('cannot edit navigation to type that already has active navigation', function (): void {
    $footer = Navigation::factory()->create([
        'name' => 'Existing Footer',
        'type' => NavigationType::Footer,
        'is_active' => true,
    ]);

    $header = Navigation::factory()->create([
        'name' => 'Header Nav',
        'type' => NavigationType::Header,
        'is_active' => true,
    ]);

    livewire(EditNavigation::class, ['record' => $header->id])
        ->fillForm([
            'type' => NavigationType::Footer->value,
        ])
        ->call('save')
        ->assertHasFormErrors(['type']);
});
