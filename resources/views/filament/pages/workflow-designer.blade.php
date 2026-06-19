<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit" size="lg" icon="heroicon-m-check-circle">
                Save as new version
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
