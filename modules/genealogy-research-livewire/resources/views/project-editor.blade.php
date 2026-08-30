<form wire:submit="save">
    <label for="research-project-name">Project name</label>
    <input id="research-project-name" type="text" wire:model="name" required>
    <label for="research-project-status">Status</label>
    <select id="research-project-status" wire:model="status">
        @foreach ($statuses as $option)
            <option value="{{ $option }}">{{ str_replace('_', ' ', ucfirst($option)) }}</option>
        @endforeach
    </select>
    @error('name') <p role="alert">{{ $message }}</p> @enderror
    @error('status') <p role="alert">{{ $message }}</p> @enderror
    <button type="submit" wire:loading.attr="disabled">Save project</button>
</form>
