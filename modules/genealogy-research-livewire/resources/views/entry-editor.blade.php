<form wire:submit="save">
    <label for="research-project-id">Project ID</label>
    <input id="research-project-id" type="text" wire:model="projectId" required>
    <label for="research-entry-kind">Entry type</label>
    <select id="research-entry-kind" wire:model="kind">
        @foreach ($kinds as $option)
            <option value="{{ $option }}">{{ str_replace('_', ' ', ucfirst($option)) }}</option>
        @endforeach
    </select>
    <label for="research-entry-title">Title</label>
    <input id="research-entry-title" type="text" wire:model="title" required>
    <label for="research-entry-body">Details</label>
    <textarea id="research-entry-body" wire:model="body"></textarea>
    @error('title') <p role="alert">{{ $message }}</p> @enderror
    <button type="submit" wire:loading.attr="disabled">Save entry</button>
</form>
