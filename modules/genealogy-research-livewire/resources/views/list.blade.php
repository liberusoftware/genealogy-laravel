<div>
    <label for="genealogy-research-list-status">Status</label>
    <select id="genealogy-research-list-status" wire:model.live="status">
        <option value="">All</option>
        @foreach (\Liberu\Genealogy\Research\Models\ResearchProject::STATUSES as $projectStatus)
            <option value="{{ $projectStatus }}">{{ ucfirst($projectStatus) }}</option>
        @endforeach
    </select>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-research-list-{{ $record->id }}">{{ $record->name }}</li>
        @endforeach
    </ul>
</div>
