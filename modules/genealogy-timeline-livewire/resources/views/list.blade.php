<div>
    <label for="genealogy-timeline-list-status">Status</label>
    <select id="genealogy-timeline-list-status" wire:model.live="status">
        <option value="">All</option>
        <option value="draft">Draft</option>
        <option value="active">Active</option>
        <option value="completed">Completed</option>
    </select>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-timeline-list-{{ $record->id }}">{{ $record->name }}</li>
        @endforeach
    </ul>
</div>
