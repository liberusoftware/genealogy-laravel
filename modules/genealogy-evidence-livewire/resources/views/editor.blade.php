<form wire:submit="save">
    <label for="evidence-name">Name</label>
    <input id="evidence-name" type="text" wire:model="name" required>
    <label for="evidence-kind">Kind</label>
    <select id="evidence-kind" wire:model="kind">
        @foreach ($kinds as $option)
            <option value="{{ $option }}">{{ str_replace('_', ' ', ucfirst($option)) }}</option>
        @endforeach
    </select>
    <label for="evidence-assertion">Assertion</label>
    <textarea id="evidence-assertion" wire:model="assertion"></textarea>
    <label for="evidence-confidence">Confidence</label>
    <input id="evidence-confidence" type="number" min="0" max="100" wire:model="confidence">
    @error('name') <p role="alert">{{ $message }}</p> @enderror
    <button type="submit" wire:loading.attr="disabled">Save evidence</button>
</form>
