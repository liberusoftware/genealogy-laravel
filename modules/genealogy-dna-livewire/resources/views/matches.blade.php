<div>
    <label for="genealogy-dna-match-search">Search matches</label>
    <input id="genealogy-dna-match-search" type="search" wire:model.live="search">
    <label for="genealogy-dna-match-status">Status</label>
    <select id="genealogy-dna-match-status" wire:model.live="status">
        <option value="">All</option>
        @foreach (\Liberu\Genealogy\Dna\Models\DnaMatch::STATUSES as $statusOption)
            <option value="{{ $statusOption }}">{{ ucfirst($statusOption) }}</option>
        @endforeach
    </select>
    <label><input type="checkbox" wire:model.live="includePrivate"> Include private matches</label>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-dna-match-{{ $record->id }}">
                {{ $record->display_name ?: $record->external_id }}
                <span>{{ $record->confidence }}%</span>
            </li>
        @endforeach
    </ul>
</div>
