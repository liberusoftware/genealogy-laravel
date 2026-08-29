<div>
    <label for="genealogy-timeline-list-status">Status</label>
    <select id="genealogy-timeline-list-status" wire:model.live="status">
        <option value="">All</option>
        @foreach (\Liberu\Genealogy\Timeline\Models\TimelineEvent::STATUSES as $timelineStatus)
            <option value="{{ $timelineStatus }}">{{ ucfirst($timelineStatus) }}</option>
        @endforeach
    </select>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-timeline-list-{{ $record->id }}">{{ $record->name }}</li>
        @endforeach
    </ul>
</div>
