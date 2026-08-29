<div>
    <form wire:submit="save" aria-label="Save DNA provider">
        <label for="genealogy-dna-provider-name">Name</label>
        <input id="genealogy-dna-provider-name" type="text" wire:model="name" required>
        <label for="genealogy-dna-provider-slug">Slug</label>
        <input id="genealogy-dna-provider-slug" type="text" wire:model="slug">
        <label for="genealogy-dna-provider-status">Status</label>
        <select id="genealogy-dna-provider-status" wire:model="providerStatus">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        <label for="genealogy-dna-provider-website">Website</label>
        <input id="genealogy-dna-provider-website" type="url" wire:model="website">
        @error('name') <p role="alert">{{ $message }}</p> @enderror
        @error('slug') <p role="alert">{{ $message }}</p> @enderror
        @error('providerStatus') <p role="alert">{{ $message }}</p> @enderror
        @error('website') <p role="alert">{{ $message }}</p> @enderror
        <button type="submit" wire:loading.attr="disabled">{{ $editingId === null ? 'Add provider' : 'Save provider' }}</button>
    </form>
    <label for="genealogy-dna-provider-search">Search providers</label>
    <input id="genealogy-dna-provider-search" type="search" wire:model.live="search">
    <label for="genealogy-dna-provider-filter-status">Filter status</label>
    <select id="genealogy-dna-provider-filter-status" wire:model.live="status">
        <option value="">All</option><option value="active">Active</option><option value="inactive">Inactive</option>
    </select>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-dna-provider-{{ $record->id }}">
                {{ $record->name }} ({{ $record->status }}, {{ $record->kits_count }} kits)
                <button type="button" wire:click="edit('{{ $record->id }}')">Edit</button>
                <button type="button" wire:click="remove('{{ $record->id }}')">Delete</button>
            </li>
        @endforeach
    </ul>
</div>
