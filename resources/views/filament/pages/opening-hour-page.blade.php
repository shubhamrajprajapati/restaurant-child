<x-filament-panels::page>

    <x-filament-panels::form wire:submit="save" id="opening_hour">
        {{ $this->form }}

        <x-filament-panels::form.actions :actions="$this->getFormActions()" />
    </x-filament-panels::form>

</x-filament-panels::page>