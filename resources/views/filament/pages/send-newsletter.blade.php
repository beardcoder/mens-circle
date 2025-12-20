<x-filament-panels::page>
    <form wire:submit="send">
        {{ $this->form }}

        <div class="mt-6">
            @foreach($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </form>
</x-filament-panels::page>
