<x-filament-panels::page>
    <x-filament-panels::form wire:submit="sendNewsletterAction">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    <x-filament-actions::modals />
</x-filament-panels::page>
