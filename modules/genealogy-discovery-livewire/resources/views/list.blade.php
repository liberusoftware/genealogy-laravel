<div>
    <button type="button" wire:click="scanDuplicates">Scan duplicates</button>
    @if ($scanReport)
        <p role="status">Scanned {{ $scanReport['scanned'] }} candidates; created {{ $scanReport['created'] }} new matches.</p>
    @endif
    <label for="genealogy-discovery-list-status">Status</label>
    <select id="genealogy-discovery-list-status" wire:model.live="status">
        <option value="">All</option>
        <option value="draft">Draft</option>
        <option value="active">Active</option>
        <option value="completed">Completed</option>
    </select>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-discovery-list-{{ $record->id }}">{{ $record->name }}</li>
        @endforeach
    </ul>
</div>
