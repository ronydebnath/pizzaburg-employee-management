<x-filament-panels::page>
    <form wire:submit.prevent="save" class="space-y-6">
        {{ $this->form }}

        <div>
            <x-filament::button type="submit" icon="heroicon-o-check">
                Save Changes
            </x-filiment::button>
        </div>
    </form>
</x-filament-panels::page>
