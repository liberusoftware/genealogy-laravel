<div>
    <label for="liberu-revenue-and-care-orchestration-list-status">Status</label>
    <select id="liberu-revenue-and-care-orchestration-list-status" wire:model.live="status">
        <option value="">All</option>
        <option value="draft">Draft</option>
        <option value="active">Active</option>
        <option value="completed">Completed</option>
    </select>
    <ul>
        @foreach ($records as $record)
            <li wire:key="liberu-revenue-and-care-orchestration-list-{{ $record->id }}">{{ $record->name }}</li>
        @endforeach
    </ul>
</div>
