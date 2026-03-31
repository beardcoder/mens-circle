<?php

declare(strict_types=1);

use App\Filament\Resources\EventResource\Pages\EditEvent;
use App\Filament\Resources\EventResource\Pages\ListEvents;
use App\Models\Event;
use App\Models\User;
use Filament\Actions\ReplicateAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

it('can replicate an event from the edit page with a new date', function (): void {
    $event = Event::factory()->published()->create([
        'title' => 'Männerkreis Straubing',
        'location' => 'Gemeindehaus',
        'max_participants' => 10,
        'cost_basis' => 'Auf Spendenbasis',
    ]);

    $newDate = now()->addMonths(2)->startOfDay();

    Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
        ->callAction(ReplicateAction::class, data: [
            'event_date' => $newDate,
        ])
        ->assertNotified();

    $replica = Event::query()->where('id', '!=', $event->id)->latest('id')->first();

    expect($replica)
        ->not->toBeNull()
        ->title->toBe('Männerkreis Straubing')
        ->location->toBe('Gemeindehaus')
        ->max_participants->toBe(10)
        ->cost_basis->toBe('Auf Spendenbasis')
        ->is_published->toBeFalse()
        ->event_date->toDateString()->toBe($newDate->toDateString());
});

it('can replicate an event from the table', function (): void {
    $event = Event::factory()->published()->create([
        'title' => 'Männerkreis Straubing',
        'location' => 'Gemeindehaus',
    ]);

    $newDate = now()->addMonths(3)->startOfDay();

    Livewire::test(ListEvents::class)
        ->callAction(TestAction::make('replicate')->table($event), data: [
            'event_date' => $newDate,
        ])
        ->assertNotified();

    expect(Event::count())->toBe(2);

    $replica = Event::query()->where('id', '!=', $event->id)->first();

    expect($replica)
        ->title->toBe('Männerkreis Straubing')
        ->is_published->toBeFalse()
        ->event_date->toDateString()->toBe($newDate->toDateString());
});

it('sets the replicated event as unpublished', function (): void {
    $event = Event::factory()->published()->create();

    $newDate = now()->addMonth()->startOfDay();

    Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
        ->callAction(ReplicateAction::class, data: [
            'event_date' => $newDate,
        ]);

    $replica = Event::query()->where('id', '!=', $event->id)->first();

    expect($replica->is_published)->toBeFalse();
});
