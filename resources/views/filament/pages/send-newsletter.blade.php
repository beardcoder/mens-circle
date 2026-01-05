<x-filament-panels::page>
    <div>
        {{ $this->form }}

        <div class="mt-6">
            <x-filament-panels::form.actions :actions="$this->getFormActions()" />
        </div>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
