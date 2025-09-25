<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Admin Settings</h2>
                <p class="text-gray-600">Manage your admin account details and HR signature.</p>
            </div>

            <x-filament-panels::form wire:submit="save">
                {{ $this->form }}

                <div class="mt-6 flex justify-end">
                    <x-filament::button type="submit" color="success">
                        Save Changes
                    </x-filament::button>
                </div>
            </x-filament-panels::form>
        </div>
    </div>
</x-filament-panels::page>


