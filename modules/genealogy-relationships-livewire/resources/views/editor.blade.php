<form wire:submit="save">
    <label for="genealogy-relationship-person">Person ID</label>
    <input id="genealogy-relationship-person" type="text" wire:model="personId" required>
    <label for="genealogy-relationship-related">Related person ID</label>
    <input id="genealogy-relationship-related" type="text" wire:model="relatedPersonId" required>
    <label for="genealogy-relationship-type">Relationship type</label>
    <select id="genealogy-relationship-type" wire:model="type">
        @foreach (\Liberu\Genealogy\Relationships\Models\Relationship::TYPES as $option)
            <option value="{{ $option }}">{{ ucfirst($option) }}</option>
        @endforeach
    </select>
    <label for="genealogy-relationship-confidence">Confidence</label>
    <input id="genealogy-relationship-confidence" type="number" min="0" max="100" wire:model="confidence">
    @error('relatedPersonId') <p role="alert">{{ $message }}</p> @enderror
    <button type="submit" wire:loading.attr="disabled">Save relationship</button>
</form>
