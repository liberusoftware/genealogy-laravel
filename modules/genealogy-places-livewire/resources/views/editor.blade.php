<form wire:submit="save">
    <label for="place-name">Place name</label>
    <input id="place-name" type="text" wire:model="name" required>
    <label for="place-parent">Parent place ID</label>
    <input id="place-parent" type="text" wire:model="parentId">
    <label for="place-jurisdiction">Jurisdiction</label>
    <input id="place-jurisdiction" type="text" wire:model="jurisdiction">
    <label for="place-latitude">Latitude</label>
    <input id="place-latitude" type="number" step="any" wire:model="latitude">
    <label for="place-longitude">Longitude</label>
    <input id="place-longitude" type="number" step="any" wire:model="longitude">
    @error('name') <p role="alert">{{ $message }}</p> @enderror
    <button type="submit" wire:loading.attr="disabled">Save place</button>
</form>
