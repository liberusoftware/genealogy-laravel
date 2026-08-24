<div>
    <label for="genealogy-relationships-list-status">Status</label>
    <select id="genealogy-relationships-list-status" wire:model.live="status">
        <option value="">All</option>
        <option value="draft">Draft</option>
        <option value="active">Active</option>
        <option value="completed">Completed</option>
    </select>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-relationships-list-{{ $record->id }}">{{ $record->name }}</li>
        @endforeach
    </ul>
</div>
