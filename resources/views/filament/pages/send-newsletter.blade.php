<x-filament-panels::page>
    <div>
        {{ $this->form }}

        <div class="mt-6">
            @foreach($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </div>
    
    <x-filament-actions::modals />
</x-filament-panels::page>
