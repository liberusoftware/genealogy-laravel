<div>
    <label for="genealogy-collaboration-proposal-search">Search proposals</label>
    <input id="genealogy-collaboration-proposal-search" type="search" wire:model.live="search">
    <label for="genealogy-collaboration-proposal-status">Status</label>
    <select id="genealogy-collaboration-proposal-status" wire:model.live="status">
        <option value="">All</option>
        <option value="proposed">Proposed</option>
        <option value="in_review">In review</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
    </select>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-collaboration-proposal-{{ $record->id }}">
                {{ $record->title }} <span>{{ $record->status }}</span>
            </li>
        @endforeach
    </ul>
</div>
