<div>
    <form wire:submit="calculate">
        <label for="genealogy-relationship-first">First person ID</label>
        <input id="genealogy-relationship-first" type="text" wire:model="firstPersonId" required>
        <label for="genealogy-relationship-second">Second person ID</label>
        <input id="genealogy-relationship-second" type="text" wire:model="secondPersonId" required>
        @error('secondPersonId') <p role="alert">{{ $message }}</p> @enderror
        <button type="submit" wire:loading.attr="disabled">Calculate relationship</button>
    </form>
    @if ($result)
        <p role="status">Relationship: {{ $result['relationship'] }}</p>
    @endif
</div>
