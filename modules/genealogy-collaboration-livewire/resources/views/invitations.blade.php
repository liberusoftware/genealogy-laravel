<div>
    <label for="genealogy-collaboration-invitation-status">Invitation status</label>
    <select id="genealogy-collaboration-invitation-status" wire:model.live="status">
        <option value="">All</option><option value="pending">Pending</option><option value="accepted">Accepted</option><option value="revoked">Revoked</option>
    </select>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-collaboration-invitation-{{ $record->id }}">{{ $record->email }} <span>{{ $record->role }} ({{ $record->status }})</span></li>
        @endforeach
    </ul>
</div>
