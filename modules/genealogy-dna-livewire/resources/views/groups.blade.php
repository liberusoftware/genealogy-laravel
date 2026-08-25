<div>
    <label for="genealogy-dna-group-search">Search groups</label>
    <input id="genealogy-dna-group-search" type="search" wire:model.live="search">
    <label for="genealogy-dna-group-status">Status</label>
    <select id="genealogy-dna-group-status" wire:model.live="status">
        <option value="">All</option>
        <option value="draft">Draft</option>
        <option value="active">Active</option>
        <option value="archived">Archived</option>
    </select>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-dna-group-{{ $record->id }}">
                {{ $record->name }} ({{ $record->matches_count }} matches)
            </li>
        @endforeach
    </ul>
</div>
