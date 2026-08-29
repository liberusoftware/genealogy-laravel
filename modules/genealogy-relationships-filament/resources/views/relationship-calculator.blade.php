<x-filament-panels::page>
    <form wire:submit="calculate" class="space-y-4">
        <label for="filament-relationship-first">First person ID</label>
        <input id="filament-relationship-first" type="text" wire:model="firstPersonId" required>
        <label for="filament-relationship-second">Second person ID</label>
        <input id="filament-relationship-second" type="text" wire:model="secondPersonId" required>
        @error('secondPersonId') <p role="alert">{{ $message }}</p> @enderror
        <button type="submit" wire:loading.attr="disabled">Calculate relationship</button>
    </form>
    @if ($result)
        <p role="status">Relationship: {{ $result['relationship'] }}</p>
    @endif
</x-filament-panels::page>
